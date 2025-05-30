<?php

return [
    'url' => 'https://dram.am',
    'site' => 'dram.am',
    'title' => 'Dram.am',
    'languages' => [
        'am' => 'Հայերեն',
        'ru' => 'Русский',
        'en' => 'English'
    ],
    'banner' => [
        'top' => '',
        'bottom' => '',
    ],
    'widget' => [
        'Menu' => [
            ['Главная', '/', 'menu/m-icon-one.png'],
            [
                ['Валюты', '/', 'menu/m-icon-two.png'],
                ['Рынки', '/', 'menu/m-icon-three.png'],
                ['Графики', '/', 'menu/m-icon-four.png'],
                ['Криптовалюта', '/', 'menu/m-icon-five.png'],
                ['ЦБ', '/', 'menu/m-icon-six.png'],
            ],
            ['Кредиты', '/', 'menu/m-icon-seven.png'],
            ['Депозиты', '/', 'menu/m-icon-eight.png'],
            ['Карты', '/', 'menu/m-icon-nine.png'],
            ['Мобильная связь', '/', 'menu/m-icon-ten.png'],
        ],
        'MainTable' => [
            'baseSymbols' => ['RUB', 'USD', 'EUR', 'GBP', 'GEL'],
            'allSymbols' => ['RUB', 'USD', 'EUR', 'GBP', 'CHF', 'CAD', 'AED', 'CNY', 'AUD', 'JPY', 'SEK'],
            'actualSec' => 3600,
            'removeSec' => 86400 * 7
        ],
        'BestExchangers' => [
            'symbols' => ['USD', 'EUR', 'RUB', 'GBP', 'GEL']
        ],
        'IntlCourses' => [
            'topSymbols' => ['BTC', 'Gold', 'RUB USD', 'RUB EUR', 'USD', 'EUR']
        ],
    ],
    'currencySymbols' => [
        'USD' => '&#1423;',
        'EUR' => '&#1423;',
        'RUB' => '&#1423;',
        'GBP' => '&#1423;',
        'GEL' => '&#1423;',
        'CAD' => '&#1423;',
        'AED' => '&#1423;',
        'CHF' => '&#1423;',
        'CNY' => '&#1423;',
        'AUD' => '&#1423;',
        'JPY' => '&#1423;',
        'SEK' => '&#1423;',

        'RUB USD' => 'ք',
        'RUB EUR' => 'ք',

        'BTC' => '$',
        'ETH' => '$',
        'USDT' => '$',
        'BNB' => '$',
        'USDC' => '$',
        'ADA' => '$',
        'HEX' => '$',
        'XRP' => '$',
        'SOL' => '$',
        'LUNA' => '$',
        'DOGE' => '$',
        'DOT' => '$',
        'AVAX' => '$',
        'HIT' => '$',
        'MATIC' => '$',

        'Gold' => '&#1423;'
    ]
];
