<?php

namespace App\Support;

/**
 * The country list behind the checkout's two selects — the delivery country
 * and the phone's dialling code — read from `config/countries.php`.
 *
 * Orders store the country *name* (`orders.ship_country`), not the ISO code:
 * the column has always held a name, and the back office prints it straight
 * onto the parcel.
 */
class Countries
{
    /**
     * @return array<string, array{name: string, dial: string}>
     */
    public static function all(): array
    {
        return config('countries', []);
    }

    /**
     * Country names, A–Z — the option values of the delivery-country select.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        $names = array_column(static::all(), 'name');

        sort($names, SORT_NATURAL | SORT_FLAG_CASE);

        return $names;
    }

    /**
     * Dialling codes, A–Z by country, deduplicated so the same code is not
     * offered twice (Jersey and the UK both dial 44).
     *
     * @return array<int, array{name: string, dial: string}>
     */
    public static function dialCodes(): array
    {
        $seen = [];

        foreach (static::all() as $country) {
            $seen[$country['dial']] ??= ['name' => $country['name'], 'dial' => $country['dial']];
        }

        $codes = array_values($seen);

        usort($codes, fn (array $a, array $b) => strnatcasecmp($a['name'], $b['name']));

        return $codes;
    }

    /**
     * The country the shop ships from, and the one the selects open on.
     */
    public static function defaultName(): string
    {
        $name = (string) config('store.contact.country', 'Lebanon');

        return in_array($name, static::names(), true) ? $name : 'Lebanon';
    }

    public static function defaultDial(): string
    {
        return (string) config('store.contact.country_code', '961');
    }
}
