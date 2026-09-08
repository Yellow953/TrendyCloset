<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Maps the colour names carried on product variants to the hex the storefront
 * paints its swatches with. Unknown colours fall back to a neutral chip rather
 * than disappearing — a filter you cannot see is worse than an approximate one.
 */
class Swatch
{
    private const FALLBACK = '#d8cec7';

    /** @var array<string, string> lowercased colour name => hex */
    private const COLORS = [
        'beige' => '#e3d5bf',
        'black' => '#2b2523',
        'blue' => '#3b5c8f',
        'brown' => '#6b4423',
        'burgundy' => '#6d2439',
        'camel' => '#c19a6b',
        'coffee' => '#4b3621',
        'dark blue' => '#1c2f4d',
        'dark grey' => '#55524e',
        'grey' => '#9d9994',
        'khaki' => '#a89a72',
        'light blue' => '#a9c6e0',
        'light grey' => '#c7c4c0',
        'mint green' => '#a8d5ba',
        'navy' => '#1f2a44',
        'olive' => '#75774a',
        'pink' => '#e8b4c0',
        'red' => '#b32134',
        'white' => '#ffffff',
        'yellow' => '#e0b93c',
    ];

    public static function hex(?string $color): string
    {
        return self::COLORS[Str::lower(trim((string) $color))] ?? self::FALLBACK;
    }

    /**
     * Whether the swatch needs a border to be visible against a white page.
     */
    public static function needsOutline(?string $color): bool
    {
        return in_array(Str::lower(trim((string) $color)), ['white'], true);
    }

    /**
     * Every colour name this class can paint, Title Cased — the admin colour
     * select offers exactly this list, so nothing typed there ever falls back
     * to the neutral chip.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return collect(array_keys(self::COLORS))->map(fn ($c) => Str::title($c))->sort()->values()->all();
    }
}
