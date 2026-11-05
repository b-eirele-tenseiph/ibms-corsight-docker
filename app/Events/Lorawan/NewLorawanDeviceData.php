<?php

namespace App\Events\Lorawan;

use App\Models\LorawanDevice;
use Illuminate\Broadcasting\{InteractsWithSockets, PrivateChannel};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class NewLorawanDeviceData implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct            (1.0) Create a new event instance.
    // broadcastOn            (2.0) Get the channels the event should broadcast
    //                              on.
    // broadcastAs            (3.0) The event's broadcast name.
    // broadcastWith          (4.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param \App\Models\LorawanDevice $device
     * @param \Illuminate\Support\Collection $dataCollection
     *
     */
    public function __construct(
        protected LorawanDevice $device,
        protected Collection $dataCollection
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
            new PrivateChannel('admin.lorawan_device'),
            new PrivateChannel("room.{$this->device->room_id}")
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
        return 'lorawan_device_data.new';
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
        // Find classification key matching the device model
        $key = $this->device->classification;

        $data = $this->dataCollection->toArray();

        // Return grouped or plain data collection
        return $key ? [$key => $data] : $data;
    }
}
