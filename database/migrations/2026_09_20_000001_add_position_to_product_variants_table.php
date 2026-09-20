<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('color');
        });

        // Seed from the current (id) order so nothing visually reshuffles
        // until an admin actually drags a row.
        DB::table('product_variants')
            ->select('id', 'product_id')
            ->orderBy('product_id')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id')
            ->each(function ($variants) {
                foreach ($variants->values() as $i => $variant) {
                    DB::table('product_variants')->where('id', $variant->id)->update(['position' => $i + 1]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
