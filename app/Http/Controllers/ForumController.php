<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCheckin;
use App\Models\User;
use App\Models\PostReaction;
use App\Models\Comment;
use App\Models\Report;
use App\Models\ModerationLog;
use App\Models\PactPrompt;
use App\Models\PactAnswer;
use Illuminate\Support\Facades\DB;
use App\Notifications\ForumInteraction;
use App\Services\GamificationService;
use App\Services\CBTAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

/**
 * Gere o Mural da Esperança (Fórum).
 * Integra processamento de NLP para deteção de crise e moderação assistida.
 */
class ForumController extends Controller
{
    protected GamificationService $gamification;
    protected CBTAnalysisService $nlpService;

    public function __construct(GamificationService $gamification, CBTAnalysisService $nlpService)
    {
        $this->gamification = $gamification;
        $this->nlpService = $nlpService;
    }

    /**
     * Lista as publicações do mural com suporte a filtros e pesquisa.
     */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'reactions', 'comments'])
            ->orderBy('is_pinned', 'desc') 
            ->latest(); 

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->has('tag') && $request->tag != 'all') {
            $query->where('tag', $request->tag);
        }

        $posts = $query->cursorPaginate(20);

        // Para pedidos AJAX (filtros ou scroll infinito), devolvemos HTML + cursor metadata.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('forum.partials.posts', compact('posts'))->render(),
                'nextCursor' => $posts->nextCursor()?->encode(),
                'hasMore' => $posts->hasMorePages(),
            ]);
        }

        return view('forum.index', compact('posts'));
    }
    
    /**
     * Exibe os detalhes de uma publicação específica e os respetivos comentários.
     */
    public function show(Post $post)
    {
        $post->load(['user', 'comments' => function($q) {
            $q->whereNull('parent_id')
              ->with(['user', 'replies.user', 'reactions', 'replies.reactions'])
              ->latest();
        }]);
        
        $post->loadCount('comments');

        $relatedPosts = Post::where('tag', $post->tag)
                            ->where('id', '!=', $post->id)
                            ->latest()
                            ->take(3)
                            ->get();

        return view('forum.show', compact('post', 'relatedPosts'));
    }

    /**
     * Regista uma nova publicação aplicando a triagem IA multicamada.
     * Suporta upload opcional de áudio (whispers) para desabafos de voz.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:1000',
            'tag' => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted',
            'audio_file' => 'nullable|file|mimes:webm,mp3,wav|max:10240',
        ]);

        $fullText = $validated['title'] . ' ' . $validated['content'];
        $analysis = $this->nlpService->analyzeForumPost($fullText);

        $audioPath = null;
        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('whispers', 'public');
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audio_path' => $audioPath,
            'tag' => $validated['tag'],
            'is_sensitive' => $request->has('is_sensitive') || $analysis['is_sensitive'],
            'risk_level' => $analysis['risk_level'],
            'sentiment' => $analysis['sentiment'],
        ]);

        $this->gamification->trackAction(Auth::user(), 'first_post');

        return response()->json(['message' => 'Post criado com sucesso!']);
    }

    /**
     * Atualiza uma publicação e reavalia o risco clínico (NLP).
     */
    public function update(Request $request, Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:1000',
            'tag' => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted'
        ]);

        $fullText = $validated['title'] . ' ' . $validated['content'];
        $analysis = $this->nlpService->analyzeForumPost($fullText);

        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tag' => $validated['tag'],
            'is_sensitive' => $request->has('is_sensitive') || $analysis['is_sensitive'],
            'risk_level' => $analysis['risk_level'],
            'sentiment' => $analysis['sentiment'],
        ]);

        return response()->json(['message' => 'Post atualizado com sucesso!']);
    }

    /**
     * Remove uma publicação (pelo autor ou por um moderador).
     */
    public function destroy(Post $post)
    {
        $user = Auth::user();

        if (!$user->isModerator() && $user->id !== $post->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        if ($user->isModerator() && $user->id !== $post->user_id) {
            ModerationLog::create([
                'moderator_id' => $user->id,
                'target_user_id' => $post->user_id,
                'target_type' => Post::class,
                'target_id' => $post->id,
                'action' => 'delete',
                'reason' => 'Violação das regras (Apagado pelo Admin)'
            ]);
        }

        $post->delete();
        return response()->json(['message' => 'Post apagado com sucesso.']);
    }

    /**
     * Processa reações emocionais a uma publicação e notifica o autor (respeitando o shadowban).
     */
    public function react(Request $request, Post $post)
    {
        if ($post->is_locked) {
            return response()->json(['error' => 'A publicação está trancada.'], 403);
        }

        $request->validate(['type' => 'required|in:hug,candle,ear']);
        $user = Auth::user();

        $existingReaction = PostReaction::where('user_id', $user->id)
                                        ->where('post_id', $post->id)
                                        ->first();

        if ($existingReaction) {
            if ($existingReaction->type == $request->type) {
                $existingReaction->delete(); 
                $action = 'removed';
            } else {
                $existingReaction->update(['type' => $request->type]); 
                $action = 'updated';
            }
        } else {
            PostReaction::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'type' => $request->type
            ]);
            $action = 'added';

            if ($post->user_id !== $user->id) {
                $this->gamification->trackAction($user, 'reaction');

                // Previne fugas de shadowban (O utilizador bloqueado interage e ninguém vê o alerta)
                if (!$user->isShadowbanned()) {
                    $customMessage = match($request->type) {
                        'hug' => 'Alguém deixou-te um abraço apertado na tua história.',
                        'candle' => 'Alguém acendeu uma vela de esperança por ti.',
                        'ear' => 'Alguém está aqui para te ouvir, em silêncio.',
                        default => 'Alguém interagiu com a tua história.',
                    };

                    $post->user->notify(new ForumInteraction($post, $user, 'reaction', $customMessage));
                }
            }
        }

        return response()->json([
            'action' => $action,
            'counts' => [
                'hug' => $post->reactions()->where('type', 'hug')->count(),
                'candle' => $post->reactions()->where('type', 'candle')->count(),
                'ear' => $post->reactions()->where('type', 'ear')->count(),
            ]
        ]);
    }

    /**
     * Adiciona um comentário ou resposta a uma publicação.
     */
    public function comment(Request $request, Post $post)
    {
        if ($post->is_locked) {
            return back()->with('error', 'A publicação encontra-se trancada.');
        }

        $request->validate([
            'body' => 'required|max:500',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'parent_id' => $request->parent_id
        ]);

        $this->gamification->trackAction(Auth::user(), 'reply');

        $recipients = collect();

        if ($post->user_id !== Auth::id()) {
            $recipients->push($post->user);
        }

        $subscribers = $post->subscribers()->where('user_id', '!=', Auth::id())->get();
        $recipients = $recipients->merge($subscribers);

        if ($request->parent_id) {
            $parent = Comment::find($request->parent_id);
            if ($parent && $parent->user_id !== Auth::id()) {
                $recipients->push($parent->user);
            }
        }

        $uniqueRecipients = $recipients->unique('id');
        
        // Bloqueio de alerta se o remetente for um fantasma
        if ($uniqueRecipients->count() > 0 && !Auth::user()->isShadowbanned()) {
            $customMessage = $request->parent_id 
                ? 'Alguém respondeu diretamente ao teu comentário.' 
                : 'Alguém partilhou palavras de conforto na tua publicação.';

            Notification::send(
                $uniqueRecipients, 
                new ForumInteraction($post, Auth::user(), 'comment', $customMessage)
            );
        }

        return back();
    }

    /**
     * Alterna o estado de afixação de uma publicação (Apenas Moderadores).
     */
    public function togglePin(Post $post)
    {
        if (!Auth::user()->isModerator()) {
            abort(403, 'Acesso não autorizado.');
        }
        $post->update(['is_pinned' => !$post->is_pinned]);
        return back();
    }

    /**
     * Tranca ou destranca uma publicação (Apenas Moderadores).
     */
    public function toggleLock(Post $post)
    {
        if (!Auth::user()->isModerator()) {
            abort(403, 'Acesso não autorizado.');
        }
        $post->update(['is_locked' => !$post->is_locked]);
        return back();
    }

    /**
     * Aplica o estado de "Shadowban" a um utilizador reportado.
     */
    public function shadowbanUser(Request $request, User $user)
    {
        if (!Auth::user()->isModerator()) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $user->update(['shadowbanned_until' => now()->addDays(7)]);

        ModerationLog::create([
            'moderator_id' => Auth::id(),
            'target_user_id' => $user->id,
            'target_type' => 'user',
            'target_id' => $user->id,
            'action' => 'shadowban',
            'reason' => 'Comportamento tóxico (Automático ou Manual via Mural)'
        ]);

        return response()->json(['message' => 'Utilizador em modo fantasma.']);
    }

    /**
     * Regista uma denúncia para análise por parte da moderação.
     */
    public function report(Request $request, Post $post)
    {
        $request->validate(['reason' => 'required|string|max:50']);

        $exists = Report::where('user_id', Auth::id())
                        ->where('post_id', $post->id)
                        ->exists();

        if (!$exists) {
            Report::create([
                'user_id' => Auth::id(),
                'post_id' => $post->id,
                'reason' => $request->reason,
            ]);
        }

        return response()->json(['message' => 'Denúncia recebida.']);
    }

    /**
     * Guarda ou remove uma publicação dos favoritos do utilizador.
     */
    public function toggleSave(Post $post)
    {
        $user = Auth::user();
        
        if ($user->savedPosts()->where('post_id', $post->id)->exists()) {
            $user->savedPosts()->detach($post->id);
            $message = 'Publicação removida dos guardados.';
            $saved = false;
        } else {
            $user->savedPosts()->attach($post->id);
            $message = 'Publicação guardada no teu perfil.';
            $saved = true;
        }

        return response()->json(['message' => $message, 'saved' => $saved]);
    }

    /**
     * Reage a um comentário específico.
     */
    public function reactToComment(Request $request, Comment $comment)
    {
        $request->validate(['type' => 'required|in:hug,heart,muscle']);
        
        $existing = \App\Models\CommentReaction::where('user_id', Auth::id())
            ->where('comment_id', $comment->id)
            ->first();

        if ($existing && $existing->type == $request->type) {
            $existing->delete();
            $action = 'removed';
        } else {
            \App\Models\CommentReaction::updateOrCreate(
                ['user_id' => Auth::id(), 'comment_id' => $comment->id],
                ['type' => $request->type]
            );
            $action = 'added';
        }

        return response()->json(['action' => $action]);
    }

    /**
     * Marca um comentário como particularmente útil pelo autor do tópico.
     */
    public function markHelpful(Comment $comment)
    {
        if (Auth::id() !== $comment->post->user_id) {
            abort(403, 'Acesso não autorizado.');
        }
        $comment->update(['is_helpful' => !$comment->is_helpful]);
        return back();
    }

    /**
     * Regista como o utilizador se sentiu após ler uma publicação sensível.
     * Se reportar tristeza, a resposta inclui uma sugestão para a Zona Calma.
     */
    public function postCheckin(Request $request, Post $post): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'emotion' => 'required|in:empathy,sadness,strength,neutral',
        ]);

        PostCheckin::updateOrCreate(
            ['post_id' => $post->id, 'user_id' => Auth::id()],
            ['emotion' => $request->emotion, 'created_at' => now()]
        );

        $response = ['status' => 'ok'];

        if ($request->emotion === 'sadness') {
            $response['suggestion'] = [
                'message' => 'É normal sentires isso. A Zona Calma tem recursos que podem ajudar.',
                'label'   => 'Ir para a Zona Calma',
                'url'     => route('calm.index'),
            ];
        }

        return response()->json($response);
    }

    /**
     * Generates an empathetic AI-powered summary of a post via OpenAI.
     * Returns cached result if already generated to avoid redundant API calls.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summarize(Post $post)
    {
        if ($post->ai_summary) {
            return response()->json(['summary' => $post->ai_summary]);
        }

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Resume este desabafo em 3 bullet points curtos e empáticos. Devolve apenas HTML <ul><li>. Valida os sentimentos sem julgar. Responde em Português de Portugal.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $post->content,
                        ],
                    ],
                    'max_tokens' => 200,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Não foi possível gerar o resumo.'], 502);
            }

            $summary = $response->json('choices.0.message.content');
            $post->update(['ai_summary' => $summary]);

            return response()->json(['summary' => $summary]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['error' => 'Serviço temporariamente indisponível.'], 503);
        }
    }

    /**
     * Ativa ou desativa a receção de notificações para uma publicação.
     */
    public function toggleSubscription(Post $post)
    {
        $user = Auth::user();
        $subscription = $post->subscribers()->toggle($user->id);
        $subscribed = count($subscription['attached']) > 0;

        return response()->json([
            'subscribed' => $subscribed,
            'message' => $subscribed ? 'Notificações ativadas para esta publicação.' : 'Notificações desativadas.'
        ]);
    }

    /**
     * Displays today's pact prompt with community answers.
     * Uses active_date for deterministic daily rotation.
     *
     * @return \Illuminate\View\View
     */
    public function pact()
    {
        $todayPrompt = PactPrompt::where('active_date', now()->toDateString())->first();

        // Fallback: rotate through all prompts if none is specifically scheduled for today
        if (!$todayPrompt) {
            $prompts = PactPrompt::orderBy('id')->get();
            $todayPrompt = $prompts->isNotEmpty()
                ? $prompts[now()->dayOfYear % $prompts->count()]
                : null;
        }

        $communityAnswers = collect();
        $myAnswer = null;

        if ($todayPrompt) {
            $communityAnswers = PactAnswer::where('pact_prompt_id', $todayPrompt->id)
                ->where('user_id', '!=', Auth::id())
                ->latest()
                ->take(20)
                ->get();

            $myAnswer = PactAnswer::where('user_id', Auth::id())
                ->where('pact_prompt_id', $todayPrompt->id)
                ->whereDate('created_at', now()->toDateString())
                ->first();
        }

        return view('calm.pact', compact('todayPrompt', 'communityAnswers', 'myAnswer'));
    }

    /**
     * Stores the user's answer to today's pact prompt.
     * One answer per user per prompt per day via updateOrCreate on created_at date.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storePact(Request $request)
    {
        $validated = $request->validate([
            'pact_prompt_id' => 'required|exists:pact_prompts,id',
            'answer' => 'required|string|max:2000',
        ]);

        $answer = Auth::user()->pactAnswers()->updateOrCreate(
            [
                'pact_prompt_id' => $validated['pact_prompt_id'],
            ],
            [
                'answer' => $validated['answer'],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'answer' => $answer]);
        }

        return back()->with('success', 'A tua reflexão foi guardada.');
    }
}