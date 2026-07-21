<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_id', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['product_id', 'visitor_id']);
            $table->index('visitor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_favorites');
    }
};
