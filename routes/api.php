<?php

use App\Http\Controllers\Api\CloudTaskEndpointController;
use App\Http\Controllers\Api\CorsightEndpointController;
use App\Http\Controllers\Api\LorawanEndpointController;
use App\Http\Controllers\Api\SwitchBotEndpointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('lorawan/endpoint', LorawanEndpointController::class)
    ->name('lorawan-endpoint');

Route::post('task-handler', CloudTaskEndpointController::class)
    ->name('cloudtask-endpoint')
    ->withoutMiddleware('throttle:api');

Route::post('switchbot/endpoint', SwitchBotEndpointController::class)
    ->name('switchbot-endpoint');

Route::post('corsight/endpoint', CorsightEndpointController::class)
    ->name('corsight-endpoint')
    ->withoutMiddleware('throttle:api');
