<?php declare(strict_types=1);

/**
 * Процесс парсинга обменников и курсов валют с сайта https://rate.am
 */

use App\App,
    App\Model\Currency,
    App\Model\Exchanger,
    App\Model\Exrcourse,
    App\Rateam,
    App\BankParser;

require_once __DIR__ . '/inc/init.php';

ob_start();
header('Content-Type: text/plain');

set_time_limit(0);
ini_set('max_execution_time', '99999999');

define('LOG_FILE', App::path('storage/cron.log'));

$symbolPack = [
    ['USD', 'EUR', 'RUR', 'GBP'],
    ['GEL', 'CHF', 'CAD', 'AED'],
    ['CNY', 'AUD', 'JPY', 'SEK']
];
$symbolAliases = ['RUR' => 'RUB'];
$symbolAmt = ['JPY' => 10];
$langs = ['en', 'am'];

$pages = [
    'banks' => [
        'is_bank' => true,
        'type' => Exrcourse::TYPE_CASH,
        'upd_field' => 'upd_cash',
        'info' => 'bank'
    ],
    'exchange-points' => [
        'is_bank' => false,
        'type' => Exrcourse::TYPE_CASH_NONCASH,
        'upd_field' => 'upd_cash',
        'info' => 'exchange-point'
    ]
];
$ws_page = 'exchange-points/cash/corporate';

function saveLog()
{
    $lastSize = file_exists(LOG_FILE) ? filesize(LOG_FILE) : 0;
    $data = ob_get_contents();
    $size = strlen($data);
    if ($lastSize > $size)
        $data .= str_repeat(' ', ($lastSize - $size));

    $fh = fopen(LOG_FILE, 'w');
    fwrite($fh, $data);
    fclose($fh);
}

set_exception_handler(function (Throwable $e) {
    $error = get_class($e) . ': ' . $e->getMessage();
    error_log($error, 0);

    echo $error . "\r\n";
    var_dump($e);

    saveLog();
    exit;
});

$symbols = App::currency();
$exchangers = App::createHdbk(
    Exchanger::class, 'raid', '*', null, ['is_bank' => 'DESC']
);
$last_ex_id = $exchangers->max('id') ?? 0;

$rateam = new Rateam;
$new_exchangers = $new_images = [];

$activeSymbols = [];
foreach ($symbolPack as $pack) {
    foreach ($pack as $i => $symbol) {
        $amt = ($symbolAmt[$symbol] ?? '1');
        $pack[$i] = "{$amt} {$symbol}";
        $activeSymbols[($symbolAliases[$symbol] ?? $symbol)] = [
            'id' => $symbols->get(
                ($symbolAliases[$symbol] ?? $symbol)
            )['id'],
            'amt' => $amt
        ];
    }
}
$pack = implode(',', $pack);
$cids = array_column($activeSymbols, 'id');

$banksFile = App::path('storage/bank_info_pure.txt');
$banks = file_exists($banksFile) ? unserialize(file_get_contents($banksFile)) : [];
$exchangersFile = App::path('storage/exchanger_info_pure.txt');
$exchangers_array = file_exists($exchangersFile) ? unserialize(file_get_contents($exchangersFile)) : [];

foreach ($pages as $path => $info) {
    $ids = $new_ids = $rates = [];

    echo "Страница: {$path} ({$pack})\r\n";
    $page = $rateam->loadPage($path, $pack);
    $exList = $rateam->takeList();
    echo 'Обменников: ' . count($exList) . "\r\n";

    foreach ($exList as $raid => $data) {
        if (!($local = $exchangers->findOne(['raid' => $raid])) || $local['name'] != $data['name']) {
            if ($local) {
                Exrcourse::delete('eid = ' . $local['id']);
                Exchanger::delete('raid = "' . $raid . '"');
            }
            $local_name = $exchangers->findOne(['name' => $data['name']]);
            if ($local_name) {
                Exrcourse::delete('eid = ' . $local_name['id']);
                Exchanger::delete('name = "' . $local_name['name'] . '"');
            }
            $local = new Exchanger([
                'id' => ++$last_ex_id,
                'name' => $data['name'],
                'branches' => $data['branches'],
                'is_bank' => $info['is_bank'],
                'upd_cash' => $data['date'],
                'upd_noncash' => $data['date'],
                'upd_card' => $data['date'],
                'raid' => $raid
            ]);
            $exchangers->push($raid, $local);
            $new_ids[$local->id] = $local->raid;
            $new_exchangers[$local->id] = [$raid, $data['logo'], $local->name];
        } else {
            $local['upd_cash'] = $data['date'];
            $local['upd_noncash'] = $data['date'];
            $local['upd_card'] = $data['date'];
        }
        if ($info['is_bank']) {
            try {
                $bankHtml = $rateam->loadInfoPage($info['info'], $data['slug']);
                $parsedData = $rateam->takeInfoList($local['id']);

                if (!isset($parsedData['name']) || !is_array($parsedData['baranches']) || count($parsedData['baranches']) === 0) {
                    throw new Exception('Некорректные или пустые данные при парсинге');
                }

                $makeLang = fn(string $value) => ['ru' => $value, 'en' => $value, 'am' => $value];

                $banks[$local['id']] = [
                    'name' => $parsedData['name'],
                    'baranches' => [],
                ];

                foreach ($parsedData['baranches'] as $branch) {
                    $banks[$local['id']]['baranches'][] = [
                        'name' => $makeLang($branch['name']),
                        'address' => $makeLang($branch['address']),
                        'phones' => $branch['phones'],
                        'emails' => $branch['emails'],
                        'of_sites' => $branch['of_sites'],
                        'socials' => $branch['socials'],
                        'hours' => $branch['hours'],
                        'latitude' => $branch['latitude'],
                        'longitude' => $branch['longitude'],
                        'img' => $branch['img'],
                    ];
                }
                echo "✓ Сохранены данные банка ID {$local['id']} - {$parsedData['name']}\r\n";

            } catch (Throwable $e) {
                echo "× Ошибка парсинга страницы банка: {$raid} — {$e->getMessage()}\r\n";
            }
        } else {
            try {
                $bankHtml = $rateam->loadInfoPage($info['info'], rawurlencode($data['slug']));
                $parsedData = $rateam->takeInfoList($local['id']);

                if (!isset($parsedData['name']) || !is_array($parsedData['baranches']) || count($parsedData['baranches']) === 0) {
                    throw new Exception('Некорректные или пустые данные при парсинге');
                }

                $makeLang = fn(string $value) => ['ru' => $value, 'en' => $value, 'am' => $value];

                $exchangers_array[$local['id']] = [
                    'name' => $parsedData['name'],
                    'baranches' => [],
                ];

                foreach ($parsedData['baranches'] as $branch) {
                    $exchangers_array[$local['id']]['baranches'][] = [
                        'name' => $makeLang($branch['name']),
                        'address' => $makeLang($branch['address']),
                        'phones' => $branch['phones'],
                        'emails' => $branch['emails'],
                        'of_sites' => $branch['of_sites'],
                        'socials' => $branch['socials'],
                        'license' => $branch['license'],
                        'hours' => $branch['hours'],
                        'latitude' => $branch['latitude'],
                        'longitude' => $branch['longitude'],
                        'img' => $branch['img'],
                    ];
                }
                echo "✓ Сохранены данные обменника ID {$local['id']} - {$parsedData['name']}\r\n";

            } catch (Throwable $e) {
                echo "× Ошибка парсинга страницы обменника: {$raid} — {$e->getMessage()}\r\n";
            }
        }

        if (
            $info['is_bank']
            && (
                !file_exists(App::path("img/exchanger/{$raid}.svg"))
                || filemtime(App::path("img/exchanger/{$raid}.svg")) < (time() - 86400 * 3)
            )) {
            $new_images[$raid] = $data['logo'];
        }
        $ids[$local->raid] = $local->id;
        foreach ($data['rates'] as $symbol => $rate) {
            $s = $symbol;
            if (isset($symbolAliases[$symbol]))
                $symbol = $symbolAliases[$symbol];

            if (!isset($activeSymbols[$symbol]))
                continue;

            $symbol = $activeSymbols[$symbol];
            if ($info['is_bank']) {
                if (isset($rate['CASH']) && !empty($rate['CASH']['buy'])
                    && !empty($rate['CASH']['sell'])) {
                    $id = Exrcourse::generateId(
                        $local['id'], $symbol['id'], Exrcourse::TYPE_CASH
                    );
                    $rates[(string)$id] = [
                        'id' => $id, 'eid' => $local['id'],
                        'cid' => $symbol['id'],
                        'type' => Exrcourse::TYPE_CASH,
                        'buy' => ($rate['CASH']['buy'] / $symbol['amt']),
                        'sell' => ($rate['CASH']['sell'] / $symbol['amt'])
                    ];
                }

                if (isset($rate['CLEARING'])
                    && !empty($rate['CLEARING']['buy'])
                    && !empty($rate['CLEARING']['sell'])) {
                    $id = Exrcourse::generateId(
                        $local['id'], $symbol['id'], Exrcourse::TYPE_NONCASH
                    );
                    $rates[(string)$id] = [
                        'id' => $id, 'eid' => $local['id'],
                        'cid' => $symbol['id'],
                        'type' => Exrcourse::TYPE_NONCASH,
                        'buy' => ($rate['CLEARING']['buy']
                            / $symbol['amt']),
                        'sell' => ($rate['CLEARING']['sell']
                            / $symbol['amt'])
                    ];
                }

                if (isset($rate['CARDTRANSACTION'])
                    && !empty($rate['CARDTRANSACTION']['buy'])
                    && !empty($rate['CARDTRANSACTION']['sell'])) {
                    $id = Exrcourse::generateId(
                        $local['id'], $symbol['id'], Exrcourse::TYPE_CARD
                    );
                    $rates[(string)$id] = [
                        'id' => $id, 'eid' => $local['id'],
                        'cid' => $symbol['id'],
                        'type' => Exrcourse::TYPE_CARD,
                        'buy' => ($rate['CARDTRANSACTION']['buy']
                            / $symbol['amt']),
                        'sell' => ($rate['CARDTRANSACTION']['sell']
                            / $symbol['amt'])
                    ];
                }
            } else {
                if (isset($rate['RETAIL'])
                    && !empty($rate['RETAIL']['buy'])
                    && !empty($rate['RETAIL']['sell'])) {
                    $id = Exrcourse::generateId(
                        $local['id'], $symbol['id'], Exrcourse::TYPE_CASH_NONCASH
                    );
                    $rates[(string)$id] = [
                        'id' => $id, 'eid' => $local['id'],
                        'cid' => $symbol['id'],
                        'type' => Exrcourse::TYPE_CASH_NONCASH,
                        'buy' => ($rate['RETAIL']['buy']
                            / $symbol['amt']),
                        'sell' => ($rate['RETAIL']['sell']
                            / $symbol['amt']),
                        'ws_buy' => ($rate['CORPORATE'] ? ($rate['CORPORATE']['buy'] / $symbol['amt']) : 'NULL'),
                        'ws_sell' => ($rate['CORPORATE'] ? ($rate['CORPORATE']['sell'] / $symbol['amt']) : 'NULL')
                    ];
                }
            }
        }
    }
    file_put_contents($banksFile, serialize($banks));
    echo "✓ Финальное сохранение всех банков: " . count($banks) . "\r\n";

    file_put_contents($exchangersFile, serialize($exchangers_array));
    echo "✓ Финальное сохранение всех обменников: " . count($exchangers_array) . "\r\n";

    echo 'Новых обменников: ' . count($new_ids) . "\r\n";
    echo 'Кол-во курсов: ' . count($rates) . "\r\n";

    Exrcourse::delete(
        'eid IN(' . implode(', ', $ids) . ')'
        . ' AND cid IN(' . implode(', ', $cids) . ')'
    );

    $fields = [];
    foreach ($ids as $raid => $id) {
        $ex = $exchangers->findOne(['raid' => $raid]);
        if (isset($new_ids[$id]))
            $fields[] = $ex->fields();
        else
            $ex->save();
    }

    if ($fields)
        Exchanger::insert(array_keys($fields[0]), $fields);
    if (!empty($rates))
        Exrcourse::insert(array_keys($rates[array_key_first($rates)]), $rates);
}
//}

echo 'Затраченное время: '
    . round((microtime(true) - App::STARTED_AT), 2) . " сек.\r\n";

echo 'Получение курсов с сайтов банков.' . "\r\n";
$bankStart = microtime(true);
$bankList = [
    'fastbank' => 'c4a69322-c3c6-46c0-8edd-1b10cb90d100',
    'armswissbank' => '95b795f4-073d-4670-993d-dfb781375a94',
    'ardshinbank' => '466fe84c-197f-4174-bc97-e1dc7960edc7',

    'vtb' => '69460818-02ec-456e-8d09-8eeff6494bce',
    'conversebank' => '2119a3f1-b233-4254-a450-304a2a5bff19',

    'inecobank' => '65351947-217c-4593-9011-941b88ee7baf',
    'mellatbank' => 'f288c3fc-f524-468c-bff7-fbd9bbc6b8d7',

    'ameriabank' => '989ba942-a5cf-4fc2-b62e-3248c4edfbbc',
    'unibank' => '133240fd-5910-421d-b417-5a9cedd5f5f7',
    'byblosbank' => 'ebd241ce-4a38-45a4-9bcd-c6e607079706',
    'araratbank' => '5ee70183-87fe-4799-802e-ef7f5e7323db',
    'acba' => 'f3ffb6cf-dbb6-4d43-b49c-f6d71350d7fb'
];
$bankparser = new BankParser;
foreach ($bankList as $method => $raid) {
    $rates = [];

    $exrid = $exchangers->findOne(['raid' => $raid])['id'];

    try {
        if (!($bdata = $bankparser->{$method}()))
            throw new Exception('Не удалось получить курсы банка!');

        $updated_at = $bdata['updated_at'];
        unset($bdata['updated_at']);

        foreach ($bdata as $rate) {
            $symbol = $rate['symbol'];
            if (isset($symbolAliases[$symbol]))
                $symbol = $symbolAliases[$symbol];

            if (!isset($activeSymbols[$symbol]) || !$rate['buy'] || !$rate['sell'])
                continue;

            $sid = $symbols->get($symbol)['id'];

            if ('cash' === $rate['type'])
                $type = Exrcourse::TYPE_CASH;
            elseif ('noncash' === $rate['type'])
                $type = Exrcourse::TYPE_NONCASH;
            elseif ('card' === $rate['type'])
                $type = Exrcourse::TYPE_CARD;
            else
                continue;

            $id = Exrcourse::generateId(
                $exrid, $sid, $type
            );
            $rates[(string)$id] = [
                'id' => $id,
                'eid' => $exrid,
                'cid' => $sid,
                'type' => $type,
                'buy' => $rate['buy'],
                'sell' => $rate['sell']
            ];
        }
    } catch (Throwable $e) {
        echo $e->getMessage();
        continue;
    }

    if (!empty($rates)) {
        Exrcourse::delete('eid = ' . $exrid);
        Exrcourse::insert(array_keys($rates[array_key_first($rates)]), $rates);
        $timeupd = time();
        Exchanger::update(
            [
                'upd_cash' => $timeupd,
                'upd_noncash' => $timeupd,
                'upd_card' => $timeupd
            ],
            'id = ' . $exrid
        );
    }
}

echo 'Затраченное время: '
    . round((microtime(true) - $bankStart), 2) . " сек.\r\n";

echo 'Получение названий и логотипов для новых обменников: '
    . count($new_exchangers) . "\r\n";

if ($new_exchangers) {
    $paths = [array_key_first($pages), array_key_last($pages)];
    foreach ($langs as $lang) {
        if (!file_exists(App::path("lang/{$lang}"))) {
            if (!mkdir(App::path("lang/{$lang}"), 0777))
                continue;
            $data = [];
        } elseif (file_exists(App::path("lang/{$lang}/exchanger.php")))
            $data = include App::path("lang/{$lang}/exchanger.php");
        else
            $data = [];

        if (!($fh = fopen(App::path("lang/{$lang}/exchanger.php"), 'w')))
            continue;

        foreach ($paths as $path) {
            $rateam->loadPage($path, '1 USD,1 EUR,1 RUR,1 GBP', ($lang == 'am' ? 'hy' : $lang));
            $names = $rateam->takeNames();
            foreach ($new_exchangers as $val) {
                if (isset($names[$val[0]]))
                    $data[$val[2]] = $names[$val[0]];
            }
        }

        fwrite($fh, "<?php\r\n\r\nreturn [");
        foreach ($data as $name => $value) {
            $name = str_replace("'", "\'", $name);
            $value = str_replace("'", "\'", $value);
            fwrite($fh, "\r\n    '{$name}' => '{$value}',");
        }
        fwrite($fh, "\r\n];\r\n");
        fclose($fh);
    }
}

foreach ($new_images as $raid => $url) {
    $imgfile = App::path("img/exchanger/{$raid}.svg");
    @unlink($imgfile);

    if (!$rateam->download($url, $imgfile)) {
        echo "\r\n" . 'Filed create image from: ' . $url;
        @unlink($imgfile);
    }
}

echo 'Общее время: '
    . round((microtime(true) - App::STARTED_AT), 2) . " сек.\r\n";
echo round((memory_get_peak_usage() / 1024), 2) . ' Kb. | '
    . round((memory_get_peak_usage(true) / 1024), 2) . ' Kb.';
saveLog();