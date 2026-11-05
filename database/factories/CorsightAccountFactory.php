<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CorsightAccount>
 */
class CorsightAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName,
            'password' => $this->faker->password,
            'endpoint_secret'    => bin2hex(random_bytes(32)),
            'endpoint_url'       => $this->faker->url,
        ];
    }
}
