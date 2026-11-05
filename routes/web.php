<?php

use App\Http\Controllers\Admin\Settings\SceneController;
use App\Http\Controllers\Admin\SwitchBot\SwitchBotDeviceController;
use App\Http\Controllers\Admin\SwitchBot\SwitchBotRemoteController;
use App\Http\Controllers\Admin\Lorawan\LorawanDeviceController;
use App\Http\Controllers\Admin\Report\ElectricMonitoringReportController;
use App\Http\Controllers\Scheduler;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Common\NotificationController;

// Default routing entry of iBMS
Route::redirect('/', '/login');

// For Google Task Scheduler
Route::get("/scheduler", Scheduler::class)->middleware('appengine.cron');

// Notifications
Route::group([
    'prefix' => '/notifications',
    'as' => 'notifications',
    'middleware' => ['auth:admin,web'],
    'controller' => NotificationController::class,
], function () {
    Route::get('/', 'index')->name('.index');
    Route::post('/mark-as-read', 'markAsRead')->name('.mark-as-read');
    Route::put('/{notification}', 'readOne')->name('.read-one');
});

// LorawanDevice controls
Route::group([
    'middleware' => ['auth:admin,web', 'can:control devices'],
    'prefix' => 'lorawan-devices',
    'as' => 'lorawan_devices',
    'controller' => LorawanDeviceController::class,
], function () {
    Route::post('/{device}/command', 'command')->name('.command');
});

// SwitchBotDevice controls
Route::group([
    'middleware' => ['auth:admin,web', 'can:control devices'],
    'prefix' => 'switch_bot_devices',
    'as' => 'switch_bot_devices',
    'controller' => SwitchBotDeviceController::class,
], function () {
    Route::post('/{device}/command', 'command')->name('.command');
});

// SwitchBotRemote controls
Route::group([
    'middleware' => ['auth:admin,web', 'can:control devices'],
    'prefix' => 'switch_bot_remotes',
    'as' => 'switch_bot_remotes',
    'controller' => SwitchBotRemoteController::class,
], function () {
    Route::post('/{remote}/command', 'command')->name('.command');
});

// Scene List
Route::group([
    'middleware' => ['auth:admin,web'],
    'prefix' => 'scenes',
    'as' => 'scenes',
    'controller' => SceneController::class,
], function () {
    Route::get('/', 'index')->name('.index');
    Route::post('/', 'store')->name('.store');
    Route::put('/{scene}', 'update')->name('.update');
    Route::delete('/{scene}', 'destroy')->name('.destroy');
    Route::get('/devices', 'fetchDevices')->name('.devices');
});

// Route for switching language
Route::get('/lang/{locale?}', function ($locale) {
    return back()->withCookie(cookie('locale',  $locale ?? config('app.locale'), 60 * 24 * 365));
})->name('switch-locale');

// Route for electric monitoring report
Route::get('/admin/electric-report/print/{jobId}', [ElectricMonitoringReportController::class, 'printReport'])
    ->name('admin-electric-report-print')
    ->middleware(ValidateSignature::class);

Route::get('/admin/electric-report/download/{filename}', [ElectricMonitoringReportController::class, 'downloadReport'])
    ->name('admin-electric-report-download');

require __DIR__ . '/roles/admin.php';
require __DIR__ . '/roles/user.php';
require __DIR__ . '/roles/staff.php';
require __DIR__ . '/roles/crew.php';
require __DIR__ . '/roles/settings.php';
require __DIR__ . '/auth.php';
