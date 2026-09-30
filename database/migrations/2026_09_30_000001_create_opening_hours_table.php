<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One row per weekday (ISO: 1 = Monday), seeded with the hours that used to
 * live in config/store.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day')->unique();
            $table->string('opens_at', 5)->nullable();
            $table->string('closes_at', 5)->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        $now = now();
        $week = [
            1 => ['15:00', '20:00', false],
            2 => ['10:30', '20:00', false],
            3 => ['10:30', '20:00', false],
            4 => ['10:30', '20:00', false],
            5 => ['10:30', '20:00', false],
            6 => ['10:30', '20:00', false],
            7 => [null, null, true],
        ];

        DB::table('opening_hours')->insert(array_map(
            fn ($day, $row) => [
                'day' => $day,
                'opens_at' => $row[0],
                'closes_at' => $row[1],
                'is_closed' => $row[2],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            array_keys($week),
            $week,
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
