<?php declare(strict_types=1);

namespace App;

echo "\n";
print_r("__DIR__:");
echo "\n";
print_r(__DIR__);
echo "\n";

echo "\n";
print_r("getcwd:");
echo "\n";
print_r(getcwd());
echo "\n";

echo "\n";
print_r("dirname(__FILE__):");
echo "\n";
print_r(dirname(__FILE__));
echo "\n";

require __DIR__ . "/../../../vendor/autoload.php";

use Exception;
use PHPHtmlParser\Dom;

class Tradingview
{

    /**
     * @var array
     */
    protected static array $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9'
        . ',image/webp,*/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Cache-Control: max-age=0',
        'Connection: keep-alive',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: none',
        'Sec-Fetch-User: ?1'
    ];

    /**
     * @var string
     */
    protected string $html = '';
    /**
     * @var resource
     */
    protected $ch;

    /**
     */
    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt_array(
            $this->ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; '
                                     . 'rv:92.0) Gecko/20100101 Firefox/92.0',
                CURLOPT_HTTPHEADER => static::$headers
            ]
        );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function &loadPage(): string
    {
        $headers = static::$headers;
        curl_setopt(
            $this->ch,
            CURLOPT_URL,
            'https://www.tradingview.com/markets/cryptocurrencies/prices-all/'
//            'https://scanner.tradingview.com/coin/scan'
        );
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        if (!($this->html = curl_exec($this->ch)))
            throw new Exception("Empty response body");
        elseif (200 !== ($code = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE)))
            throw new Exception("ResponseCode: {$code}");

        return $this->html;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCourses(): array
    {
        $page = &$this->loadPage();
        $dom = new Dom;
        $dom->loadStr($page);

        $rows = $dom->find('tbody')->find('tr');

        $data = [];

        foreach ($rows as $row) {
            $firstTd = $row->find('td')[0];
            $shortName = $firstTd->find('a')[0]->innerHtml;
            $price = explode('<span', $row->find('td')[2]->innerHtml);
            $img = $firstTd->find('img')[0];
            $data[$shortName] = [
                'price' => $price[0],
                'name' => $firstTd->find('sup')[0]->innerHtml,
                'image' => $img ? $img->getAttribute('src') : null,
            ];
        }

//        if (!($pos1 = strpos($page, 'window.initData.screener_data = ')))
//            throw new Exception;
//        elseif (!($pos2 = strpos($page, "\n", $pos1)))
//            throw new Exception;
////        echo "\n";
////        print_r(__FILE__ . ': ' . __LINE__);
////        echo "\n";
//
//        $json = trim(substr($page, ($pos1 + 33), ($pos2 - $pos1 - 33)), "\r\n\";");
//        $json = json_decode(str_replace('\\', '', $json), true);
//        $data = [];
//        foreach ($json['data'] as $item) {
//            $data[substr($item['short_name'], 0, -3)] = [
//                'price' => $item['d'][4],
//                'name' => $item['d'][1],
//                'image' => $item['base_currency_logo_url']
//            ];
//        }

        return $data;
    }

    /**
     * @param string $url
     * @param string $path
     *
     * @return bool
     */
    public function download(string $url, string $path): bool
    {
        if (file_exists($path))
            return true;
        if (!($fh = fopen($path, 'w')))
            return false;

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_FILE, $fh);
        curl_exec($this->ch);
        //curl_setopt($this->ch, CURLOPT_FILE, null);
        //curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        fclose($fh);

        return true;
    }

}
