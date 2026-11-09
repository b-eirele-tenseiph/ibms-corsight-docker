<?php

namespace App\Jobs\Corsight;

use App\Events\Corsight\CorsightDataUpdated;
use App\Models\CorsightDevice;
use App\Models\CorsightDeviceData;
use App\Services\Corsight\CorsightEventProjector;
use App\Services\Corsight\PersonSessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandleCorsightEventData implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct    (1.0) Initialize the job with the resolved device
    //                      (nullable), raw event payload and identifiers.
    // handle         (2.0) Persist the event only for a registered device
    //                      with storage enabled; unmatched labels are
    //                      dropped.
    // failed         (3.0) Handle a job failure.
    // isDuplicate    (4.0) Check whether the event_id was already stored.
    // persist        (5.0) Write the slim diary row and sync the person
    //                      session (appearance / camera_status events).

    // <Notes> The projector and session service are injected into handle()
    //         by the container — dispatch() passes constructor arguments
    //         positionally, so services must not be constructor params.

    use Dispatchable, Queueable;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initialize the job with the resolved device
     *            (nullable), raw event payload and identifiers.
     *
     * @param  \App\Models\CorsightDevice|null  $device
     * @param  array  $payload
     * @param  string|null  $eventId
     * @param  string|null  $type
     * @param  string|null  $cameraId
     */
    public function __construct(
        protected ?CorsightDevice $device,
        protected array $payload,
        protected ?string $eventId,
        protected ?string $type,
        protected ?string $cameraId,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handle
     * <Function> Persist the event only for a registered device with
     *            storage enabled, unless the event_id is already stored.
     *
     * <Notes> A registered camera with data storage disabled is ack'd
     *         but not stored. A camera label that matches no device is
     *         dropped.
     *
     * @param  \App\Services\Corsight\CorsightEventProjector  $projector
     * @param  \App\Services\Corsight\PersonSessionService  $sessions
     * @return void
     */
    public function handle(
        CorsightEventProjector $projector,
        PersonSessionService $sessions
    ): void {
        if (! $this->device) {
            return;
        }

        if (! $this->device->enable_data_storage) {
            return;
        }

        if ($this->isDuplicate()) {
            return;
        }

        $this->persist($projector, $sessions);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Job Failed', [
            'task' => __CLASS__,
            'event_id' => $this->eventId,
            'camera_id' => $this->cameraId,
            'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> isDuplicate
     * <Function> Check whether the event_id was already stored.
     *
     * <Notes> The daemon replays events from its retry queue, so a repeated
     *         event_id is an expected duplicate, not a new event.
     *
     * @return bool
     */
    private function isDuplicate(): bool
    {
        return filled($this->eventId)
            && CorsightDeviceData::where('event_id', $this->eventId)->exists();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> persist
     * <Function> Write the slim diary row and sync the person session.
     *
     * <Notes> Both writes run in one transaction: if the session sync
     *         fails, the diary row rolls back too, so a daemon retry can
     *         reprocess the event instead of getting caught by the
     *         duplicate gate. Appearance and camera_status events carry
     *         the session logic; any other event type is not stored.
     *
     * @param  \App\Services\Corsight\CorsightEventProjector  $projector
     * @param  \App\Services\Corsight\PersonSessionService  $sessions
     * @return void
     */
    private function persist(CorsightEventProjector $projector, PersonSessionService $sessions): void
    {
        $projected = $projector->project($this->payload);

        if ($projected === null) {
            return;
        }

        DB::transaction(function () use ($projected, $sessions) {
            CorsightDeviceData::create([
                'event_id' => $this->eventId,
                'camera_id' => $this->cameraId ?? '',
                'device_id' => $this->device?->id,
                'event_type' => $this->type ?? 'unknown',
                'context' => $projected,
                'received_at' => now(),
            ]);

            match ($this->type) {
                'appearance' => $sessions->handleAppearance($this->device, $this->payload),
                'camera_status' => $sessions->handleCameraStatus($this->device, $this->payload),
                default => null,
            };
        });

        // Notify connected CCTV dashboards that fresh data is available so
        // they can refresh without a manual page reload.
        CorsightDataUpdated::dispatch(
            $this->device->room?->name ?? $this->device->name,
            $this->type ?? 'unknown',
        );
    }
}
