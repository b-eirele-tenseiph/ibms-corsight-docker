<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Manufacturer::updateOrCreate([
            'name' => Manufacturer::MFR_NETVOX,
            'prefix_regex' => "/^(00137a[0-9a-f]{10})$/i"
        ]);

        Manufacturer::updateOrCreate([
            'name' => Manufacturer::MFR_ABEEWAY,
            'prefix_regex' => "/^(20635f[0-9a-f]{10})$/i"
        ]);

        Manufacturer::updateOrCreate([
            'name' => Manufacturer::MFR_MILESIGHT,
            'prefix_regex' => "/^(24e124[0-9a-f]{10})$/i"
        ]);

        Manufacturer::updateOrCreate([
            'name' => Manufacturer::MFR_REMOTELOCK,
            'prefix_regex' => "/^(AC000W0[0-9a-f]{10})$/i"
        ]);


        Manufacturer::updateOrCreate([
            'name' => Manufacturer::MFR_ACREL,
            'prefix_regex' => "/^(ADW[0-9a-f]{10})$/i"
        ]);
    }
}
