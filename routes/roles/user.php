<?php

use App\Http\Controllers\Common\EmergencyController;
use App\Http\Controllers\User\Amenities\AmenitiesController;
use App\Http\Controllers\User\Promo\PromoController;
use App\Http\Controllers\Admin\Settings\SceneController;
use App\Http\Controllers\User\Dashboard\DashboardController;
use App\Http\Controllers\User\Dashboard\DeviceGridPositionController;
use App\Http\Controllers\User\Restaurant\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => '/user',
    'as' => 'user',
    'middleware' => ['auth', 'only:web', 'allowed_access']
], function () {

    // User > Dashboard
    Route::group([
        'prefix' => '{room:number}/dashboard',
        'as' => '.dashboard',
        'controller' => DashboardController::class
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::get('/switches', 'getSwitches')->name('.switch-load');
        Route::get('/thermostat', 'getThermostat')->name('.thermostat-load');
        Route::get('/devices', 'getDevices')->name('.device-load');
        Route::get('/sensors', 'getSensors')->name('.sensors-load');
        Route::get('/outdoor-meter', 'getOutdoorMeter')->name('.outdoor-meter-load');
        Route::get('/people-counter', 'getPeopleCounter')->name('.people-counter-load');
        Route::get('/occupancy-temp-light', 'getOccuTempLight')->name('.occu-temp-light-load');
        Route::get('/co2-temp-humid', 'getCO2TempHumid')->name('.co2-temp-humid-load');
        Route::get('/curtains', 'getCurtains')->name('.curtain-load');
        Route::get('/blind-tilt', 'getBlindTilt')->name('.blind-tilt-load');
        Route::get('/aircon', 'getAircon')->name('.aircon-load');
        Route::get('/projector', 'getProjector')->name('.projector-load');
        Route::get('/television', 'getTelevision')->name('.television-load');
    });

    // User > Dashboard > Promos
    Route::group([
        'prefix' => 'dashboard',
        'as' => '.dashboard',
        'controller' => DashboardController::class
    ], function () {
        Route::post('/hide-promo-popup', 'hidePromo')->name('.hide-promo');
    });

    // User > Dashboard > Grid Positions
    Route::controller(DeviceGridPositionController::class)->group(function () {
        Route::post('/grid-positions', 'store')->name('.grid-store');
    });

    Route::group([
        'prefix' => '/promos',
        'as' => '.promos',
        'controller' => PromoController::class,
        'middleware' => ['can:access promos']
    ], function () {
        Route::get('/', 'index')->name('.index');
    });

    Route::group([
        'prefix' => '/amenities',
        'as' => '.amenities',
        'controller' => AmenitiesController::class,
        'middleware' => ['can:access amenities']
    ], function () {
        Route::get('/', 'index')->name('.index');
    });

    Route::group([
        'prefix' => '/emergency',
        'as' => '.emergency',
        'controller' => EmergencyController::class,
    ], function () {
        Route::post('/trigger', 'roomEmergency')->name('.trigger-emergency');
    });

    Route::group([
        'prefix' => '/scenes',
        'as' => '.scenes',
        'controller' => SceneController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::post('/{scene}/execute', 'executeCommand')->name('.command-execute');
    });

    Route::group([
        'prefix' => '/orders',
        'as' => '.restaurant',
        'controller' => RestaurantController::class,
        'middleware' => ['can:access restaurant'],
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::post('/', 'store')->name('.store');
        Route::put('/{restaurant}', 'cancel')->name('.cancel');
        Route::get('/history', 'index')->name('.history');
    });
});
