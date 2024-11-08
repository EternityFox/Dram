<?php

return [
    '/' => [
        'icon' => 'm-icon-one.png',
        'text' => 'Главная',
    ],
    '' => [
        'icon' => 'm-icon-two.png',
        'text' => 'Валюты',
        'submenu' => [
            '/markets' => [
                'icon' => 'm-icon-three.png',
                'text' => 'Рынки',
            ],
            '/charts' => [
                'icon' => 'm-icon-four.png',
                'text' => 'Графики',
            ],
        ],
    ],
    'hidden' => [
        '/mobile-commun' => [
            'icon' => 'm-icon-ten.png',
            'text' => 'Мобильная связь',
        ],
        '' => [
            'icon' => 'm-icon-seven.png',
            'text' => 'Кредиты 2',
            'submenu' => [
                '/markets2' => [
                    'icon' => 'm-icon-three.png',
                    'text' => 'Рынки 2',
                ],
                '/charts2' => [
                    'icon' => 'm-icon-four.png',
                    'text' => 'Графики 2',
                ],
            ],
        ],
    ],
];