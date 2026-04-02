<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\Meditation;
use App\Models\PlaylistSong;
use App\Services\AI\AIReflectionService;
use App\Services\Playlist\PlaylistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CalmZoneController extends Controller
{
    public function __construct(
        private readonly PlaylistService $playlistService,
        private readonly AIReflectionService $reflectionService,
    ) {}

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

    /**
     * Secção de meditações guiadas e mindfulness agrupadas por categoria.
     * Conteúdo gerido pelo administrador via Filament.
     */
    public function meditations(): View
    {
        // Agrupamento em PHP após uma única query — evita N+1 com groupBy em Eloquent.
        $allMeditations = Meditation::active()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $categories = $allMeditations->groupBy('category');

        return view('calm.meditations', compact('categories'));
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
     * Diário de combustão: escrita terapêutica com prompts rotativos diários.
     * Prompts definidos em config/burn-prompts.php para facilitar curadoria.
     */
    public function burn(): View
    {
        $entries = DailyLog::where('user_id', Auth::id())
            ->whereNotNull('note')
            ->orderBy('log_date', 'desc')
            ->take(7)
            ->get();

        $prompts = config('burn-prompts');
        $dailyPrompt = $prompts[now()->dayOfYear % count($prompts)];

        return view('calm.burn', compact('entries', 'dailyPrompt'));
    }

    /**
     * Respiração somática: parâmetros das técnicas para a animação do timer no frontend.
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
     * Envia mensagem ao "eu do futuro" via OpenAI com suporte a contexto multi-turno.
     * Lógica isolada no AIReflectionService para separação de responsabilidades.
     */
    public function sendReflection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:1000',
        ]);

        try {
            $reply = $this->reflectionService->reply(
                Auth::user(),
                $validated['message'],
                $validated['history'] ?? [],
            );

            return response()->json(['reply' => $reply]);
        } catch (\Illuminate\Http\Client\ConnectionException) {
            return response()->json(['error' => 'Serviço temporariamente indisponível.'], 503);
        } catch (\RuntimeException) {
            return response()->json(['error' => 'Não foi possível contactar o teu eu do futuro.'], 502);
        }
    }

    /**
     * Lista os itens do cofre do utilizador autenticado (âncoras emocionais pessoais).
     * Variável $lights para corresponder à metáfora "luzes" usada no frontend.
     */
    public function vault(): View
    {
        $lights = Auth::user()->vaultItems()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('calm.vault', compact('lights'));
    }

    /**
     * Guarda um novo item no cofre (uma "luz" — apenas texto).
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
     * Sugere uma música para a playlist comunitária.
     * Delega resolução de capa e criação ao PlaylistService.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function suggestSong(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'artist' => 'nullable|string|max:100',
            'spotify_url' => 'nullable|url',
        ]);

        if (empty($validated['title']) && empty($validated['spotify_url'])) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, preenche o Nome da Música OU um link do Spotify.',
            ], 422);
        }

        $song = $this->playlistService->suggest(Auth::user(), $validated);

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
     * Alterna o voto numa música da playlist.
     * Cada utilizador só pode votar uma vez por música — lógica isolada no PlaylistService.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function voteSong(PlaylistSong $song, Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->playlistService->toggleVote(Auth::user(), $song);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, ...$result]);
        }

        return back();
    }
}
