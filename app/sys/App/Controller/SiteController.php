<?php declare(strict_types=1);

namespace App\Controller;

//require "vendor/autoload.php";

use App\Model\Currency;
use App\Model\Exchanger;
use App\Rateam;
use Core\Controller,
    App\App,
    App\Widget\MainTable,
    App\Widget\BestExchangers,
    App\Widget\IntlCourses,
    App\Widget\Converter;
use Core\Widget;

//use PHPHtmlParser\Dom;

class SiteController extends Controller
{
    /**
     * @param string|null $course
     * @param string|null $type
     *
     * @return array
     */
    protected function actionIndex(
        ?string $course = null, ?string $type = null
    )
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();
        return [
            'site/index',
            [
                'mainTable' => new MainTable($course, $type, $settings),
                'bestExchangers' => new BestExchangers,
                'intlCourses' => new IntlCourses($settings),
                'converter' => new Converter('cash', 'buy', 'direct', 'USD', 'AMD', $settings),
                'settings' => $settings,
                'menu' => $menu,
                'navigations' => $navigations,
            ]
        ];
    }

    protected function actionBank($id)
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

        $bankInfo = unserialize(file_get_contents(__DIR__ . '/../../../storage/bank_info_pure.txt'));
        if (empty($bankInfo[$id]))
            return $this->actionNotFound();
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/bank',
            [
                'settings' => $settings,
                'menu' => $menu,
                'bankInfo' => $bankInfo[$id],
                'navigations' => $navigations,
                'id' => $id,
                'model' => App::exchanger()->get(10)
            ]
        ];
    }
    protected function actionFuelCompany($id)
    {
        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        // Извлечение данных о топливной компании из базы данных
        $stmt = App::db()->prepare("SELECT * FROM fuel_companies WHERE id = ?");
        $stmt->execute([$id]);
        $fuelCompanyInfo = $stmt->fetch();

        if (!$fuelCompanyInfo) {
            return $this->actionNotFound();
        }

        // Подготовка данных для представления
        $address = $fuelCompanyInfo['address'] ?? '-';
        $phones = json_decode($fuelCompanyInfo['phones'], true) ?? [];
        $emails = json_decode($fuelCompanyInfo['emails'], true) ?? [];
        $website = $fuelCompanyInfo['website'] ?? '';
        $socials = json_decode($fuelCompanyInfo['socials'], true) ?? [];
        $workingHours = json_decode($fuelCompanyInfo['working_hours'], true) ?? [];

        return [
            'site/fuel_company',
            [
                'settings' => $settings,
                'menu' => $menu,
                'fuelCompanyInfo' => $fuelCompanyInfo,
                'address' => $address,
                'phones' => $phones,
                'emails' => $emails,
                'website' => $website,
                'socials' => $socials,
                'workingHours' => $workingHours,
                'id' => $id,
            ]
        ];
    }

    protected function actionExchanger($id)
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

        $bankInfo = unserialize(file_get_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt'));
        if (empty($bankInfo[$id]))
            return $this->actionNotFound();
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/exchanger',
            [
                'settings' => $settings,
                'menu' => $menu,
                'bankInfo' => $bankInfo[$id],
                'id' => $id,
                'navigations' => $navigations,
            ]
        ];
    }

    protected function staticWidgetPage(Widget $widget, $settings)
    {
//        $query = App::db()->query("SELECT * FROM settings");
//        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/static_widget',
            [
                'settings' => $settings,
                'menu' => $menu,
                'widget' => $widget,
                'navigations' => $navigations,
            ]
        ];
    }

    protected function actionConverter()
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        return $this->staticWidgetPage(new Converter('cash', 'buy', 'direct', 'USD', 'AMD', $settings), $settings);
    }

    protected function actionCharts()
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        return $this->staticWidgetPage(new IntlCourses($settings), $settings);
    }

    protected function staticPage(string $pageName)
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

        $content = unserialize(file_get_contents(__DIR__ . '/../../../storage/static/' . $pageName . '.txt'));
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/static',
            [
                'settings' => $settings,
                'menu' => $menu,
                'content' => $content,
                'navigations' => $navigations,
            ]
        ];
    }

    protected function actionAbout()
    {

        return $this->staticPage('about');
    }

    protected function actionFaq()
    {

        return $this->staticPage('faq');
    }

    protected function actionContacts()
    {

        return $this->staticPage('contacts');
    }

    protected function actionAdvertising()
    {

        return $this->staticPage('advertising');
    }

    protected function actionNotFound()
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        if (is_array($menuLeft))
            unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

//        $content = unserialize(file_get_contents(__DIR__ . '/../../../storage/static/' . $pageName . '.txt'));
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/404',
            [
                'settings' => $settings,
                'menu' => $menu,
//                'content' => $content,
                'navigations' => $navigations,
            ]
        ];
    }

    /**
     * @param string $course
     * @param string $type
     */
    protected function actionTable(string $course, string $type)
    {
        $widget = new MainTable($course, $type);

        header('Content-Type: application/json');
        echo json_encode(['table' => $widget->renderTable(true)]);
    }

    /**
     * @param int $num
     * @param string $symbol
     * @param string|null $course
     * @param string|null $type
     */
    public function actionChangeSymbol(
        int $num, string $symbol, ?string $course = null, ?string $type = null
    )
    {
        $widget = new MainTable($course, $type);

        $symbol = strtoupper($symbol);
        $request = App::request();

        if (0 > $num || 3 < $num)
            $num = 0;

        if (App::currency()->has($symbol)) {
            $symbols = $widget->activeSymbols;
            if (false !== ($key = array_search($symbol, $symbols))) {
                $symbols[$num] = $symbol;
                $symbols[$key] = '';
                foreach ($widget->config('baseSymbols') as $name) {
                    if (in_array($name, $symbols))
                        continue;
                    $symbols[$key] = $name;
                    break;
                }
            } else {
                $symbols[$num] = $symbol;
            }
            $symbols = $widget->activeSymbols = array_values($symbols);
            $request->setCookie('tableSymbols', implode(',', $symbols));
        }

        ob_start();

        header('Content-Type: application/json');
        echo json_encode([
            'symbolPanel' => $widget->renderSymbolPanel(true),
            'table' => $widget->renderTable(true)
        ]);
    }

    protected function actionChart(string $symbol)
    {
        $labels = $data = [];
        if (($curr = App::currency()->get($symbol))
            || ($curr = App::otherCurrency()->get(rawurldecode($symbol)))
        ) {
            $start = (new \DateTime('now 00:00:00 -7 day'))->getTimestamp();
            $sql = 'SELECT * FROM course'
                . " WHERE cid = {$curr['id']} AND date_at > {$start}"
                . ' ORDER BY date_at DESC';
            $query = App::db()->query($sql);
            while ($res = $query->fetch()) {
                $data[date('Y.m.d', (int)$res['date_at'])][] = $res['price'];
            }
            $data = array_reverse($data);
            foreach ($data as $key => $val) {
                $data[$key] = array_sum($val) / count($val);
            }
        } elseif (($curr = App::crypto()->get($symbol))
            || ($curr = App::metall()->get($symbol))
        ) {
            $sql = 'SELECT * FROM course'
                . " WHERE cid = {$curr['id']}"
                . ' ORDER BY date_at DESC LIMIT 0, 24';
            $query = App::db()->query($sql);
            while ($res = $query->fetch()) {
                $data[date('H:i', (int)$res['date_at'])] = $res['price'];
            }
            $data = array_reverse($data);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'symbol' => $curr['symbol'],
            'labels' => array_keys($data),
            'data' => array_values($data),
        ]);
    }

    public function actionPlateSearch()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Метод не разрешен"]);
            exit;
        }

        $plateNumber = $_POST['plate_number'] ?? null;
        if (!$plateNumber) {
            echo json_encode(["status" => "error", "message" => "Введите номерной знак"]);
            exit;
        }

        if (
            preg_match('/^\d{3}/', $plateNumber) && (preg_match('/^000/', $plateNumber) || preg_match('/00$/', $plateNumber)) ||
            preg_match('/\d{3}$/', $plateNumber) && (preg_match('/^00/', $plateNumber) || preg_match('/000$/', $plateNumber))
        ) {
            echo json_encode([
                "status" => "error",
                "message" => "В поиск не попадают номера с нулями"
            ]);
            exit;
        }

        $session = curl_init();
        $url = "https://roadpolice.am/ru/plate-number-search";
        curl_setopt($session, CURLOPT_URL, $url);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_HEADER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($session, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");

        $response = curl_exec($session);
        preg_match('/XSRF-TOKEN=([^;]+)/', $response, $tokenMatch);
        preg_match('/rd_session=([^;]+)/', $response, $sessionMatch);

        if (empty($tokenMatch[1]) || empty($sessionMatch[1])) {
            echo json_encode(["status" => "error", "message" => "Не удалось получить XSRF-TOKEN или сессию"]);
            exit;
        }
        $csrfToken = urldecode($tokenMatch[1]);
        $rdSession = $sessionMatch[1];
        $postData = http_build_query(["number" => strtoupper($plateNumber)]);
        $headers = [
            "Content-Type: application/x-www-form-urlencoded",
            "X-XSRF-TOKEN: $csrfToken",
            "Cookie: XSRF-TOKEN=$csrfToken; rd_session=$rdSession",
            "Referer: $url",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        ];

        curl_setopt($session, CURLOPT_URL, $url);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($session);
        $httpCode = curl_getinfo($session, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($session, CURLINFO_CONTENT_TYPE);
        curl_close($session);

        if (strpos($contentType, "text/html") !== false) {
            echo json_encode([
                "status" => "error",
                "message" => "Сервер вернул HTML-код",
                "http_code" => $httpCode
            ]);
            exit;
        }

        if ($httpCode == 200) {
            echo $response;
        } else {
            echo json_encode(["status" => "error", "message" => "Ошибка HTTP: " . $httpCode, "response" => $response]);
        }
        exit;
    }

    protected function actionConverterAjax(string $type, string $fromCurrency, string $toCurrency)
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $tableCurrency = ('AMD' == $fromCurrency ? $toCurrency : $fromCurrency);

        $transaction = ('AMD' == $fromCurrency ? 'sell' : 'buy');
        $tableType = (('AMD' != $fromCurrency && 'AMD' != $toCurrency) ? 'cross' : 'direct');

        $converter = new Converter($type, $transaction, $tableType, $fromCurrency, $toCurrency, $settings, $tableCurrency);

//        if ('AMD' != $fromCurrency && 'AMD' != $toCurrency) {
//            foreach ($converter->banksSorted[$fromCurrency] as $key => $item) {
//                $converter->banksSorted[$fromCurrency][$key]['price'] = round($item['price'] / $converter->converter[$toCurrency]['price'], 2);
//            }
//
//            foreach ($converter->exchangersSorted[$fromCurrency] as $key => $item) {
//                $converter->exchangersSorted[$fromCurrency][$key]['price'] = round($item['price'] / $converter->converter[$toCurrency]['price'], 2);
//            }
//
//            foreach ($converter->banksAndExchangersSorted[$fromCurrency] as $key => $item) {
//                $converter->banksAndExchangersSorted[$fromCurrency][$key]['price'] = round($item['price'] / $converter->converter[$toCurrency]['price'], 2);
//            }
//        }

        header('Content-Type: application/json');
        echo json_encode([
//            'converter' => $converter->converter,
//            'banksSorted' => $converter->banksSorted,
//            'exchangersSorted' => $converter->exchangersSorted,
//            'banksAndExchangersSorted' => $converter->banksAndExchangersSorted,
            'data' => $converter->renderTable(),
        ]);
    }

    protected function actionBigParseBank()
    {
        echo <<<HTML
            <h1 style="text-align: center;">BigParseBank</h1>
HTML;

        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

//        $rateam = new Rateam;
//
//        $symbolPack = ['USD', 'EUR', 'RUR', 'GBP'];
//        $pack = implode(',', $symbolPack);
//
//        $rateam->loadPage('banks/cash', $pack);
//
//        $exList = $rateam->takeNamesAndUrls();
//
////        echo "<pre style='margin-left: 60px;'>";
////        print_r(count($exList));
////        echo "</pre>";
////
////        echo "<pre style='margin-left: 60px;'>";
////        print_r($exList);
////        echo "</pre>";
//
//        $dom = new Dom;
//        foreach ($exList as $raid => $item) {
////            $raid = 'c4a69322-c3c6-46c0-8edd-1b10cb90d100';
////            $item['url'] = $exList[$raid]['url'];
//            $dom->loadFromUrl($item['url']);
////            $html = $dom->outerHtml;
//
//            $el = $dom->find('table.bankpagebankcontact')[0];
//
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($el->outerHtml);
////            echo "</pre>";
//
//            $lines = $el->find('tr');
//
////            $res = [];
//
////        foreach ($lines as $line) {
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($line->find('td')[0]->outerHtml);
////            echo "</pre>";
////            $res[$line->find('th')[0]->outerHtml] = $line->find('td')[0]->outerHtml;
////        }
//
//            $data = [
//                'head_office' => $lines[0]->find('td')[0]->outerHtml,
//                'phone' => $lines[1]->find('td')[0]->outerHtml,
//                'fax' => $lines[2]->find('td')[0]->outerHtml,
//                'url' => $lines[4]->find('td')[0]->outerHtml,
//            ];
//
//            $branchs = $dom->find('table#search_rb')[0];
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($branchs->outerHtml);
////            echo "</pre>";
//
//            $branchList = $branchs->find('tr');
//
////        echo "<pre style='margin-left: 60px;'>";
////        print_r(count($branchList));
////        echo "</pre>";
//
//            $branchData = [];
//
//            $i = 0;
//            foreach ($branchList as $branchItem) {
//                $i++;
//
//                if (1 == $i) continue;
//
//                $td = $branchItem->find('td');
//
////                echo "<pre style='margin-left: 60px;'>";
////                print_r($branchItem);
////                echo "</pre>";
//
//                $branchData[] = [
//                    'name' => $td[1]->find('a')[0]->innerHtml,
//                    'address' => str_replace('<br />', ' ',$td[2]->find('a')[0]->innerHtml),
//                    'phone' => $td[3]->find('a')[0]->innerHtml,
//                ];
//            }
//
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($branchData);
////            echo "</pre>";
//
//            $data['baranches'] = $branchData;
//
//            $exList[$raid] = array_merge($exList[$raid], $data);
//
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($data);
////            echo "</pre>";
//        }
//
//            echo "<pre style='margin-left: 60px;'>";
//            print_r($exList);
//            echo "</pre>";
//
//        file_put_contents(__DIR__ . '/../../../storage/2023_10_09_bank_info.txt', serialize($exList));
//        die(__FILE__ . ': ' . __LINE__);
        $exList = unserialize(file_get_contents(__DIR__ . '/../../../storage/2023_10_09_bank_info.txt'));
//        echo "<pre style='margin-left: 60px;'>";
//        print_r($exList);
//        echo "</pre>";

        $sql = 'SELECT id, raid FROM exchanger';
        $query = App::db()->query($sql);

        $bankData = [];

        while ($res = $query->fetch()) {
//            $data[date('Y.m.d', (int) $res['date_at'])][] = $res['price'];
            $bankData[$res['raid']] = $res['id'];
//            echo "<pre style='margin-left: 60px;'>";
//            print_r($res);
//            echo "</pre>";
        }
//        echo "<pre style='margin-left: 60px;'>";
//        print_r($bankData);
//        echo "</pre>";

        $bankInfoPure = [];

        foreach ($exList as $raid => $item) {
            $item['head_office'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['head_office']));
            $item['phone'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['phone']));
            $item['fax'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['fax']));
            $item['url'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['url']));
            $item['raid'] = $raid;

//            $item['name'] = ['ru' => $item['name'], 'en' => $item['name'], 'am' => $item['name']];
            $item['url'] = ['ru' => $item['url'], 'en' => $item['url'], 'am' => $item['url']];
            $item['head_office'] = ['ru' => $item['head_office'], 'en' => $item['head_office'], 'am' => $item['head_office']];
            $item['phone'] = ['ru' => $item['phone'], 'en' => $item['phone'], 'am' => $item['phone']];
            $item['fax'] = ['ru' => $item['fax'], 'en' => $item['fax'], 'am' => $item['fax']];

            foreach ($item['baranches'] as $key => $branch) {
                $item['baranches'][$key] = [
                    'name' => ['ru' => $branch['name'], 'en' => $branch['name'], 'am' => $branch['name']],
                    'address' => ['ru' => $branch['address'], 'en' => $branch['address'], 'am' => $branch['address']],
                    'phone' => ['ru' => $branch['phone'], 'en' => $branch['phone'], 'am' => $branch['phone']],
                ];
            }

            $bankInfoPure[$bankData[$raid]] = $item;
        }

//        echo "<pre style='margin-left: 60px;'>";
//        print_r($bankInfoPure);
//        echo "</pre>";

        $exListOld = unserialize(file_get_contents(__DIR__ . '/../../../storage/bank_info.txt'));

        $bankInfoPureOld = [];

        foreach ($exListOld as $raid => $item) {
//            echo "<pre style='margin-left: 60px;'>";
//            print_r($raid);
//            echo "</pre>";
            if (!isset($bankData[$raid])) {
//                $bankNotSetted[] = $raid;
                continue;
            }

            $item['head_office'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['head_office']));
            $item['phone'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['phone']));
            $item['fax'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['fax']));
            $item['url'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['url']));
            $item['raid'] = $raid;

//            $item['name'] = ['ru' => $item['name'], 'en' => $item['name'], 'am' => $item['name']];
            $item['url'] = ['ru' => $item['url'], 'en' => $item['url'], 'am' => $item['url']];
            $item['head_office'] = ['ru' => $item['head_office'], 'en' => $item['head_office'], 'am' => $item['head_office']];
            $item['phone'] = ['ru' => $item['phone'], 'en' => $item['phone'], 'am' => $item['phone']];
            $item['fax'] = ['ru' => $item['fax'], 'en' => $item['fax'], 'am' => $item['fax']];

            foreach ($item['baranches'] as $key => $branch) {
                $item['baranches'][$key] = [
                    'name' => ['ru' => $branch['name'], 'en' => $branch['name'], 'am' => $branch['name']],
                    'address' => ['ru' => $branch['address'], 'en' => $branch['address'], 'am' => $branch['address']],
                    'phone' => ['ru' => $branch['phone'], 'en' => $branch['phone'], 'am' => $branch['phone']],
                ];
            }

            $bankInfoPureOld[$bankData[$raid]] = $item;
        }

//        echo "<pre>";
//        print_r($bankInfoPure[2]);
//        echo "</pre>";

//        echo "<pre>";
//        print_r(array_keys($bankInfoPureOld));
//        echo "</pre>";
//
//        echo "<pre>";
//        print_r(array_keys($bankInfoPure));
//        echo "</pre>";

        $newBankIds = [];
        foreach (array_keys($bankInfoPure) as $bankId) {
            if (!in_array($bankId, array_keys($bankInfoPureOld))) {
                $newBankIds[] = $bankId;
            }
        }
        asort($newBankIds);

//        echo "<pre>";
//        print_r($newBankIds);
//        echo "</pre>";

//        echo "<pre>";
//        print_r($bankNotSetted);
//        echo "</pre>";

//        die(__FILE__ . ': ' . __LINE__);

//        foreach ($newBankIds as $newBankId) {
//            $bankInfoPureOld[$newBankId] = $bankInfoPure[$newBankId];
//        }
        $bankIdUpdates = [2];
        foreach ($bankIdUpdates as $bankIdUpdate) {
            $bankInfoPureOld[$bankIdUpdate] = $bankInfoPure[$bankIdUpdate];
        }

//        file_put_contents(__DIR__ . '/../../../storage/bank_info_pure.txt', serialize($bankInfoPureOld));
        die(__FILE__ . ': ' . __LINE__);

        return [
            'site/big_parse',
            [
                'settings' => $settings,
                'menu' => $menu,
            ]
        ];
    }

    protected function actionBigParseExchanger()
    {
        echo <<<HTML
            <h1 style="text-align: center;">BigParseExchanger</h1>
HTML;

        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

//        $rateam = new Rateam;
//
//        $symbolPack = ['USD', 'EUR', 'RUR', 'GBP'];
//        $pack = implode(',', $symbolPack);
//
//        $rateam->loadPage('exchange-points/cash/retail', $pack);
//
//        $exList = $rateam->takeNamesAndUrls();
//
////        echo "<pre style='margin-left: 60px;'>";
////        print_r(count($exList));
////        echo "</pre>";
////
////        echo "<pre style='margin-left: 60px;'>";
////        print_r($exList);
////        echo "</pre>";
//
//        $dom = new Dom;
//        foreach ($exList as $raid => $item) {
////            $raid = '2cb6fa8f-2d6f-421f-b9d7-373e94505185';
////            $item['url'] = $exList[$raid]['url'];
//            $dom->loadFromUrl($item['url']);
////            $html = $dom->outerHtml;
//
//            $el = $dom->find('table.bankpagebankcontact')[0];
//
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($el->outerHtml);
////            echo "</pre>";
//
//            $lines = $el->find('tr');
//
////            $res = [];
//
//            foreach ($lines as $line) {
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($line->find('th')[0]->outerHtml . ': ' . $line->find('td')[0]->outerHtml);
////            echo "</pre>";
//                $res[$line->find('th')[0]->outerHtml] = $line->find('td')[0]->outerHtml;
//            }
//
//            $data = [
//                'head_office' => str_replace('<br />', ' ', $lines[0]->find('td')[0]->outerHtml),
//                'phone' => $lines[1]->find('td')[0]->outerHtml,
//                'fax' => $lines[3]->find('td')[0]->outerHtml,
//                'url' => $lines[5]->find('td')[0]->outerHtml,
//            ];
//
//            $branchs = $dom->find('table#search_rb')[0];
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($branchs->outerHtml);
////            echo "</pre>";
//
//            $branchList = $branchs->find('tr');
//
////        echo "<pre style='margin-left: 60px;'>";
////        print_r(count($branchList));
////        echo "</pre>";
//
//            $branchData = [];
//
//            $i = 0;
//            foreach ($branchList as $branchItem) {
//                $i++;
//
//                if (1 == $i) continue;
//
//                $td = $branchItem->find('td');
//
////                echo "<pre style='margin-left: 60px;'>";
////                print_r($branchItem);
////                echo "</pre>";
//
//                $branchData[] = [
//                    'name' => str_replace('<br />', ' ', $td[1]->find('a')[0]->innerHtml),
//                    'address' => str_replace('<br />', ' ',$td[2]->find('a')[0]->innerHtml),
//                    'phone' => $td[3]->find('a')[0]->innerHtml,
//                ];
//            }
//
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($branchData);
////            echo "</pre>";
//
//            $data['baranches'] = $branchData;
//
//            $exList[$raid] = array_merge($exList[$raid], $data);
//
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($data);
////            echo "</pre>";
//        }
////
////            echo "<pre style='margin-left: 60px;'>";
////            print_r($exList);
////            echo "</pre>";
////
//        file_put_contents(__DIR__ . '/../../../storage/2023_10_09_exchanger_info.txt', serialize($exList));
//        die(__FILE__ . ': ' . __LINE__);
        $exList = unserialize(file_get_contents(__DIR__ . '/../../../storage/2023_10_09_exchanger_info.txt'));
//        echo "<pre style='margin-left: 60px;'>";
//        print_r($exList);
//        echo "</pre>";

        $sql = 'SELECT id, raid FROM exchanger';
        $query = App::db()->query($sql);

        $bankData = [];

        while ($res = $query->fetch()) {
//            $data[date('Y.m.d', (int) $res['date_at'])][] = $res['price'];
            $bankData[$res['raid']] = $res['id'];
//            echo "<pre style='margin-left: 60px;'>";
//            print_r($res);
//            echo "</pre>";
        }
//        echo "<pre style='margin-left: 60px;'>";
//        print_r($bankData);
//        echo "</pre>";

        $bankInfoPure = [];
//        $bankNotSetted = [];

        foreach ($exList as $raid => $item) {
//            echo "<pre style='margin-left: 60px;'>";
//            print_r($raid);
//            echo "</pre>";
            if (!isset($bankData[$raid])) {
//                $bankNotSetted[] = $raid;
                continue;
            }

            $item['head_office'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['head_office']));
            $item['phone'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['phone']));
            $item['fax'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['fax']));
            $item['url'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['url']));
            $item['raid'] = $raid;

//            $item['name'] = ['ru' => $item['name'], 'en' => $item['name'], 'am' => $item['name']];
            $item['url'] = ['ru' => $item['url'], 'en' => $item['url'], 'am' => $item['url']];
            $item['head_office'] = ['ru' => $item['head_office'], 'en' => $item['head_office'], 'am' => $item['head_office']];
            $item['phone'] = ['ru' => $item['phone'], 'en' => $item['phone'], 'am' => $item['phone']];
            $item['fax'] = ['ru' => $item['fax'], 'en' => $item['fax'], 'am' => $item['fax']];

            foreach ($item['baranches'] as $key => $branch) {
                $item['baranches'][$key] = [
                    'name' => ['ru' => $branch['name'], 'en' => $branch['name'], 'am' => $branch['name']],
                    'address' => ['ru' => $branch['address'], 'en' => $branch['address'], 'am' => $branch['address']],
                    'phone' => ['ru' => $branch['phone'], 'en' => $branch['phone'], 'am' => $branch['phone']],
                ];
            }

            $bankInfoPure[$bankData[$raid]] = $item;
        }

//        echo "<pre style='margin-left: 60px;'>";
//        print_r($bankInfoPure);
//        echo "</pre>";

        $exListOld = unserialize(file_get_contents(__DIR__ . '/../../../storage/exchanger_info.txt'));

        $bankInfoPureOld = [];

        foreach ($exListOld as $raid => $item) {
//            echo "<pre style='margin-left: 60px;'>";
//            print_r($raid);
//            echo "</pre>";
            if (!isset($bankData[$raid])) {
//                $bankNotSetted[] = $raid;
                continue;
            }

            $item['head_office'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['head_office']));
            $item['phone'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['phone']));
            $item['fax'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['fax']));
            $item['url'] = trim(str_replace(['<td> ', ' </td>'], ' ', $item['url']));
            $item['raid'] = $raid;

//            $item['name'] = ['ru' => $item['name'], 'en' => $item['name'], 'am' => $item['name']];
            $item['url'] = ['ru' => $item['url'], 'en' => $item['url'], 'am' => $item['url']];
            $item['head_office'] = ['ru' => $item['head_office'], 'en' => $item['head_office'], 'am' => $item['head_office']];
            $item['phone'] = ['ru' => $item['phone'], 'en' => $item['phone'], 'am' => $item['phone']];
            $item['fax'] = ['ru' => $item['fax'], 'en' => $item['fax'], 'am' => $item['fax']];

            foreach ($item['baranches'] as $key => $branch) {
                $item['baranches'][$key] = [
                    'name' => ['ru' => $branch['name'], 'en' => $branch['name'], 'am' => $branch['name']],
                    'address' => ['ru' => $branch['address'], 'en' => $branch['address'], 'am' => $branch['address']],
                    'phone' => ['ru' => $branch['phone'], 'en' => $branch['phone'], 'am' => $branch['phone']],
                ];
            }

            $bankInfoPureOld[$bankData[$raid]] = $item;
        }

//        echo "<pre>";
//        print_r($bankInfoPure);
//        echo "</pre>";

//        echo "<pre>";
//        print_r(array_keys($bankInfoPureOld));
//        echo "</pre>";
//
//        echo "<pre>";
//        print_r(array_keys($bankInfoPure));
//        echo "</pre>";

//        $newExchIds = [];
//        foreach (array_keys($bankInfoPure) as $bankId) {
//            if (!in_array($bankId, array_keys($bankInfoPureOld))) {
//                $newExchIds[] = $bankId;
//            }
//        }
//        asort($newExchIds);
//
//        echo "<pre>";
//        print_r($newExchIds);
//        echo "</pre>";

//        echo "<pre>";
//        print_r($bankNotSetted);
//        echo "</pre>";

//        die(__FILE__ . ': ' . __LINE__);

//        foreach ($newExchIds as $newExchId) {
//            $bankInfoPureOld[$newExchId] = $bankInfoPure[$newExchId];
//        }

//        file_put_contents(__DIR__ . '/../../../storage/2023_10_09_exchanger_info_pure.txt', serialize($bankInfoPureOld));
        die(__FILE__ . ': ' . __LINE__);

        return [
            'site/big_parse',
            [
                'settings' => $settings,
                'menu' => $menu,
            ]
        ];
    }

    protected function actionFixExchanger()
    {
//        echo <<<HTML
//            <h1 style="text-align: center;">BigParseExchanger</h1>
//HTML;
//
//        $query = App::db()->query("SELECT * FROM settings");
//        $settings = $query->fetch();
//
//        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
//        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
//
//        $menu['left']['hidden'] = $menuLeft['hidden'];
//
//        unset($menuLeft['hidden']);
//
//        $menu['left']['basic'] = $menuLeft;
//
//        return [
//            'site/big_parse',
//            [
//                'settings' => $settings,
//                'menu' => $menu,
//            ]
//        ];
//        $exchangerInfoOld = unserialize(file_get_contents(__DIR__ . '/../../../storage/exchanger_info.txt'));
//
//        echo "<pre>";
//        print_r($exchangerInfoOld);
//        echo "</pre>";

        $exchangerInfo = unserialize(file_get_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt'));

        $exchangerInfoKeys = array_keys($exchangerInfo);
        sort($exchangerInfoKeys);

        echo "<pre>";
        print_r($exchangerInfoKeys);
        echo "</pre>";

        $removeSec = App::config("widget>MainTable>removeSec");
        $removeTime = (time() - $removeSec);

        $exchangersAndBanks = App::createHdbk(
            Exchanger::class, 'id', '*', null, ['upd_cash' => 'DESC']
        );

//        echo "<pre>";
//        print_r($exchangersAndBanks);
//        echo "</pre>";

        $exchangersFullInfo = [];
        $exchangers = [];

        foreach ($exchangersAndBanks as $exch) {
            if ($exch->is_bank) {
                continue;
            }/* elseif ($exch->upd_cash < $removeTime) {
                continue;
            }*/

            $exchangers[] = $exch->id;
            $exchangersFullInfo[] = $exch;
        }

        echo "<pre>";
        print_r($exchangersFullInfo);
        echo "</pre>";

//        $exchangersKeys = array_keys($exchangers);
        sort($exchangers);

        echo "<pre>";
        print_r($exchangers);
        echo "</pre>";

        $newExchangers = [];
        foreach ($exchangers as $exchangerId) {
            if (array_search($exchangerId, $exchangerInfoKeys)) {
                continue;
            }
            $newExchangers[] = $exchangerId;
        }

        echo "<pre>";
        print_r($newExchangers);
        echo "</pre>";
    }

    protected function actionNumberSearch()
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/number_search',
            [
                'settings' => $settings,
                'menu' => $menu,
                'navigations' => $navigations,
            ]
        ];
    }

    protected function actionFuel()
    {
        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        // Fetch fuel types from the database
        $fuelTypesStmt = App::db()->query("SELECT id, name FROM fuel_types");
        $fuelTypes = $fuelTypesStmt->fetchAll(\PDO::FETCH_KEY_PAIR); // Maps id => name

        // Fetch fuel companies from the database
        $companiesStmt = App::db()->query("SELECT id, slug, name, logo, updated_at AS updated FROM fuel_companies");
        $fuelCompanies = $companiesStmt->fetchAll();

        // Fetch fuel data from the database
        $fuelDataStmt = App::db()->query("
            SELECT fc.slug, ft.name AS fuel_type, fd.price 
            FROM fuel_data fd 
            JOIN fuel_companies fc ON fd.company_id = fc.id
            JOIN fuel_types ft ON fd.fuel_type_id = ft.id
        ");
        $fuelData = [];
        while ($row = $fuelDataStmt->fetch()) {
            $fuelData[$row['slug']][$row['fuel_type']] = $row['price'];
        }

        return ['site/table_fuel', [
            'settings' => $settings,
            'menu' => $menu,
            'fuelCompanies' => $fuelCompanies,
            'fuelData' => $fuelData,
            'fuelTypes' => array_values($fuelTypes),
        ]];
    }

    protected function actionPage($slug)
    {
        $stmt = App::db()->prepare("SELECT * FROM pages WHERE slug = ?");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if (!$page) {
            return $this->actionNotFound();
        }

        $title = $page['seo_title'] ?: "";
        $meta = [
            'description' => $page['seo_description'] ?? 'Default description',
            'keywords' => $page['seo_keywords'] ?? 'Default keywords',
        ];

        $lang = App::lang();

        // Безопасная обработка PHP-кода
        $content = $page['content'];
        if (preg_match('/<\?(php|=)/i', $content)) { // Учитываем <?php и <?=
            // Создаем временный файл для выполнения PHP
            $tempFile = tempnam(sys_get_temp_dir(), 'page_');
            file_put_contents($tempFile, $content);
            ob_start();
            include $tempFile;
            $content = ob_get_clean();
            unlink($tempFile);
        }

        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/page',
            [
                'title' => $title,
                'content' => $content,
                'meta' => $meta,
                'settings' => $settings,
                'menu' => $menu,
                'navigations' => $navigations,
                'lang' => $lang,
            ]
        ];
    }
}
