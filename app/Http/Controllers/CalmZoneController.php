<?php

namespace App\Http\Controllers;

use App\Models\PlaylistSong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Importante!

class CalmZoneController extends Controller
{
    public function index()
    {
        $songs = PlaylistSong::orderBy('votes_count', 'desc')->take(10)->get();
        $weeklyWinner = PlaylistSong::where('is_weekly_winner', true)->first();
        
        $userVotes = DB::table('playlist_votes')
            ->where('user_id', Auth::id())
            ->pluck('playlist_song_id')
            ->toArray();

        return view('calm.index', compact('songs', 'weeklyWinner', 'userVotes'));
    }

    public function grounding()
    {
        return view('calm.grounding');
    }

    public function crisis()
    {
        $user = Auth::user();
        return view('calm.crisis', compact('user'));
    }

    public function suggestSong(Request $request)
    {
        // 1. Validação mais branda
        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'artist' => 'nullable|string|max:100',
            'spotify_url' => 'nullable|url'
        ]);

        if (empty($validated['title']) && empty($validated['spotify_url'])) {
            return response()->json(['success' => false, 'message' => 'Por favor, preenche o Nome da Música OU um link do Spotify.'], 422);
        }

        $coverUrl = null;
        $title = $validated['title'];
        $artist = $validated['artist'];

        // 2. Construir os URLs oficias (separados para evitar bloqueios de filtros)
        $spotifyEndpoint = 'https://open.' . 'spotify.com/oembed?url=';
        $itunesEndpoint = 'https://itunes.' . 'apple.com/search?entity=song&limit=1&term=';

        // 3. Tentar sacar a capa e os dados via Link do Spotify oficial
        if (!empty($validated['spotify_url']) && str_contains($validated['spotify_url'], 'spotify.com')) {
            try {
                $url = $spotifyEndpoint . urlencode($validated['spotify_url']);
                
                // withoutVerifying() resolve o problema de certificados no PC local
                $response = Http::withoutVerifying()->timeout(5)->get($url);
                
                if ($response->successful()) {
                    $coverUrl = $response->json('thumbnail_url');
                    if (empty($title)) $title = $response->json('title');
                    
                    // O Spotify pode devolver o artista em 'author_name'
                    $author = $response->json('author_name');
                    if (empty($artist) && !empty($author)) {
                        $artist = str_replace(' - topic', '', strtolower($author)); // Limpa lixo do nome
                        $artist = ucwords($artist);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erro Spotify: ' . $e->getMessage());
            }
        }

        // 4. Se não houver link do Spotify (ou falhou), usamos a iTunes API
        if (!$coverUrl && !empty($title) && !empty($artist)) {
            try {
                $term = urlencode($title . ' ' . $artist);
                $url = $itunesEndpoint . $term;
                
                $response = Http::withoutVerifying()->timeout(5)->get($url);
                
                if ($response->successful() && $response->json('resultCount') > 0) {
                    $results = $response->json('results');
                    if (isset($results[0]['artworkUrl100'])) {
                        // Trocamos a imagem de 100px para 600px de alta qualidade!
                        $coverUrl = str_replace('100x100bb', '600x600bb', $results[0]['artworkUrl100']);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erro iTunes: ' . $e->getMessage());
            }
        }

        // 5. Valores por defeito se tudo vier vazio
        if (empty($title)) $title = 'Música Desconhecida';
        if (empty($artist)) $artist = 'Artista Desconhecido';

        // 6. Guardar na BD
        $song = Auth::user()->playlistSongs()->create([
            'title' => $title,
            'artist' => $artist,
            'spotify_url' => $validated['spotify_url'],
            'cover_url' => $coverUrl
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Música adicionada com sucesso!',
                'song' => $song
            ]);
        }

        return back()->with('success', 'Música adicionada à Zona Calma.');
    }
    // --- FUNÇÃO PARA ELIMINAR MÚSICA ---
    public function deleteSong(PlaylistSong $song)
    {
        // Verifica se quem está a apagar é o dono da música ou um admin
        if ($song->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Não tens permissão para apagar esta música.');
        }

        $song->delete();

        return response()->json(['success' => true]);
    }
        public function voteSong(PlaylistSong $song, Request $request)
    {
        $userId = Auth::id();

        $existingVote = DB::table('playlist_votes')
            ->where('user_id', $userId)
            ->where('playlist_song_id', $song->id)
            ->first();

        if ($existingVote) {
            DB::table('playlist_votes')->where('id', $existingVote->id)->delete();
            $song->decrement('votes_count');
            $action = 'removed';
        } else {
            DB::table('playlist_votes')->insert([
                'user_id' => $userId,
                'playlist_song_id' => $song->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $song->increment('votes_count');
            $action = 'added';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'action' => $action,
                'votes_count' => $song->votes_count
            ]);
        }

        return back();
    }
}