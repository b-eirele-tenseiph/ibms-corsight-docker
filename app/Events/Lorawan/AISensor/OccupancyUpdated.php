<?php

namespace App\Events\Lorawan\AISensor;

use App\Models\DailyOccupancy;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// AI Sensor configured as line_crossing_counter
class OccupancyUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $occupancy          (1.0) The newly updated occupancy data.
    // __construct         (2.0) Create a new event instance.
    // broadcastOn         (3.0) Get the channels the event should broadcast on.
    // broadcastAs         (4.0) The event's broadcast name.
    // broadcastWith       (5.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $data
     * <Function> The newly updated occupancy data.
     *
     * @var \App\Models\DailyOccupancy
     */
    private $occupancy;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param \App\Models\DailyOccupancy $occupancy
     * @return void
     */
    public function __construct(DailyOccupancy $occupancy)
    {
        $this->occupancy = $occupancy;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> broadcastOn
     * <Function> Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("room.{$this->occupancy->room_id}");
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> broadcastAs
     * <Function> The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'occupancy.updated';
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> broadcastWith
     * <Function> Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->occupancy->toArray();
    }
}
