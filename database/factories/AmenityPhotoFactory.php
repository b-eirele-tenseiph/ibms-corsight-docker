<?php

namespace Database\Factories;

use App\Models\Amenity;
use App\Models\AmenityPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AmenityPhotoFactory extends Factory
{
    protected $model = AmenityPhoto::class;

    public function definition(): array
    {
        return [
            // will create an Amenity (UUID) and use its id
            'amenity_id' => Amenity::factory(),
            // simple fake path (no real file needed for tests)
            'file_path'  => 'amenities/photos/' . Str::uuid() . '.jpg',
        ];
    }

    /**
     * Bind the photo to an existing Amenity.
     */
    public function forAmenity(Amenity $amenity): self
    {
        return $this->state(fn() => ['amenity_id' => $amenity->id]);
    }
}
