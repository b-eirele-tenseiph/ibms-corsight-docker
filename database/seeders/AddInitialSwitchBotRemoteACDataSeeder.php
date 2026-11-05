<?php

namespace Database\Seeders;

use App\Models\SwitchBotRemote;
use App\Models\SwitchBotRemoteData;
use Illuminate\Database\Seeder;

class AddInitialSwitchBotRemoteACDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $context = config('settings.sb_ac_default');

        SwitchBotRemote::ofType('Air Conditioner')->get()->each(function ($remote) use ($context) {
            $remote->data()->create([
                'via' => SwitchBotRemoteData::VIA_API,
                'context' => $context,
                'received_at' => now()
            ]);

            SwitchBotRemote::withoutEvents(function () use ($remote, $context) {
                $remote->latestData()->updateOrCreate(
                    [
                        'device_type' => get_class($remote),
                        'device_id' => $remote->id
                    ],
                    ['context' => $context]
                );
            });
        });
    }
}
