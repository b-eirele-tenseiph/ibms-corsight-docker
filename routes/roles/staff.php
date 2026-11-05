<?php

use App\Http\Controllers\Admin\SwitchBotLockAccess\SwitchBotLockAccessController;
use App\Http\Controllers\Staff\Amenity\AmenityController;
use App\Http\Controllers\Staff\Dashboard\DashboardController;
use App\Http\Controllers\Staff\Promo\PromoController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'staff',
    'as' => 'staff',
    'middleware' => ['auth:admin', 'only:admin', 'admin.enabled'],
], function () {

    // staff > front desk (room access)
    Route::group([
        'prefix' => 'dashboard',
        'as' => '.dashboard',
        'middleware' => ['can:manage room access'],
        'controller' => DashboardController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::post('/{room}/access', 'show')->name('.room-access');
        Route::post('/{room}/access/new', 'store')->name('.room-access-new');
        Route::put('/{access}/access/update', 'update')->name('.room-access-update');
        Route::delete('/{access}/access/delete', 'destroy')->name('.room-access-delete');
    });

    // staff > switchBot lock status
    Route::controller(SwitchBotLockAccessController::class)->group(function () {
        Route::post('/lock/{access}/update', 'update')->name('.lock-access-update');
    });

    // staff > promos
    Route::group([
        'prefix' => 'promo-management',
        'as' => '.promo_management',
        'middleware' => ['can:manage promos'],
        'controller' => PromoController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::post('/promos', 'store')->name('.store');
        Route::post('/promos/{promo}/update', 'update')->name('.update');
        Route::delete('/promos/{promo}/destroy', 'destroy')->name('.destroy');
    });

    // staff > amenities
    Route::group([
        'prefix' => 'amenity-management',
        'as' => '.amenity_management',
        'middleware' => ['can:manage amenities'],
        'controller' => AmenityController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::post('/amenities', 'store')->name('.store');
        Route::post('/amenities/{amenity}/update', 'update')->name('.update');
        Route::delete('/amenities/{amenity}/destroy', 'destroy')->name('.destroy');
        Route::patch('/amenities/{amenity}/visibility', 'toggleVisibility')->name('.toggle-visibility');
    });
});
