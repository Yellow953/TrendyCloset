<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('pending');
            $table->string('email')->nullable();

            // Shipping details — the address is asked for a piece at a time at
            // checkout, so the back office can read a label off the order
            // instead of parsing one free-text line.
            $table->string('ship_name');
            $table->string('ship_phone');
            $table->string('ship_street');
            $table->string('ship_building')->nullable();
            $table->string('ship_floor', 60)->nullable();
            $table->string('ship_details')->nullable();
            $table->string('ship_city');
            $table->string('ship_region')->nullable();
            $table->string('ship_postcode')->nullable();
            $table->string('ship_country')->nullable();

            // Money
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            // The reporting screens filter by date, and usually by status with
            // it — every figure on the analytics page leans on one of these.
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
