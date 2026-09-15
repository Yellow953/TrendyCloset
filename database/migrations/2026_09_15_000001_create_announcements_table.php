<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lines of the header's pre-header bar, managed from the back office. Several
 * can be live at once — the bar rotates through them — rather than the one
 * hard-coded line (or the live Offer's headline) it used to show.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('message', 200);
            $table->string('link_label', 40)->nullable();
            $table->string('link_url', 2048)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
