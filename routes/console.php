<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\SwitchBot\SwitchBotKeypad\DeleteSwitchBotKeypadPasscode;
use App\Jobs\SwitchBot\SwitchBotKeypad\CreateSwitchBotKeypadPasscode;

Schedule::command('scene:execute-scheduled')->everyMinute()->timezone('UTC')->withoutOverlapping();

// SwitchBot Lock Access Jobs
Schedule::job(new DeleteSwitchBotKeypadPasscode)->everyMinute();
Schedule::job(new CreateSwitchBotKeypadPasscode)->everyMinute();

// SwitchBot Jobs
Schedule::job(new \App\Jobs\SwitchBot\MonitorDeviceBatteryStatus)->dailyAt('08:00')->timezone('UTC')->withoutOverlapping();
Schedule::job(new App\Jobs\SwitchBot\FetchDeviceStatus())->everyMinute()->timezone('UTC')->withoutOverlapping();

// File Management Job
Schedule::job(new App\Jobs\Pruning\PermanentlyDeleteArchivedFiles())->dailyAt('00:00')->timezone('Asia/Manila')->withoutOverlapping();

// Corsight People Counting Jobs
Schedule::command('corsight:prune')->dailyAt('01:00')->timezone('Asia/Manila')->withoutOverlapping();
