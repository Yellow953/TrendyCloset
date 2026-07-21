<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand identity
    |--------------------------------------------------------------------------
    |
    | `brand` is the short suffix appended to page titles; `brand_full` is the
    | legal/display name used in structured data and the Organization node.
    | Every absolute URL on the site (canonicals, sitemap, Open Graph) is built
    | from APP_URL — set that to the real https:// domain in production and the
    | whole SEO layer follows. Nothing here hard-codes a domain.
    |
    */

    'brand' => 'Trendy Closet',
    'brand_full' => 'Trendy Closet by Leila Konsol',
    'founder' => 'Leila Konsol',
    // A middot, not a dash: product names already contain em-dashes
    // ("Wide Leg Jeans — Ecru"), and two dashes in one title reads as a typo.
    'separator' => ' · ',

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Used by any page that does not set its own. The description is what shows
    | under the result in a search engine, so it is written as a sentence a
    | shopper would read, not a keyword list.
    |
    */

    'tagline' => 'Curated fashion for the whole family',

    // Kept under 155 characters so search engines show it whole rather than
    // clipping it mid-sentence.
    'description' => 'A curated fashion boutique by Leila Konsol — hand-picked dresses, knitwear, denim and outerwear, with free shipping over $150 and 30-day returns.',

    // Relative to public/. Replace with a 1200x630 social card when one exists.
    'image' => 'images/logo-512.png',

    'locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Commerce
    |--------------------------------------------------------------------------
    |
    | Product::money() renders a leading "$", so schema.org Offers are priced in
    | USD. Change both together if the storefront ever switches currency.
    |
    */

    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Contact & social
    |--------------------------------------------------------------------------
    |
    | `social` feeds the Organization node's sameAs array, which is how search
    | engines and LLMs tie the site to its profiles. Empty entries are dropped,
    | so an unset handle simply does not appear.
    |
    */

    'email' => env('SEO_EMAIL', 'hello@trendycloset.com'),

    'twitter' => env('SEO_TWITTER', ''),

    'social' => [
        'instagram' => env('SEO_INSTAGRAM', 'https://instagram.com/trendycloset.byleilakonsol'),
        'facebook' => env('SEO_FACEBOOK', ''),
        'tiktok' => env('SEO_TIKTOK', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification tokens
    |--------------------------------------------------------------------------
    |
    | Optional. Set in .env once the property is claimed; empty values render
    | no tag at all.
    |
    */

    'verification' => [
        'google' => env('SEO_GOOGLE_VERIFICATION', ''),
        'bing' => env('SEO_BING_VERIFICATION', ''),
        'pinterest' => env('SEO_PINTEREST_VERIFICATION', ''),
    ],

];
