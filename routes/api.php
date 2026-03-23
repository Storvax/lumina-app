<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\DashboardController;

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
    });
});
