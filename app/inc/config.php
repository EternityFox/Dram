<?php declare(strict_types=1);

return [
    'home' => 'https://dram.am/',
    'paths' => [
        'storage' => 'app/storage',
        'lang' => 'storage/lang',
        'view' => 'app/view',
        'img' => 'app/../img',
        'sqlite' => 'storage/db.sqlite'
    ],
    'routes' => [
//        '.*' => 'site/notFound',
        '' => 'site/index',
        'page/(?<slug>[^/]+)' => 'site/page',
        'bank/(?<id>\d+)' => 'site/bank',
        'exchanger/(?<id>\d+)' => 'site/exchanger',
        '(?<lang>ru|en|am)' => 'site/index',
        'web' => 'site/index',
        'mob' => 'site/index',
        'converter' => 'site/converter',
        'charts' => 'site/charts',
        'about' => 'site/about',
        'plate-number-search.*' => 'site/numberSearch',
        'faq' => 'site/faq',
        'contacts' => 'site/contacts',
        'advertising' => 'site/advertising',
        '(?<course>direct|cross)(/(?<type>cash|noncash|card)).*' => 'site/index',
        'ajax' => [
            '(?<course>direct|cross)/(?<type>cash|noncash|card)' => 'site/table',
            '(?<num>\d+)_(?<symbol>[A-Z]+)/(?<course>direct|cross)/(?<type>cash|noncash|card)' => 'site/changeSymbol',
            'chart/(?<symbol>[A-Za-z\d%]+)' => 'site/chart',
            'converter/(?<type>cash|noncash|card)/(?<fromCurrency>[A-Z]+)/(?<toCurrency>[A-Z]+)' => 'site/converterAjax',
            'plate-search' => 'site/plateSearch',
            '.*' => 'error/ajax'
        ],
        'login' => 'admin/login',
        'admin' => [
//            '.*' => 'admin/index',
            '' => 'admin/index',
            'pages' => 'admin/pages',
            'create-page' => 'admin/createPage',
            'edit-page/(?<id>\d+)' => 'admin/editPage',
            'delete-page/(?<id>\d+)' => 'admin/deletePage',
            'bank/(?<id>\d+)' => 'admin/bank',
            'exchanger/(?<id>\d+)' => 'admin/exchanger',
        ],
//        'bigparsebankvslsdkfjsdhfjvskdl' => 'site/bigParseBank',
//        'bigparseexchangervslsdkfjsdhfjvskdl' => 'site/bigParseExchanger',
        'fixexchangervslsdkfjsdhfjvskdl' => 'site/fixExchanger',
    ]
];
