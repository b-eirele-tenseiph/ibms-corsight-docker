<?php

namespace App\Events\SwitchBot;

use App\Models\SwitchBotDevice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSwitchBotDeviceData implements ShouldBroadcastNow
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct      (1.0) Create a new event instance.
    // broadcastOn      (2.0) Get the channels the event should broadcast on.
    // broadcastAs      (3.0) The event's broadcast name.
    // broadcastWith    (4.0) Get the data to broadcast.

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param  \App\Models\SwitchBotDeviceData $device
     * @return void
     */
    public function __construct(
        protected SwitchBotDevice $device,
        protected array $dataCollection
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> broadcastOn
     * <Function> Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("admin.switch_bot_device"),
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
        return 'switch_bot_device_data.new';
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

        // Return grouped or plain data collection
        return $key ? [$key => $this->dataCollection] : $this->dataCollection;
    }
}
