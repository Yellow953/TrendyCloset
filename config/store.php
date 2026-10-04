<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront contact
    |--------------------------------------------------------------------------
    |
    | The WhatsApp number behind the floating chat button, in international
    | format with no "+", spaces or dashes (wa.me will not accept those).
    | Set WHATSAPP_NUMBER in .env; leaving it empty hides the button.
    |
    */

    'whatsapp' => [
        'number' => env('WHATSAPP_NUMBER', '96176158735'),
        'message' => env('WHATSAPP_MESSAGE', 'Hi Pamela! I have a question about a piece on Trendy Closet.'),
    ],

    'contact' => [
        'phone_display' => '+961 76 158 735',
        'country' => 'Lebanon',
        'country_code' => '961',
        'address' => ['Tal el Zaatar, Dekwaneh', 'Mount Lebanon, Lebanon'],
        'map_url' => 'https://maps.app.goo.gl/HgJqRHujguCD8Job7',
        'map_embed' => 'https://maps.google.com/maps?q='.rawurlencode('Trendy Closet, Tal el Zaatar, Dekwaneh, Lebanon').'&z=16&output=embed',
        'response_time' => 'within a few hours',
    ],

    'notifications' => [
        'orders' => env('ORDER_NOTIFY_EMAIL') ?: env('SEO_EMAIL', 'Trendycloset.byleilakonsol@gmail.com'),
    ],

];
