<?php

namespace App\Events\SwitchBot;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourcesSyncing implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $resource        (1.0) The switch bot type of resource.
    // $message         (2.0) The current status of syncing as message.
    // __construct      (3.0) Create a new event instance.
    // broadcastOn      (4.0) Get the channels the event should broadcast on.
    // broadcastAs      (5.0) The event's broadcast name.
    // broadcastWith    (6.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $resource
     * <Function> The switch bot type of resource.
     *
     * @var String
     */
    protected String $resource;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $message
     * <Function> The current status of syncing as message.
     *
     * @var String
     */
    protected String $message;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @return void
     */
    public function __construct(String $resource, String $message)
    {
        $this->resource = $resource;
        $this->message = $message;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> broadcastOn
     * <Function> Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('admin');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> broadcastAs
     * <Function> The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return "switchbot.{$this->resource}.resources.syncing";
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> broadcastWith
     * <Function> Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }
}
