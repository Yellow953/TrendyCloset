<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

/**
 * Puts the three shipped hero slides into the database so the back office has
 * something to edit. Idempotent: a table with slides in it is left alone, so
 * this can be re-run over a live database without overwriting merchandising.
 */
class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        if (HeroSlide::query()->exists()) {
            return;
        }

        foreach (HeroSlide::defaults() as $slide) {
            HeroSlide::create($slide);
        }
    }
}
