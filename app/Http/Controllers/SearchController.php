<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Resource;
use App\Models\Room;
use Illuminate\Http\Request;

/**
 * Pesquisa global que agrega resultados de Posts, Recursos e Salas.
 *
 * A pesquisa só dispara com >= 2 caracteres para evitar queries demasiado
 * amplas. Os filtros emocionais (tag) e de tipo de conteúdo podem ser
 * combinados livremente.
 */
class SearchController extends Controller
{
    private const MIN_QUERY_LENGTH = 2;

    public function index(Request $request)
    {
        $query    = trim($request->input('q', ''));
        $emotion  = $request->input('emotion');
        $type     = $request->input('type');
        $safe     = $request->boolean('safe', true);

        $posts     = collect();
        $resources = collect();
        $rooms     = collect();

        $hasQuery = mb_strlen($query) >= self::MIN_QUERY_LENGTH;

        if ($hasQuery) {
            $term = '%' . $query . '%';

            if (! $type || $type === 'posts') {
                $posts = $this->searchPosts($term, $emotion, $safe);
            }

            if (! $type || $type === 'resources') {
                $resources = $this->searchResources($term);
            }

            if (! $type || $type === 'rooms') {
                $rooms = $this->searchRooms($term);
            }
        }

        $totalResults = $posts->count() + $resources->count() + $rooms->count();

        return view('search.index', compact(
            'query', 'emotion', 'type', 'safe',
            'posts', 'resources', 'rooms', 'totalResults', 'hasQuery'
        ));
    }

    /**
     * Posts do Mural — filtrados por tag emocional e sensibilidade.
     * O ShadowbanScope do model é aplicado automaticamente.
     */
    private function searchPosts(string $term, ?string $emotion, bool $safe)
    {
        $q = Post::where(function ($query) use ($term) {
            $query->where('title', 'LIKE', $term)
                  ->orWhere('content', 'LIKE', $term);
        });

        if ($emotion) {
            $q->where('tag', $emotion);
        }

        if ($safe) {
            $q->where('is_sensitive', false);
        }

        return $q->latest()->limit(12)->get();
    }

    /**
     * Recursos da Biblioteca — apenas aprovados, ordenados por popularidade.
     */
    private function searchResources(string $term)
    {
        return Resource::where('is_approved', true)
            ->where(function ($query) use ($term) {
                $query->where('title', 'LIKE', $term)
                      ->orWhere('author', 'LIKE', $term)
                      ->orWhere('description', 'LIKE', $term);
            })
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->limit(8)
            ->get();
    }

    /**
     * Salas públicas ativas — exclui salas privadas (buddy sessions).
     */
    private function searchRooms(string $term)
    {
        return Room::where('is_active', true)
            ->where('is_private', false)
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', $term)
                      ->orWhere('description', 'LIKE', $term);
            })
            ->limit(6)
            ->get();
    }
}
