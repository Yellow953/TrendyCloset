<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

/**
 * Puts the shop's standing delivery line into the database as an editable
 * announcement. Idempotent: a table with announcements in it is left alone.
 */
class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        if (Announcement::query()->exists()) {
            return;
        }

        $fallback = Announcement::fallback();

        Announcement::create([
            'message' => $fallback->message,
            'link_label' => $fallback->link_label,
            'link_url' => $fallback->link_url,
            'position' => 0,
            'is_active' => true,
        ]);
    }
}
