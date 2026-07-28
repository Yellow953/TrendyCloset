<?php

namespace App\Support;

/**
 * Responsive variants for catalogue and editorial imagery.
 *
 * Every remote photograph on the storefront is an Unsplash URL carrying its own
 * `w` (and sometimes `h`) — a category circle 170px wide was still downloading
 * the 900px file. Unsplash resizes on the fly, so one URL is really a whole
 * ladder of sizes: {@see srcset()} writes that ladder out and the browser picks
 * the rung it needs.
 *
 * Two rules keep this safe to apply everywhere:
 *
 * - The URL's own `w` is the ceiling. We only ever offer *smaller* candidates,
 *   so no slot can start fetching a bigger file than it does today.
 * - `h` scales with `w`. The hero is cropped 1400×1200; resizing width alone
 *   would quietly change its aspect ratio.
 *
 * Uploads from the back office are local files with no resizer behind them —
 * {@see srcset()} returns null for those and the `<x-img>` component simply
 * renders the one `src` it was given.
 */
class Img
{
    /** Hosts that resize from query parameters. */
    private const RESIZABLE_HOST = 'images.unsplash.com';

    /**
     * Candidate widths, in device pixels. Deliberately dense at the small end:
     * that is where the thumbnails live, and where the savings are.
     *
     * @var array<int, int>
     */
    private const LADDER = [160, 240, 320, 480, 640, 800, 1000, 1280, 1600, 2000];

    /**
     * A `srcset` for this image, or null when the URL cannot be resized.
     */
    public static function srcset(?string $url): ?string
    {
        if (! $url || ! self::isResizable($url)) {
            return null;
        }

        [$base, $query] = self::split($url);

        $max = (int) ($query['w'] ?? 0);

        if ($max <= 0) {
            return null;
        }

        $widths = array_values(array_filter(self::LADDER, fn (int $w) => $w < $max));
        $widths[] = $max;

        // One candidate is just the URL we already had — no point advertising it.
        if (count($widths) < 2) {
            return null;
        }

        $set = [];

        foreach ($widths as $w) {
            $set[] = self::at($base, $query, $w).' '.$w.'w';
        }

        return implode(', ', $set);
    }

    /**
     * The image's intrinsic aspect, when the URL states one — lets the browser
     * hold the right amount of space before the bytes land.
     *
     * @return array{width: int, height: int}|null
     */
    public static function dimensions(?string $url): ?array
    {
        if (! $url || ! self::isResizable($url)) {
            return null;
        }

        [, $query] = self::split($url);

        $w = (int) ($query['w'] ?? 0);
        $h = (int) ($query['h'] ?? 0);

        return $w > 0 && $h > 0 ? ['width' => $w, 'height' => $h] : null;
    }

    /**
     * The same photograph at a different width, cropped to the same aspect.
     */
    public static function width(?string $url, int $width): ?string
    {
        if (! $url || ! self::isResizable($url)) {
            return $url;
        }

        [$base, $query] = self::split($url);

        return self::at($base, $query, $width);
    }

    private static function isResizable(string $url): bool
    {
        return str_contains(parse_url($url, PHP_URL_HOST) ?? '', self::RESIZABLE_HOST);
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private static function split(string $url): array
    {
        $base = strtok($url, '?');
        $query = [];

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return [$base, $query];
    }

    /**
     * @param  array<string, string>  $query
     */
    private static function at(string $base, array $query, int $width): string
    {
        $source = (int) ($query['w'] ?? 0);
        $height = (int) ($query['h'] ?? 0);

        $query['w'] = (string) $width;

        // Keep the crop: a 1400×1200 hero asked for at 700 is 700×600.
        if ($height > 0 && $source > 0) {
            $query['h'] = (string) max(1, (int) round($height * $width / $source));
        }

        return $base.'?'.http_build_query($query);
    }
}
