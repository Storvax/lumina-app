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
use App\Http\Controllers\BuddyController;
use App\Http\Controllers\CalmZoneController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/fogueira', [RoomController::class, 'index'])->name('rooms.index');

Route::middleware(['auth', 'verified'])->group(function () {


    Route::post('/users/{user}/oferecer-apoio', [\App\Http\Controllers\GamificationController::class, 'sendGentleChallenge'])->name('users.challenge');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/perfil/notificacoes', [\App\Http\Controllers\ProfileController::class, 'updateNotificationPrefs'])->name('profile.notifications');

    /*
    |--------------------------------------------------------------------------
    | Fórum (Mural da Esperança)
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Chat (A Fogueira)
    |--------------------------------------------------------------------------
    */
    Route::controller(ChatController::class)->group(function () {
        Route::get('/sala/{room:slug}', 'show')->name('chat.show');
        
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::post('/{room}/message', 'send')->name('send');
            Route::patch('/{room}/message/{message}', 'updateMessage')->name('update');
            Route::post('/{room}/read', 'markAsRead')->name('read');
            Route::post('/preferences/mode', 'toggleViewMode')->name('mode');
            
            Route::post('/{room}/message/{message}/react', 'react')->name('react');
            Route::delete('/messages/{message}', 'destroyMessage')->name('delete');
            Route::post('/messages/{message}/report', 'reportMessage')->name('report');
            
            Route::post('/{room}/mute/{targetUser}', 'muteUser')->name('mute');
            Route::post('/{room}/pin', 'pinMessage')->name('pin');
            Route::post('/{room}/follow/{targetUser}', 'togglePresenceAlert')->name('follow');
            Route::post('/{room}/crisis', 'toggleCrisisMode')->name('crisis');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Biblioteca de Recursos
    |--------------------------------------------------------------------------
    */
    Route::controller(LibraryController::class)->prefix('biblioteca')->name('library.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/sugerir', 'store')->name('store');
        Route::post('/{resource}/votar', 'toggleVote')->name('vote');
    });

    /*
    |--------------------------------------------------------------------------
    | Diário Emocional
    |--------------------------------------------------------------------------
    */
    Route::controller(DailyLogController::class)->prefix('diario')->name('diary.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
    });

    /*
    |--------------------------------------------------------------------------
    | Sistema Buddy (Ouvinte)
    |--------------------------------------------------------------------------
    */
    Route::controller(BuddyController::class)->prefix('ouvinte')->name('buddy.')->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::post('/pedir', 'requestBuddy')->name('request');
        Route::post('/candidatura', 'apply')->name('apply');
        Route::post('/{session}/aceitar', 'acceptSession')->name('accept');
        Route::post('/{session}/escalar', 'escalate')->name('escalate');
        Route::post('/{session}/avaliar', 'evaluate')->name('evaluate');
    });

    /*
    |--------------------------------------------------------------------------
    | Perfil & Configurações de Conta
    |--------------------------------------------------------------------------
    */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/perfil', 'show')->name('profile.show');
        Route::get('/profile/edit', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
        
        // Custom Profile Features
        Route::post('/perfil/energia', 'updateEnergy')->name('profile.energy');
        Route::post('/perfil/seguranca', 'updateSafetyPlan')->name('profile.safety');
        Route::post('/perfil/respirar', 'logBreathing')->name('profile.breathe');
        Route::post('/perfil/tags', 'updateTags')->name('profile.tags');
        Route::post('/perfil/jornada', 'storeMilestone')->name('profile.milestones.store');
        Route::delete('/perfil/jornada/{milestone}', 'destroyMilestone')->name('profile.milestones.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Utilitários
    |--------------------------------------------------------------------------
    */
    Route::post('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['status' => 'success']);
    })->name('notifications.read');

    /*
    |--------------------------------------------------------------------------
    | Privacidade e Segurança
    |--------------------------------------------------------------------------
    */
    Route::controller(PrivacyController::class)->prefix('privacidade')->name('privacy.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/exportar', 'exportData')->name('export');
        Route::post('/hibernar', 'hibernate')->name('hibernate');
    });

    /*
    |--------------------------------------------------------------------------
    | Zona Calma
    |--------------------------------------------------------------------------
    */
    Route::controller(CalmZoneController::class)->prefix('zona-calma')->name('calm.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/grounding', 'grounding')->name('grounding');
        Route::get('/crise', 'crisis')->name('crisis');
        
        Route::post('/playlist/sugerir', 'suggestSong')->name('playlist.suggest');
        Route::post('/playlist/{song}/votar', 'voteSong')->name('playlist.vote');
        Route::delete('/playlist/{song}', 'deleteSong')->name('playlist.delete');
    });
});

require __DIR__.'/auth.php';