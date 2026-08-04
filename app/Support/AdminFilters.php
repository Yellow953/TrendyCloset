<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * The filters currently narrowing an admin list, so an empty table can say
 * which conditions produced it instead of leaving the reader to re-read the
 * form above.
 */
class AdminFilters
{
    /**
     * Turn a spec of `param => label` (or `param => [label, options]`, where
     * options maps a raw value to the wording used in the select) into the
     * subset actually present on the request. Each chip carries the URL that
     * drops just that one.
     *
     * @param  array<string, string|array{0: string, 1: array<array-key, string>}>  $spec
     * @return array<int, array{key: string, label: string, value: string, url: string}>
     */
    public static function active(array $spec): array
    {
        $chips = [];

        foreach ($spec as $key => $definition) {
            $raw = request()->query($key);

            if ($raw === null || $raw === '' || is_array($raw)) {
                continue;
            }

            [$label, $options] = is_array($definition) ? $definition : [$definition, []];

            $chips[] = [
                'key' => $key,
                'label' => $label,
                'value' => (string) ($options[$raw] ?? $raw),
                'url' => static::without($key),
            ];
        }

        return $chips;
    }

    /**
     * The current URL with one param removed. `page` goes too — a narrower
     * result set rarely has the page you were on.
     */
    public static function without(string $key): string
    {
        $query = Arr::except(request()->query(), [$key, 'page']);

        return request()->url().($query ? '?'.http_build_query($query) : '');
    }
}
