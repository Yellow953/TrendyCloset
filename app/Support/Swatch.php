<?php

namespace App\Support;

use App\Models\Color;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Maps the colour names carried on product variants to the hex the storefront
 * paints its swatches with. The client manages the list itself (`Color`, the
 * "Colors" admin screen); unknown colours fall back to a neutral chip rather
 * than disappearing — a filter you cannot see is worse than an approximate one.
 */
class Swatch
{
    private const FALLBACK = '#d8cec7';

    /**
     * Every colour ever saved, active or not — hex()/needsOutline() must still
     * resolve a variant whose colour was since deactivated. Cached forever and
     * busted by Color::booted() on save/delete.
     *
     * Cached as a plain array of scalars rather than the Eloquent collection
     * itself — the database cache store round-trips through serialize(), and
     * an array needs no class autoloading to come back out intact.
     *
     * @return Collection<int, array{name: string, hex: string, is_active: bool}>
     */
    private static function all(): Collection
    {
        return collect(Cache::rememberForever(
            'colors.map',
            fn () => Color::query()->get(['name', 'hex', 'is_active'])
                ->map(fn (Color $c) => ['name' => $c->name, 'hex' => $c->hex, 'is_active' => $c->is_active])
                ->all(),
        ));
    }

    public static function hex(?string $color): string
    {
        $name = Str::lower(trim((string) $color));
        $match = self::all()->first(fn (array $c) => Str::lower($c['name']) === $name);

        return $match ? $match['hex'] : self::FALLBACK;
    }

    /**
     * Whether the swatch needs a border to be visible against a white page.
     */
    public static function needsOutline(?string $color): bool
    {
        return self::isLight(self::hex($color));
    }

    /**
     * Every colour name selectable on a new variant, Title Cased — the admin
     * colour select offers exactly this list, so nothing chosen there ever
     * falls back to the neutral chip.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return self::all()
            ->where('is_active', true)
            ->map(fn (array $c) => Str::title($c['name']))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Title Cased name => hex, for the admin eyedropper to match a sampled
     * pixel against the nearest named swatch client-side.
     *
     * @return array<string, string>
     */
    public static function map(): array
    {
        return self::all()
            ->where('is_active', true)
            ->mapWithKeys(fn (array $c) => [Str::title($c['name']) => $c['hex']])
            ->all();
    }

    /**
     * Perceived brightness (ITU-R BT.601) above which a swatch needs a ring to
     * stay visible on a white page — not just literal white, but anything
     * pale enough to disappear against it (Ivory, Champagne, Light Beige…).
     */
    private static function isLight(string $hex): bool
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) !== 6) {
            return false;
        }

        [$r, $g, $b] = array_map(fn ($h) => hexdec($h), str_split($hex, 2));

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255 > 0.78;
    }
}
