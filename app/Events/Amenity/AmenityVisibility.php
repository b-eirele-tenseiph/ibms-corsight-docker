<?php

namespace App\Events\Amenity;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class AmenityVisibility implements ShouldBroadcastNow
{
    use SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $amenityId          (1.0) The unique identifier of the amenity.
    // $isVisible          (1.1) The visibility state of the amenity.
    // __construct         (2.0) Create a new event instance.
    // broadcastOn         (3.0) Get the channels the event should broadcast on.
    // broadcastAs         (4.0) The event's broadcast name.
    // broadcastWith       (5.0) Get the data to broadcast.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $amenityId
     * <Function> The unique identifier of the amenity.
     *
     * @var string
     */
    public string $amenityId;

    /**
     * <Layer number> (1.1)
     *
     * <Processing name> $isVisible
     * <Function> The visibility state of the amenity.
     *
     * @var bool
     */
    public bool $isVisible;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param string $amenityId
     * @param bool   $isVisible
     * @return void
     */
    public function __construct(string $amenityId, bool $isVisible)
    {
        $this->amenityId = $amenityId;
        $this->isVisible = $isVisible;
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
        // generic private channel (see room-scoped variant below)
        return new PrivateChannel('amenities');
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
        return 'AmenityVisibility';
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
            'amenity_id' => $this->amenityId,
            'is_visible' => $this->isVisible,
        ];
    }
}
