<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogue imagery can now be uploaded from the back office as well as pasted
 * in as a URL (the seeded Unsplash art). `url` stays the single thing the views
 * render; these columns record the disk path *only* for images we own, so
 * deleting a row can delete the file without ever touching a remote URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('disk_path')->nullable()->after('url');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('disk_path');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
