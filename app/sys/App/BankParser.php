<?php declare(strict_types=1);

namespace App;

class BankParser
{

    protected $ch;
    protected array $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;'
        . 'q=0.9,image/webp,*\/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Connection: keep-alive',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: none',
        'Sec-Fetch-User: ?1',
        'Upgrade-Insecure-Requests: 1'
    ];

    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt_array(
            $this->ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => $this->headers,
                CURLOPT_COOKIEFILE => ''
            ]
        );
    }

    public function fastbank()
    {
        $urls = [
            'cash' => 'https://mobileapi.fcc.am/FCBank.Mobile.Api_V2/api/publicInfo/getRates?langID=1',
            'noncash' => 'https://mobileapi.fcc.am/FCBank.Mobile.Api_V2/api/publicInfo/getRates?langID=1&payType=0'
        ];

        $result = [];
        foreach ($urls as $type => $url) {
            curl_setopt($this->ch, CURLOPT_URL, $url);
            if (!($res = curl_exec($this->ch)) || !($json = json_decode($res)))
                continue;

            foreach ($json->Rates as $rate) {
                $result[] = [
                    'symbol' => $rate->Id,
                    'type' => $type,
                    'buy' => $rate->Buy,
                    'sell' => $rate->Sale
                ];
            }
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function armswissbank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://www.armswissbank.am/include/ajax.php?asd');
        if (!($res = curl_exec($this->ch)) || !($json = json_decode($res)))
            return [];

        foreach ($json->lmasbrate as $rate) {
            $result[] = [
                'symbol' => $rate->ISO,
                'type' => 'cash',
                'buy' => $rate->BID_cash,
                'sell' => $rate->OFFER_cash
            ];
            $result[] = [
                'symbol' => $rate->ISO,
                'type' => 'noncash',
                'buy' => $rate->BID,
                'sell' => $rate->OFFER
            ];

            if (empty($result['updated_at']) && $rate->inserttime)
                $result['updated_at'] = strtotime($rate->inserttime);
        }

        return $result;
    }

    public function ardshinbank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://website-api.ardshinbank.am/currency');
        if (!($res = curl_exec($this->ch)) || !($json = json_decode($res)))
            return [];

        foreach ($json->data->currencies as $type => $rates) {
            if ('no_cash' === $type)
                $type = 'noncash';

            foreach ($rates as $rate) {
                $result[] = [
                    'symbol' => $rate->type,
                    'type' => $type,
                    'buy' => (float) $rate->buy,
                    'sell' => (float) $rate->sell
                ];
            }
        }

        $result['updated_at'] = strtotime($json->updatedAt);

        return $result;
    }

    public function vtb()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://www.vtb.am/ru/currency');
        if (
            !($res = curl_exec($this->ch))
            || !($pos = mb_strpos($res, 'let currencyObj =', 0, 'UTF-8'))
        )
            return [];

        $res = trim(mb_substr(
                        $res, ($pos + 17), (mb_strpos($res, '};', $pos, 'UTF-8') - $pos - 16), 'UTF-8'
                    ));
        $res = str_replace([' ', "\r", "\n", ',}', ',]', '\''], ['', '', '', '}', ']', '"'], $res);
        if (!($json = json_decode($res, true)))
            return [];

        foreach ($json as $type => $rates) {
            if ('nonCash' == $type)
                $type = 'noncash';

            foreach ($rates as $rate) {
                $symbol = array_key_first($rate);
                $result[] = [
                    'symbol' => $symbol,
                    'type' => $type,
                    'buy' => (float) $rate[$symbol]['buy'],
                    'sell' => (float) $rate[$symbol]['sale']
                ];
            }
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function conversebank()
    {
        $result = [];

        $headers = $this->headers;
        $headers[1] = 'Accept-Language: hy';
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_URL, 'https://sapi.conversebank.am/api/v2/currencyrates');
        if (!($res = curl_exec($this->ch)) || !($json = json_decode($res))) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

            return [];
        } else {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        foreach ($json as $type => $rates) {
            if ('Card' === $type)
                $type = 'card';
            elseif ('Cash' === $type)
                $type = 'cash';
            elseif ('Non Cash' === $type)
                $type = 'noncash';
            else
                continue;

            $convert = [];
            foreach ($rates as $rate) {
                $result[] = [
                    'symbol' => $rate->currency->iso,
                    'type' => $type,
                    'buy' => (float) $rate->buy,
                    'sell' => (float) $rate->sell
                ];

                if ('AMD' !== $rate->iso2)
                    $convert[array_key_last($result)] = $rate->iso2;
            }

            foreach ($convert as $key => $from) {
                $item = &$result[$key];
                foreach ($result as $res) {
                    if ($res['type'] !== $type || $res['symbol'] !== $from)
                        continue;

                    $item['buy'] = (int) ($item['buy'] * $res['sell']);
                    $item['sell'] = (int) ($item['sell'] * $res['buy']);
                }
            }
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function inecobank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://www.inecobank.am/api/rates/');
        if (!($res = curl_exec($this->ch)) || !($json = json_decode($res)))
            return [];

        $types = [
            'cash' => 'cash',
            'cashless' => 'noncash',
            'card' => 'card'
        ];

        foreach ($json->items as $rate) {
            foreach ($types as $key => $type) {
                if (empty($rate->{$key}))
                    continue;

                $result[] = [
                    'symbol' => $rate->code,
                    'type' => $type,
                    'buy' => (float) $rate->{$key}->buy,
                    'sell' => (float) $rate->{$key}->sell
                ];
            }
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function mellatbank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://api.mellatbank.am/api/v1/rate/list?');
        if (!($res = curl_exec($this->ch)) || !($json = json_decode($res)))
            return [];

        foreach ($json->result->data as $rate) {
            if (isset($rate->buy) && isset($rate->sell))
                $result[] = [
                    'symbol' => $rate->currency,
                    'type' => 'noncash',
                    'buy' => (float) $rate->buy,
                    'sell' => (float) $rate->sell
                ];
            if (isset($rate->buyCash) && isset($rate->sellCash))
                $result[] = [
                    'symbol' => $rate->currency,
                    'type' => 'cash',
                    'buy' => (float) $rate->buy,
                    'sell' => (float) $rate->sell
                ];
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function ameriabank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://ameriabank.am/en/');
        curl_exec($this->ch);
        curl_setopt($this->ch, CURLOPT_URL, 'https://ameriabank.am/en/');
        if (!($res = curl_exec($this->ch)))
            return [];
        elseif (!preg_match_all(
            '/<td align="left">(.+?)<\/td>'
            . '.*?<td.*?>(.*?)<\/td>'
            . '.*?<td.*?>(.*?)<\/td>'
            . '.*?<td.*?>(.*?)<\/td>'
            . '.*?<td.*?>(.*?)<\/td>/',
            $res,
            $matches,
            PREG_SET_ORDER
        ))
            return $res;

        foreach ($matches as $rate) {
            if (is_numeric($rate[2]) && is_numeric($rate[3]))
                $result[] = [
                    'symbol' => $rate[1],
                    'type' => 'cash',
                    'buy' => (float) $rate[2],
                    'sell' => (float) $rate[3]
                ];
            if (is_numeric($rate[4]) && is_numeric($rate[5]))
                $result[] = [
                    'symbol' => $rate[1],
                    'type' => 'noncash',
                    'buy' => (float) $rate[4],
                    'sell' => (float) $rate[5]
                ];
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function unibank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://www.unibank.am/en/');
        if (!($res = curl_exec($this->ch)))
            return [];
        elseif (!preg_match_all(
            '/<li><span>(.*?)<\/span><\/li>'
            . '.*?<li><span>(.*?)<\/span><\/li>'
            . '.*?<li><span>(.*?)<\/span><\/li>/s',
            $res,
            $matches,
            PREG_SET_ORDER
        ))
            return $res;

        $type = 'cash';
        $symbols = [];
        foreach ($matches as $rate) {
            if (isset($symbols[$rate[1]])) {
                ++$symbols[$rate[1]];
                if (2 == $symbols[$rate[1]] && $type == 'cash')
                    $type = 'noncash';
                elseif (3 == $symbols[$rate[1]])
                    break;
            } elseif ($type !== 'noncash') {
                $type = 'cash';
                $symbols[$rate[1]] = 1;
            }

            if (is_numeric($rate[2]) && is_numeric($rate[3]))
                $result[] = [
                    'symbol' => $rate[1],
                    'type' => $type,
                    'buy' => (float) $rate[2],
                    'sell' => (float) $rate[3]
                ];
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function byblosbank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://www.byblosbankarmenia.am/en');
        if (!($res = curl_exec($this->ch)))
            return [];
        elseif (!preg_match_all(
            '/<span class="text text-18 color-grey font-uppercase">([^<]+)<\/span>'
            . '.*?<td class="text text-18 color-grey">([^<]+)<\/td>'
            . '.*?<td class="text text-18 color-grey">([^<]+)<\/td>/s',
            $res,
            $matches,
            PREG_SET_ORDER
        ))
            return $res;

        $symbols = [];
        $type = 'cash';
        foreach ($matches as $rate) {
            $rate = [$rate[0], trim($rate[1]), trim($rate[2]), trim($rate[3])];
            if (isset($symbols[$rate[1]])) {
                ++$symbols[$rate[1]];
                if (2 == $symbols[$rate[1]] && $type == 'cash')
                    $type = 'noncash';
                elseif (3 == $symbols[$rate[1]])
                    break;
            } elseif ($type !== 'noncash') {
                $type = 'cash';
                $symbols[$rate[1]] = 1;
            }

            if (is_numeric($rate[2]) && is_numeric($rate[3]))
                $result[] = [
                    'symbol' => $rate[1],
                    'type' => $type,
                    'buy' => (float) $rate[2],
                    'sell' => (float) $rate[3]
                ];
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function araratbank()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://www.araratbank.am/en/');
        if (!($res = curl_exec($this->ch)))
            return [];
        elseif (!preg_match_all(
            '/<td class="exchange__table-cell fb fs20">(.+?)<\/td>'
            . '.*?<td class="exchange__table-cell fs20">(.*?)<\/td>'
            . '.*?<td class="exchange__table-cell fs20">(.*?)<\/td>/s',
            $res,
            $matches,
            PREG_SET_ORDER
        ))
            return $res;

        $symbols = [];
        $type = 'cash';
        foreach ($matches as $rate) {
            $rate = [$rate[0], trim($rate[1]), trim($rate[2]), trim($rate[3])];
            if (isset($symbols[$rate[1]])) {
                ++$symbols[$rate[1]];
                if (2 == $symbols[$rate[1]] && $type == 'cash')
                    $type = 'noncash';
                elseif (3 == $symbols[$rate[1]])
                    break;
            } elseif ($type !== 'noncash') {
                $type = 'cash';
                $symbols[$rate[1]] = 1;
            }

            if (is_numeric($rate[2]) && is_numeric($rate[3]))
                $result[] = [
                    'symbol' => $rate[1],
                    'type' => $type,
                    'buy' => (float) $rate[2],
                    'sell' => (float) $rate[3]
                ];
        }

        $result['updated_at'] = time();

        return $result;
    }

    public function acba()
    {
        $result = [];

        curl_setopt($this->ch, CURLOPT_URL, 'https://www.acba.am/en/');
        if (!($res = curl_exec($this->ch)))
            return [];
        elseif (!preg_match_all(
            '/<img src=\'pics\/currency\/([A-Z]+)\.png\' alt="" \/>'
            . '.*?<div class=\'price-num\'>(.*?)<\/div>'
            . '.*?<div class=\'price-num\'>(.*?)<\/div>/s',
            $res,
            $matches,
            PREG_SET_ORDER
        ))
            return $res;

        $symbols = [];
        $type = 'cash';
        foreach ($matches as $rate) {
            $rate = [$rate[0], trim($rate[1]), trim($rate[2]), trim($rate[3])];
            if (isset($symbols[$rate[1]])) {
                ++$symbols[$rate[1]];
                if (2 == $symbols[$rate[1]] && $type == 'cash')
                    $type = 'noncash';
                elseif (3 == $symbols[$rate[1]] && $type == 'noncash')
                    $type = 'card';
                elseif (4 == $symbols[$rate[1]])
                    break;
            } elseif ($type !== 'noncash' && $type !== 'card') {
                $type = 'cash';
                $symbols[$rate[1]] = 1;
            }

            if (is_numeric($rate[2]) && is_numeric($rate[3]))
                $result[] = [
                    'symbol' => $rate[1],
                    'type' => $type,
                    'buy' => (float) $rate[2],
                    'sell' => (float) $rate[3]
                ];
        }

        $result['updated_at'] = time();

        return $result;
    }

}

//$p = new BankParser;
//var_dump($p->unibank());
