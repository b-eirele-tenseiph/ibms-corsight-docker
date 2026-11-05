<?php

namespace App\Services\Order;

use App\Events\Order\NewOrderPlaced;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlaceOrderService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $order             (1.0) The order model instance.
    // $orderDetails      (2.0) The mapped order details stored as a collection.
    // save               (3.0) Entry point for saving the order.
    // createOrder        (4.0) Create new order instance to be used on saving the
    //                          details.
    // mapOrderDetails    (5.0) Map array of data and generate actual details.
    // saveDetails        (6.0) Save the details using the order instance.
    // dispatchEvent      (7.0) Publish event for realtime processing.
    // getOrders          (8.0) Retrieve paginated list of order records.
    // getFoods           (9.0) Retrieve all available foods.
    // getFoodCategories  (10.0) Retrieve all food categories.
    // getUserOrders      (11.0) Get the order history for the user.
    // cancelOrder        (12.0) Cancel the selected order of the user
    //                           with remarks.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $order
     * <Function> The order model instance.
     *
     * @var \App\Models\Order
     */
    private Order $order;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $orderDetails
     * <Function> The mapped order details stored as a collection.
     *
     * @var \Illuminate\Support\Collection
     */
    private $orderDetails;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> save
     * <Function> Entry point for saving the order
     *
     * @param  array $data
     * @return void
     */
    public function save(array $data)
    {
        // Start a database transaction
        DB::beginTransaction();

        $this
            ->createOrder()
            ->mapOrderDetails($data)
            ->saveDetails($data)
            ->dispatchEvent();

        // Commit the transaction
        DB::commit();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> createOrder
     * <Function> Create new order instance to be used on saving the details.
     *
     * @return self
     */
    private function createOrder(): self
    {
        // Fetch logged in tenant user
        $user = auth()->user();

        // Create order for user
        $this->order = $user->orders()->create([
            'room_id' => $user->room->id,
            'status'  => 'new',
        ]);

        return $this;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> mapOrderDetails
     * <Function> Map array of data and generate actual details.
     *
     * @param  array $data
     * @return self
     */
    private function mapOrderDetails(array $data): self
    {
        $this->orderDetails = collect($data['orders'])->map(function ($item) {
            // Change this to eloquent
            $food = Food::find($item['id']);

            // Get quantity from request data
            $quantity = $item['quantity'];

            return [
                'food_id' => $food->id,
                'quantity' => $quantity,
                'unit_price' => $food->price,
                'total_amount' => $food->price * $quantity
            ];
        });

        return $this;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> saveDetails
     * <Function> Save the details using the order instance.
     *
     * @return self
     */
    private function saveDetails(): self
    {
        // Create details for the order here then save to db
        $this->order->details()->createMany($this->orderDetails);

        return $this;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> dispatchEvent
     * <Function> Publish event for realtime processing.
     *
     * @return self
     */
    private function dispatchEvent(): self
    {
        // Publish event of new order for crew dashboard
        NewOrderPlaced::dispatch($this->order);

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getOrders
     * <Function> Retrieve paginated list of order records.
     *
     * @param array $params
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrders(array $params)
    {
        [$search, $sort, $direction] = [
            $params['search'] ?? null,
            $params['sort'] ?? null,
            $params['direction'] ?? null,
        ];

        $orders = Order::with(['room', 'user', 'details.food'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere('orders.id', (int) $search);
                    }
                    $q->orWhereHas('room', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($sort === 'user', function ($query) use ($direction) {
                $query->leftJoin('users', 'users.id', '=', 'orders.user_id')
                    ->orderBy('users.name', $direction ?? 'asc')
                    ->select('orders.*'); // avoid ambiguous columns
            })
            ->when($sort === 'room_name', function ($query) use ($direction) {
                $query->leftJoin('rooms', 'rooms.id', '=', 'orders.room_id')
                    ->orderBy('rooms.name', $direction ?? 'asc')
                    ->select('orders.*'); // avoid ambiguous columns
            })
            ->when($sort === 'id', fn($q) => $q->orderBy('orders.id', $direction ?? 'desc'))
            ->when($sort === 'status', fn($q) => $q->orderBy('orders.status', $direction ?? 'asc'))
            ->paginate(12);

        return $orders;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getFoods
     * <Function> Retrieve all available foods.
     *
     * @return Collection
     */
    public function getFoods(): Collection
    {
        return Food::where('available', true)->get();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getFoodCategories
     * <Function> Retrieve all food categories.
     *
     * @return Collection
     */
    public function getFoodCategories(): Collection
    {
        return FoodCategory::all();
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getUserOrders
     * <Function> Get the order history for the user.
     *
     * @return Collection
     */
    public function getUserOrders(): Collection
    {
        return auth()->user()
            ->orders()
            ->latest()
            ->with(['room', 'user', 'details.food'])
            ->get();
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> cancelOrder
     * <Function> Cancel the selected order of the user with remarks.
     *
     * @param string $id
     * @param string $remarks
     * @return void
     *
     * @throws \Exception
     */
    public function cancelOrder(string $id, string $remarks)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);
        if ($order->status === 'new') {
            $order->update([
                'status' => 'cancelled',
                'remarks' => $remarks
            ]);
        }
    }
}
