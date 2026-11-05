<?php

namespace App\Observers\Order;

use App\Events\Order\NewOrderPlaced as OrderUpdatedForCrew;
use App\Events\Order\OrderStatusUpdated as OrderUpdatedForUser;
use App\Models\Activity;
use App\Models\Admin;
use App\Models\Order;
use App\Notifications\UserNotification;
use App\Traits\Notification\ShouldNotifyAdmin;

class OrderObserver
{
    use ShouldNotifyAdmin;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // // order           (1.0) Stores the current Order instance
    //                          for notification use.
    // created            (2.0) Fired after an Order is created.
    // updated            (3.0) Fired after an Order is updated.
    // notification       (4.0) Implementation of the notification data array.
    // notifiables        (5.0) Override trait to target specific crew members.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> order
     * <Function> Stores the current Order instance for notification usage.
     *
     */
    protected Order $order;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> created
     * <Function> Fired after an Order is created.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order): void
    {
        // Ensure status is "new" on create
        if (empty($order->status)) {
            $order->forceFill(['status' => 'new'])->saveQuietly();
        }

        // Store order instance temporarily for use in the notification method
        $this->order = $order;

        // Use trait to send notification to crew
        $this->sendAdminNotification();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updated
     * <Function> Fired after an Order is updated.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $this->order = $order;
        if ($order->status === 'cancelled') {
            if (!auth('admin')->check()) {
                $this->sendAdminNotification();
            }

            $order->user?->notify(new UserNotification([
                'nature'  => Activity::TYPE_FOOD,
                'title'   => __('notification.orders.order_cancelled'),
                'message' => __('messages.information.I009.message', [
                    'order'   => $order->id,
                    'user'    => auth('admin')->check() ? __('defaults.crew_staff') : 'You',
                    'remarks' => $order->remarks
                ]),
                'url'     => route('user.restaurant.index', ['order_id' => $order->id]),
            ]));
        }

        $statusMap = [
            'on cook'   => ['title' => 'order_preparing', 'msg' => 'I004'],
            'completed' => ['title' => 'order_completed', 'msg' => 'I005'],
        ];
        if (isset($statusMap[$order->status])) {
            $config = $statusMap[$order->status];

            $titleKey = "notification.orders.{$config['title']}";
            $messageKey = "messages.information.{$config['msg']}.message";

            $order->user?->notify(new UserNotification([
                'nature'  => Activity::TYPE_FOOD,
                'title'   => __($titleKey),
                'message' => __($messageKey, ['order' => $order->id]),
                'url'     => route('user.restaurant.index', ['order_id' => $order->id]),
            ]));
        }

        OrderUpdatedForCrew::dispatch($order);
        OrderUpdatedForUser::dispatch($order);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> notification
     * <Function> Implementation of the notification data array.
     *
     * @return array
     */
    protected function notification(): array
    {
        if ($this->order->status === 'cancelled') {
            return [
                'nature'  => Activity::TYPE_FOOD,
                'title'   => __('notification.orders.order_cancelled'),
                'message' => __('messages.information.I009.message', [
                    'order'   => $this->order->id,
                    'user'    => $this->order->user?->name ?? 'User',
                    'remarks' => $this->order->remarks ?? 'No reason provided',
                ]),
                'url'     => route('crew.orders.history', ['search' => $this->order->id]),
            ];
        }

        return [
            'nature'  => Activity::TYPE_FOOD,
            'title'   => __('notification.orders.new_order_placed'),
            'message' => __('messages.information.I003.message', [
                'order' => $this->order->id,
                'user'  => $this->order->user?->name ?? 'Guest',
            ]),
            'url'     => route('crew.orders.history', ['search' => $this->order->id]),
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> notifiables
     * <Function> Override default to notify only users with the crew role.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function notifiables()
    {
        return Admin::whereHas('roles', function ($query) {
            $query->where('name', 'crew');
        })->get();
    }
}
