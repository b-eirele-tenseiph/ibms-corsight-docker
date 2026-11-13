<?php

namespace App\Events\Corsight;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CorsightDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct    (1.0) Create a new event instance.
    // broadcastOn    (2.0) Get the channels the event should broadcast on.
    // broadcastAs    (3.0) The event's broadcast name.
    // broadcastWith  (4.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * <Notes> Carries just enough context to be self-describing on the wire;
     *         the dashboard treats this as a "data changed" signal and
     *         re-fetches the full payload from the corsight-data endpoint.
     *
     * @param  string  $room
     * @param  string  $eventType
     */
    public function __construct(
        protected string $room,
        protected string $eventType,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> broadcastOn
     * <Function> Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.corsight'),
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> broadcastAs
     * <Function> The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'corsight_data.new';
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> broadcastWith
     * <Function> Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'room' => $this->room,
            'event_type' => $this->eventType,
            'timestamp' => now()->toIso8601ZuluString(),
        ];
    }
}
