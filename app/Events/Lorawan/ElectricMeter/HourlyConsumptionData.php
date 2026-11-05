<?php

namespace App\Events\Lorawan\ElectricMeter;

use App\Models\HourlyConsumption;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HourlyConsumptionData implements ShouldBroadcast
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
     * @param \App\Models\HourlyConsumption $data
     * @return void
     */
    public function __construct(private HourlyConsumption $data) {}

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
        return new PrivateChannel('energy_consumption');
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
        return 'electric_meter.hourly_data';
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
            'id'     => $this->data->device->id,
            'hour'   => Carbon::parse($this->data->date)->format('Y-m-d H:i:s'),
            'alias'  => $this->data->consumption['type'],
            'value'  => $this->data->consumption['value'],
        ];
    }
}
