<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or home
Route::get('/', fn () => redirect()->route('login'));

// Locale switcher
Route::post('/locale', function (\Illuminate\Http\Request $request) {
    $request->validate(['locale' => 'required|in:en,km,vi,th']);
    session(['locale' => $request->locale]);
    if (auth()->check()) {
        auth()->user()->update(['locale' => $request->locale]);
    }
    return back();
})->name('locale.set');

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Home — all roles
    Route::get('/home', [HomeController::class, 'index'])
        ->middleware('role:admin,master,staff')
        ->name('home');
    Route::post('/home/bet', [HomeController::class, 'store'])
        ->middleware('role:admin,master,staff')
        ->name('home.bet');

    // Record — all roles (scoped by role in controller)
    Route::get('/record', [RecordController::class, 'index'])
        ->middleware('role:admin,master,staff')
        ->name('record');

    // Result — all roles read; admin can write
    Route::get('/result', [ResultController::class, 'index'])
        ->middleware('role:admin,master,staff')
        ->name('result');
    Route::post('/result', [ResultController::class, 'store'])
        ->middleware('role:admin')
        ->name('result.store');
    Route::put('/result/{result}', [ResultController::class, 'update'])
        ->middleware('role:admin')
        ->name('result.update');

    // Setting — admin and master only (staff cannot access)
    Route::get('/setting', [SettingController::class, 'index'])
        ->middleware('role:admin,master')
        ->name('setting');
    Route::post('/setting', [SettingController::class, 'update'])
        ->middleware('role:admin,master')
        ->name('setting.update');

    // Account — all roles (scoped)
    Route::get('/account', [AccountController::class, 'index'])
        ->middleware('role:admin,master,staff')
        ->name('account');
    Route::post('/account/password', [AccountController::class, 'changePassword'])
        ->middleware('role:admin,master,staff')
        ->name('account.password');

    // User management (admin + master)
    Route::post('/account/users', [AccountController::class, 'storeUser'])
        ->middleware('role:admin,master')
        ->name('account.users.store');
    Route::put('/account/users/{user}', [AccountController::class, 'updateUser'])
        ->middleware('role:admin,master')
        ->name('account.users.update');
    Route::delete('/account/users/{user}', [AccountController::class, 'destroyUser'])
        ->middleware('role:admin')
        ->name('account.users.destroy');

    // Report — admin + master only
    Route::get('/report', [ReportController::class, 'index'])
        ->middleware('role:admin,master')
        ->name('report');
});

require __DIR__.'/auth.php';
