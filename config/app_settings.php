<?php

return [
    'loyalty' => [
        'enabled' => true,
        'ranks' => [
            'bronze_min_amount' => 500000,
            'silver_min_amount' => 2000000,
            'gold_min_amount' => 5000000,
            'platinum_min_amount' => 15000000,
        ],
        'discounts' => [
            'regular' => [
                'type' => 'percent',
                'value' => 0,
            ],
            'bronze' => [
                'type' => 'percent',
                'value' => 2,
            ],
            'silver' => [
                'type' => 'percent',
                'value' => 5,
            ],
            'gold' => [
                'type' => 'percent',
                'value' => 8,
            ],
            'platinum' => [
                'type' => 'percent',
                'value' => 12,
            ],
        ],
        'rewards' => [
            'points_rate' => 100,
            'points_value' => 1000,
        ],
    ],
    'general' => [
        'site_name' => 'PacificStore',
        'contact_email' => 'admin@pacific.store',
        'contact_phone' => '0292 3798 668',
        'address' => '168 Nguyễn Văn Cừ Nối Dài, An Bình, Ninh Kiều, Cần Thơ',
    ],
];
