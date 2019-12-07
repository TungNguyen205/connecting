<?php
return [
    'scopes' => [
        'read_products',
        'write_price_rules',
        'write_price_rules'
    ],
    'redirect_before_install' => env('APP_URL').'/auth',
];