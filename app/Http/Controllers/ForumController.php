<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\PostReaction;
use App\Models\Comment;
use App\Models\Report;
use App\Models\ModerationLog; 
use App\Notifications\ForumInteraction;
use App\Services\GamificationService; // <--- Importado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class ForumController extends Controller
{
    protected $gamification;

    // Injeção do Serviço de Gamificação
    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    // --- FUNÇÃO AUXILIAR: AUTO-TAGGING DE CONTEÚDO SENSÍVEL ---
    private function detectSensitiveContent($text)
    {
        $triggers = ['suicidio', 'suicídio', 'morte', 'sangue', 'cortar', 'abuso', 'violencia', 'matar', 'morrer'];
        foreach ($triggers as $word) {
            if (Str::contains(Str::lower($text), $word)) return true;
        }
        return false;
    }

    // --- LISTAGEM (MURAL) ---
    public function index(Request $request)
    {
        $query = Post::with(['user', 'reactions', 'comments'])
            ->orderBy('is_pinned', 'desc') 
            ->latest(); 

        // 1. Filtro de Pesquisa
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // 2. Filtro de Tags (Emoções)
        if ($request->has('tag') && $request->tag != 'all') {
            $query->where('tag', $request->tag);
        }

        $posts = $query->paginate(20)->appends($request->query());

        if ($request->ajax()) {
            return view('forum.partials.posts', compact('posts'))->render();
        }

        return view('forum.index', compact('posts'));
    }

    // --- VER POST INDIVIDUAL ---
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
                            ->latest()->take(3)->get();

        return view('forum.show', compact('post', 'relatedPosts'));
    }

    // --- CRIAR POST ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:1000',
            'tag' => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted'
        ]);

        $autoSensitive = $this->detectSensitiveContent($validated['title'] . ' ' . $validated['content']);

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tag' => $validated['tag'],
            'is_sensitive' => $request->has('is_sensitive') || $autoSensitive,
        ]);

        // --- GAMIFICAÇÃO: Criar Tópico ---
        $this->gamification->trackAction(Auth::user(), 'first_post');

        return response()->json(['message' => 'Post criado!']);
    }

    // --- ATUALIZAR POST ---
    public function update(Request $request, Post $post)
    {
        if (Auth::id() !== $post->user_id) abort(403);

        $validated = $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:1000',
            'tag' => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted'
        ]);

        $autoSensitive = $this->detectSensitiveContent($validated['title'] . ' ' . $validated['content']);

        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tag' => $validated['tag'],
            'is_sensitive' => $request->has('is_sensitive') || $autoSensitive,
        ]);

        return response()->json(['message' => 'Post atualizado!']);
    }

    // --- APAGAR POST ---
    public function destroy(Post $post)
    {
        if (!Auth::user()->isModerator() && Auth::id() !== $post->user_id) {
            abort(403, 'Sem permissão.');
        }

        if (Auth::user()->isModerator() && Auth::id() !== $post->user_id) {
            ModerationLog::create([
                'moderator_id' => Auth::id(),
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

    // --- REAGIR A POST ---
    public function react(Request $request, Post $post)
    {
        if ($post->is_locked) return response()->json(['error' => 'Locked'], 403);

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

            // --- GAMIFICAÇÃO: Reagir a Post ---
            // Só conta se for uma nova reação (não updates) e se não for ao próprio post
            if ($post->user_id !== $user->id) {
                $this->gamification->trackAction($user, 'reaction');
                $post->user->notify(new ForumInteraction($post, $user, 'reaction'));
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

    // --- COMENTAR E RESPONDER ---
    public function comment(Request $request, Post $post)
    {
        if ($post->is_locked) return back()->with('error', 'Trancado.');

        $request->validate([
            'body' => 'required|max:500',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'parent_id' => $request->parent_id
        ]);

        // --- GAMIFICAÇÃO: Comentar/Ajudar ---
        $this->gamification->trackAction(Auth::user(), 'reply');

        // --- NOTIFICAÇÕES ---
        $recipients = collect();

        // 1. Autor do Post
        if ($post->user_id !== Auth::id()) {
            $recipients->push($post->user);
        }

        // 2. Subscritores
        $subscribers = $post->subscribers()->where('user_id', '!=', Auth::id())->get();
        $recipients = $recipients->merge($subscribers);

        // 3. Autor do Comentário Pai
        if ($request->parent_id) {
            $parent = \App\Models\Comment::find($request->parent_id);
            if ($parent && $parent->user_id !== Auth::id()) {
                $recipients->push($parent->user);
            }
        }

        $uniqueRecipients = $recipients->unique('id');
        
        if ($uniqueRecipients->count() > 0) {
            Notification::send(
                $uniqueRecipients, 
                new ForumInteraction($post, Auth::user(), 'comment')
            );
        }

        return back();
    }

    // --- MODERAÇÃO: FIXAR (PIN) ---
    public function togglePin(Post $post)
    {
        if (!Auth::user()->isModerator()) abort(403);
        $post->update(['is_pinned' => !$post->is_pinned]);
        return back();
    }

    // --- MODERAÇÃO: TRANCAR (LOCK) ---
    public function toggleLock(Post $post)
    {
        if (!Auth::user()->isModerator()) abort(403);
        $post->update(['is_locked' => !$post->is_locked]);
        return back();
    }

    // --- MODERAÇÃO: SHADOWBAN ---
    public function shadowbanUser(Request $request, User $user)
    {
        if (!Auth::user()->isModerator()) abort(403);
        
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

    // --- SEGURANÇA: REPORTAR ---
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

    // --- UTILIDADE: GUARDAR POST ---
    public function toggleSave(Post $post)
    {
        $user = Auth::user();
        
        if ($user->savedPosts()->where('post_id', $post->id)->exists()) {
            $user->savedPosts()->detach($post->id);
            $message = 'Post removido dos guardados.';
            $saved = false;
        } else {
            $user->savedPosts()->attach($post->id);
            $message = 'Post guardado no teu perfil.';
            $saved = true;
        }

        return response()->json(['message' => $message, 'saved' => $saved]);
    }

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

    // --- NOVA: MARCAR COMO ÚTIL ---
    public function markHelpful(Comment $comment)
    {
        if (Auth::id() !== $comment->post->user_id) abort(403);
        $comment->update(['is_helpful' => !$comment->is_helpful]);
        return back();
    }

    // --- NOVO: SUBSCREVER POST ---
    public function toggleSubscription(Post $post)
    {
        $user = Auth::user();
        $subscription = $post->subscribers()->toggle($user->id);
        $subscribed = count($subscription['attached']) > 0;
        
        return response()->json([
            'subscribed' => $subscribed,
            'message' => $subscribed ? 'Notificações ativadas para este post.' : 'Notificações desativadas.'
        ]);
    }
}