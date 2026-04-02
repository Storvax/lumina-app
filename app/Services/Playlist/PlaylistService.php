<?php

declare(strict_types=1);

namespace App\Services\Playlist;

use App\Models\PlaylistSong;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlaylistService
{
    /**
     * Cria uma sugestão de música com resolução automática de capa.
     * Tenta Spotify oEmbed primeiro, iTunes Search como fallback.
     * Se ambos falharem, persiste a música sem capa.
     */
    public function suggest(User $user, array $data): PlaylistSong
    {
        $title    = $data['title'] ?? null;
        $artist   = $data['artist'] ?? null;
        $spotifyUrl = $data['spotify_url'] ?? null;
        $coverUrl = null;

        if ($spotifyUrl && str_contains($spotifyUrl, 'spotify.com')) {
            [$coverUrl, $title, $artist] = $this->resolveSpotifyMetadata($spotifyUrl, $title, $artist);
        }

        if (!$coverUrl && $title && $artist) {
            $coverUrl = $this->resolveItunesCover($title, $artist);
        }

        return $user->playlistSongs()->create([
            'title'       => $title  ?: 'Música Desconhecida',
            'artist'      => $artist ?: 'Artista Desconhecido',
            'spotify_url' => $spotifyUrl,
            'cover_url'   => $coverUrl,
        ]);
    }

    /**
     * Alterna o voto de um utilizador numa música.
     * Devolve array com ação executada e contagem atual de votos.
     *
     * @return array{action: string, votes_count: int}
     */
    public function toggleVote(User $user, PlaylistSong $song): array
    {
        $existing = DB::table('playlist_votes')
            ->where('user_id', $user->id)
            ->where('playlist_song_id', $song->id)
            ->first();

        if ($existing) {
            DB::table('playlist_votes')->where('id', $existing->id)->delete();
            $song->decrement('votes_count');
            return ['action' => 'removed', 'votes_count' => $song->votes_count];
        }

        DB::table('playlist_votes')->insert([
            'user_id'          => $user->id,
            'playlist_song_id' => $song->id,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $song->increment('votes_count');

        return ['action' => 'added', 'votes_count' => $song->votes_count];
    }

    /**
     * Obtém metadados via Spotify oEmbed.
     * Devolve [$coverUrl, $title, $artist] — null onde não disponível.
     *
     * @return array{string|null, string|null, string|null}
     */
    private function resolveSpotifyMetadata(string $spotifyUrl, ?string $title, ?string $artist): array
    {
        try {
            $res = Http::withoutVerifying()->timeout(5)
                ->get('https://open.spotify.com/oembed?url=' . urlencode($spotifyUrl));

            if ($res->successful()) {
                $coverUrl = $res->json('thumbnail_url');
                if (empty($title)) $title = $res->json('title');

                $author = $res->json('author_name');
                if (empty($artist) && $author) {
                    $artist = ucwords(str_replace(' - topic', '', strtolower($author)));
                }

                return [$coverUrl, $title, $artist];
            }
        } catch (\Exception $e) {
            Log::warning('Spotify oEmbed falhou', ['error' => $e->getMessage()]);
        }

        return [null, $title, $artist];
    }

    /**
     * Obtém capa via iTunes Search API como fallback.
     */
    private function resolveItunesCover(string $title, string $artist): ?string
    {
        try {
            $res = Http::withoutVerifying()->timeout(5)
                ->get('https://itunes.apple.com/search?entity=song&limit=1&term=' . urlencode("{$title} {$artist}"));

            if ($res->successful() && $res->json('resultCount') > 0) {
                $artwork = $res->json('results.0.artworkUrl100');
                if ($artwork) {
                    return str_replace('100x100bb', '600x600bb', $artwork);
                }
            }
        } catch (\Exception $e) {
            Log::warning('iTunes Search falhou', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
