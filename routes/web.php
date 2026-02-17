<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\DailyLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página Inicial (Pública)
Route::get('/', function () {
    return view('welcome');
});

// A Fogueira - Lista de Salas (Pública)
Route::get('/fogueira', [RoomController::class, 'index'])->name('rooms.index');

// Dashboard (Opcional, se não usares podes remover ou redirecionar para o perfil)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// --- ROTAS AUTENTICADAS ---
Route::middleware(['auth'])->group(function () {

    // 1. MURAL DA ESPERANÇA
    Route::get('/mural', [ForumController::class, 'index'])->name('forum.index');
    Route::post('/mural/criar', [ForumController::class, 'store'])->name('forum.store');
    Route::get('/mural/{post}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/mural/{post}/reagir', [ForumController::class, 'react'])->name('forum.react');
    Route::post('/mural/{post}/comentar', [ForumController::class, 'comment'])->name('forum.comment');

    // 2. DIÁRIO DE BORDO
    Route::get('/diario', [DailyLogController::class, 'index'])->name('diary.index');
    Route::post('/diario', [DailyLogController::class, 'store'])->name('diary.store');

    Route::get('/coco', [DailyLogController::class, 'index'])->name('library.index');
    
    // (Nota: Removi a rota duplicada 'library.index' que tinhas aqui a apontar para o diário)

    // 3. PERFIL DO UTILIZADOR (Visualização & Funcionalidades)
    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/perfil/energia', [ProfileController::class, 'updateEnergy'])->name('profile.energy');
    Route::post('/perfil/seguranca', [ProfileController::class, 'updateSafetyPlan'])->name('profile.safety');

    // 4. CHAT (Salas)
    Route::get('/sala/{room:slug}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/sala/{room}/send', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/sala/{room}/message/{message}/react', [ChatController::class, 'react'])->name('chat.react');

    // 5. PERFIL (Edição de Conta - Padrão do Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::delete('/forum/{post}', [ForumController::class, 'destroy'])->name('forum.destroy')->middleware('auth');

    Route::delete('/chat/message/{message}', [ChatController::class, 'destroyMessage'])->name('chat.message.destroy')->middleware('auth');
});

require __DIR__.'/auth.php';