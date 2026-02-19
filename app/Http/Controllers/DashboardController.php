<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GamificationService;

class DashboardController extends Controller
{
    public function index(GamificationService $gamification)
    {
        $user = Auth::user();

        // 1. Garante que o utilizador tem as missões de hoje atribuídas
        $gamification->assignDailyMissions($user);

        // 2. Vai buscar as missões de hoje para enviar para a View
        $dailyMissions = $user->missions()
            ->wherePivot('assigned_date', now()->toDateString())
            ->get();

        return view('dashboard', compact('dailyMissions'));
    }
}