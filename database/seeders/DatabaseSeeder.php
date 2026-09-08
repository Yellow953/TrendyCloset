<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@admin.com'],
            [
                'name' => 'Admin',
                'password' => 'qwe123',
                'role' => UserRole::Admin,
            ],
        );

        $this->call(CatalogSeeder::class);
        $this->call(HeroSlideSeeder::class);
    }
}
