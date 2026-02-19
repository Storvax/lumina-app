<?php

namespace App\Http\Controllers;

use App\Models\PlaylistSong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalmZoneController extends Controller
{
    /**
     * Hub principal da Zona Calma.
     */
    public function index()
    {
        $songs = PlaylistSong::orderBy('votes_count', 'desc')->take(10)->get();
        $weeklyWinner = PlaylistSong::where('is_weekly_winner', true)->first();

        return view('calm.index', compact('songs', 'weeklyWinner'));
    }

    /**
     * Exercício interativo de Grounding (5-4-3-2-1).
     */
    public function grounding()
    {
        return view('calm.grounding');
    }

    /**
     * Modo de Crise Pessoal (Imersivo).
     */
    public function crisis()
    {
        $user = Auth::user();
        return view('calm.crisis', compact('user'));
    }

    /**
     * Submeter música para a Playlist Comunitária.
     */
    public function suggestSong(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'artist' => 'required|string|max:100',
            'spotify_url' => 'nullable|url'
        ]);

        Auth::user()->playlistSongs()->create($validated);

        return back()->with('success', 'Música sugerida para a Zona Calma.');
    }

    /**
     * Votar numa música da playlist.
     */
    public function voteSong(PlaylistSong $song)
    {
        $userId = Auth::id();

        $existingVote = DB::table('playlist_votes')
            ->where('user_id', $userId)
            ->where('playlist_song_id', $song->id)
            ->first();

        if ($existingVote) {
            DB::table('playlist_votes')->where('id', $existingVote->id)->delete();
            $song->decrement('votes_count');
        } else {
            DB::table('playlist_votes')->insert([
                'user_id' => $userId,
                'playlist_song_id' => $song->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $song->increment('votes_count');
        }

        return back();
    }
}