<?php

namespace App\Http\Controllers;

use App\Models\WallPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Gestão do mural artístico ("The Wall").
 *
 * Galeria onde os utilizadores partilham imagens/desenhos que
 * representam o seu estado emocional. Todas as imagens requerem
 * aprovação de moderação antes de serem visíveis publicamente.
 */
class WallController extends Controller
{
    public function index(): View
    {
        $posts = WallPost::approved()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(12);

        return view('wall.index', compact('posts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image'   => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'caption' => 'nullable|string|max:100',
        ]);

        $path = $request->file('image')->store('wall', 'public');

        WallPost::create([
            'user_id'    => Auth::id(),
            'image_path' => $path,
            'caption'    => $request->input('caption'),
        ]);

        return back()->with('status', 'wall-post-submitted');
    }
}
