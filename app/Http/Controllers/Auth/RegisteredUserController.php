<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        // --- 1. CONSTRUÇÃO DA IDENTIDADE E EXPECTATIVAS ---
        $tags = [];
        
        // Expectativas (Togetherall concept)
        if ($request->expectation === 'support') $tags[] = 'Procura Apoio';
        if ($request->expectation === 'share') $tags[] = 'Quer Partilhar';
        if ($request->expectation === 'listen') $tags[] = 'Bom Ouvinte';
        if ($request->expectation === 'learn') $tags[] = 'Procura Aprender';

        // Estado Emocional
        if ($request->feeling === 'overwhelmed') $tags[] = 'Sobrecarregado(a)';
        if ($request->feeling === 'sad') $tags[] = 'Tristeza';
        if ($request->feeling === 'lonely') $tags[] = 'Solidão';
        if ($request->feeling === 'anxious') $tags[] = 'Ansiedade';

        // Identidade Visual / Aura (Headspace concept) -> Define a Bio inicial!
        $bio = '"A cuidar de mim, um dia de cada vez."'; // Default
        if ($request->aura === 'calm') $bio = 'A procurar paz e a respirar fundo em cada passo. 🍃';
        if ($request->aura === 'hope') $bio = 'Focado(a) em encontrar a luz ao fundo do túnel. ✨';
        if ($request->aura === 'warm') $bio = 'Aqui para dar e receber um abraço virtual. 🫂';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'emotional_tags' => count($tags) > 0 ? $tags : null,
            'bio' => $bio,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // --- 2. DESTINO DINÂMICO (Woebot concept) ---
        $preference = $request->preference;
        
        if ($preference === 'listen') {
            return redirect(route('calm.index', absolute: false)); // Vai direto para a Zona Calma
        } elseif ($preference === 'talk') {
            return redirect(route('rooms.index', absolute: false)); // Vai direto para a Fogueira (Chat)
        } elseif ($preference === 'read_write') {
            return redirect(route('forum.index', absolute: false)); // Vai direto para o Mural
        }

        return redirect(route('dashboard', absolute: false));
    }
}