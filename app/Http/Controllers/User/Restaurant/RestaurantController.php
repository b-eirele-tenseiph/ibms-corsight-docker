<?php

namespace App\Http\Controllers\User\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Services\Order\PlaceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RestaurantController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct      (1.0) Delegate Services across controller.
    // index            (2.0) Display the restaurant/order history page.
    // store            (3.0) Store a newly created food order in storage.
    // cancel           (4.0) Update the order status (Cancel order)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Delegate Services across controller
     *
     * @param \App\Services\Order\PlaceOrderService $placeOrderService
     */
    public function __construct(
        protected PlaceOrderService $placeOrderService
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display the restaurant page
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $orders = $this->placeOrderService->getUserOrders();

        // If Axios calls this, return just the JSON data
        if ($request->wantsJson()) {
            return response()->json($orders);
        }

        // Otherwise, return the full Inertia page
        return Inertia::render('user/restaurant/Index', [
            'foods' => $this->placeOrderService->getFoods(),
            'foodCategories' => $this->placeOrderService->getFoodCategories(),
            'orders' => $orders
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Order\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->placeOrderService->save($request->validated());

        return back()->with(
            Message::success(__('flash.added', ['attrib' => 'Food Order']))
        );
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> cancel
     * <Function> Update the order status (Cancel order)
     *
     * @param \App\Http\Requests\Order\CancelOrderRequest $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(CancelOrderRequest $request, string $id): RedirectResponse
    {
        $this->placeOrderService->cancelOrder($id, $request->remarks);

        return back()->with(
            Message::success(__('flash.updated', ['attrib' => 'Order cancellation']))
        );
    }
}
