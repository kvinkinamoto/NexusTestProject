<?php

return [
    'liqpay' => [
        'public_key' => env('LIQPAY_PUBLIC', 'sandbox_i82481044811'),
        'private_key' => env('LIQPAY_PRIVATE', 'sandbox_eWmf4PGTUMvKY9D8cWwkozKll1q6LvAK2prlSaNd'),
    ],
    'monobank' => [
        'store_id' => env('MONOBANK_STORE_ID', 'test_store_with_confirm'),
        'secret' => env('MONOBANK_SECRET', 'secret_98765432--123-123'),
        'url' => env('MONOBANK_URL', 'https://u2-demo-ext.mono.st4g3.com'),
    ],
    'privat' => [
        'api_url' => env('PRIVAT_API_URL', 'https://payparts2.privatbank.ua/ipp/v2'),
        'store_id' => env('PRIVAT_STORE_ID', '4AAD1369CF734B64B70F'),
        'password' => env('PRIVAT_PASSWORD', '75bef16bfdce4d0e9c0ad5a19b9940df'),
    ],
];
