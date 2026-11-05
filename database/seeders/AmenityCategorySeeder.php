<?php

namespace Database\Seeders;

use App\Models\AmenityCategory;
use Illuminate\Database\Seeder;

class AmenityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Accommodation and Rooms',
            'Dining and Beverages',
            'Wellness and Recreation',
            'Transportation Services',
            'Business and Events',
            'Entertainment and Leisure',
            'Retail and Convenience',
            'Outdoor and Scenic Areas',
            'Public Facilities',
            'Special Experiences',
        ];

        foreach ($categories as $category) {
            AmenityCategory::updateOrCreate(
                ['name' => $category],
                ['name' => $category]
            );
        }
    }
}
