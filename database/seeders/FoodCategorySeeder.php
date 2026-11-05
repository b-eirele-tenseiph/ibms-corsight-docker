<?php

namespace Database\Seeders;

use App\Models\FoodCategory;
use Illuminate\Database\Seeder;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (FoodCategory::CATEGORIES as $category) {
            FoodCategory::updateOrCreate([
                'name'        => $category,
                'description' => ''
            ]);
        }
    }
}
