<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BetController;
use App\Http\Controllers\Api\V1\RecordController;
use App\Http\Controllers\Api\V1\ResultController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\AccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public — auth
    Route::post('/login', [AuthController::class, 'login']);

    // Authenticated
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // All roles
        Route::middleware('role:admin,master,staff')->group(function () {
            Route::apiResource('bets', BetController::class)->only(['index', 'store', 'show']);
            Route::get('/records', [RecordController::class, 'index']);
            Route::get('/results', [ResultController::class, 'index']);
            Route::get('/settings', [SettingController::class, 'index']);
            Route::post('/settings', [SettingController::class, 'update']);
            Route::get('/account', [AccountController::class, 'index']);
            Route::post('/account/password', [AccountController::class, 'changePassword']);
        });

        // Admin + master
        Route::middleware('role:admin,master')->group(function () {
            Route::get('/report/summary', [ReportController::class, 'summary']);
            Route::get('/report/daily', [ReportController::class, 'daily']);
            Route::get('/report/staff', [ReportController::class, 'staff']);
        });

        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::post('/results', [ResultController::class, 'store']);
            Route::put('/results/{result}', [ResultController::class, 'update']);
        });
    });
});
