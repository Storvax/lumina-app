<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Resource;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Pesquisa global com PostgreSQL Full-Text Search (ts_rank + índices GIN).
 *
 * Ordena por relevância em vez de data, usando pesos A/B/C por campo.
 * Em SQLite (desenvolvimento local) cai automaticamente para LIKE, mantendo
 * a mesma interface sem requerer PostgreSQL na máquina do developer.
 * A pesquisa só dispara com >= 2 caracteres para evitar queries demasiado amplas.
 */
class SearchController extends Controller
{
    private const MIN_QUERY_LENGTH = 2;

    private bool $usesFts;

    public function __construct()
    {
        $this->usesFts = DB::getDriverName() === 'pgsql';
    }

    public function index(Request $request): View
    {
        $query   = trim($request->input('q', ''));
        $emotion = $request->input('emotion');
        $type    = $request->input('type');

        // Utilizadores não-moderadores não podem desativar o filtro de conteúdo sensível —
        // previne acesso a posts marcados como sensíveis por via do parâmetro de URL.
        $safe = $request->user()?->isModerator()
            ? $request->boolean('safe', true)
            : true;

        $posts     = collect();
        $resources = collect();
        $rooms     = collect();

        $hasQuery = mb_strlen($query) >= self::MIN_QUERY_LENGTH;

        if ($hasQuery) {
            if (! $type || $type === 'posts') {
                $posts = $this->searchPosts($query, $emotion, $safe);
            }

            if (! $type || $type === 'resources') {
                $resources = $this->searchResources($query);
            }

            if (! $type || $type === 'rooms') {
                $rooms = $this->searchRooms($query);
            }
        }

        $totalResults = $posts->count() + $resources->count() + $rooms->count();

        return view('search.index', compact(
            'query', 'emotion', 'type', 'safe',
            'posts', 'resources', 'rooms', 'totalResults', 'hasQuery'
        ));
    }

    /**
     * Posts do Mural ordenados por relevância FTS (título pesa mais que corpo).
     * O ShadowbanScope do model filtra shadowbans automaticamente.
     */
    private function searchPosts(string $query, ?string $emotion, bool $safe)
    {
        if ($this->usesFts) {
            $tsQuery = $this->toTsQuery($query);

            $q = Post::selectRaw("
                    posts.*,
                    ts_rank(
                        setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                        setweight(to_tsvector('portuguese', coalesce(content, '')), 'B'),
                        to_tsquery('portuguese', ?)
                    ) AS relevance
                ", [$tsQuery])
                ->whereRaw("
                    setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                    setweight(to_tsvector('portuguese', coalesce(content, '')),'B')
                    @@ to_tsquery('portuguese', ?)
                ", [$tsQuery]);
        } else {
            $term = '%' . $query . '%';
            $q = Post::where(fn ($b) => $b->where('title', 'LIKE', $term)->orWhere('content', 'LIKE', $term));
        }

        if ($emotion) {
            $q->where('tag', $emotion);
        }

        if ($safe) {
            $q->where('is_sensitive', false);
        }

        return $this->usesFts
            ? $q->orderByDesc('relevance')->limit(12)->get()
            : $q->latest()->limit(12)->get();
    }

    /**
     * Recursos da Biblioteca — relevância FTS com fallback ao votos para empate.
     */
    private function searchResources(string $query)
    {
        if ($this->usesFts) {
            $tsQuery = $this->toTsQuery($query);

            return Resource::where('is_approved', true)
                ->selectRaw("
                    resources.*,
                    ts_rank(
                        setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                        setweight(to_tsvector('portuguese', coalesce(author, '')), 'B') ||
                        setweight(to_tsvector('portuguese', coalesce(description, '')), 'C'),
                        to_tsquery('portuguese', ?)
                    ) AS relevance
                ", [$tsQuery])
                ->whereRaw("
                    setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                    setweight(to_tsvector('portuguese', coalesce(author, '')), 'B') ||
                    setweight(to_tsvector('portuguese', coalesce(description, '')), 'C')
                    @@ to_tsquery('portuguese', ?)
                ", [$tsQuery])
                ->withCount('votes')
                ->orderByDesc('relevance')
                ->orderByDesc('votes_count')
                ->limit(8)
                ->get();
        }

        $term = '%' . $query . '%';

        return Resource::where('is_approved', true)
            ->where(fn ($b) => $b->where('title', 'LIKE', $term)
                ->orWhere('author', 'LIKE', $term)
                ->orWhere('description', 'LIKE', $term))
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->limit(8)
            ->get();
    }

    /**
     * Salas públicas ativas — nome pesa mais que descrição no ranking.
     */
    private function searchRooms(string $query)
    {
        if ($this->usesFts) {
            $tsQuery = $this->toTsQuery($query);

            return Room::where('is_active', true)
                ->where('is_private', false)
                ->selectRaw("
                    rooms.*,
                    ts_rank(
                        setweight(to_tsvector('portuguese', coalesce(name, '')), 'A') ||
                        setweight(to_tsvector('portuguese', coalesce(description, '')), 'B'),
                        to_tsquery('portuguese', ?)
                    ) AS relevance
                ", [$tsQuery])
                ->whereRaw("
                    setweight(to_tsvector('portuguese', coalesce(name, '')), 'A') ||
                    setweight(to_tsvector('portuguese', coalesce(description, '')), 'B')
                    @@ to_tsquery('portuguese', ?)
                ", [$tsQuery])
                ->orderByDesc('relevance')
                ->limit(6)
                ->get();
        }

        $term = '%' . $query . '%';

        return Room::where('is_active', true)
            ->where('is_private', false)
            ->where(fn ($b) => $b->where('name', 'LIKE', $term)->orWhere('description', 'LIKE', $term))
            ->limit(6)
            ->get();
    }

    /**
     * Converte a string de pesquisa para o formato tsquery do PostgreSQL.
     * Cada palavra torna-se um prefixo com :* para suportar pesquisa parcial
     * (ex: "ansi" encontra "ansiedade"). Palavras são ligadas por &.
     */
    private function toTsQuery(string $query): string
    {
        $words = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        $sanitized = array_map(
            fn (string $w) => preg_replace('/[^a-zA-ZÀ-ÿ0-9]/u', '', $w) . ':*',
            $words
        );

        $sanitized = array_filter($sanitized, fn (string $w) => $w !== ':*');

        return implode(' & ', $sanitized) ?: $query;
    }
}
