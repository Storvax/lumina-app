<?php

declare(strict_types=1);

namespace App\Services\Forum;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use App\Notifications\ForumInteraction;
use App\Services\AI\CBTAnalysisService;
use App\Services\Gamification\GamificationService;
use Illuminate\Support\Facades\Notification;

/**
 * Encapsula a lógica de negócio do Mural da Esperança:
 * criação/atualização de publicações com triagem NLP, toggles de reação
 * com notificações emocionais e publicação de comentários.
 */
class ForumService
{
    public function __construct(
        private CBTAnalysisService $nlpService,
        private GamificationService $gamification,
    ) {}

    /**
     * Analisa o texto via NLP e cria a publicação.
     * Retorna o modelo persistido para que o controller possa devolver resposta imediata.
     */
    public function processAndCreatePost(User $user, array $validated, ?string $audioPath): Post
    {
        $fullText = $validated['title'] . ' ' . ($validated['content'] ?? '');
        $analysis = $this->nlpService->analyzeForumPost($fullText);

        $post = Post::create([
            'user_id'      => $user->id,
            'title'        => $validated['title'],
            'content'      => $validated['content'] ?? null,
            'audio_path'   => $audioPath,
            'tag'          => $validated['tag'],
            'is_sensitive' => ($validated['is_sensitive'] ?? false) || $analysis['is_sensitive'],
            'risk_level'   => $analysis['risk_level'],
            'sentiment'    => $analysis['sentiment'],
        ]);

        $this->gamification->trackAction($user, 'first_post');

        return $post;
    }

    /**
     * Re-analisa o risco clínico e actualiza a publicação.
     */
    public function processAndUpdatePost(Post $post, array $validated): Post
    {
        $fullText = $validated['title'] . ' ' . $validated['content'];
        $analysis = $this->nlpService->analyzeForumPost($fullText);

        $post->update([
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'tag'          => $validated['tag'],
            'is_sensitive' => ($validated['is_sensitive'] ?? false) || $analysis['is_sensitive'],
            'risk_level'   => $analysis['risk_level'],
            'sentiment'    => $analysis['sentiment'],
        ]);

        return $post;
    }

    /**
     * Toggle de reação emocional (hug/candle/ear).
     * Retorna a contagem actualizada de todos os tipos e a ação executada.
     * Notifica o autor apenas em novas reações; utilizadores shadowbanned são silenciados.
     */
    public function toggleReaction(User $user, Post $post, string $type): array
    {
        $existing = PostReaction::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            PostReaction::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'type'    => $type,
            ]);
            $action = 'added';

            if ($post->user_id !== $user->id) {
                $this->gamification->trackAction($user, 'reaction');

                if (!$user->isShadowbanned()) {
                    $customMessage = match ($type) {
                        'hug'    => 'Alguém deixou-te um abraço apertado na tua história.',
                        'candle' => 'Alguém acendeu uma vela de esperança por ti.',
                        'ear'    => 'Alguém está aqui para te ouvir, em silêncio.',
                        default  => 'Alguém interagiu com a tua história.',
                    };
                    $post->user->notify(new ForumInteraction($post, $user, 'reaction', $customMessage));
                }
            }
        }

        return [
            'action' => $action,
            'counts' => [
                'hug'    => $post->reactions()->where('type', 'hug')->count(),
                'candle' => $post->reactions()->where('type', 'candle')->count(),
                'ear'    => $post->reactions()->where('type', 'ear')->count(),
            ],
        ];
    }

    /**
     * Cria o comentário/resposta e envia notificações aos destinatários relevantes.
     * Utilizadores shadowbanned não disparam notificações (invisibilidade total).
     */
    public function addComment(User $user, Post $post, array $data): Comment
    {
        $comment = $post->comments()->create([
            'user_id'   => $user->id,
            'body'      => $data['body'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        $this->gamification->trackAction($user, 'reply');

        $recipients = collect();

        if ($post->user_id !== $user->id) {
            $recipients->push($post->user);
        }

        $subscribers = $post->subscribers()->where('user_id', '!=', $user->id)->get();
        $recipients  = $recipients->merge($subscribers);

        if (!empty($data['parent_id'])) {
            $parent = Comment::find($data['parent_id']);
            if ($parent && $parent->user_id !== $user->id) {
                $recipients->push($parent->user);
            }
        }

        $uniqueRecipients = $recipients->unique('id');

        if ($uniqueRecipients->isNotEmpty() && !$user->isShadowbanned()) {
            $customMessage = !empty($data['parent_id'])
                ? 'Alguém respondeu diretamente ao teu comentário.'
                : 'Alguém partilhou palavras de conforto na tua publicação.';

            Notification::send(
                $uniqueRecipients,
                new ForumInteraction($post, $user, 'comment', $customMessage)
            );
        }

        return $comment;
    }

    /**
     * Toggle de reação a um comentário.
     * Um utilizador só pode ter um tipo de reação por comentário de cada vez.
     */
    public function toggleCommentReaction(User $user, Comment $comment, string $type): string
    {
        $existing = CommentReaction::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existing && $existing->type === $type) {
            $existing->delete();
            return 'removed';
        }

        CommentReaction::updateOrCreate(
            ['user_id' => $user->id, 'comment_id' => $comment->id],
            ['type' => $type]
        );

        return 'added';
    }
}
