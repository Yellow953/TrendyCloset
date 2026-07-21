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
        'black' => '#2b2523',
        'blush' => '#d9b3a7',
        'camel' => '#c19a6b',
        'champagne' => '#e7d3b8',
        'charcoal' => '#4a4744',
        'ecru' => '#efe6d7',
        'grey' => '#9d9994',
        'indigo' => '#38496b',
        'khaki' => '#a89a72',
        'light wash' => '#b9cbdd',
        'oat' => '#ded2be',
        'rust' => '#a8552f',
        'sage' => '#8a9a8e',
        'sand' => '#d9c7ab',
        'terracotta' => '#c06a4c',
        'vintage blue' => '#7b95b5',
        'white' => '#ffffff',
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
        return in_array(Str::lower(trim((string) $color)), ['white', 'ecru', 'champagne'], true);
    }
}
