<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 48);
            $table->string('visitor_id', 64);
            $table->string('session_id', 32)->nullable();
            $table->string('path', 255);
            $table->string('referrer_host', 128)->nullable();
            $table->string('referrer', 512)->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['name', 'created_at']);
            $table->index(['visitor_id', 'created_at']);
            $table->index(['session_id', 'created_at']);
            $table->index(['path', 'created_at']);
            $table->index(['referrer_host', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_events');
    }
};
