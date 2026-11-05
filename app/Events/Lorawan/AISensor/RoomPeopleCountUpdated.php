<?php

namespace App\Events\Lorawan\AISensor;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

// AI Sensor configured as region_people_counter
class RoomPeopleCountUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct     (1.0) Create a new event instance.
    // broadcastOn     (2.0) Get the channels the event should broadcast on.
    // broadcastAs     (3.0) The event's broadcast name.
    // broadcastWith   (4.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param  array $data
     */
    public function __construct(
        protected array $data
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> broadcastOn
     * <Function> Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel("admin.room_people_count")
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
    public function broadcastAs()
    {
        return 'people_count_data.new'; //listen to front end 
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> broadcastWith
     * <Function> Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->data;
    }
}
