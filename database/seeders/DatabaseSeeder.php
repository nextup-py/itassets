<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@itassets.test'),
        ], [
            'name'     => env('ADMIN_NAME', 'Admin'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
        ]);

        Setting::set('exchange_rate_usd_pyg', 6500);

        $this->call([
            RoleSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
