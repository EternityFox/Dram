<?php declare(strict_types=1);

use App\App;

/**
 * Проверка основных директорий, создание таблиц в БД
 */
require_once __DIR__ . '/inc/init.php';

header('Content-Type: text/plain');

$errors = [];
$dirs = [
    App::path('storage'),
    App::path('lang'),
    App::path('img/exchanger'),
    App::path('img/currency')
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true))
            $errors[] = "Create: {$dir}";
    } elseif (!is_writable($dir)) {
        if (!chmod($dir, 0777))
            $errors[] = "Permission: {$dir}";
    }
}

if ($errors)
    throw new Exception(implode("\r\n", $errors));
elseif (file_exists(App::path('sqlite')) && !unlink(App::path('sqlite')))
    throw new Exception('Delete: ' . App::path('sqlite'));

$db = App::get('db');
$db->exec(<<<SQL
-- Валюты
CREATE TABLE currency (
    id INTEGER PRIMARY KEY,
    symbol TEXT,
    type INTEGER DEFAULT 1, -- 1 - национальная, 2 - криптовалюта
    name TEXT DEFAULT '',
    img TEXT DEFAULT '',
    pos INTEGER DEFAULT 0,
    UNIQUE(symbol, type)
);

-- Курсы валют
CREATE TABLE course (
    id INTEGER PRIMARY KEY,
    cid INTEGER REFERENCES currency(id),
    price REAL,
    date_at INTEGER,
    UNIQUE(cid, date_at)
);

-- Обменники
CREATE TABLE exchanger (
    id INTEGER PRIMARY KEY,
    name TEXT UNIQUE,
    branches INTEGER,
    is_bank INTEGER DEFAULT 0,
    upd_cash INTEGER DEFAULT 0,
    upd_noncash INTEGER DEFAULT 0,
    upd_card INTEGER DEFAULT 0,
    raid TEXT DEFAULT '' -- rate.am ID
);

-- Курсы валют
CREATE TABLE exrcourse (
    id REAL PRIMARY KEY, -- ID = (eid * 1000) + cid + (type / 10)
    type INTEGER, -- 1 - cash, 2 - noncash, 4 - card
    eid INTEGER REFERENCES exchanger(id),
    cid INTEGER REFERENCES currency(id),
    buy REAL,
    sell REAL,
    ws_buy REAL, -- >1000USD
    ws_sell REAL -- >1000USD
);

-- Основные валюты
INSERT INTO currency (id, symbol, type, name, pos) VALUES
(1, "USD", 1, 'Американский Доллар', 1),
(2, "EUR", 1, 'Евро', 2),
(3, "RUB", 1, 'Российский Рубль', 3),
(4, "GBP", 1, 'Британский фунт', 4),
(5, "GEL", 1, 'Грузинский Лари', 5),
(6, "CHF", 1, 'Швейцарский Франк', 6),
(7, "CAD", 1, 'Канадский Доллар', 7),
(8, "AED", 1, 'Дирхам ОАЭ', 8),
(9, "CNY", 1, 'Китайский Юань', 9),
(10, "AUD", 1, 'Австралийский Доллар', 10),
(11, "JPY", 1, 'Японская Йена', 11),
(12, "SEK", 1, 'Шведская Крона', 12);
SQL
);
