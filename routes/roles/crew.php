<?php

use App\Http\Controllers\Crew\Dashboard\DashboardController;
use App\Http\Controllers\Crew\Food\FoodController;
use App\Http\Controllers\Crew\Order\OrderController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'crew',
    'as' => 'crew',
    'middleware' => ['auth:admin', 'only:admin', 'admin.enabled']
], function () {

    // crew > dashboard
    Route::group([
        'prefix' => '/dashboard',
        'as' => '.dashboard'
    ], function () {

        Route::controller((DashboardController::class))->group(function () {
            Route::get('/', 'index')->name('.index');
            Route::put('/{order}/cancel', 'cancel')->name('.orders-cancel');
        });
    });

    // crew > orders
    Route::group([
        'prefix' => '/orders',
        'as' => '.orders',
        'middleware' => ['can:manage orders']
    ], function () {

        Route::controller(OrderController::class)->group(function () {
            Route::get('/history', 'index')->name('.history');
            Route::put('/{order}/status/update', 'updateStatus')->name('.status-update');
        });
    });

    // crew > food menu
    Route::group([
        'prefix' => '/food-menu',
        'as' => '.foods',
        'middleware' => ['can:manage orders']
    ], function () {

        // crew/foods
        Route::controller(FoodController::class)->group(function () {
            Route::get('/', 'index')->name('.index');;
            Route::post('/', 'store')->name('.store');
            Route::post('/{food}/update', 'update')->name('.update');
            Route::delete('/{food}/delete', 'destroy')->name('.delete');
            Route::patch('/{food}/patch', 'updateAvailability')->name('.patch');
        });
    });
});
