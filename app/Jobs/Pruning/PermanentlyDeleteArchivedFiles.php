<?php

namespace App\Jobs\Pruning;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PermanentlyDeleteArchivedFiles implements ShouldQueue
{
    use Queueable;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle               (1.0) Execute the action.
    // failed               (2.0) Logs job failure details.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Execute the action.
     *
     * @return void
     */
    public function handle(): void
    {
        $cutoff = now()->subDays(30)->timestamp;
        $disk = Storage::disk('gcs');

        // Delete files older than 30 days from GCS and database
        $deletedFiles = collect($disk->allFiles('archived/'))
            ->filter(function ($file) use ($disk, $cutoff) {
                return $disk->lastModified($file) < $cutoff;
            })
            ->tap(function ($files) use ($disk) {
                $files->each(function ($file) use ($disk) {
                    $disk->delete($file);
                });
            })
            ->map(function ($file) {
                return basename($file);
            });

        if ($deletedFiles->isNotEmpty()) {
            DB::table(env('DB_DATABASE') . '.files')->whereIn('name', $deletedFiles->all())->delete();
        }

        //  Delete database records that don't exist in GCS
        $currentFiles = collect(['reports/', 'archived/'])
            ->flatMap(function ($folder) use ($disk) {
                return $disk->allFiles($folder);
            })
            ->map(function ($file) {
                return basename($file);
            });

        DB::table(env('DB_DATABASE') . '.files')->whereNotIn('name', $currentFiles->all())->delete();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> failed
     * <Function> Logs job failure details.
     *
     * @param \Throwable $e
     * @return void
     */
    public function failed(Throwable $e): void
    {
        Log::error('RoomDeviceCounter Failed', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
