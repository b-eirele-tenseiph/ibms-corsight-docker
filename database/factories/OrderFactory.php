<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Define your order attributes here
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            // Add a default value for status
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the order has details.
     *
     * @param int $count
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hasDetails($count = 3, $attributes = [])
    {
        return $this->has(
            Order::factory()->count($count)->state($attributes),
            'details'
        );
    }
}
