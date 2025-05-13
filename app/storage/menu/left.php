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
            '/charts' => [
                'icon' => 'm-icon-four.png',
                'text' => 'Графики',
            ],
        ],
    ],
];
