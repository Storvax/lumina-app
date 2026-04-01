<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\PlaylistSong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CalmZoneController extends Controller
{
    public function index(): View
    {
        $songs = PlaylistSong::orderBy('votes_count', 'desc')->take(10)->get();
        $weeklyWinner = PlaylistSong::where('is_weekly_winner', true)->first();

        $userVotes = DB::table('playlist_votes')
            ->where('user_id', Auth::id())
            ->pluck('playlist_song_id')
            ->toArray();

        return view('calm.index', compact('songs', 'weeklyWinner', 'userVotes'));
    }

    public function grounding(): View
    {
        return view('calm.grounding');
    }

    public function crisis(): View
    {
        return view('calm.crisis', ['user' => Auth::user()]);
    }

    public function sounds(): View
    {
        return view('calm.sounds', [
            'categories' => config('sound-library.categories', []),
        ]);
    }

    /**
     * Combustion diary: therapeutic writing with daily rotating prompts.
     *
     * @return \Illuminate\View\View
     */
    public function burn(): View
    {
        $entries = DailyLog::where('user_id', Auth::id())
            ->whereNotNull('note')
            ->orderBy('log_date', 'desc')
            ->take(7)
            ->get();

        $prompts = [
            'O que gostarias de deixar ir hoje?',
            'Descreve uma emoção que carregas há demasiado tempo.',
            'Se a tua dor tivesse uma cor, qual seria?',
            'Escreve uma carta ao teu eu de há um ano.',
            'O que te impede de descansar verdadeiramente?',
            'Qual foi o momento mais difícil desta semana?',
            'Que pensamento repetitivo gostarias de silenciar?',
            'Descreve o peso que sentes agora, sem filtros.',
            'Se pudesses gritar algo ao mundo, o que seria?',
            'O que significa "estar bem" para ti neste momento?',
            'Escreve sobre algo que nunca disseste a ninguém.',
            'Que parte de ti merece mais compaixão?',
            'O que farias se não tivesses medo?',
            'Descreve um lugar onde te sentes seguro/a.',
            'Qual é a tua maior necessidade emocional agora?',
            'Escreve uma frase que resuma o teu dia.',
            'O que aprendeste sobre ti esta semana?',
            'Descreve um momento de coragem recente.',
            'Que hábito gostarias de transformar?',
            'Escreve sobre algo que te surpreendeu em ti.',
            'O que te dá esperança, mesmo nos dias cinzentos?',
            'Descreve como te sentes fisicamente neste momento.',
            'Que conversa interna precisas de ter contigo?',
            'Se pudesses mudar uma coisa no teu dia, qual seria?',
            'Escreve algo que precisas de ouvir.',
            'Qual é a emoção dominante neste instante?',
            'O que significa autocuidado para ti?',
            'Descreve um pequeno gesto que te fez bem recentemente.',
            'Que limite precisas de estabelecer?',
            'Escreve uma promessa gentil a ti mesmo/a.',
        ];

        $dailyPrompt = $prompts[now()->dayOfYear % count($prompts)];

        return view('calm.burn', compact('entries', 'dailyPrompt'));
    }

    /**
     * Somatic breathing: technique parameters for the frontend timer animation.
     *
     * @return \Illuminate\View\View
     */
    public function breathe(): View
    {
        $techniques = [
            [
                'name' => 'Respiração 4-7-8',
                'description' => 'Inspirar 4s, segurar 7s, expirar 8s. Ativa o sistema parassimpático.',
                'inhale' => 4, 'hold' => 7, 'exhale' => 8, 'cycles' => 4,
            ],
            [
                'name' => 'Respiração Caixa',
                'description' => 'Inspirar 4s, segurar 4s, expirar 4s, segurar 4s. Equilíbrio total.',
                'inhale' => 4, 'hold' => 4, 'exhale' => 4, 'hold_after' => 4, 'cycles' => 6,
            ],
            [
                'name' => 'Expiração Prolongada',
                'description' => 'Inspirar 3s, expirar 6s. Foco no alívio da tensão.',
                'inhale' => 3, 'hold' => 0, 'exhale' => 6, 'cycles' => 8,
            ],
            [
                'name' => 'Respiração Diafragmática',
                'description' => 'Respiração lenta pela barriga. 5s inspirar, 5s expirar.',
                'inhale' => 5, 'hold' => 0, 'exhale' => 5, 'cycles' => 10,
            ],
        ];

        return view('calm.breathe', compact('techniques'));
    }

    public function heartbeat(): View
    {
        return view('calm.heartbeat');
    }

    public function reflection(): View
    {
        return view('calm.reflection');
    }

    /**
     * Sends a reflection message to OpenAI acting as the user's compassionate
     * "future self" using first-person plural ("Nós conseguimos").
     * Supports conversation history for multi-turn context.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendReflection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:1000',
        ]);

        $user = Auth::user();

        // Build message chain: system prompt → prior turns → current message
        $messages = [
            [
                'role' => 'system',
                'content' => "És o 'eu do futuro' do utilizador — uma versão mais sábia, mais calma e mais compassiva dele/a daqui a 5 anos. Usa a primeira pessoa do plural ('Nós conseguimos', 'Nós sabemos'). Responde com empatia profunda, validação emocional e esperança realista. Nunca dês conselhos clínicos. Fala em Português de Portugal. Sê breve (2-4 frases). O nome do utilizador é {$user->name}.",
            ],
        ];

        // Append prior conversation turns for multi-turn context
        if (!empty($validated['history'])) {
            foreach ($validated['history'] as $turn) {
                $messages[] = [
                    'role' => $turn['role'],
                    'content' => $turn['content'],
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $validated['message'],
        ];

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => $messages,
                    'max_tokens' => 250,
                    'temperature' => 0.8,
                ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Não foi possível contactar o teu eu do futuro.'], 502);
            }

            return response()->json(['reply' => $response->json('choices.0.message.content')]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['error' => 'Serviço temporariamente indisponível.'], 503);
        }
    }

    /**
     * Lists the authenticated user's vault items (personal emotional anchors).
     * Variable named $lights to match the frontend's "luzes" metaphor.
     *
     * @return \Illuminate\View\View
     */
    public function vault(): View
    {
        $lights = Auth::user()->vaultItems()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('calm.vault', compact('lights'));
    }

    /**
     * Stores a new vault item (a single "luz" — text content only).
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeVaultItem(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $item = Auth::user()->vaultItems()->create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return back()->with('success', 'Guardado no teu Cofre de Luz.');
    }

    /**
     * Suggests a song for the community playlist.
     * Attempts to fetch cover art from Spotify oEmbed or iTunes Search API.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function suggestSong(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'artist' => 'nullable|string|max:100',
            'spotify_url' => 'nullable|url'
        ]);

        if (empty($validated['title']) && empty($validated['spotify_url'])) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, preenche o Nome da Música OU um link do Spotify.',
            ], 422);
        }

        $coverUrl = null;
        $title = $validated['title'];
        $artist = $validated['artist'];

        // Cover art resolution: Spotify oEmbed first, iTunes Search as fallback
        if (!empty($validated['spotify_url']) && str_contains($validated['spotify_url'], 'spotify.com')) {
            try {
                $response = Http::withoutVerifying()->timeout(5)
                    ->get('https://open.spotify.com/oembed?url=' . urlencode($validated['spotify_url']));

                if ($response->successful()) {
                    $coverUrl = $response->json('thumbnail_url');
                    if (empty($title)) $title = $response->json('title');

                    $author = $response->json('author_name');
                    if (empty($artist) && !empty($author)) {
                        $artist = ucwords(str_replace(' - topic', '', strtolower($author)));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Spotify oEmbed fetch failed', ['error' => $e->getMessage()]);
            }
        }

        if (!$coverUrl && !empty($title) && !empty($artist)) {
            try {
                $response = Http::withoutVerifying()->timeout(5)
                    ->get('https://itunes.apple.com/search?entity=song&limit=1&term=' . urlencode("{$title} {$artist}"));

                if ($response->successful() && $response->json('resultCount') > 0) {
                    $artwork = $response->json('results.0.artworkUrl100');
                    if ($artwork) {
                        $coverUrl = str_replace('100x100bb', '600x600bb', $artwork);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('iTunes Search fetch failed', ['error' => $e->getMessage()]);
            }
        }

        $song = Auth::user()->playlistSongs()->create([
            'title' => $title ?: 'Música Desconhecida',
            'artist' => $artist ?: 'Artista Desconhecido',
            'spotify_url' => $validated['spotify_url'],
            'cover_url' => $coverUrl,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Música adicionada com sucesso!', 'song' => $song]);
        }

        return back()->with('success', 'Música adicionada à Zona Calma.');
    }

    public function deleteSong(PlaylistSong $song): JsonResponse
    {
        if ($song->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Não tens permissão para apagar esta música.');
        }

        $song->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Toggles a vote on a playlist song. Each user can only vote once per song.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function voteSong(PlaylistSong $song, Request $request): JsonResponse|RedirectResponse
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
            return response()->json(['success' => true, 'action' => $action, 'votes_count' => $song->votes_count]);
        }

        return back();
    }
}
