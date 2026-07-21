<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Registration is disabled — the admin account is created here.
        // Override with ADMIN_EMAIL / ADMIN_PASSWORD in .env.
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@trendycloset.com')],
            [
                'name' => env('ADMIN_NAME', 'Leila Konsol'),
                'password' => env('ADMIN_PASSWORD', 'password'),
            ],
        );

        $this->call(CatalogSeeder::class);
    }
}
