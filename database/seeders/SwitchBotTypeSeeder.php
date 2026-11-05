<?php

namespace Database\Seeders;

use App\Models\SwitchBotDevice;
use App\Models\SwitchBotHub;
use App\Models\SwitchBotRemote;
use App\Models\SwitchBotType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SwitchBotTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed SwitchBot Device types
        foreach (SwitchBotDevice::TYPES as $type) {
            SwitchBotType::updateOrCreate([
                'name' => $type,
                'category' => SwitchBotType::CATEGORY_DEVICE,
            ], [
                'description' => $type,
            ]);
        }

        // Seed SwitchBot Hub types
        foreach (SwitchBotHub::TYPES as $type) {
            SwitchBotType::updateOrCreate([
                'name' => $type,
                'category' => SwitchBotType::CATEGORY_HUB,
            ], [
                'description' => $type,
            ]);
        }

        // Seed SwitchBot Remote types
        foreach (SwitchBotRemote::TYPES as $type) {
            SwitchBotType::updateOrCreate([
                'name' => $type,
                'category' => SwitchBotType::CATEGORY_REMOTE,
            ], [
                'description' => $type,
            ]);
        }
    }
}
