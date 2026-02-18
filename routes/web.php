<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\DailyLogController;

/*
|--------------------------------------------------------------------------
| Rotas Web da Aplicação
|--------------------------------------------------------------------------
| Organização modular para facilitar a manutenção e escalabilidade.
*/

// --- Rotas Públicas ---
Route::view('/', 'welcome');
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

    Route::post('/users/{user}/shadowban', [ForumController::class, 'shadowbanUser'])->name('users.shadowban');
    
    Route::controller(ForumController::class)->prefix('comentarios/{comment}')->name('comments.')->group(function () {
        Route::post('/reagir', 'reactToComment')->name('react');
        Route::post('/util', 'markHelpful')->name('helpful');
    });

    /**
     * Módulo: Chat (A Fogueira)
     */
    Route::controller(ChatController::class)->group(function () {
        Route::get('/sala/{room:slug}', 'show')->name('chat.show');
        
        // API Chat
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::post('/{room}/message', 'send')->name('send');
            Route::patch('/{room}/message/{message}', 'updateMessage')->name('update'); // <--- Nova Rota de Edição
            Route::post('/{room}/read', 'markAsRead')->name('read'); // <--- Nova Rota de Leitura
            
            Route::post('/{room}/message/{message}/react', 'react')->name('react'); // Mantido legacy path se necessário
            Route::delete('/messages/{message}', 'destroyMessage')->name('delete');
            Route::post('/messages/{message}/report', 'reportMessage')->name('report');
            
            // Moderação
            Route::post('/{room}/mute/{targetUser}', 'muteUser')->name('mute');
            Route::post('/{room}/pin', 'pinMessage')->name('pin');
            Route::post('/{room}/follow/{targetUser}', 'togglePresenceAlert')->name('follow');

            // Rota para Modo Crise
            Route::post('/chat/{room}/crisis', [ChatController::class, 'toggleCrisisMode'])->name('chat.crisis');
            Route::post('/preferences/mode', 'toggleViewMode')->name('mode');
        });
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