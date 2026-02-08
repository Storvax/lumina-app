<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\DailyLogController;

Route::middleware(['auth'])->group(function () {
    
    // Ver o Mural
    Route::get('/mural', [ForumController::class, 'index'])->name('forum.index');
    
    // Criar Post (API interna)
    Route::post('/mural/criar', [ForumController::class, 'store'])->name('forum.store');

    
    Route::get('/mural/{post}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/mural/{post}/reagir', [ForumController::class, 'react'])->name('forum.react');
    Route::post('/mural/{post}/comentar', [ForumController::class, 'comment'])->name('forum.comment');

    Route::get('/diario', [DailyLogController::class, 'index'])->name('diary.index');
    Route::post('/diario', [DailyLogController::class, 'store'])->name('diary.store');
    

});

Route::middleware('auth')->group(function () {
    Route::get('/sala/{room:slug}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/sala/{room}/send', [ChatController::class, 'send'])->name('chat.send');
});

Route::get('/fogueira', [RoomController::class, 'index'])->name('rooms.index');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/sala/{room}/message/{message}/react', [ChatController::class, 'react'])->name('chat.react');

require __DIR__.'/auth.php';
