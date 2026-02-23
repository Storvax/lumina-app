<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    /**
     * Página principal da Biblioteca (com filtros).
     */
    public function index(Request $request)
    {
        $query = Resource::where('is_approved', true)
            ->withCount('votes');

        // Filtro por Tipo
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Ordenação (Mais votados primeiro por defeito)
        $resources = $query->orderByDesc('votes_count')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('library.index', compact('resources'));
    }

    /**
     * Votar num recurso (Toggle).
     */
    public function toggleVote(Resource $resource)
    {
        $user = Auth::user();
        
        if ($resource->votes()->where('user_id', $user->id)->exists()) {
            $resource->votes()->detach($user->id);
            $status = 'removed';
        } else {
            $resource->votes()->attach($user->id);
            $status = 'added';
        }

        return response()->json([
            'status' => $status,
            'count' => $resource->votes()->count()
        ]);
    }
    
    // Método Store para "Adicionar Recurso" (simplificado)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'type' => 'required|in:book,video,podcast',
        ]);
    
        // O recurso é criado como "is_approved = false" para que os moderadores revejam no Filament
        Resource::create([
            'title' => $validated['title'],
            'url' => $validated['url'],
            'type' => $validated['type'],
            'is_approved' => false, 
            'icon' => match($validated['type']) {
                'book' => 'ri-book-3-line',
                'video' => 'ri-play-circle-line',
                'podcast' => 'ri-mic-line',
                default => 'ri-links-line'
            },
            'color' => match($validated['type']) {
                'book' => 'indigo',
                'video' => 'amber',
                'podcast' => 'rose',
                default => 'slate'
            }
        ]);
    
        return back()->with('success', 'Obrigado! A tua sugestão foi enviada para revisão da equipa.');
    }
}