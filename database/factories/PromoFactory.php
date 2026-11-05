<?php
// database/factories/PromoFactory.php

namespace Database\Factories;

use App\Models\Promo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promo>
 */
class PromoFactory extends Factory
{
    protected $model = Promo::class;

    public function definition(): array
    {
        return [
            'title'       => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'photo_file_path' => 'images/promos/' . $this->faker->unique()->lexify('promo_????.jpg'),
            'valid_start_date' => now(),
            'valid_end_date' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }

    public function relativeImage(?string $path = null): self
    {
        return $this->state(fn() => [
            'photo_file_path' => $path ?? ('promos/' . $this->faker->unique()->word() . '.jpg'),
        ]);
    }

    public function absoluteImage(?string $url = null): self
    {
        return $this->state(fn() => [
            'photo_file_path' => $url ?? $this->faker->imageUrl(800, 600, 'hotel', true),
        ]);
    }
}
