<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use App\Models\ManufacturerModel;
use Illuminate\Database\Seeder;

class ManufacturerModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $milesight = Manufacturer::where('name', 'milesight')->get();
        foreach ($milesight as $manufacturer) {
            foreach (ManufacturerModel::MILESIGHT_MODELS as $model) {
                ManufacturerModel::updateOrCreate([
                    'manufacturer_id' => $manufacturer->id,
                    'name' => $model
                ]);
            }
        }

        $netvox = Manufacturer::where('name', 'netvox')->get();
        foreach ($netvox as $manufacturer) {
            foreach (ManufacturerModel::NETVOX_MODELS as $model) {
                ManufacturerModel::updateOrCreate([
                    'manufacturer_id' => $manufacturer->id,
                    'name' => $model
                ]);
            }
        }

        $abeeway = Manufacturer::where('name', 'abeeway')->get();
        foreach ($abeeway as $manufacturer) {
            foreach (ManufacturerModel::ABEEWAY_MODELS as $model) {
                ManufacturerModel::updateOrCreate([
                    'manufacturer_id' => $manufacturer->id,
                    'name' => $model
                ]);
            }
        }

        $remotelock = Manufacturer::where('name', 'remotelock')->get();
        foreach ($remotelock as $manufacturer) {
            foreach (ManufacturerModel::REMOTELOCK_MODELS as $model) {
                ManufacturerModel::updateOrCreate([
                    'manufacturer_id' => $manufacturer->id,
                    'name' => $model
                ]);
            }
        }

        $acrel = Manufacturer::where('name', 'acrel')->get();
        foreach ($acrel as $manufacturer) {
            foreach (ManufacturerModel::ACREL_MODELS as $model) {
                ManufacturerModel::updateOrCreate([
                    'manufacturer_id' => $manufacturer->id,
                    'name' => $model
                ]);
            }
        }
    }
}
