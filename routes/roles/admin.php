<?php

use App\Http\Controllers\Admin\AdminAccount\AdminAccountController;
use App\Http\Controllers\Admin\ClimateAnalytics\ClimateDataController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\ElectricMonitoring\ElectricMonitoringController;
use App\Http\Controllers\Admin\Logs\ActivityLogsController;
use App\Http\Controllers\Admin\Lorawan\LorawanDeviceController;
use App\Http\Controllers\Admin\Lorawan\LorawanGatewayController;
use App\Http\Controllers\Admin\MonitoringAndControls\AirconController;
use App\Http\Controllers\Admin\MonitoringAndControls\PlugController;
use App\Http\Controllers\Admin\MonitoringAndControls\SwitchController;
use App\Http\Controllers\Admin\Report\ElectricMonitoringReportController;
use App\Http\Controllers\Admin\Report\WaterBillingReportController;
use App\Http\Controllers\Admin\Room\RoomController;
use App\Http\Controllers\Admin\RoomArea\RoomAreaController;
use App\Http\Controllers\Admin\RoomMonitoring\RoomMonitoringController;
use App\Http\Controllers\Admin\Settings\ElectricityRateController;
use App\Http\Controllers\Admin\Settings\FileController;
use App\Http\Controllers\Admin\Settings\GeneralController;
use App\Http\Controllers\Admin\Settings\RoleController;
use App\Http\Controllers\Admin\Settings\SceneController;
use App\Http\Controllers\Admin\Settings\WaterRateController;
use App\Http\Controllers\Admin\SwitchBot\SwitchBotAccountController;
use App\Http\Controllers\Admin\SwitchBot\SwitchBotDeviceController;
use App\Http\Controllers\Admin\SwitchBot\SwitchBotHubController;
use App\Http\Controllers\Admin\SwitchBot\SwitchBotRemoteController;
use App\Http\Controllers\Admin\Corsight\CorsightAccountController;
use App\Http\Controllers\Admin\Corsight\CorsightDeviceController;
use App\Http\Controllers\Admin\Corsight\CorsightPersonController;
use App\Http\Controllers\Admin\SwitchBotLockAccess\SwitchBotLockAccessController;
use App\Http\Controllers\Admin\UserAccount\UserAccountController;
use App\Http\Controllers\Common\RoleImpersonationController;
use Illuminate\Support\Facades\Route;

// Admin Routes
Route::group([
    'prefix' => 'admin',
    'as' => 'admin',
    'middleware' => ['auth:admin', 'only:admin', 'admin.enabled'],
], function () {

    // admin > dashboard
    Route::group([
        'prefix' => '/dashboard',
        'as' => '.dashboard',
    ], function () {
        Route::controller((DashboardController::class))->group(function () {
            Route::get('/', 'index')->name('.index');
            Route::get('/devices', 'getRoom')->name('.devices');
            Route::get('/devices/climate/monitor', 'getClimate')->name('.devices-climate-monitor');
            Route::get('/visitor-data', 'visitorData')->name('.visitor-data');
            Route::get('/device-count-data', 'deviceCountData')->name('.devices-count-data');
            Route::get('/rooms-data', 'roomsData')->name('.rooms-data');
            Route::get('/device-summary-data', 'deviceSummaryData')->name('.devices-summary-data');
            Route::get('/device-status', 'deviceStatus')->name('.device-status');
            Route::get('/recent-activity', 'recentActivity')->name('.recent-activity');
            Route::get('/environment-sensors-data', 'environmentSensorData')->name('.environment-sensors-data');
            Route::get('/electric-consumption-data', 'electricConsumptionData')->name('.electric-consumption-data');
            Route::get('/energy-data', 'deviceEnergyData')->name('.device-energy-data');
            Route::get('/carbon-footprint-data', 'carbonFootprintData')->name('.device-carbon-footprint-data');
            Route::get('/power-factor', 'powerFactorData')->name('.device-power-factor-data');
            Route::get('/daily-energy-data', 'dailyEnergyData')->name('.daily-energy-data');
            Route::get('/hourly-energy-data', 'hourlyEnergyData')->name('.hourly-energy-data');
            Route::get('/monthly-energy-data', 'monthlyEnergyData')->name('.monthly-energy-data');
            Route::get('/electric-meters',  'electricMeters')->name('.electric-meters');
            Route::get('/corsight-data', 'corsightData')->name('.corsight-data');
        });
    });

    // admin > climate analytics
    Route::group([
        'prefix' => '/climate-analytics',
        'as' => '.climate-analytics',
        'middleware' => ['can:climate analytics'],
        'controller' => ClimateDataController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::get('/climate/slave_power', 'getClimateStatus')->name('.power.status');
        Route::get('/climate/fan_speed', 'getClimateFanSpeed')->name('.fan.speed');
        Route::get('/climate/temperature', 'getClimateTemperature')->name('.temperature');
        Route::get('/climate/setpoint', 'getClimateSetpoint')->name('.setpoint');
        Route::get('/climate/actualAndSetpoint', 'getClimateActualAndSetPoint')->name('.actual-and-setpoint');
        Route::get('/climate/readings', 'getClimateReadings')->name('.readings');
    });

    Route::group([
        'prefix' => '/water-billing-report',
        'as' => '.water-billing-report',
        'middleware' => ['can:manage water billing'],
        'controller' => WaterBillingReportController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::get('/water-rate', 'getWaterRateData')->name('.water-rate-data');
    });

    // admin > room monitoring
    Route::group([
        'prefix' => '/room-monitoring',
        'as' => '.room-monitoring',
        'middleware' => ['can:room monitoring'],
        'controller' => RoomMonitoringController::class,
    ], function () {
        Route::get('/{room?}', 'index')->whereNumber('room')->name('.index');
        Route::get('/switches', 'getByType')->name('.switch-load');
        Route::get('/thermostat', 'getByType')->name('.thermostat-load');
        Route::get('/devices', 'getByType')->name('.device-load');
        Route::get('/curtains', 'getByType')->name('.curtain-load');
        Route::get('/blind-tilt', 'getByType')->name('.blind-tilt-load');
        Route::get('/aircon', 'getByType')->name('.aircon-load');
        Route::get('/projector', 'getByType')->name('.projector-load');
        Route::get('/television', 'getByType')->name('.television-load');
    });

    // admin > electric monitoring
    Route::group([
        'prefix' => '/electric-monitoring',
        'as' => '.electric-monitoring',
        'middleware' => ['can:electric monitoring'],
        'controller' => ElectricMonitoringController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::post('/total', 'getElectricMeterTotalData')->name('.total-data');
        Route::post('/consumption', 'getElectricMeterConsumptionData')->name('.consumption-data');
        Route::post('/current', 'getElectricMeterCurrentData')->name('.current-data');
        Route::post('/voltage', 'getElectricMeterVoltageData')->name('.voltage-data');
        Route::post('/power', 'getElectricMeterPowerData')->name('.power-data');
        Route::post('/powerFactor', 'getElectricMeterPowerFactorData')->name('.power-factor-data');
        Route::post('/frequency', 'getElectricMeterFrequencyData')->name('.frequency');
        Route::post('/insights', 'getInsightsData')->name('.insights');
    });

    Route::group([
        'prefix' => '/electric-report',
        'as' => '.electric-report',
        'middleware' => ['can:electric monitoring'],
        'controller' => ElectricMonitoringReportController::class,
    ], function () {
        Route::get('/', 'index')->name('.index');
        Route::get('/report', 'generateReport')->name('.generate');
        //
        Route::get('/report/summary', 'getSummaryData')->name('.summary-data');
        Route::get('/report/total', 'getTotalConsumptionPerMeterData')->name('.total-consumption-data');
        Route::get('/report/consumption', 'getOverallConsumptionReportData')->name('.consumption-data');
        Route::get('/report/hourly', 'getHourlyConsumptionData')->name('.hourly-consumption-data');
        Route::get('/report/monthly', 'getMonthlyConsumptionData')->name('.monthly-consumption-data');
        Route::get('/report/energyCost', 'getEnergyCostData')->name('.energy-cost-data');
        Route::get('/report/carbonFootprint', 'getCarbonFootprintData')->name('.carbon-footprint-data');
    });

    // admin > monitoring and controls
    Route::group([
        'prefix' => '/monitoring-controls',
        'as' => '.monitoring-controls',
        'middleware' => ['can:monitoring controls'],
    ], function () {

        // admin > monitoring and controls > aircons
        Route::group([
            'prefix' => '/aircons',
            'as' => '.aircons',
        ], function () {
            Route::get('/', AirconController::class)->name('.index');
        });

        // admin > monitoring and controls > switches
        Route::group([
            'prefix' => '/switches',
            'as' => '.switches',
        ], function () {
            Route::get('/', SwitchController::class)->name('.index');
        });

        // admin > monitoring and controls > plugs
        Route::group([
            'prefix' => '/plugs',
            'as' => '.plugs',
        ], function () {
            Route::get('/', PlugController::class)->name('.index');
        });
    });

    // admin > management
    Route::group([
        'prefix' => '/management',
        'as' => '.management',
    ], function () {

        // admin > management > rooms
        Route::group([
            'prefix' => '/rooms',
            'as' => '.rooms',
            'middleware' => ['can:manage rooms'],
            'controller' => RoomController::class,
        ], function () {
            Route::get('/', 'index')->name('.index');
            Route::post('/', 'store')->name('.store');
            Route::put('{room}', 'update')->name('.update');
            Route::delete('{room}', 'destroy')->name('.destroy');
            Route::delete('/floor/{id}', 'deleteFloor')->name('.floor-delete');
            Route::post('/import', 'import')->name('.import');
            Route::get('/template', 'template')->name('.import-template');
            Route::get('/export', 'export')->name('.export');
        });

        // admin > management > room areas
        Route::group([
            'prefix' => '/areas',
            'as' => '.areas',
            'controller' => RoomAreaController::class,
        ], function () {
            Route::post('/{room}/fetch', 'fetch')->name('.fetch');
        });

        // admin > management > remote lock
        Route::group([
            'prefix' => '/remote-locks',
            'as' => '.remote-locks',
            'middleware' => ['can:manage remotelock'],
            'controller' => DashboardController::class, // @@todo Update
        ], function () {
            Route::get('/', 'index')->name('.index'); // @@todo Update
        });

        // admin > management > lorawan
        Route::group([
            'prefix' => '/lorawan',
            'as' => '.lorawan',
            'middleware' => ['can:manage lorawan'],
        ], function () {

            // admin > management > lorawan > gateways
            Route::group([
                'prefix' => '/gateways',
                'as' => '.gateways',
                'controller' => LorawanGatewayController::class, // @@todo Update
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/', 'store')->name('.store');
                Route::put('/{gateway}', 'update')->name('.update');
                Route::delete('/{gateway}', 'destroy')->name('.destroy');
                Route::get('/export', 'export')->name('.export');
                Route::post('/import', 'import')->name('.import');
                Route::get('/template', 'template')->name('.import-template');
            });

            // admin > management > lorawan > devices
            Route::group([
                'prefix' => '/devices',
                'as' => '.devices',
                'controller' => LorawanDeviceController::class,
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/', 'store')->name('.store');
                Route::put('/{device}', 'update')->name('.update');
                Route::delete('/{device}', 'destroy')->name('.destroy');
                Route::get('/devices/{device}/settings', 'settings')->name('.settings-index');
                Route::put('/devices/{device}/settings', 'updateSettings')->name('.settings-update');
                Route::get('/export', 'export')->name('.export');
                Route::post('/import', 'import')->name('.import');
                Route::get('/template', 'getTemplate')->name('.template');
            });
        });

        // admin > management > switch bot
        Route::group([
            'prefix' => '/switchbot',
            'as' => '.switchbot',
            'middleware' => ['can:manage switchbot'],
        ], function () {

            // admin > management > switch bot > accounts
            Route::group([
                'prefix' => '/accounts',
                'as' => '.accounts',
                'controller' => SwitchBotAccountController::class,
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/accounts', 'store')->name('.store');
                Route::put('/accounts/{account}', 'update')->name('.update');
                Route::delete('/accounts/{account}', 'destroy')->name('.destroy');

                Route::post('/accounts/{account}/credentials', 'getAuthenticationCredentials')->name('.credentials');
                Route::post('/accounts/webhook', 'getWebhook')->name('.show-webhook');

                Route::post('/import', 'import')->name('.import');
                Route::get('/export', 'export')->name('.export');
                Route::get('/generate/template', 'generateTemplate')->name('.generate-template');
            });

            // admin > management > switch bot > hubs
            Route::group([
                'prefix' => '/hubs',
                'as' => '.hubs',
                'controller' => SwitchBotHubController::class,
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::get('/sync', 'sync')->name('.sync');
                Route::post('/sync', 'store')->name('.sync-store');
                Route::put('{hub}', 'update')->name('.update');
                Route::get('/export', 'export')->name('.export');
            });

            // admin > management > switch bot > devices
            Route::group([
                'prefix' => '/devices',
                'as' => '.devices',
                'controller' => SwitchBotDeviceController::class,
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::get('/synchronize', 'synchronize')->name('.sync');
                Route::post('/synchronize', 'store')->name('.sync-store');
                Route::put('{device}', 'update')->name('.update');
                Route::put('/{device}/settings', 'updateSettings')->name('.settings');
                Route::get('/export', 'export')->name('.export');
            });

            // admin > management > switch bot > remotes
            Route::group([
                'prefix' => '/remotes',
                'as' => '.remotes',
                'controller' => SwitchBotRemoteController::class,
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::get('/scan', 'sync')->name('.sync');
                Route::post('/scan', 'store')->name('.store');
                Route::put('{remote}', 'update')->name('.update');
                Route::put('/{remote}/settings', 'updateSettings')->name('.settings');
                Route::get('/export', 'export')->name('.export');
            });
        });

        // admin > management > corsight
        Route::group([
            'prefix' => '/corsight',
            'as' => '.corsight',
            'middleware' => ['can:manage corsight']
        ], function () {

            // admin > management > corsight > accounts
            Route::group([
                'prefix' => '/accounts',
                'as' => '.accounts',
                'controller' => CorsightAccountController::class
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/accounts', 'store')->name('.store');
                Route::put('/accounts/{account}', 'update')->name('.update');
                Route::delete('/accounts/{account}', 'destroy')->name('.destroy');
                Route::post('/accounts/{account}/credentials', 'getCredentials')->name('.credentials');
            });

            // admin > management > corsight > devices
            Route::group([
                'prefix' => '/devices',
                'as' => '.devices',
                'controller' => CorsightDeviceController::class
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/devices', 'store')->name('.store');
                Route::put('/devices/{device}', 'update')->name('.update');
                Route::put('/devices/{device}/settings', 'updateSettings')->name('.settings');
                Route::delete('/devices/{device}', 'destroy')->name('.destroy');
            });

            // admin > management > corsight > people
            Route::group([
                'prefix' => '/people',
                'as' => '.people',
                'controller' => CorsightPersonController::class
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::get('/{session}', 'show')->name('.show');
            });
        });

        // admin > management > logs
        Route::group([
            'prefix' => '/logs',
            'as' => '.logs',
            'middleware' => ['can:manage logs'],
            'controller' => ActivityLogsController::class,
        ], function () {
            Route::get('/', 'index')->name('.index');
            Route::put('/{error}/update', 'update')->name('.error-update');
            Route::get('/export', 'export')->name('.export');
        });

        // admin > management > accounts
        Route::group([
            'prefix' => '/accounts',
            'as' => '.accounts',
        ], function () {

            // admin > management > accounts > admins
            Route::group([
                'prefix' => '/admins',
                'as' => '.admins',
                'controller' => AdminAccountController::class,
                'middleware' => ['can:manage admin accounts'],
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/', 'store')->name('.store');
                Route::put('/{admin}', 'update')->name('.update');
                Route::delete('/{admin}', 'destroy')->name('.destroy');
                Route::put('/{admin}/option', 'optionEnabled')->name('.enable');
                Route::put('/{admin}/generate-token', 'generateResetPasswordLink')->name('.generate-token');
            });

            // admin > management > accounts > users
            Route::group([
                'prefix' => '/users',
                'as' => '.users',
                'controller' => UserAccountController::class,
                'middleware' => ['can:manage user accounts'],
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/users', 'store')->name('.store');
                Route::put('/users/{user}', 'update')->name('.update');
                Route::delete('/users/{user}', 'destroy')->name('.destroy');
                Route::get('/export', 'export')->name('.export');
            });
        });

        // admin > switchBot lock status
        Route::controller(SwitchBotLockAccessController::class)->group(function () {
            Route::post('/lock/{access}/update', 'update')->name('.lock-access-update');
        });

        // admin > management > settings
        Route::group([
            'prefix' => '/settings',
            'as' => '.settings',
        ], function () {

            // admin > management > system > general
            Route::group([
                'prefix' => '/system',
                'as' => '.system',
                'controller' => GeneralController::class,
            ], function () {
                Route::get('/general', 'general')->name('.general-index');

                Route::group(['middleware' => ['can:manage rates']], function () {
                    Route::get('/rate', 'rates')->name('.rate-index');
                });

                Route::group(['middleware' => ['can:manage files']], function () {
                    Route::get('/file', 'file')->name('.file-index');
                });
            });

            // admin > management > system > rate
            Route::group(['middleware' => ['can:manage rates']], function () {
                Route::group([
                    'prefix' => '/rates/electricity',
                    'as' => '.rates.electricity',
                    'controller' => ElectricityRateController::class,
                ], function () {
                    Route::post('/', 'store')->name('.store');
                    Route::put('/{rate}', 'update')->name('.update');
                    Route::delete('/{rate}', 'destroy')->name('.destroy');
                });

                Route::group([
                    'prefix' => '/rates/water',
                    'as' => '.rates.water',
                    'controller' => WaterRateController::class,
                ], function () {
                    Route::post('/', 'store')->name('.store');
                    Route::put('/{rate}', 'update')->name('.update');
                    Route::delete('/{rate}', 'destroy')->name('.destroy');
                });
            });

            // admin > management > system > files
            Route::group([
                'prefix' => '/file',
                'as' => '.file',
                'controller' => FileController::class,
                'middleware' => ['can:manage files'],
            ], function () {
                Route::get('/download', 'download')->name('.download');
                Route::post('/check', 'checkFile')->name('.check');
                Route::delete('/{file}', 'destroy')->name('.destroy');
                Route::post('/{file}/restore', 'restore')->withTrashed()->name('.restore');
                Route::delete('/{file}/force', 'forceDelete')->withTrashed()->name('.force-delete');
            });

            // admin > management > settings > roles
            Route::group([
                'prefix' => '/roles',
                'as' => '.roles',
                'middleware' => ['can:manage roles'],
                'controller' => RoleController::class,
            ], function () {
                Route::get('/', 'index')->name('.index');
                Route::post('/', 'store')->name('.store');
                Route::put('/{role}', 'update')->name('.update');
                Route::delete('/delete', 'delete')->name('.delete');
            });

            // admin > management > settings > scenes
            Route::group([
                'prefix' => '/scenes',
                'as' => '.scenes',
                'middleware' => ['can:manage scenes'],
                'controller' => SceneController::class,
            ], function () {
                Route::get('/', 'index')->name('.index');
            });
        });

        Route::group([
            'prefix' => '/impersonate',
            'as' => '.impersonate',
            'controller' => RoleImpersonationController::class,
            'middleware' => ['role:super admin'],
        ], function () {
            Route::get('/', 'getRole')->name('.index');
            Route::post('/role', 'store')->name('.store');
            Route::post('/role/delete', 'destroy')->name('.destroy');
        });
    });
});
