<?php

use App\Http\Controllers\Admin\PasswordReset\PasswordResetController;
use App\Http\Controllers\Auth\Admin\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administrator Auth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register authentication routes for the admin users.
|
*/

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::controller(AdminAuthenticatedSessionController::class)->group(function () {
            Route::get('login', 'create')->name('admin.login');
            Route::post('login', 'store');
        });
    });
    Route::middleware('auth:admin')->group(function () {
        Route::controller(AdminAuthenticatedSessionController::class)->group(function () {
            Route::post('logout', 'destroy')->name('admin.logout');
        });
    });
});

/*
|--------------------------------------------------------------------------
| User Auth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register authentication routes for the users.
|
*/

Route::middleware('guest')->group(function () {
    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('login', 'create')->name('login');
        Route::post('login', 'store');
    });

    Route::controller(PasswordResetController::class)->group(function () {
        Route::get('/reset/{token}', 'index')->name('password.reset')->middleware('reset.validity');
        Route::post('/reset', 'update')->name('password.reset-update');
    });
});

Route::middleware('auth')->group(function () {
    // Route::get('verify-email', EmailVerificationPromptController::class)
    //     ->name('verification.notice');

    // Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    //     ->middleware(['signed', 'throttle:6,1'])
    //     ->name('verification.verify');

    // Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    //     ->middleware('throttle:6,1')
    //     ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
