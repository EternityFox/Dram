<?php declare(strict_types=1);

namespace App;
use Exception;

class CBAam
{


    /**
     * @var array
     */
    protected static array $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;'
        . 'q=0.9,image/webp,*\/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Connection: keep-alive',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: none',
        'Sec-Fetch-User: ?1',
        'Upgrade-Insecure-Requests: 1',
        'Cookie: CBA=language=EN; currencyList=USD,GBP,AUD,EUR,CAD,JPY,SEK,CHF,CNY,AED,RUB,GEL',
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
                CURLOPT_HTTPHEADER => static::$headers,
                CURLOPT_CONNECTTIMEOUT => 0,
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
//            'https://www.cba.am/en/SitePages/default.aspx'
            'https://cb.am/latest.json.php'
        );
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        if (!($this->html = curl_exec($this->ch))) {
//        if (!($this->html = file_get_contents('https://cb.am/latest.json.php'))) {
            throw new Exception("Empty response body");
        }
        elseif (200 !== ($code = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE)))
            throw new Exception("ResponseCode: {$code}");

//print_r(__FILE__ . ': ' . __LINE__ . PHP_EOL);
//print_r($this->html);
//die();
        return $this->html;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCourses(): array
    {
        $page = &$this->loadPage();
//        if (!preg_match_all(
//            '~<em class="w_50">'
//            . '<b>([a-zA-Z]+)</b></em>'
//            . '<em class="w_50">(\d+)</em>'
//            . '<em class="w_50">([\d.]+)</em>~U',
//            $page, $matches, PREG_SET_ORDER
//        ))
//            return [];
//
//        $courses = [];
//        foreach ($matches as $match) {
//            $courses[$match[1]] = (1 != $match[2])
//                ? ($match[3] / $match[2]) : (float) $match[3];
//        }

        $courses = array_filter(
            json_decode($page, true),
            function ($key) {
                $currencies = ['USD', 'GBP', 'AUD', 'EUR', 'CAD', 'JPY', 'SEK', 'CHF', 'CNY', 'AED', 'RUB', 'GEL'];
                return in_array($key, $currencies);
            },
            ARRAY_FILTER_USE_KEY);
//print_r(__FILE__ . ': ' . __LINE__ . PHP_EOL);
//print_r($courses);
//die();
        return $courses;
    }

    /**
     * @param string|null $date
     *
     * @return string
     * @throws \Exception
     */
    public function &loadHistoryPage(?string $date = null): string
    {
        $headers = static::$headers;
        curl_setopt(
            $this->ch,
            CURLOPT_URL,
            'https://www.cba.am/ru/sitepages/ExchangeArchive.aspx'
            . ($date ? "?FilterDate={$date}" : '')
        );
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        if (!($this->html = curl_exec($this->ch)))
            throw new Exception("Empty response body");
        elseif (200 !== ($code = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE)))
            throw new Exception("ResponseCode: {$code}");

        return $this->html;
    }

    /**
     * @param string|null $date
     *
     * @return array
     * @throws \Exception
     */
    public function getHistoryCourses(?string $date = null): array
    {
        $page = &$this->loadHistoryPage($date);
        if (!preg_match_all(
            '~<tr class="gray_(td|td_light)">'
            . '.*>([a-zA-Z]+)<.+>(\d+)<.+<td>([\d.]+)<'
            . '.+</tr>~U',
            $page, $matches, PREG_SET_ORDER
        ))
            return [];

        $courses = [];
        foreach ($matches as $match) {
            $courses[$match[2]] = (1 != $match[3])
                ? ($match[4] / $match[3]) : (float) $match[4];
        }

        return $courses;
    }

}
