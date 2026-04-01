<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OnboardingApiController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DiaryController;
use App\Http\Controllers\Api\V1\MissionController;
use App\Http\Controllers\Api\V1\CalmZoneController;

Route::prefix('v1')->group(function () {
    // Rotas públicas com throttle apertado para prevenir enumeração de contas e brute-force.
    Route::middleware('throttle:20,1')->prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        Route::post('/onboarding', [OnboardingApiController::class, 'store']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/diary', [DiaryController::class, 'index']);
        // Escrita de diário com throttle próprio — contexto de crise pode gerar múltiplas submissões.
        Route::middleware('throttle:content-creation')->post('/diary', [DiaryController::class, 'store']);

        Route::get('/missions', [MissionController::class, 'index']);
        Route::patch('/missions/{mission_id}/progress', [MissionController::class, 'updateProgress']);

        Route::get('/calm-zone/vault', [CalmZoneController::class, 'vaultIndex']);
        Route::middleware('throttle:content-creation')->group(function () {
            Route::post('/calm-zone/vault', [CalmZoneController::class, 'vaultStore']);
            Route::delete('/calm-zone/vault/{item_id}', [CalmZoneController::class, 'vaultDestroy']);
        });
    });
});
