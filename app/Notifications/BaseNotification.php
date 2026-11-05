<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BaseNotification extends Notification
{
    use Queueable;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $data            (1.0) Notification data array.
    // __construct      (2.0) Construct BaseNotification with data array.
    // via              (3.0) Get the notification's delivery channels.
    // toArray          (4.0) Get the array representation of the notification.
    // toDatabase       (5.0) Get the database representation of the notification.
    // toBroadcast      (6.0) Get the broadcast representation of the notification.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $data
     * <Function> Notification data array.
     *
     * @var array
     */
    private array $data;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Construct BaseNotification with data array.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> via
     * <Function> Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> toArray
     * <Function> Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> toDatabase
     * <Function> Get the database representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> toBroadcast
     * <Function> Get the broadcast representation of the notification.
     *            Wraps data in the same structure as the database payload
     *            so the frontend receives a consistent format.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'         => $this->id,
            'type'       => static::class,
            'read_at'    => null,
            'created_at' => now()->toISOString(),
            'data'       => $this->toArray($notifiable),
        ]);
    }
}
