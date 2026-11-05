<?php

namespace App\Http\Controllers\Crew\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Http\Requests\Order\CancelOrderRequest;

class DashboardController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // index          (1.0) The crew role's dashboard with realtime status.
    // cancel         (2.0) Cancel an order with a reason (remarks).

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> index
     * <Function> The crew role's dashboard with realtime status.
     *
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $orders = Order::with(['room', 'user', 'details.food'])
            ->whereDate('created_at', now())
            ->get();

        return inertia('crew/dashboard/Index')->with(compact('orders'));
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> cancel
     * <Function> Cancel an order with a reason (remarks).
     *
     * @param \App\Http\Requests\Order\CancelOrderRequest $request
     */
    public function cancel(CancelOrderRequest $request, Order $order)
    {
        $order->update([
            'status'  => 'cancelled',
            'remarks' => $request->validated()['remarks'],
        ]);

        return back()->with(
            Message::success(__('flash.updated', ['attrib' => 'Order cancellation']))
        );
    }
}
