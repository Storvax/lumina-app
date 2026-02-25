<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\PostReaction;
use App\Models\Comment;
use App\Models\Report;
use App\Models\ModerationLog;
use Illuminate\Support\Facades\DB;
use App\Notifications\ForumInteraction;
use App\Services\GamificationService;
use App\Services\CBTAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $posts = $query->paginate(20)->appends($request->query());

        // BUG CORRIGIDO: Se for um pedido AJAX (filtros ou scroll infinito), 
        // devolvemos APENAS a grelha de cartões, sem a Navbar nem o Layout.
        // Adicionámos os headers `X-Requested-With` no Axios para garantir que o Laravel deteta o AJAX.
        if ($request->ajax() || $request->wantsJson()) {
            return view('forum.partials.posts', compact('posts'))->render();
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
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:1000',
            'tag' => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted'
        ]);

        $fullText = $validated['title'] . ' ' . $validated['content'];
        $analysis = $this->nlpService->analyzeForumPost($fullText);

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
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

        // updateOrInsert para lidar silenciosamente com check-ins repetidos
        DB::table('post_checkins')->updateOrInsert(
            ['post_id' => $post->id, 'user_id' => Auth::id()],
            ['emotion' => $request->emotion, 'created_at' => now()]
        );

        $response = ['status' => 'ok'];

        // Se o leitor se sentiu triste, sugerimos a Zona Calma em vez de deixá-lo sem apoio
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
}