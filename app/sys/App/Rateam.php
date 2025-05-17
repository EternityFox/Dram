<?php declare(strict_types=1);

namespace App;

use Exception;

class Rateam
{

    /**
     * @var array
     */
    protected static array $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Connection: keep-alive',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: none',
        'Sec-Fetch-User: ?1',
        'Upgrade-Insecure-Requests: 1',
        'Referer: https://www.rate.am/ru/armenian-dram-exchange-rates/banks/non-cash?tp=0&rt=1'
    ];

    /**
     * @var string
     */
    protected string $html = '';
    protected string $path = '';
    protected string $pack = '';
    protected string $href = '';
    /**
     * @var resource
     */
    protected $ch;

    /**
     */
    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt_array($this->ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:92.0) Gecko/20100101 Firefox/92.0',
            CURLOPT_HTTPHEADER => static::$headers
        ]);
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

        curl_setopt($this->ch, CURLOPT_FILE, $fh);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_exec($this->ch);
        fclose($fh);

        // ВОССТАНАВЛИВАЕМ
        curl_setopt($this->ch, CURLOPT_FILE, null);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

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
    public function &loadPage(string $path, string $symbols, string $lang = 'ru'): string
    {
        $this->path = $path;
        $this->pack = $symbols;

        $headers = static::$headers;
        $headers[] = "Cookie: Cookie.CurrencyList={$symbols}";

        curl_setopt($this->ch, CURLOPT_URL, "https://www.rate.am/{$lang}/armenian-dram-exchange-rates/{$path}");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($this->ch);

        if ($response === false) {
            throw new Exception("Empty response body: " . curl_error($this->ch));
        }

        $code = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE);
        if ($code !== 200) {
            throw new Exception("ResponseCode: {$code}");
        }

        $this->html = $response;
        return $this->html;
    }

    public function &loadInfoPage(
        string $path, string $url, string $lang = 'ru'
    ): string
    {
        $this->path = $path;
        $this->href = $url;
        $headers = static::$headers;

        curl_setopt(
            $this->ch,
            CURLOPT_URL,
            "https://www.rate.am/{$lang}/{$path}/{$url}"
        );
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($this->ch);
        $contentType = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);

        if ($response === false) {
            throw new \Exception("Empty response body: " . curl_error($this->ch));
        }

        if (strpos($contentType, 'text/html') === false) {
            throw new \Exception("Unexpected Content-Type: {$contentType}");
        }

        $code = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE);
        if ($code !== 200) {
            throw new \Exception("ResponseCode: {$code}");
        }

        $this->html = $response;
        return $this->html;
    }

    /**
     * @return array
     * @throws \Exception
     */
    function takeInfoList($id)
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($this->html);
        $xpath = new \DOMXPath($dom);

        $main = $xpath->query('//main')[0];
        if (!$main) return [];

        $nameNode = $xpath->query('.//h1', $main)[0];
        $orgName = $nameNode ? trim($nameNode->nodeValue) : '';

        $branchBlocks = $xpath->query("//div[contains(@class, 'flex') and contains(@class, 'flex-col') and contains(@class, 'items-start') and contains(@class, 'border') and contains(@class, 'rounded-lg')]");
        $branches = [];

        foreach ($branchBlocks as $block) {
            $childDivs = (new \DOMXPath($block->ownerDocument))->query(".//div[@id]", $block);

            foreach ($childDivs as $child) {
                $childXpath = new \DOMXPath($child->ownerDocument);

                $cityNameNode = $childXpath->query(".//span[contains(@class, 'font-bold') and contains(@class, 'text-base')]", $child);
                $addressNode = $childXpath->query(".//span[contains(@class, 'gap-2')]", $child);
                $phoneNodes = $childXpath->query(".//a[starts-with(@href, 'tel:')]", $child);
                $emailNodes = $childXpath->query(".//a[starts-with(@href, 'mailto:')]", $child);
                $siteNodes = $childXpath->query(".//a[contains(@class, 'gap-2') and contains(@target, '_blank')]", $child);
                $socialNodes = $childXpath->query(".//div[contains(@class, 'gap-5')]//a", $child);
                $latNode = $childXpath->query(".//input[@name='latitude']", $child);
                $lngNode = $childXpath->query(".//input[@name='longitude']", $child);
                $licenseNode = $xpath->query(".//div[contains(@class, 'text-xs') and contains(text(), 'Лицензия:')]/span", $main);
                $license = $licenseNode->length > 0 ? trim($licenseNode->item(0)->textContent) : '';

                $imgNode = $childXpath->query(".//img", $child);
                $imgUrl = '';

                if ($imgNode->length > 0) {
                    $srcRaw = $imgNode->item(0)->getAttribute('src');
                    if (strpos($srcRaw, 'undefined.svg') !== false) {
                        $imgUrl = '';
                    } else {
                        $parsed = parse_url($srcRaw);
                        $q = [];
                        if (isset($parsed['query'])) {
                            parse_str($parsed['query'], $q);
                        }
                        if (!empty($q['url'])) {
                            $imgUrl = $q['url'];
                        } else {
                            $imgUrl = 'https://rate.am' . $srcRaw;
                        }

                        if (
                            $imgUrl
                            && (
                                !file_exists(App::path("img/logos/{$id}.webp"))
                                || filemtime(App::path("img/logos/{$id}.webp")) < (time() - 86400 * 3)
                            )
                        ) {
                            $imgfile = App::path("img/logos/{$id}.webp");
                            @unlink($imgfile);

                            if (!$this->download($imgUrl, $imgfile)) {
                                echo "\r\n" . 'Filed create image from: ' . $imgUrl;
                                @unlink($imgfile);
                            }
                        }

                        $imgUrl = "img/logos/{$id}.webp";
                    }
                }
                $hours = '';
                $hoursBlock = $childXpath->query(".//div[contains(@class, 'gap-1.5')]", $child);
                foreach ($hoursBlock as $block) {
                    if (strpos($block->textContent, 'Понедельник') !== false ||
                        strpos($block->textContent, 'Круглосуточно') !== false ||
                        strpos($block->textContent, 'Работает') !== false) {
                        $hourLines = $childXpath->query(".//span[contains(@class, 'flex') and contains(@class, 'items-center') and contains(@class, 'justify-between')]", $block);
                        if ($hourLines->length > 0) {
                            foreach ($hourLines as $line) {
                                $hours .= trim($line->textContent) . "<br />";
                            }
                        } else {
                            $hours = trim($block->textContent);
                        }
                        break;
                    }
                }

                $name = $cityNameNode->length ? trim($cityNameNode->item(0)->textContent) : '';
                if (!$name) continue;

                $address = $addressNode->length ? trim($addressNode->item(0)->textContent) : '';

                $phones = [];
                foreach ($phoneNodes as $node) {
                    $phones[] = [
                        'text' => trim($node->textContent),
                        'href' => $node->getAttribute('href'),
                    ];
                }

                $emails = [];
                foreach ($emailNodes as $node) {
                    $emails[] = [
                        'text' => trim($node->textContent),
                        'href' => $node->getAttribute('href'),
                    ];
                }

                $sites = [];
                foreach ($siteNodes as $node) {
                    $href = $node->getAttribute('href');
                    if (filter_var($href, FILTER_VALIDATE_URL)) {
                        $sites[] = $href;
                    }
                }

                $latitude = $latNode->length ? $latNode->item(0)->getAttribute('value') : '';
                $longitude = $lngNode->length ? $lngNode->item(0)->getAttribute('value') : '';

                $socials = [];
                foreach ($socialNodes as $s) {
                    $href = $s->getAttribute('href');
                    if (!in_array($href, $socials)) {
                        $socials[] = $href;
                    }
                }

                $branches[$name] = [
                    'name' => $name,
                    'address' => $address,
                    'phones' => $phones,
                    'emails' => $emails,
                    'of_sites' => $sites,
                    'socials' => $socials,
                    'hours' => $hours,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'img' => $imgUrl,
                    'license' => $license
                ];
            }
        }

        return [
            'name' => $orgName,
            'baranches' => $branches,
        ];
    }


    public function takeList(): array
    {
        $spos = mb_strpos($this->html, '\\"organizationRates\\"', 0, 'UTF-8');
        $fpos = mb_strpos($this->html, '\\"organizations\\"', $spos, 'UTF-8');
        $rates = mb_substr($this->html, ($spos + 22), ($fpos - $spos - 23), 'UTF-8');
        $rates = str_replace('\\', '', $rates);
        $rates = json_decode($rates, true);

        //$f2pos = mb_strpos($this->html, ',[\\"$\\"', $fpos, 'UTF-8');
        $f2pos = mb_strpos($this->html, '}},\\"organizationType\\"', $fpos, 'UTF-8');
        $organizations = mb_substr($this->html, ($fpos + 18), ($f2pos - $fpos - 18), 'UTF-8') . '}}';
        $organizations = str_replace([':\\"', '\\":', '\\"}', '\\",', ',\\"', '\\"]', '{\\"', '[\\"'], [':"', '":', '"}', '",', ',"', '"]', '{"', '["'], $organizations);
        $organizations = json_decode($organizations);
        $list = [];
        foreach ($organizations as $name => $org) {
            if (empty($rates[$name]))
                continue;
            $list[$org->oldReference] = [
                'name' => trim(str_replace(['\r\n', '\\'], '', $org->name)),
                'slug' => $org->slug == null ? $org->code : $org->slug,
                'logo' => 'https://www.rate.am/images/bankIcons/' . $name . '.svg',
                'branches' => isset($org->branches) ? count($org->branches) : 1,
                'date' => (int)substr((string)$rates[$name]['lastUpdated'], 0, 10),
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
            $url = explode('href=', $val[0]);
//            $url =  explode('\'', $url[1]);
            $url = explode('"', $url[1]);
            $list[$val[1]] = [
                'name' => $val[2],
//                'url' => '<a href="https://rate.am' . $url[1] . '">' . $val[2] . '</a>',
                'url' => 'https://rate.am' . $url[1],
            ];
        }

        return $list;
    }

}
