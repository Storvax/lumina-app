<?php

namespace App\Http\Controllers;

use App\Models\PlaylistSong;
use App\Models\DailyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

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

    public function sounds()
    {
        return view('calm.sounds', ['categories' => config('sound-library.categories')]);
    }

    /**
     * Diário de Combustão — escrita terapêutica com prompts diários.
     */
    public function combustion()
    {
        $user = Auth::user();

        $entries = DailyLog::where('user_id', $user->id)
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

        return view('calm.combustion', compact('entries', 'dailyPrompt'));
    }

    /**
     * Respiração Somática — exercícios guiados de respiração.
     */
    public function breathing()
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

        return view('calm.breathing', compact('techniques'));
    }

    /**
     * Reflexão do Tempo — chat com o "eu do futuro" via IA.
     */
    public function reflection()
    {
        return view('calm.reflection');
    }

    /**
     * Reflexão do Tempo — processar mensagem e obter resposta do "eu do futuro".
     */
    public function reflectionChat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "És o 'eu do futuro' do utilizador — uma versão mais sábia, mais calma e mais compassiva dele/a daqui a 5 anos. Responde com empatia profunda, validação emocional e esperança realista. Nunca dês conselhos clínicos. Usa a segunda pessoa ('tu'). Fala em Português de Portugal. Sê breve (2-4 frases). O nome do utilizador é {$user->name}.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $validated['message'],
                    ],
                ],
                'max_tokens' => 250,
                'temperature' => 0.8,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Não foi possível contactar o teu eu do futuro.'], 502);
        }

        return response()->json(['reply' => $response->json('choices.0.message.content')]);
    }

    /**
     * Cofre de Luz — listar itens guardados do utilizador.
     */
    public function vault()
    {
        $items = Auth::user()->vaultItems()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('calm.vault', compact('items'));
    }

    /**
     * Cofre de Luz — guardar novo item (texto, imagem ou link).
     */
    public function storeVaultItem(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:text,image,link',
            'title' => 'nullable|string|max:100',
            'content' => 'required|string|max:2000',
        ]);

        $item = Auth::user()->vaultItems()->create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return back()->with('success', 'Guardado no teu Cofre de Luz.');
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