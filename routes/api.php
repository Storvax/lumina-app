<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DiaryController;
use App\Http\Controllers\Api\V1\MissionController;
use App\Http\Controllers\Api\V1\CalmZoneController;

// API v1 prefix
Route::prefix('v1')->group(function () {
    // Public routes (sem autenticação)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes (com autenticação Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Diary endpoints
        Route::get('/diary', [DiaryController::class, 'index']);
        Route::post('/diary', [DiaryController::class, 'store']);

        // Missions endpoints
        Route::get('/missions', [MissionController::class, 'index']);
        Route::patch('/missions/{mission_id}/progress', [MissionController::class, 'updateProgress']);

        // Calm Zone endpoints (Vault)
        Route::get('/calm-zone/vault', [CalmZoneController::class, 'vaultIndex']);
        Route::post('/calm-zone/vault', [CalmZoneController::class, 'vaultStore']);
        Route::delete('/calm-zone/vault/{item_id}', [CalmZoneController::class, 'vaultDestroy']);
    });
});
