<?php

namespace App\Http\Controllers\Crew\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Requests\Order\UpdateStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Order\StoreRequest;
use App\Services\Order\PlaceOrderService;
use Illuminate\Http\RedirectResponse;
use App\Services\FlashMessage\FlashMessageService as Message;

class OrderController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct      (1.0) Constructor for initializing OrderService.
    // index            (2.0) The crew role's dashboard with realtime status.
    // updateStatus     (3.0) Update the status of the order.
    // show             (4.0) Load the order details of existing and deleted 
    //                        users.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing OrderService
     *
     */
    public function __construct(private PlaceOrderService $placeOrderService) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> The crew role's dashboard with realtime status.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort', 'direction', 'page']);

        return inertia('crew/order-history/Index', [
            'orders' => $this->placeOrderService->getOrders($filters),
            'filters' => (object) $filters,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updateStatus
     * <Function> Update the status of the order.
     *
     * @param  \App\Http\Requests\Order\UpdateStatusRequest $request
     * @param  \App\Models\Order                            $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(UpdateStatusRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->newStatus());

        return back()
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'Order status'])
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> show
     * <Function> Load the order details of existing and deleted users.
     *
     * @param  \App\Models\Order             $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        $orderDetailsOfExistingAndDeletedUser = $order->load([
            'room',
            'details.food',
            'user' => function ($query) {
                $query->withTrashed();
            },
        ]);

        return response()->json([
            'order' => $orderDetailsOfExistingAndDeletedUser
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> store
     * <Function> Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Order\StoreRequest $request
     * @param  \App\Services\Order\PlaceOrderService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request, PlaceOrderService $service): RedirectResponse
    {

        $service->save($request->validated());

        return back()
            ->with(Message::success(
                __('flash.added', ['attrib' => 'Food Order'])
            ));;
    }
}
