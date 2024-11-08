<?php declare(strict_types=1);

namespace App\Widget;
use Core\Widget,
    App\App,
    Core\Utils\Hdbk,
    App\Model\Course,
    DateTime;

class Converter extends Widget
{

    /**
     * @var array
     */
    public array $converter = [];
    /**
     * @var mixed|null
     */
    private $settings;

    /**
     * @var array
     */
    protected array $currencies = ['USD', 'EUR', 'RUB', 'GBP', 'GEL', 'CHF', 'CAD', 'AED', 'CNY', 'AUD', 'JPY', 'SEK'];

    /**
     * @var string
     */
    public string $tableCurrency;

    /**
     * @var string
     */
    public string $tableCrossCurrency;

    /**
     * @var array
     */
    public array $banksSorted = [];

    /**
     * @var array
     */
    public array $exchangersSorted = [];

    /**
     * @var array
     */
    public array $banksAndExchangersSorted = [];

    /**
     * @var array
     */
    public array $banksSortedCrossSell = [];

    /**
     * @var array
     */
    public array $exchangersSortedCrossSell = [];

    /**
     * @var array
     */
    public array $banksAndExchangersSortedCrossSell = [];

    /**
     * @var string
     */
    public string $transaction;

    /**
     * @var string
     */
    public string $tableType;

    public function __construct(string $type, string $transaction, string $tableType, string $fromCurrency, string $toCurrency, $settings = null, string $tableCurrency  = 'USD')
    {
        $cb = $this->cbCourses();
        $this->settings = $settings;
        $this->tableCurrency = $tableCurrency;
        $this->tableCrossCurrency = $toCurrency;
        $this->tableType = $tableType;

        unset($cb['RUB USD']);
        unset($cb['RUB EUR']);

        $widget = new MainTable('direct', $type);
        $widget->activeSymbols = $this->currencies;
//        $widget->crossSymbols = $this->currencies;

        $curses = $widget->createDirectTable();
//        $curses = ('direct' == $tableType ? $widget->createDirectTable() : $widget->createCrossTable());

        unset($curses[2]);

        if ('cross' == $tableType) {
            if ($curses[0] && is_array($curses[0])) {
                foreach ($curses[0] as $bankKey => $bank) {
                    $pureCurses = [];
                    if ($bank['courses'] && is_array($bank['courses'])) {
                        foreach ($bank['courses'] as $currencyCross => $price) {
//                    $currency = explode('/', $currencyCross);
//                    if ($fromCurrency == $currency[0]) {
//                        $pureCurses[$currency[1]] = ['buy' => $price['price']];
                            if (isset($curses[0][$bankKey]['courses'][$toCurrency]) && $curses[0][$bankKey]['courses'][$toCurrency]['sell']) {
//                        $curses[0][$bankKey]['courses'][$currencyCross] = ['buy' => round($price['buy'] / $curses[0][$bankKey]['courses'][$toCurrency]['sell'], 4)];
                                $pureCurses[$currencyCross]['buy'] =  round(
                                    $price['buy'] / $curses[0][$bankKey]['courses'][$toCurrency]['sell'],
                                    4
                                );
                            }
                            if (isset($curses[0][$bankKey]['courses'][$fromCurrency]) && $curses[0][$bankKey]['courses'][$fromCurrency]['sell']) {
                                $pureCurses[$currencyCross]['sell'] = round(
                                    $price['buy'] / $curses[0][$bankKey]['courses'][$fromCurrency]['sell'],
                                    4
                                );
                            }
//                    }
//                    unset($curses[0][$bankKey]['courses'][$currencyCross]);
                        }
                        $curses[0][$bankKey]['courses'] = $pureCurses;
                    }
                }
            }

            if ($curses[1] && is_array($curses[1])) {
                foreach ($curses[1] as $exchangerKey => $exchanger) {
                    $pureCurses = [];
                    if ($exchanger['courses'] && is_array($exchanger['courses'])) {
                        foreach ($exchanger['courses'] as $currencyCross => $price) {
//                    $currency = explode('/', $currencyCross);
//                    if ($fromCurrency == $currency[0]) {
//                        $pureCurses[$currency[1]] = ['buy' => $price['price']];
                            if (isset($curses[1][$exchangerKey]['courses'][$toCurrency]) && $curses[1][$exchangerKey]['courses'][$toCurrency]['sell']) {
//                            $curses[1][$exchangerKey]['courses'][$currencyCross] = ['buy' => round($price['buy'] / $curses[1][$exchangerKey]['courses'][$toCurrency]['sell'], 4)];
                                $pureCurses[$currencyCross]['buy'] = round(
                                    $price['buy'] / $curses[1][$exchangerKey]['courses'][$toCurrency]['sell'],
                                    4
                                );
                            }
                            if (isset($curses[1][$exchangerKey]['courses'][$fromCurrency]) && $curses[1][$exchangerKey]['courses'][$fromCurrency]['sell']) {
//                            $curses[1][$exchangerKey]['courses'][$currencyCross] = ['buy' => round($price['buy'] / $curses[1][$exchangerKey]['courses'][$toCurrency]['sell'], 4)];
                                $pureCurses[$currencyCross]['sell'] = round(
                                    $price['buy'] / $curses[1][$exchangerKey]['courses'][$fromCurrency]['sell'],
                                    4
                                );
                            }
//                    }
//                    unset($curses[0][$bankKey]['courses'][$currencyCross]);
                        }
                        $curses[1][$exchangerKey]['courses'] = $pureCurses;
                    }
                }
            }
        }

        $currenciesValues = [];

        foreach ($this->currencies as $currency) {
            $currenciesValues[$currency] = ['price' => 0, 'type' => '', 'name' => '', 'logo' => ''];
        }

        $banksBuy = [];
        $banksSell = [];

        if ($curses[0] && is_array($curses[0])) {
            foreach ($curses[0] as $bank) {
                foreach ($bank['courses'] as $currency => $prices) {
                    if (isset($prices['buy']) && $currenciesValues[$currency]['price'] < $prices['buy']) {
                        $currenciesValues[$currency]['price'] = $prices['buy'];
                        $currenciesValues[$currency]['type'] = 'bank';
                        $currenciesValues[$currency]['name'] = $bank['name'];
                        $currenciesValues[$currency]['logo'] = $bank['logo'];
                    }

                    if (isset($prices['buy'])) {
                        $banksBuy[$currency][] = ['name' => $bank['name'], 'type' => 'bank', 'logo' => $bank['logo'], 'price' => $prices['buy']];
                    }

                    if (isset($prices['sell'])) {
                        $banksSell[$currency][] = ['name' => $bank['name'], 'type' => 'bank', 'logo' => $bank['logo'], 'price' => $prices['sell']];
                    }
                }
            }
        }
        $banksSortedBuy = [];

        if ($banksBuy && is_array($banksBuy)) {
            foreach ($banksBuy as $currency => $bank) {
                $price  = array_column($bank, 'price');
                $name = array_column($bank, 'name');

                array_multisort($price, SORT_DESC, $name, SORT_ASC, $bank);

//            $banksSortedBuy[$currency] = array_slice($bank, 0, 5);
                $banksSortedBuy[$currency] = $bank;
            }
        }

        $banksSortedSell = [];

        if ($banksSell && is_array($banksSell)) {
            foreach ($banksSell as $currency => $bank) {
                $price  = array_column($bank, 'price');
                $name = array_column($bank, 'name');

                array_multisort($price, SORT_ASC, $name, SORT_ASC, $bank);

//            $banksSortedSell[$currency] = array_slice($bank, 0, 5);
                $banksSortedSell[$currency] = $bank;
            }
        }

        $exchangersBuy = [];
        $exchangersSell = [];

        if (isset($curses[1]) && is_array($curses[1])) {
            foreach ($curses[1] as $exchanger) {
                foreach ($exchanger['courses'] as $currency => $prices) {
                    if (isset($prices['buy']) && $currenciesValues[$currency]['price'] < $prices['buy']) {
                        $currenciesValues[$currency]['price'] = $prices['buy'];
                        $currenciesValues[$currency]['type'] = 'exchanger';
                        $currenciesValues[$currency]['name'] = $exchanger['name'];
                        $currenciesValues[$currency]['logo'] = $exchanger['logo'];
                    }

                    if (isset($prices['buy'])) {
                        $exchangersBuy[$currency][] = ['name' => $exchanger['name'], 'type' => 'exchanger', 'logo' => $exchanger['logo'], 'price' => $prices['buy']];
                    }

                    if (isset($prices['sell'])) {
                        $exchangersSell[$currency][] = ['name' => $exchanger['name'], 'type' => 'exchanger', 'logo' => $exchanger['logo'], 'price' => $prices['sell']];
                    }
                }
            }
        }

        $exchangersSortedBuy = [];

        if ($exchangersBuy && is_array($exchangersBuy)) {
            foreach ($exchangersBuy as $currency => $exchanger) {
                $price  = array_column($exchanger, 'price');
                $name = array_column($exchanger, 'name');

                array_multisort($price, SORT_DESC, $name, SORT_ASC, $exchanger);

//            $exchangersSortedBuy[$currency] = array_slice($exchanger, 0, 5);
                $exchangersSortedBuy[$currency] = $exchanger;
            }
        }

        $exchangersSortedSell = [];

        if ($exchangersSell && is_array($exchangersSell)) {
            foreach ($exchangersSell as $currency => $exchanger) {
                $price  = array_column($exchanger, 'price');
                $name = array_column($exchanger, 'name');

                array_multisort($price, SORT_ASC, $name, SORT_ASC, $exchanger);

//            $exchangersSortedSell[$currency] = array_slice($exchanger, 0, 5);
                $exchangersSortedSell[$currency] = $exchanger;
            }
        }

        $banksAndExchangersBuy = array_merge_recursive($banksSortedBuy, $exchangersSortedBuy);
        $banksAndExchangersSortedBuy = [];

        if ($banksAndExchangersBuy && is_array($banksAndExchangersBuy)) {
            foreach ($banksAndExchangersBuy as $currency => $bankOrExchanger) {
                $price  = array_column($bankOrExchanger, 'price');
                $name = array_column($bankOrExchanger, 'name');

                array_multisort($price, SORT_DESC, $name, SORT_ASC, $bankOrExchanger);

//            $banksAndExchangersSortedBuy[$currency] = array_slice($bankOrExchanger, 0, 5);
                $banksAndExchangersSortedBuy[$currency] = array_filter($bankOrExchanger, function ($val, $key) use ($bankOrExchanger) {
                    return $val['price'] == $bankOrExchanger[0]['price'];
                }, ARRAY_FILTER_USE_BOTH);
            }
        }

        $banksAndExchangersSell = array_merge_recursive($banksSortedSell, $exchangersSortedSell);
        $banksAndExchangersSortedSell = [];

        if ($banksAndExchangersSell && is_array($banksAndExchangersSell)) {
            foreach ($banksAndExchangersSell as $currency => $bankOrExchanger) {
                $price  = array_column($bankOrExchanger, 'price');
                $name = array_column($bankOrExchanger, 'name');

                array_multisort($price, SORT_ASC, $name, SORT_ASC, $bankOrExchanger);

//            $banksAndExchangersSortedSell[$currency] = array_slice($bankOrExchanger, 0, 5);
                $banksAndExchangersSortedSell[$currency] = array_filter($bankOrExchanger, function ($val, $key) use ($bankOrExchanger) {
                    return $val['price'] == $bankOrExchanger[0]['price'];
                }, ARRAY_FILTER_USE_BOTH);
            }
        }

        if ($cb && is_array($cb)) {
            foreach ($cb as $currency => $course) {
                if (0 === $currenciesValues[$currency]['price']) {
                    unset($cb[$currency]);
                } else {
                    $cb[$currency]['price'] = $currenciesValues[$currency]['price'];
                }
            }
        }

        $this->converter = $cb;
//        $this->banksSortedBuy = $banksSortedBuy;
//        $this->exchangersSortedBuy = $exchangersSortedBuy;
//        $this->banksAndExchangersSortedBuy = $banksAndExchangersSortedBuy;
//        $this->banksSortedSell = $banksSortedSell;
//        $this->exchangersSortedSell = $exchangersSortedSell;
//        $this->banksAndExchangersSortedSell = $banksAndExchangersSortedSell;
        $this->banksSorted = ('buy' == $transaction ? $banksSortedBuy : $banksSortedSell);
        $this->exchangersSorted = ('buy' == $transaction ? $exchangersSortedBuy : $exchangersSortedSell);
        $this->banksAndExchangersSorted = ('buy' == $transaction ? $banksAndExchangersSortedBuy : $banksAndExchangersSortedSell);

        if ('cross' == $this->tableType) {
            $this->banksSortedCrossSell = $banksSortedSell;
            $this->exchangersSortedCrossSell = $exchangersSortedSell;
            $this->banksAndExchangersSortedCrossSell = $banksAndExchangersSortedSell;
        }
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $data = parent::getData();
        $data['settings'] = $this->settings;

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function cbCourses(): array
    {
        $data = [];
        $end = (new \DateTime('now 00:00:00'))->getTimestamp();
        $start = $end - 86400;
        $ids = implode(', ', App::currency()->column('id'));
        $ids .= ', 186, 187';

        $sql = 'SELECT *,'
            . ' (SELECT avg(price)'
            . ' FROM course c2'
            . ' WHERE cid = c1.cid'
            . " AND date_at >= {$start} AND date_at <= {$end}"
            . ') as sr'
            . ' FROM course c1'
            . " WHERE cid IN({$ids})"
            . ' GROUP BY cid'
            . ' HAVING date_at = MAX(date_at)';
        $query = App::db()->query($sql);

        while ($res = $query->fetch()) {
            $symbol = App::currency()->findOne(['id' => (int) $res['cid']]);
            if (!$symbol)
                $symbol = App::otherCurrency()->findOne(['id' => (int) $res['cid']]);

            $data[$symbol->symbol] = [
                'symbol' => $symbol,
                'price' => $res['price'],
                'diff' => ($res['sr'] && $res['sr'] !== $res['price'])
                    ? round(($res['price'] - $res['sr']), 4) : '0.00'
            ];
        }
        return $data;
    }

    public function renderTable()
    {
        return static::render("Converter_table", true);
    }
}