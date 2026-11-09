<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('ADMIN_SEED_PASSWORD');

        if (! $password) {
            if (! app()->environment('local', 'testing')) {
                throw new \RuntimeException('Set ADMIN_SEED_PASSWORD in .env before seeding the admin account.');
            }

            $password = 'Tensei001@';
        }

        Admin::unguarded(fn () => Admin::firstOrCreate(
            ['username' => 'tp_super_admin'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Tensei PH Super Admin',
                'email' => 'tpadmin@example.com',
                'password' => Hash::make($password),
                'enabled' => true,
                'email_verified_at' => now(),
            ]
        ));
    }
}
