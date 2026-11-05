<?php

namespace App\Events\Monitoring;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActiveRoomUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $data           (1.0) The SwitchBotDeviceData model instance.
    // __construct     (2.0) Create a new event instance.
    // broadcastOn     (3.0) Get the channels the event should broadcast on.
    // broadcastAs     (4.0) The event's broadcast name.
    // broadcastWith   (5.0) Get the data to broadcast.

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @return void
     */
    public function __construct(private array $data) {}

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> broadcastOn
     * <Function> Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.room.active')
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> broadcastAs
     * <Function> The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'active_area.new';
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> broadcastWith
     * <Function> Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return $this->data;
    }
}
