<?php declare(strict_types=1);

use App\App,
    App\CBAam,
    App\Tradingview,
    App\Model\Currency,
    App\Model\Course;

require_once __DIR__ . '/inc/init.php';

ob_start();
header('Content-Type: text/plain');

define('LOG_FILE', App::path('storage/cron_courses.log'));

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

set_exception_handler(function(Throwable $e) {
    $error = get_class($e) . ': ' . $e->getMessage();
    error_log($error, 0);

    echo $error . "\r\n";
    var_dump($e);

    saveLog();
    exit;
});

$cb = new CBAam;
$courses = [];
foreach ($cb->getCourses() as $symbol => $price) {
    if (!($currency = App::currency()->get($symbol)))
        continue;
    $courses[] = [$currency['id'], $price, App::TIME];
}
Course::insert(['cid', 'price', 'date_at'], $courses);

$courses = $images = [];
$crypto = App::createHdbk(Currency::class, 'symbol', '*', ['type' => 2]);
$pos = $crypto->max('pos') ?? 0;
//echo "\n";
//print_r(__FILE__ . ': ' . __LINE__);
//echo "\n";
$tview = new Tradingview;
foreach ($tview->getCourses() as $symbol => $data) {
    if (!($currency = $crypto->get($symbol))) {
        $currency = new Currency([
            'symbol' => $symbol,
            'type' => 2,
            'name' => $data['name'],
            'img' => '',
            'pos' => ++$pos
        ]);
        $currency->save();
        $crypto->push($symbol, $currency);

        if ($data['image'])
            $images[$symbol] = $data['image'];
    }
    $courses[] = [$currency['id'], $data['price'], App::TIME];
}
Course::insert(['cid', 'price', 'date_at'], $courses);

foreach ($images as $symbol => $url) {
    $tview->download($url, App::path("img/currency/{$symbol}.svg"));
}
echo "\n";
print_r(__FILE__ . ': ' . __LINE__);
echo "\n";

$courses = [];
if (($content = file_get_contents(
        'https://b24.am/en/',
        false,
        stream_context_create([
            'http' => [
                'method' => 'GET',
                'header'=> "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n"
                           . "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3\r\n"
                           . "Upgrade-Insecure-Requests: 1\r\n"
                           . "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:92.0) Gecko/20100101 Firefox/92.0"
            ]
        ])
    )) && preg_match(
        '#<div class="box_nm"> ?Gold ?</div>.?<div class="box_pr"> ?([\d\.]+) ?</div>#siU',
        $content, $matches
    ))
    $courses[] = [188, $matches[1], App::TIME];

if (($content = file_get_contents(
        'https://www.cbr.ru/scripts/XML_daily.asp',
        false,
        stream_context_create([
            'http' => [
                'method' => 'GET',
                'header'=> "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n"
                           . "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3\r\n"
                           . "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:92.0) Gecko/20100101 Firefox/92.0"
            ]
        ])
    )) && preg_match_all(
        '#<CharCode>(USD|EUR)</.+<Value>([\d\,\.]+)</#siU',
        $content, $matches, PREG_SET_ORDER
    )) {
    foreach ($matches as $match) {
        $item = round(floatval(str_replace(',', '.', $match[2])), 2);
        $courses[] = [('USD' === $match[1] ? 186 : 187), $item, App::TIME];
    }
}

if ($courses)
    Course::insert(['cid', 'price', 'date_at'], $courses);
