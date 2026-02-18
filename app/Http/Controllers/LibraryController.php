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
            'type' => 'required|in:book,podcast,video,article',
            'url' => 'required|url',
        ]);
        
        $resource = Resource::create([
            ...$validated,
            'user_id' => Auth::id(),
            'is_approved' => Auth::user()->isModerator(), // Auto-aprova se for mod
        ]);
        
        return back()->with('status', 'Recurso sugerido com sucesso!');
    }
}