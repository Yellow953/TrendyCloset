<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

/**
 * Seeds the client's starting colour list: the names the storefront already
 * shipped with (formerly hard-coded in Swatch.php), plus every name
 * CatalogSeeder/ProductVariantFactory already write onto variants — so the
 * admin's strict colour select never leaves an existing variant without a
 * valid option. Idempotent via updateOrCreate on name.
 */
class ColorSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'Aubergine', 'hex' => '#3d1f2e'],
            ['name' => 'Beige', 'hex' => '#e3d5bf'],
            ['name' => 'Black', 'hex' => '#2b2523'],
            ['name' => 'Blue', 'hex' => '#3b5c8f'],
            ['name' => 'Brown', 'hex' => '#6b4423'],
            ['name' => 'Burgundy', 'hex' => '#6d2439'],
            ['name' => 'Camel', 'hex' => '#c19a6b'],
            ['name' => 'Coffee', 'hex' => '#4b3621'],
            ['name' => 'Dark Blue', 'hex' => '#1c2f4d'],
            ['name' => 'Dark Green', 'hex' => '#2f4a35'],
            ['name' => 'Dark Grey', 'hex' => '#55524e'],
            ['name' => 'Dark Purple', 'hex' => '#4a2c53'],
            ['name' => 'Green', 'hex' => '#4b7a52'],
            ['name' => 'Greige', 'hex' => '#ada699'],
            ['name' => 'Grey', 'hex' => '#9d9994'],
            ['name' => 'Khaki', 'hex' => '#a89a72'],
            ['name' => 'Light Beige', 'hex' => '#efe6d5'],
            ['name' => 'Light Blue', 'hex' => '#a9c6e0'],
            ['name' => 'Light Green', 'hex' => '#a3c9a0'],
            ['name' => 'Light Grey', 'hex' => '#c7c4c0'],
            ['name' => 'Light Purple', 'hex' => '#c3aed6'],
            ['name' => 'Mint Green', 'hex' => '#a8d5ba'],
            ['name' => 'Mocha', 'hex' => '#8a6a52'],
            ['name' => 'Navy', 'hex' => '#1f2a44'],
            ['name' => 'Olive', 'hex' => '#75774a'],
            ['name' => 'Pink', 'hex' => '#e8b4c0'],
            ['name' => 'Red', 'hex' => '#b32134'],
            ['name' => 'Taupe', 'hex' => '#8b7d6b'],
            ['name' => 'White', 'hex' => '#ffffff'],
            ['name' => 'Yellow', 'hex' => '#e0b93c'],

            // Used by CatalogSeeder/ProductVariantFactory but never in the old
            // Swatch::COLORS constant — these silently rendered the neutral
            // fallback chip before this table existed.
            ['name' => 'Blush', 'hex' => '#f0d5d0'],
            ['name' => 'Champagne', 'hex' => '#f0e2c0'],
            ['name' => 'Charcoal', 'hex' => '#3a3a3a'],
            ['name' => 'Clay', 'hex' => '#b06a4f'],
            ['name' => 'Crimson', 'hex' => '#9e1b32'],
            ['name' => 'Ecru', 'hex' => '#e6dfc8'],
            ['name' => 'Forest', 'hex' => '#25402b'],
            ['name' => 'Gold', 'hex' => '#d4af37'],
            ['name' => 'Indigo', 'hex' => '#2e2d5e'],
            ['name' => 'Light Wash', 'hex' => '#a7bcd4'],
            ['name' => 'Oat', 'hex' => '#ddd0b8'],
            ['name' => 'Powder Blue', 'hex' => '#b8d4e3'],
            ['name' => 'Rust', 'hex' => '#a8461f'],
            ['name' => 'Sage', 'hex' => '#9caf88'],
            ['name' => 'Sand', 'hex' => '#d9c7a3'],
            ['name' => 'Sky', 'hex' => '#87ceeb'],
            ['name' => 'Stone', 'hex' => '#a8a397'],
            ['name' => 'Terracotta', 'hex' => '#c1653c'],
            ['name' => 'Vintage Blue', 'hex' => '#5b7c99'],
        ])->values()->each(fn (array $row, int $i) => Color::updateOrCreate(
            ['name' => $row['name']],
            ['hex' => $row['hex'], 'position' => $i, 'is_active' => true],
        ));
    }
}
