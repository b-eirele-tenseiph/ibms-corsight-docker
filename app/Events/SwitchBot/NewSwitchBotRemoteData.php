<?php

namespace App\Events\SwitchBot;

use App\Models\SwitchBotRemote;
use App\Models\SwitchBotRemoteData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSwitchBotRemoteData implements ShouldBroadcast
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
     * @param  \App\Models\SwitchBotRemoteData $remote
     * @return void
     */
    public function __construct(
        protected SwitchBotRemote $remote,
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
            new PrivateChannel("admin.switch_bot_remote"),
            new PrivateChannel("room.{$this->remote->room->id}")
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
        return 'switch_bot_remote_data.new';
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
        $key = $this->remote->classification;

        // Return grouped or plain data collection
        return $key ? [$key => $this->dataCollection] : $this->dataCollection;
    }
}
