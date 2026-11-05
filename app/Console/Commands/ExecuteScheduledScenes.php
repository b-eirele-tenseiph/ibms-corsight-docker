<?php

namespace App\Console\Commands;

use App\Models\Scene;
use App\Services\ActivityLog\SceneExecutionLogService;
use App\Services\Holiday\PhilippineHolidayService;
use App\Services\Scenes\ScenesService;
use Illuminate\Console\Command;

class ExecuteScheduledScenes extends Command
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $signature        (1.0) The name and signature of the console command.
    // $description      (2.0) The console command description.
    // handle            (3.0) Execute the console command.

    /**
     * <Layer number> (1.0)
     * 
     * <Processing name> $signature
     * <Function> The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scene:execute-scheduled';

    /**
     * <Layer number> (2.0)
     * 
     * <Processing name> $description
     * <Function> The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for and executes any scenes scheduled for the current minute.';

    /**
     * <Layer number> (3.0)
     * 
     * <Processing name> handle
     * <Function> Execute the console command.
     * 
     */
    public function handle(
        ScenesService $svc,
        SceneExecutionLogService $logService,
        PhilippineHolidayService $holidays
    ): int {
        $now  = now('Asia/Manila');
        $day  = now('UTC')->format('l');
        $time = now('UTC')->format('H:i');

        if ($holidays->isHoliday($now)) {
            $this->info("Scene scheduler skipped — Philippine holiday: {$now->toDateString()}");
            return self::SUCCESS;
        }

        /** @var \Illuminate\Support\Collection<int, \App\Models\Scene> $scenes */
        $scenes = Scene::where('scheduled', 1)
            ->whereRaw('JSON_CONTAINS(days, ?)', [json_encode($day)])
            ->whereRaw('TIME_FORMAT(time, "%H:%i") = ?', [$time])
            ->get();

        foreach ($scenes as $scene) {
            $hasError = $svc->executeSceneCommands($scene);

            if (!$hasError) {
                $logService->handle($scene);
            }
        }

        return self::SUCCESS;
    }
}
