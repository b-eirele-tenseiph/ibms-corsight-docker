<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::query()->delete();

        Admin::factory()->state([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Tensei PH Super Admin',
            'username' => 'tp_super_admin',
            'enabled' => true,
            'email' => 'tpadmin@example.com',
            'password' => Hash::make('Tensei001@')
        ])->create();
    }
}
