<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Notifications\AdminNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationFactory extends Factory
{
    protected $model = DatabaseNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid,
            'type' => AdminNotification::class,
            'notifiable_type' => Admin::class,
            'notifiable_id' => Admin::factory(),
            'data' => [
                'nature' => 'information',
                'title' => 'Room Access',
                'message' => '[SE Room] : A Guest has logged in.',
                'url' => route('admin.notifications', [
                    'search'    => $this->faker->numberBetween(100, 200),
                    'end_date'  => now()->toDateString(),
                    'expanded'  => $this->faker->boolean()
                ])
            ],
            'read_at' => null,
        ];
    }
}
