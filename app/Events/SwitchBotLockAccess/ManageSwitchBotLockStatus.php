<?php

namespace App\Events\SwitchBotLockAccess;

use App\Models\SwitchBotLockAccess;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ManageSwitchBotLockStatus implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $data               (1.0) The data where the data collection belongs to.
    // __construct         (2.0) Create a new event instance.
    // broadcastOn         (3.0) Get the channels the event should broadcast on.
    // broadcastAs         (4.0) The event's broadcast name.
    // broadcastWith       (5.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $data
     * <Function> The data where the data collection belongs to.
     *
     * @var \App\Models\SwitchBotLockAccess
     */
    private $data;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param \App\Models\SwitchBotLockAccess $data
     * @return void
     */
    public function __construct(SwitchBotLockAccess $data)
    {
        $this->data = $data;
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
        return new PrivateChannel('admin.switch_bot_lock_access');
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
        return 'switch_bot_keypad.status';
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
        return [
            'device'   => [
                'user_id' => $this->data->user_id,
                'status' => $this->data->status
            ],
        ];
    }
}
