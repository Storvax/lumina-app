<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        // CORREÇÃO: Adicionei 'reactions' e 'comments' ao with()
        // Isto carrega tudo de uma vez e garante que ->reactions nunca é null
        $query = Post::with(['user', 'reactions', 'comments'])->latest();

        if ($request->has('tag') && $request->tag != 'all') {
            $query->where('tag', $request->tag);
        }

        $posts = $query->paginate(20);

        if ($request->ajax()) {
            return view('forum.partials.posts', compact('posts'))->render();
        }

        return view('forum.index', compact('posts'));
    }

    public function show(Post $post)
    {
        // Carregar autor e comentários
        $post->load(['user', 'comments.user']);

        // Posts Relacionados: Mesma tag, exceto o próprio post
        $relatedPosts = Post::where('tag', $post->tag)
                            ->where('id', '!=', $post->id)
                            ->latest()
                            ->take(3)
                            ->get();

        return view('forum.show', compact('post', 'relatedPosts'));
    }

    public function store(Request $request)
    {
        // (O teu código de store mantém-se igual)
        $validated = $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:1000',
            'tag' => 'required|in:hope,vent,anxiety',
            'is_sensitive' => 'sometimes|accepted'
        ]);

        Post::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tag' => $validated['tag'],
            'is_sensitive' => $request->has('is_sensitive'),
        ]);

        return response()->json(['message' => 'Post criado!']);
    }

    public function react(Request $request, Post $post)
    {
        // Valida se o tipo é válido
        $request->validate(['type' => 'required|in:hug,candle,ear']);
        $user = Auth::user();

        // Verifica se este user já reagiu a este post
        $existingReaction = PostReaction::where('user_id', $user->id)
                                        ->where('post_id', $post->id)
                                        ->first();

        if ($existingReaction) {
            // Se já reagiu...
            if ($existingReaction->type == $request->type) {
                // ...com o mesmo ícone: remove (toggle off)
                $existingReaction->delete();
                $action = 'removed';
            } else {
                // ...com ícone diferente: atualiza (troca de Abraço para Vela, por exemplo)
                $existingReaction->update(['type' => $request->type]);
                $action = 'updated';
            }
        } else {
            // Se ainda não reagiu, cria nova reação
            PostReaction::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'type' => $request->type
            ]);
            $action = 'added';
        }

        // Retorna as contagens atualizadas para o Frontend
        return response()->json([
            'action' => $action,
            'counts' => [
                'hug' => $post->reactions()->where('type', 'hug')->count(),
                'candle' => $post->reactions()->where('type', 'candle')->count(),
                'ear' => $post->reactions()->where('type', 'ear')->count(),
            ]
        ]);
    }
    // Novo: Guardar Comentário
    public function comment(Request $request, Post $post)
    {
        $request->validate(['body' => 'required|max:300']);
        
        $post->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body
        ]);

        return back(); // Volta para a página do post
    }

    public function destroy(Post $post)
    {
        // Verifica se é moderador OU se é o dono do post
        if (!auth()->user()->isModerator() && auth()->id() !== $post->user_id) {
            abort(403);
        }

        $post->delete();

        return back()->with('status', 'Post removido com sucesso.');
    }
}