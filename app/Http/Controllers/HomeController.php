<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache; // <--- Importante
use Illuminate\Support\Facades\DB;    // <--- Importante
use App\Models\Post;
use App\Models\Room;
use App\Models\DailyLog;

class HomeController extends Controller
{
    public function index()
    {
        // 1. PULSO DA COMUNIDADE (Cache de 5 minutos)
        $communityStats = Cache::remember('community_pulse', 300, function () {
            return [
                // Pessoas nas salas (baseado em visitas nos últimos 15 min)
                'online' => DB::table('room_visits')
                    ->where('updated_at', '>=', now()->subMinutes(15))
                    ->count(),
                
                // Sala mais ativa do dia (com mais mensagens)
                'top_room' => Room::withCount(['messages' => function($q) {
                        $q->whereDate('created_at', now());
                    }])
                    ->orderByDesc('messages_count')
                    ->value('name') ?? 'Geral', // Fallback se não houver mensagens
                
                // Posts criados hoje
                'posts_today' => Post::whereDate('created_at', now())->count()
            ];
        });

        // 2. Dados do Fórum (Mural da Esperança)
        $recentPosts = Post::with('user')
            ->withCount('reactions', 'comments')
            ->latest()
            ->take(3)
            ->get();

        // 3. Personalização para Utilizador Autenticado
        $userMood = null;
        $moodSuggestion = null;

        if (Auth::check()) {
            $lastLog = DailyLog::where('user_id', Auth::id())->latest()->first();

            if ($lastLog) {
                $userMood = $lastLog->mood;
                
                $moodSuggestion = match ($userMood) {
                    'anxious', 'stress' => ['text' => 'A sala de "Respiração" tem estado ativa.', 'link' => '#calma', 'icon' => 'ri-windy-line', 'color' => 'teal'],
                    'sad', 'lonely' => ['text' => 'O "Mural da Esperança" tem mensagens novas.', 'link' => '#forum', 'icon' => 'ri-heart-pulse-line', 'color' => 'rose'],
                    'angry' => ['text' => 'A energia está alta? Descarrega no Diário.', 'link' => route('diary.index'), 'icon' => 'ri-book-open-line', 'color' => 'amber'],
                    'happy', 'excited' => ['text' => 'Partilha essa energia na Fogueira.', 'link' => route('rooms.index'), 'icon' => 'ri-fire-line', 'color' => 'orange'],
                    default => ['text' => 'Regista o teu dia.', 'link' => route('diary.index'), 'icon' => 'ri-pencil-line', 'color' => 'indigo']
                };
            }
        }

        // 4. Recursos da Biblioteca (Top 3 mais votados)
        $featuredResources = \App\Models\Resource::withCount('votes')
            ->where('is_approved', true)
            ->orderByDesc('votes_count')
            ->take(3)
            ->get();

        return view('welcome', compact('recentPosts', 'userMood', 'moodSuggestion', 'communityStats'));
    }
}