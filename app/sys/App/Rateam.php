<?php declare(strict_types=1);

namespace App;
use Exception;

class Rateam
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
        'Referer: https://www.rate.am/ru/armenian-dram-exchange-rates/'
        . 'banks/non-cash?tp=0&rt=1'
    ];

    /**
     * @var string
     */
    protected string $html = '';
    protected string $path = '';
    protected string $pack = '';
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

    /**
     * @param string $path
     * @param string $symbols
     * @param string $lang
     *
     * @return string
     * @throws \Exception
     */
    public function &loadPage(
        string $path, string $symbols, string $lang = 'ru'
    ): string
    {
        $this->path = $path;
        $this->pack = $symbols;
        $headers = static::$headers;
        $headers[] = "Cookie: Cookie.CurrencyList={$symbols}";
        curl_setopt(
            $this->ch,
            CURLOPT_URL,
            "https://www.rate.am/{$lang}/armenian-dram-exchange-rates/{$path}"
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
    public function takeList(): array
    {
        $spos = mb_strpos($this->html, '\\"organizationRates\\"', 0, 'UTF-8');
        $fpos = mb_strpos($this->html, '\\"organizations\\"', $spos, 'UTF-8');
        $rates = mb_substr($this->html, ($spos + 22), ($fpos - $spos - 23), 'UTF-8');
        $rates = str_replace('\\', '', $rates);
        $rates = json_decode($rates, true);

        //$f2pos = mb_strpos($this->html, ',[\\"$\\"', $fpos, 'UTF-8');
        $f2pos = mb_strpos($this->html, '}},\\"organizationType\\"', $fpos, 'UTF-8');
        $organizations = mb_substr($this->html, ($fpos + 18), ($f2pos - $fpos - 18), 'UTF-8'). '}}';
        $organizations = str_replace([':\\"', '\\":', '\\"}', '\\",', ',\\"', '\\"]', '{\\"', '[\\"'], [':"', '":', '"}', '",', ',"', '"]', '{"', '["'], $organizations);
        $organizations = json_decode($organizations);

        $list = [];
        foreach ($organizations as $name => $org) {
            if (empty($rates[$name]))
                continue;
            $list[$org->oldReference] = [
                'name' => trim(str_replace(['\r\n', '\\'], '', $org->name)),
                'logo' => 'https://www.rate.am/images/bankIcons/' . $name . '.svg',
                'branches' => isset($org->branches) ? count($org->branches) : 1,
                'date' => (int) substr((string) $rates[$name]['lastUpdated'], 0, 10),
                'rates' => $rates[$name]['rates']
            ];
        }

        return $list;

        /*
        $fpos2 = mb_strpos($listing, '</table>', 0, 'UTF-8');
        $listing2 = mb_substr($listing, 0, $fpos2, 'UTF-8');
        $list = [];

//        preg_match_all(
//            '~<tr id="([^_"]+)".+src=\'([^\']+)\'.+>([^<]+)</a>'
//            . '.+>(\d+)</a>.+="date".*>([^<]+)?<'
//            . '.+<td.*>(.*)</td>.+<td.*>(.*)</td>.+<td.*>(.*)</td>'
//            . '.+<td.*>(.*)</td>.+<td.*>(.*)</td>.+<td.*>(.*)</td>'
//            . '.+<td.*>(.*)</td>.+<td.*>(.*)</td>~Us',
//            $listing, $matches, PREG_SET_ORDER
//        );

        if (!preg_match_all(
                '~<tr id="([^_"]+)".+src=[\'|"]([^\']+)[\'|"].+>([^<]+)</a>'
                . '.+>(\d+)</a>.+="date".*>([^<]+)?<'
                . '.+<td.*>(.*)</td>.+<td.*>(.*)</td>.+<td.*>(.*)</td>'
                . '.+<td.*>(.*)</td>.+<td.*>(.*)</td>.+<td.*>(.*)</td>'
                . '.+<td.*>(.*)</td>.+<td.*>(.*)</td>~Us',
                $listing, $matches, PREG_SET_ORDER
            ) || empty($matches))
            throw new Exception;

        foreach ($matches as $vals) {
            $list[$vals[1]] = [
                'name' => $vals[3],
                'logo' => $vals[2],
                'branches' => (int) $vals[4],
                'date' => (strtotime(strtr($vals[5], $repl)) ?: 0),
                'rates' => [
                    [
                        (float) preg_replace('/<.+?>/', '', $vals[6]),
                        (float) preg_replace('/<.+?>/', '', $vals[7])
                    ],
                    [
                        (float) preg_replace('/<.+?>/', '', $vals[8]),
                        (float) preg_replace('/<.+?>/', '', $vals[9])
                    ],
                    [
                        (float) preg_replace('/<.+?>/', '', $vals[10]),
                        (float) preg_replace('/<.+?>/', '', $vals[11])
                    ],
                    [
                        (float) preg_replace('/<.+?>/', '', $vals[12]),
                        (float) preg_replace('/<.+?>/', '', $vals[13])
                    ]
                ]
            ];
        }*/

        return $list;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function takeNames(): array
    {
        $list = [];
        $data = $this->takeList();
        foreach ($data as $id => $one) {
            $list[$id] = $one['name'];
        }
        return $list;
        /*
        $spos = mb_strpos($this->html, '<tr id=', 0, 'UTF-8');
        $fpos = mb_strpos($this->html, '</table>', $spos, 'UTF-8');
        $listing = mb_substr($this->html, $spos, $fpos, 'UTF-8');
        $list = [];

        if (!preg_match_all(
                '~<tr id="([^"]+)".+>([^<]+)</a>~Us',
                $listing, $matches, PREG_SET_ORDER
            ) || empty($matches))
            throw new Exception;

        foreach ($matches as $val) {
            $list[$val[1]] = $val[2];
        }

        return $list;*/
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function takeNamesAndUrls(): array
    {
        $spos = mb_strpos($this->html, '<tr id=', 0, 'UTF-8');
        $fpos = mb_strpos($this->html, '</table>', $spos, 'UTF-8');
        $listing = mb_substr($this->html, $spos, $fpos, 'UTF-8');
        $list = [];

        if (!preg_match_all(
                '~<tr id="([^"]+)".+>([^<]+)</a>~Us',
                $listing, $matches, PREG_SET_ORDER
            ) || empty($matches))
            throw new Exception;

//        echo "<pre style='margin-left: 60px;'>";
//        print_r($matches);
//        echo "</pre>";

        foreach ($matches as $val) {
            $url =  explode('href=', $val[0]);
//            $url =  explode('\'', $url[1]);
            $url =  explode('"', $url[1]);
            $list[$val[1]] = [
                'name' => $val[2],
//                'url' => '<a href="https://rate.am' . $url[1] . '">' . $val[2] . '</a>',
                'url' => 'https://rate.am' . $url[1],
            ];
        }

        return $list;
    }

}
