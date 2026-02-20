<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // --- ATUALIZAÇÃO DO STREAK DE LOGIN ---
        $user = Auth::user();
        $today = now()->startOfDay();
        $lastActivity = $user->last_activity_at ? \Carbon\Carbon::parse($user->last_activity_at)->startOfDay() : null;

        if (!$lastActivity || $lastActivity->lessThan($today)) {
            // Se a última atividade foi exatamente ontem
            if ($lastActivity && $lastActivity->equalTo($today->copy()->subDay())) {
                $user->increment('current_streak');
            } 
            // Se passou mais de um dia, ou é a primeira vez
            elseif (!$lastActivity || $lastActivity->lessThan($today->copy()->subDay())) {
                $user->current_streak = 1;
            }
            
            // Atualiza a data de última atividade
            $user->last_activity_at = now();
            $user->save();
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
