<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\API\AuthController;
use Modules\Customers\Http\Controllers\API\ForgotPasswordController;

Route::prefix('v1')->group(function () {
    // Auth Login
    Route::prefix('auth')->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/', [AuthController::class, 'index']);
            Route::put('/update', [AuthController::class, 'update']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        Route::post('/sign-up', [AuthController::class, 'register']);

        Route::post('/sign-in', [AuthController::class, 'login']);

        Route::post('/verify', [AuthController::class, 'verify']);

        Route::post('/resend', [AuthController::class, 'resend']);

        Route::prefix('forgot-password')->group(function () {
            Route::post('/', [ForgotPasswordController::class, 'send']);
            Route::post('/update', [ForgotPasswordController::class, 'update']);
        });
    });
});
