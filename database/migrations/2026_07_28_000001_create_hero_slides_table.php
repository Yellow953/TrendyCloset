<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The rotating home-page hero. It used to be an editorial array on
 * StoreController; it is merchandising, so it belongs to whoever runs the shop.
 *
 * `image_url` is the single thing the view renders — an upload or a remote URL,
 * exactly as on `categories` — and `image_path` records the disk path only for
 * files we own, so deleting a slide can delete its file without ever touching
 * someone else's URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->string('accent')->nullable();
            $table->text('copy')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_credit')->nullable();
            $table->string('image_credit_href', 2048)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
