<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            RolesAndPermissionsSeeder::class,
            ManufacturerSeeder::class,
            ManufacturerModelSeeder::class,
            SwitchBotTypeSeeder::class,
            AddInitialSwitchBotRemoteACDataSeeder::class,
            AmenityCategorySeeder::class,
            FoodCategorySeeder::class,
        ]);
    }
}
