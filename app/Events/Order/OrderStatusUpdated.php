<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $order          (1.0) The order instace will details.
    // __construct     (2.0) Create a new event instance.
    // broadcastOn     (3.0) Get the channels the event should broadcast on.
    // broadcastAs     (4.0) The event's broadcast name.
    // broadcastWith   (5.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $order
     * <Function> The order instace will details.
     *
     * @var \App\Models\Order
     */
    private Order $order;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param  \App\Models\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load(['room', 'user', 'details.food']);
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
        return new PrivateChannel("order-status-updated");
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
        return 'order.status.new';
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
        return [
            'order' => $this->order,
        ];
    }
}
