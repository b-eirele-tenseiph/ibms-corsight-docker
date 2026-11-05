<?php

namespace App\Console\Commands;

use App\Models\CorsightDeviceData;
use App\Models\CorsightPersonSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CorsightPrune extends Command
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $signature     (1.0) The name and signature of the console command.
    // $description   (2.0) The console command description.
    // handle         (3.0) Delete expired diary rows, clear expired visit
    //                      photos and delete expired person sessions.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $signature
     * <Function> The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corsight:prune {--dry-run : Report what would be pruned without deleting anything}';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $description
     * <Function> The console command description.
     *
     * @var string
     */
    protected $description = 'Apply the Corsight data retention policy (diary rows, visit photos, person sessions).';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> handle
     * <Function> Deletes diary rows older than corsight.retention.diary_days,
     *            clears visit photos older than corsight.retention.photo_days
     *            and deletes finished sessions older than
     *            corsight.retention.session_days. In-progress sessions are
     *            never touched.
     *
     */
    public function handle(): int
    {
        $diaryCutoff = now()->subDays((int) config('corsight.retention.diary_days', 90));
        $photoCutoff = now()->subDays((int) config('corsight.retention.photo_days', 90));
        $sessionCutoff = now()->subDays((int) config('corsight.retention.session_days', 365));

        $diaryRows = CorsightDeviceData::query()->where('received_at', '<', $diaryCutoff);
        $photos = CorsightPersonSession::query()
            ->where('status', '!=', 'in')
            ->whereNotNull('image')
            ->where('enter_at', '<', $photoCutoff);
        $sessions = CorsightPersonSession::query()
            ->where('status', '!=', 'in')
            ->where('enter_at', '<', $sessionCutoff);

        if ($this->option('dry-run')) {
            $this->info("Dry run — would delete {$diaryRows->count()} diary rows, "
                . "clear {$photos->count()} visit photos and delete {$sessions->count()} person sessions.");

            return self::SUCCESS;
        }

        $deletedDiary = $diaryRows->delete();
        $clearedPhotos = $photos->update(['image' => null]);
        $deletedSessions = $sessions->delete();

        $summary = [
            'diary_rows' => $deletedDiary,
            'photos_cleared' => $clearedPhotos,
            'sessions_deleted' => $deletedSessions,
        ];

        Log::info('Corsight retention prune completed.', $summary);
        $this->info("Pruned {$deletedDiary} diary rows, cleared {$clearedPhotos} visit photos, deleted {$deletedSessions} person sessions.");

        return self::SUCCESS;
    }
}
