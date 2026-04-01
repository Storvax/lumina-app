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
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SelfAssessmentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\CommunityReportController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/offline', fn () => view('offline'))->name('offline');
Route::get('/comunidade/impacto', [CommunityReportController::class, 'index'])->name('community.report');
Route::get('/pesquisar', [SearchController::class, 'index'])->middleware(['auth', 'onboarding'])->name('search.index');
Route::get('/fogueira', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/salas/silencio', [RoomController::class, 'silentRoom'])->middleware(['auth', 'onboarding'])->name('rooms.silent');

/*
|--------------------------------------------------------------------------
| Onboarding — acessível a utilizadores autenticados que ainda não completaram o processo
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::controller(OnboardingController::class)->prefix('bem-vindo')->name('onboarding.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
    });

    // Rotas de 2FA — acessíveis a utilizadores autenticados sem o middleware de 2FA para evitar loop.
    Route::controller(\App\Http\Controllers\TwoFactorController::class)->prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/setup', 'setup')->name('setup');
        Route::post('/confirm', 'confirm')->name('confirm');
        Route::post('/disable', 'disable')->name('disable');
        Route::get('/challenge', 'challenge')->name('challenge');
        Route::post('/verify', 'verify')->name('verify');
    });
});

Route::middleware(['auth', 'onboarding'])->group(function () {


    Route::post('/users/{user}/oferecer-apoio', [\App\Http\Controllers\GamificationController::class, 'sendGentleChallenge'])
        ->middleware('throttle:gamification')
        ->name('users.challenge');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/perfil/notificacoes', [\App\Http\Controllers\ProfileController::class, 'updateNotificationPrefs'])->name('profile.notifications');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    /*
    |--------------------------------------------------------------------------
    | Fórum (Mural da Esperança)
    |--------------------------------------------------------------------------
    */
    Route::controller(ForumController::class)->prefix('mural')->name('forum.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/criar', 'store')->middleware('throttle:content-creation')->name('store');
        
        Route::prefix('{post}')->group(function () {
            Route::get('/', 'show')->name('show');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
            Route::post('/reagir', 'react')->name('react');
            Route::post('/comentar', 'comment')->middleware('throttle:content-creation')->name('comment');
            Route::post('/report', 'report')->middleware('throttle:reports')->name('report');
            Route::post('/save', 'toggleSave')->name('save');
            Route::post('/subscrever', 'toggleSubscription')->name('subscribe');
            Route::post('/checkin', 'postCheckin')->name('checkin');
            Route::post('/summarize', 'summarize')->middleware('throttle:ai-actions')->name('summarize');
            Route::patch('/pin', 'togglePin')->name('pin');
            Route::patch('/lock', 'toggleLock')->name('lock');
        });
    });

    Route::post('/users/{user}/shadowban', [ForumController::class, 'shadowbanUser'])
        ->middleware('throttle:reports')
        ->name('users.shadowban');
    
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
            Route::post('/messages/{message}/report', 'reportMessage')->middleware('throttle:reports')->name('report');
            
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
        Route::post('/sugerir', 'store')->middleware('throttle:suggestions')->name('store');
        Route::post('/{resource}/votar', 'toggleVote')->middleware('throttle:suggestions')->name('vote');
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
        Route::post('/pedir', 'requestBuddy')->middleware('throttle:buddy-actions')->name('request');
        Route::post('/candidatura', 'apply')->middleware('throttle:buddy-actions')->name('apply');
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
        Route::get('/perfil/tendencias', 'moodTrends')->name('profile.mood-trends');
        Route::get('/perfil/passaporte', 'exportPassport')->name('profile.passport');
        Route::patch('/perfil/acessibilidade', 'updateAccessibility')->name('profile.accessibility');
        Route::post('/api/user/tour-completed', [App\Http\Controllers\OnboardingController::class, 'markTourCompleted'])->name('tour.completed');
    });

    /*
    |--------------------------------------------------------------------------
    | Triagem Terapêutica (Smart Match)
    |--------------------------------------------------------------------------
    */
    Route::get('/terapia', [\App\Http\Controllers\TherapyController::class, 'index'])->name('therapy.index');
    Route::post('/terapia/triagem', [\App\Http\Controllers\TherapyController::class, 'matchChat'])->middleware('throttle:ai-actions')->name('therapy.match');

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
        Route::post('/exportar', 'exportData')->middleware('throttle:privacy-actions')->name('export');
        Route::post('/hibernar', 'hibernate')->middleware('throttle:privacy-actions')->name('hibernate');
    });

    /*
    |--------------------------------------------------------------------------
    | Auto-avaliação (PHQ-9 / GAD-7)
    |--------------------------------------------------------------------------
    */
    Route::controller(SelfAssessmentController::class)->prefix('auto-avaliacao')->name('assessment.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/resultado/{assessment}', 'show')->name('result');
        Route::get('/{type}', 'create')->name('create');
        Route::post('/{type}', 'store')->name('store');
    });

    /*
    |--------------------------------------------------------------------------
    | The Wall (Galeria Artística)
    |--------------------------------------------------------------------------
    */
    Route::controller(WallController::class)->prefix('the-wall')->name('wall.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->middleware('throttle:content-creation')->name('store');
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
        Route::get('/sons', 'sounds')->name('sounds');
        Route::get('/combustao', 'burn')->name('burn');
        Route::get('/respiracao', 'breathe')->name('breathe');
        Route::get('/sintonia', 'heartbeat')->name('heartbeat');
        Route::get('/reflexao', [CalmZoneController::class, 'reflection'])->name('reflection');
        Route::post('/reflexao/enviar', [CalmZoneController::class, 'sendReflection'])->middleware('throttle:ai-actions')->name('reflection.send');
        Route::get('/cofre', 'vault')->name('vault');
        Route::post('/cofre', 'storeVaultItem')->name('vault.store');

        Route::post('/playlist/sugerir', 'suggestSong')->middleware('throttle:suggestions')->name('playlist.suggest');
        Route::post('/playlist/{song}/votar', 'voteSong')->middleware('throttle:suggestions')->name('playlist.vote');
        Route::delete('/playlist/{song}', 'deleteSong')->name('playlist.delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Diário do Pacto (integrado na comunidade)
    |--------------------------------------------------------------------------
    */
    Route::get('/comunidade/pacto', [\App\Http\Controllers\PactController::class, 'show'])->name('forum.pact');
    Route::post('/comunidade/pacto/responder', [\App\Http\Controllers\PactController::class, 'store'])->name('forum.pact.store');
});

/*
|--------------------------------------------------------------------------
| Portal do Terapeuta (protegido por TherapistMiddleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\TherapistMiddleware::class])->group(function () {
    Route::get('/terapeuta', [\App\Http\Controllers\TherapistController::class, 'dashboard'])->name('therapist.dashboard');
    Route::post('/terapeuta/missao', [\App\Http\Controllers\TherapistController::class, 'assignMission'])->name('therapist.assign');
    Route::post('/terapeuta/somatico', [\App\Http\Controllers\TherapistController::class, 'triggerSomaticSync'])->name('therapist.somatic');
});

/*
|--------------------------------------------------------------------------
| Dashboard Corporativo B2B (protegido por CorporateMiddleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\CorporateMiddleware::class])->group(function () {
    Route::get('/empresa', [\App\Http\Controllers\CorporateController::class, 'dashboard'])->name('corporate.dashboard');
});

require __DIR__.'/auth.php';