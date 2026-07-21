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
        'number' => env('WHATSAPP_NUMBER', '33100000000'),
        'message' => env('WHATSAPP_MESSAGE', 'Hi Leila! I have a question about a piece on Trendy Closet.'),
    ],

];
