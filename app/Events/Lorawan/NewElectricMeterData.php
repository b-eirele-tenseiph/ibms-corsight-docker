<?php

namespace App\Events\Lorawan;

use App\Models\LorawanDevice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewElectricMeterData implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
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
     *
     * @param \App\Models\LorawanDevice  $device
     * @param \Illuminate\Support\Collection  $dataCollection
     * @return void
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
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): PrivateChannel
    {
        // Broadcast to admin controls
        return new PrivateChannel('admin.electric_meter');
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
        return 'electric_meter_data.new';
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> broadcastWith
     * <Function> Get the data to broadcast.
     *
     * @return void
     */
    public function broadcastWith(): array
    {
        // Find classification key matching the device model
        $key = $this->device->classification;

        // Return grouped or plain data collection
        return $key ? [$key => $this->dataCollection->toArray()] : $this->dataCollection->toArray();
    }
}
