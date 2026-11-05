<?php

namespace App\Jobs\Lorawan\Thermostat;

use App\Models\ClimateDataRecord;
use App\Models\LorawanDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class MonitorThermostatStatusData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy */
    /**************************************************************************/
    // __construct      (1.0) Initialize job with device and decoded rows.
    // handle           (2.0) Upsert an hourly climate record per tracked type.
    // hourBucket       (3.0) Compute the UTC hour bucket of a reading.
    // $recordedTypes   (4.0) The data types tracked for analysis.
    // failed           (5.0) Log job failure details.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initialize job with device and decoded rows.
     *
     * @param  Collection<string>  $rows
     */
    public function __construct(
        protected LorawanDevice $device,
        protected Collection $rows,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handle
     * <Function> Upsert an hourly climate record for every tracked data type
     *            carried by the uplink.
     */
    public function handle(): void
    {
        foreach ($this->rows as $row) {
            $type = $row['type'] ?? null;
            $value = $row['context']['value'] ?? null;

            if (! in_array($type, $this->recordedTypes, true) || $value === null) {
                continue;
            }

            $bucket = $this->hourBucket(Carbon::parse($row['received_at']));

            ClimateDataRecord::updateOrCreate(
                [
                    'device_id' => $this->device->id,
                    'date' => $bucket->format('Y-m-d H:i:s'),
                    'type' => $type,
                ],
                ['value' => (string) $value],
            )->touch();
        }
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> hourBucket
     * <Function> Compute the UTC hour bucket of a reading. Readings are
     *            stored in the next UTC hour, matching the electric meter's
     *            finalized-hour convention.
     */
    protected function hourBucket(Carbon $timestamp): Carbon
    {
        return $timestamp->utc()->startOfHour()->addHour();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $recordedTypes
     * <Function> The data types tracked for analysis.
     *
     * @var list<string>
     */
    protected array $recordedTypes = [
        'slave_power',
        'fan_speed',
        'temperature',
        'current_setpoint',
    ];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> failed
     * <Function> Log job failure details.
     */
    public function failed(Throwable $exception): void
    {
        // Error handling
        Log::error('Job Failed', [
            'task' => __CLASS__,
            'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ],
        ]);
    }
}
