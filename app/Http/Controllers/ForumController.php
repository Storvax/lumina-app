<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\ModerationLog;
use App\Models\Post;
use App\Models\PostCheckin;
use App\Models\Report;
use App\Models\User;
use App\Services\Forum\ForumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Gere o Mural da Esperança (Fórum).
 * A lógica de negócio (NLP, reações, comentários) está em ForumService.
 */
class ForumController extends Controller
{
    public function __construct(private ForumService $forumService) {}

    /**
     * Lista as publicações do mural com suporte a filtros e pesquisa.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Post::with(['user', 'reactions', 'comments'])
            ->orderBy('is_pinned', 'desc')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tag') && $request->tag !== 'all') {
            $query->where('tag', $request->tag);
        }

        $posts = $query->cursorPaginate(20);

        // Para pedidos AJAX (filtros ou scroll infinito), devolvemos HTML + cursor metadata.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'       => view('forum.partials.posts', compact('posts'))->render(),
                'nextCursor' => $posts->nextCursor()?->encode(),
                'hasMore'    => $posts->hasMorePages(),
            ]);
        }

        return view('forum.index', compact('posts'));
    }

    /**
     * Exibe os detalhes de uma publicação específica e os respetivos comentários.
     */
    public function show(Post $post): View
    {
        $post->load(['user', 'comments' => function ($q) {
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
     * Suporta "Whispered Wall": o utilizador pode publicar texto OU áudio,
     * mas o título é sempre obrigatório para indexação e acessibilidade.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'required|max:100',
            'content'      => 'nullable|required_without:audio_file|string|max:1000',
            'tag'          => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted',
            'audio_file'   => 'nullable|required_without:content|file|mimes:webm,mp3,wav,ogg|max:10240',
        ]);

        $audioPath = $request->hasFile('audio_file')
            ? $request->file('audio_file')->store('whispers', 'public')
            : null;

        $this->forumService->processAndCreatePost(Auth::user(), $validated, $audioPath);

        return response()->json(['message' => 'Post criado com sucesso!']);
    }

    /**
     * Atualiza uma publicação e reavalia o risco clínico (NLP).
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        if (Auth::id() !== $post->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'title'        => 'required|max:100',
            'content'      => 'required|max:1000',
            'tag'          => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted',
        ]);

        $this->forumService->processAndUpdatePost($post, $validated);

        return response()->json(['message' => 'Post atualizado com sucesso!']);
    }

    /**
     * Remove uma publicação (pelo autor ou por um moderador).
     */
    public function destroy(Post $post): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isModerator() && $user->id !== $post->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        if ($user->isModerator() && $user->id !== $post->user_id) {
            ModerationLog::create([
                'moderator_id'   => $user->id,
                'target_user_id' => $post->user_id,
                'target_type'    => Post::class,
                'target_id'      => $post->id,
                'action'         => 'delete',
                'reason'         => 'Violação das regras (Apagado pelo Admin)',
            ]);
        }

        $post->delete();

        return response()->json(['message' => 'Post apagado com sucesso.']);
    }

    /**
     * Toggle de reação emocional (hug/candle/ear).
     */
    public function react(Request $request, Post $post): JsonResponse
    {
        if ($post->is_locked) {
            return response()->json(['error' => 'A publicação está trancada.'], 403);
        }

        $request->validate(['type' => 'required|in:hug,candle,ear']);

        return response()->json(
            $this->forumService->toggleReaction(Auth::user(), $post, $request->type)
        );
    }

    /**
     * Adiciona um comentário ou resposta a uma publicação.
     */
    public function comment(Request $request, Post $post): RedirectResponse
    {
        if ($post->is_locked) {
            return back()->with('error', 'A publicação encontra-se trancada.');
        }

        $request->validate([
            'body'      => 'required|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $this->forumService->addComment(Auth::user(), $post, $request->only('body', 'parent_id'));

        return back();
    }

    /**
     * Alterna o estado de afixação de uma publicação (apenas Moderadores).
     */
    public function togglePin(Post $post): RedirectResponse
    {
        if (!Auth::user()->isModerator()) {
            abort(403, 'Acesso não autorizado.');
        }

        $post->update(['is_pinned' => !$post->is_pinned]);

        return back();
    }

    /**
     * Tranca ou destranca uma publicação (apenas Moderadores).
     */
    public function toggleLock(Post $post): RedirectResponse
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
    public function shadowbanUser(Request $request, User $user): JsonResponse
    {
        if (!Auth::user()->isModerator()) {
            abort(403, 'Acesso não autorizado.');
        }

        $user->update(['shadowbanned_until' => now()->addDays(7)]);

        ModerationLog::create([
            'moderator_id'   => Auth::id(),
            'target_user_id' => $user->id,
            'target_type'    => 'user',
            'target_id'      => $user->id,
            'action'         => 'shadowban',
            'reason'         => 'Comportamento tóxico (Automático ou Manual via Mural)',
        ]);

        return response()->json(['message' => 'Utilizador em modo fantasma.']);
    }

    /**
     * Regista uma denúncia para análise por parte da moderação.
     */
    public function report(Request $request, Post $post): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:50']);

        $exists = Report::where('user_id', Auth::id())
            ->where('post_id', $post->id)
            ->exists();

        if (!$exists) {
            Report::create([
                'user_id' => Auth::id(),
                'post_id' => $post->id,
                'reason'  => $request->reason,
            ]);
        }

        return response()->json(['message' => 'Denúncia recebida.']);
    }

    /**
     * Guarda ou remove uma publicação dos favoritos do utilizador.
     */
    public function toggleSave(Post $post): JsonResponse
    {
        $user = Auth::user();

        if ($user->savedPosts()->where('post_id', $post->id)->exists()) {
            $user->savedPosts()->detach($post->id);
            return response()->json(['message' => 'Publicação removida dos guardados.', 'saved' => false]);
        }

        $user->savedPosts()->attach($post->id);

        return response()->json(['message' => 'Publicação guardada no teu perfil.', 'saved' => true]);
    }

    /**
     * Reage a um comentário específico.
     */
    public function reactToComment(Request $request, Comment $comment): JsonResponse
    {
        $request->validate(['type' => 'required|in:hug,heart,muscle']);

        $action = $this->forumService->toggleCommentReaction(Auth::user(), $comment, $request->type);

        return response()->json(['action' => $action]);
    }

    /**
     * Marca um comentário como particularmente útil pelo autor do tópico.
     */
    public function markHelpful(Comment $comment): RedirectResponse
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
    public function postCheckin(Request $request, Post $post): JsonResponse
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
     * Gera um resumo empático via OpenAI, com cache no campo ai_summary
     * para evitar chamadas redundantes à API.
     */
    public function summarize(Post $post): JsonResponse
    {
        if ($post->ai_summary) {
            return response()->json(['summary' => $post->ai_summary]);
        }

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'Resume este desabafo em 3 bullet points curtos e empáticos. Devolve apenas HTML <ul><li>. Valida os sentimentos sem julgar. Responde em Português de Portugal.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $post->content,
                        ],
                    ],
                    'max_tokens'  => 200,
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
    public function toggleSubscription(Post $post): JsonResponse
    {
        $user         = Auth::user();
        $subscription = $post->subscribers()->toggle($user->id);
        $subscribed   = count($subscription['attached']) > 0;

        return response()->json([
            'subscribed' => $subscribed,
            'message'    => $subscribed
                ? 'Notificações ativadas para esta publicação.'
                : 'Notificações desativadas.',
        ]);
    }
}
