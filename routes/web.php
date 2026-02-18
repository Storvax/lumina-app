<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
/*
|--------------------------------------------------------------------------
| Rotas Web da Aplicação
|--------------------------------------------------------------------------
*/

// --- Rotas Públicas ---

// Homepage com "Smart Welcome" e "Pulso"
Route::get('/', [HomeController::class, 'index'])->name('home');

// Lista de Salas
Route::get('/fogueira', [RoomController::class, 'index'])->name('rooms.index');

// --- Rotas Autenticadas ---
Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('/dashboard', 'dashboard')->name('dashboard');

    /**
     * Módulo: Mural (Fórum)
     */
    Route::controller(ForumController::class)->prefix('mural')->name('forum.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/criar', 'store')->name('store');
        
        Route::prefix('{post}')->group(function () {
            Route::get('/', 'show')->name('show');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
            Route::post('/reagir', 'react')->name('react');
            Route::post('/comentar', 'comment')->name('comment');
            Route::post('/report', 'report')->name('report');
            Route::post('/save', 'toggleSave')->name('save');
            Route::post('/subscrever', 'toggleSubscription')->name('subscribe');
            Route::patch('/pin', 'togglePin')->name('pin');
            Route::patch('/lock', 'toggleLock')->name('lock');
        });
    });

    // Ações de Utilizador no Fórum
    Route::post('/users/{user}/shadowban', [ForumController::class, 'shadowbanUser'])->name('users.shadowban');
    
    // Comentários
    Route::controller(ForumController::class)->prefix('comentarios/{comment}')->name('comments.')->group(function () {
        Route::post('/reagir', 'reactToComment')->name('react');
        Route::post('/util', 'markHelpful')->name('helpful');
    });

    /**
     * Módulo: Chat (A Fogueira)
     */
    Route::controller(ChatController::class)->group(function () {
        Route::get('/sala/{room:slug}', 'show')->name('chat.show');
        
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::post('/{room}/message', 'send')->name('send');
            Route::patch('/{room}/message/{message}', 'updateMessage')->name('update');
            Route::post('/{room}/read', 'markAsRead')->name('read');
            Route::post('/preferences/mode', 'toggleViewMode')->name('mode'); // Preferência UI
            
            Route::post('/{room}/message/{message}/react', 'react')->name('react');
            Route::delete('/messages/{message}', 'destroyMessage')->name('delete');
            Route::post('/messages/{message}/report', 'reportMessage')->name('report');
            
            // Moderação
            Route::post('/{room}/mute/{targetUser}', 'muteUser')->name('mute');
            Route::post('/{room}/pin', 'pinMessage')->name('pin');
            Route::post('/{room}/follow/{targetUser}', 'togglePresenceAlert')->name('follow');
            Route::post('/chat/{room}/crisis', 'toggleCrisisMode')->name('crisis');
        });
    });

    /**
     * Módulo: Biblioteca (Recursos)
     * CORREÇÃO: Usar LibraryController::class
     */
    Route::controller(LibraryController::class)->prefix('biblioteca')->name('library.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/sugerir', 'store')->name('store');
        Route::post('/{resource}/votar', 'toggleVote')->name('vote');
    });

    /**
     * Módulo: Diário
     */
    Route::controller(DailyLogController::class)->prefix('diario')->name('diary.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
    });

    /**
     * Módulo: Perfil
     */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/perfil', 'show')->name('profile.show');
        Route::post('/perfil/energia', 'updateEnergy')->name('profile.energy');
        Route::post('/perfil/seguranca', 'updateSafetyPlan')->name('profile.safety');
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // Utilitários
    Route::post('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['status' => 'success']);
    })->name('notifications.read');
});

require __DIR__.'/auth.php';