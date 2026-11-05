<?php

namespace App\Events\Lorawan\Climate;

use App\Models\ClimateDataRecord;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClimateDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy */
    /**************************************************************************/
    // __construct         (1.0) Create a new event instance.
    // broadcastOn         (2.0) Get the channels the event should broadcast on.
    // broadcastAs         (3.0) The event's broadcast name.
    // broadcastWith       (4.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     */
    public function __construct(
        protected ClimateDataRecord $record,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> broadcastOn
     * <Function> Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.climate_data'),
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> broadcastAs
     * <Function> The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'climate_data.' . $this->record->type . '.new';
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> broadcastWith
     * <Function> Get the data to broadcast. The label is the full bucket key
     *            converted from the stored UTC instant to the display
     *            timezone, so the chart can match it against any displayed
     *            range.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->record->device?->room_id,
            'room_area_id' => $this->record->device?->room_area_id,
            'labels' => $this->record->date
                ->copy()
                ->tz('Asia/Manila')
                ->format('Y-m-d H:i'),
            'value' => $this->record->value,
        ];
    }
}
