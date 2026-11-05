<?php

use App\Http\Controllers\Admin\AccountInformation\PasswordController;
use App\Http\Controllers\Admin\AccountInformation\ProfileController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin',
    'as' => 'admin',
    'middleware' => ['auth:admin'],
], function () {
    Route::redirect('/settings', '/settings/profile');

    // admin settings
    Route::group([
        'prefix' => '/settings',
        'as' => '.settings',
    ], function () {

        // admin settings > profile
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'edit')->name('.profile-edit');
            Route::patch('/profile', 'update')->name('.profile-update');
        });

        // admin settings > password
        Route::controller(PasswordController::class)->group(function () {
            Route::get('/password', 'edit')->name('.password-edit');
            Route::put('/password', 'update')->name('.password-update');
        });
    });
});
