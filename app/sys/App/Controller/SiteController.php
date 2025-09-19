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
        $db = App::db();

        $lang = 'name_' . (App::request()->getCookie('lang') ?: 'am');

        $settings = $db->query("SELECT * FROM settings")->fetch();
        $navigations = $db->query("SELECT * FROM navigation")->fetchAll();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        // компания теперь только id, slug, name, logo
        $stmt = $db->prepare("SELECT id, slug, name, logo FROM fuel_companies WHERE id = ? AND moderation_status !='pending'");
        $stmt->execute([$id]);
        $fuelCompanyInfo = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$fuelCompanyInfo) return $this->actionNotFound();

        // права на редактирование
        $editUrl = '';
        $canEdit = false;
        if (!empty($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            $uStmt = $db->prepare("SELECT id, role, company_id FROM users WHERE app_token = ?");
            $uStmt->execute([$token]);
            $authUser = $uStmt->fetch(\PDO::FETCH_ASSOC);
            $adminCred = $db->query("SELECT login, password FROM settings")->fetch();
            if ($authUser && $authUser['role'] === 'company' && (int)$authUser['company_id'] === (int)$id) {
                $canEdit = true;
                $editUrl = '/user/company';
            }
            if (self::hash($adminCred['login'] . $adminCred['password']) == $token) {
                $canEdit = true;
                $editUrl = '/user/company/' . (int)$id;
            }
        }

        // точки компании + города + регионы
        $rows = $db->prepare("
        SELECT
            cp.id, cp.company_id, cp.city_id,
            cp.address, cp.phones, cp.emails, cp.working_hours,
            cp.website, cp.socials, cp.latitude, cp.longitude,
            c.id AS city_real_id, c.$lang AS city_name,
            r.id AS region_id, r.slug AS region_slug, r.$lang AS region_name
        FROM company_points cp
        JOIN cities  c ON c.id = cp.city_id
        JOIN regions r ON r.id = c.region_id
        WHERE cp.company_id = :cid AND cp.moderation_status !='pending'
        ORDER BY r.name_ru, c.name_ru, COALESCE(cp.address,'')
    ");
        $rows->execute([':cid' => $id]);
        $points = $rows->fetchAll(\PDO::FETCH_ASSOC);

        // helper-декодеры (json или "a, b, c")
        $toList = function ($v): array {
            if (!$v) return [];
            $a = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($a)) return array_values(array_filter(array_map('trim', $a)));
            return array_values(array_filter(array_map('trim', explode(',', (string)$v))));
        };
        $toMap = function ($v): array {
            if (!$v) return [];
            $a = json_decode($v, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($a)) ? $a : [];
        };

        // цены по point-id
        $pointIds = array_map(fn($r) => (int)$r['id'], $points);
        $pricesByPoint = [];
        if ($pointIds) {
            $in = implode(',', array_fill(0, count($pointIds), '?'));
            $ps = $db->prepare("
            SELECT fd.company_point_id, ft.name AS fuel_name, fd.price, fd.updated_at
            FROM fuel_data fd
            JOIN fuel_types ft ON ft.id = fd.fuel_type_id
            WHERE fd.company_point_id IN ($in) AND fd.moderation_status !='pending'
            ORDER BY ft.name
        ");
            $ps->execute($pointIds);
            foreach ($ps->fetchAll(\PDO::FETCH_ASSOC) as $p) {
                $pid = (int)$p['company_point_id'];
                $pricesByPoint[$pid][] = [
                    'name' => $p['fuel_name'],
                    'price' => $p['price'],
                    'updated_at' => $p['updated_at'],
                ];
            }
        }

        // дерево: region -> city -> points
        $regionsTree = [];
        foreach ($points as $r) {
            $rid = (int)$r['region_id'];
            $cid = (int)$r['city_real_id'];
            $pid = (int)$r['id'];

            $regionsTree[$rid]['id'] = $rid;
            $regionsTree[$rid]['slug'] = $r['region_slug'];
            $regionsTree[$rid]['name'] = $r['region_name'];

            $regionsTree[$rid]['cities'][$cid]['id'] = $cid;
            $regionsTree[$rid]['cities'][$cid]['name'] = $r['city_name'];

            $regionsTree[$rid]['cities'][$cid]['points'][$pid] = [
                'id' => $pid,
                'address' => $r['address'] ?: '',
                'phones' => $toList($r['phones']),
                'emails' => $toList($r['emails']),
                'working_hours' => $toMap($r['working_hours']),
                'website' => $r['website'] ?: '',
                'socials' => $toList($r['socials']),
                'latitude' => $r['latitude'],
                'longitude' => $r['longitude'],
                'prices' => $pricesByPoint[$pid] ?? [],
            ];
        }

        // сортировка
        ksort($regionsTree);
        foreach ($regionsTree as &$reg) {
            if (!empty($reg['cities'])) {
                ksort($reg['cities']);
                foreach ($reg['cities'] as &$ct) {
                    if (!empty($ct['points'])) ksort($ct['points']);
                }
                unset($ct);
            }
        }
        unset($reg);

        return [
            'site/fuel_company',
            [
                'settings' => $settings,
                'menu' => $menu,
                'fuelCompanyInfo' => $fuelCompanyInfo,
                'regionsTree' => $regionsTree,
                'id' => $id,
                'navigations' => $navigations,
                'canEdit' => $canEdit,
                'editUrl' => $editUrl,
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
        // --- Конфиг ---
        $OUT_IP              = '45.150.8.84';
        $BASE_URL            = 'https://roadpolice.am';
        $PAGE_URL            = $BASE_URL . '/ru/plate-number-search';
        $UA                  = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $CACHE_TTL           = 6 * 3600;
        $RATE_LIMIT_SECONDS  = 8;
        $BACKOFF_403_SECONDS = 25 * 60;
        $DELAY_MS_MIN        = 200;
        $DELAY_MS_MAX        = 800;
        $MAX_ATTEMPTS        = 2;
        $DEBUG_LOG           = sys_get_temp_dir() . '/rp_debug.log';

        $dbg = function(string $msg) use ($DEBUG_LOG) {
            @file_put_contents($DEBUG_LOG, '['.date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
        };

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Метод не разрешен"]);
            return;
        }

        $plate = strtoupper(trim($_POST['plate_number'] ?? ''));
        if ($plate === '') {
            echo json_encode(["status" => "error", "message" => "Введите номерной знак"]);
            return;
        }

        // фильтр "нули"
        if (
            preg_match('/^\d{3}/', $plate) && (preg_match('/^000/', $plate) || preg_match('/00$/', $plate)) ||
            preg_match('/\d{3}$/', $plate) && (preg_match('/^00/', $plate) || preg_match('/000$/', $plate))
        ) {
            echo json_encode(["status" => "error", "message" => "В поиск не попадают номера с нулями"]);
            return;
        }

        // --- Кэш/лимиты ---
        $h        = sha1($plate);
        $tmpDir   = sys_get_temp_dir();
        $cacheF   = $tmpDir . "/rp_cache_$h.json";
        $rateF    = $tmpDir . "/rp_rate_$h.touch";
        $backoffF = $tmpDir . "/rp_backoff.flag";

        // mutex
        $lockF = $tmpDir . '/rp_mutex.lock';
        $lockH = fopen($lockF, 'c');
        if (!$lockH || !flock($lockH, LOCK_EX | LOCK_NB)) {
            echo json_encode(["status" => "error", "message" => "Попробуйте ещё раз через пару секунд"]);
            if ($lockH) fclose($lockH);
            return;
        }

        try {
            $dbg("---- NEW REQUEST plate={$plate} ----");

            if (is_file($backoffF)) {
                $until = (int)trim(@file_get_contents($backoffF));
                if ($until > time()) {
                    $dbg("Backoff active, until=$until");
                    echo json_encode(["status" => "error", "message" => "Сервис временно недоступен, повторите позже"]);
                    return;
                }
                @unlink($backoffF);
            }

            if (is_file($cacheF) && (time() - filemtime($cacheF) < $CACHE_TTL)) {
                $dbg("Cache hit: $cacheF");
                readfile($cacheF);
                return;
            }

            if (is_file($rateF) && (time() - filemtime($rateF) < $RATE_LIMIT_SECONDS)) {
                $dbg("Rate limit hit: $rateF");
                echo json_encode(["status" => "error", "message" => "Слишком часто. Попробуйте чуть позже."]);
                return;
            }
            @touch($rateF);

            usleep(mt_rand($DELAY_MS_MIN, $DELAY_MS_MAX) * 1000);

            $attempt   = 0;
            $lastError = null;

            while ($attempt < $MAX_ATTEMPTS) {
                $attempt++;
                $dbg("Attempt #$attempt");

                $cookieFile = tempnam($tmpDir, 'rp_');
                $dbg("Cookie file: $cookieFile");

                // файлик для вербоза cURL
                $curlStderr = fopen($tmpDir . '/rp_curl_'.$attempt.'.log', 'w');

                $common = [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_USERAGENT      => $UA,
                    CURLOPT_ENCODING       => '',
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2TLS,
                    CURLOPT_COOKIEJAR      => $cookieFile,
                    CURLOPT_COOKIEFILE     => $cookieFile,
                    CURLOPT_TIMEOUT        => 25,
                    CURLOPT_INTERFACE      => $OUT_IP,
                    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
                    CURLOPT_VERBOSE        => true,
                    CURLOPT_STDERR         => $curlStderr,
                ];

                // Сбор заголовков ответа
                $respHeaders = [];
                $headerFn = function($ch, $header) use (&$respHeaders) {
                    $respHeaders[] = $header;
                    return strlen($header);
                };

                // 1) GET страницы
                $ch = curl_init($PAGE_URL);
                curl_setopt_array($ch, $common + [
                        CURLOPT_HTTPGET    => true,
                        CURLOPT_HTTPHEADER => [
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language: ru,en;q=0.8',
                            'Sec-Fetch-Site: same-origin',
                            'Sec-Fetch-Mode: navigate',
                            'Sec-Fetch-Dest: document',
                        ],
                        CURLOPT_HEADERFUNCTION => $headerFn,
                    ]);
                $html = curl_exec($ch);
                $info = curl_getinfo($ch);
                $err  = curl_error($ch);
                curl_close($ch);
                fclose($curlStderr);

                $dbg("GET code={$info['http_code']} ct=".($info['content_type'] ?? '')." err={$err}");
                $dbg("GET headers:\n".implode('', $respHeaders));

                if ($html === false) {
                    $lastError = 'Сетевая ошибка (GET): ' . $err;
                    @unlink($cookieFile);
                    break;
                }
                if ((int)$info['http_code'] === 403) {
                    @file_put_contents($backoffF, (string)(time() + $BACKOFF_403_SECONDS));
                    @unlink($cookieFile);
                    echo json_encode(["status" => "error", "message" => "Доступ временно ограничен (403). Попробуйте позже."]);
                    return;
                }

                // ПРОВЕРИМ, что cookie-файл не пуст
                $cookieSize = @filesize($cookieFile);
                $dbg("Cookie file size after GET: ".(int)$cookieSize);
                $dbg("Cookie file content after GET:\n".@file_get_contents($cookieFile));

                // Вытащим XSRF и rd из cookie-файла
                $xsrf = null; $rd = null;
                foreach (@file($cookieFile) ?: [] as $line) {
                    if ($line === '' || $line[0] === '#') continue;
                    $p = explode("\t", $line);
                    if (count($p) >= 7) {
                        if ($p[5] === 'XSRF-TOKEN') $xsrf = urldecode(trim($p[6]));
                        if ($p[5] === 'rd_session')  $rd   = trim($p[6]);
                    }
                }

                // Fallback: если cookie-файл пуст — попробуем вытащить из заголовков Set-Cookie
                if (!$xsrf || !$rd) {
                    foreach ($respHeaders as $hline) {
                        if (stripos($hline, 'Set-Cookie:') === 0) {
                            if (!$xsrf && preg_match('/XSRF-TOKEN=([^;]+)/', $hline, $m)) $xsrf = urldecode($m[1]);
                            if (!$rd   && preg_match('/rd_session=([^;]+)/', $hline, $m)) $rd   = $m[1];
                        }
                    }
                    $dbg("Fallback from headers: xsrf=".(bool)$xsrf." rd=".(bool)$rd);
                }

                // Sanctum fallback
                if (!$xsrf || !$rd) {
                    $curlStderr = fopen($tmpDir . '/rp_curl_sanctum_'.$attempt.'.log', 'w');
                    $ch = curl_init($BASE_URL . '/sanctum/csrf-cookie');
                    curl_setopt_array($ch, $common + [
                            CURLOPT_HTTPGET    => true,
                            CURLOPT_HTTPHEADER => [
                                'Accept: */*',
                                'Referer: ' . $PAGE_URL,
                                'Sec-Fetch-Site: same-origin',
                                'Sec-Fetch-Mode: no-cors',
                                'Sec-Fetch-Dest: empty',
                            ],
                        ]);
                    curl_exec($ch);
                    $i2 = curl_getinfo($ch);
                    $e2 = curl_error($ch);
                    curl_close($ch);
                    fclose($curlStderr);
                    $dbg("SANCTUM code={$i2['http_code']} err={$e2}");
                    $dbg("Cookie file content after SANCTUM:\n".@file_get_contents($cookieFile));

                    foreach (@file($cookieFile) ?: [] as $line) {
                        if ($line === '' || $line[0] === '#') continue;
                        $p = explode("\t", $line);
                        if (count($p) >= 7) {
                            if ($p[5] === 'XSRF-TOKEN') $xsrf = urldecode(trim($p[6]));
                            if ($p[5] === 'rd_session')  $rd   = trim($p[6]);
                        }
                    }
                }

                if (!$xsrf || !$rd) {
                    $dbg("FAIL: no xsrf/rd after all. xsrfPresent=".(int)(bool)$xsrf." rdPresent=".(int)(bool)$rd);
                    $lastError = "Не удалось получить XSRF/сессию";
                    @unlink($cookieFile);
                    if ($attempt < $MAX_ATTEMPTS) {
                        usleep(mt_rand(300, 600) * 1000);
                        continue;
                    }
                    break;
                }

                $dbg("OK: got xsrf (".strlen($xsrf)." bytes) and rd");

                usleep(mt_rand($DELAY_MS_MIN, $DELAY_MS_MAX) * 1000);

                // 2) POST
                $post = http_build_query(['number' => $plate]);
                $respHeaders = []; // заново
                $curlStderr = fopen($tmpDir . '/rp_curl_post_'.$attempt.'.log', 'w');
                $ch = curl_init($PAGE_URL);
                curl_setopt_array($ch, $common + [
                        CURLOPT_POST       => true,
                        CURLOPT_POSTFIELDS => $post,
                        CURLOPT_HTTPHEADER => [
                            'Accept: application/json, text/plain, */*',
                            'Content-Type: application/x-www-form-urlencoded',
                            'Origin: ' . $BASE_URL,
                            'Referer: ' . $PAGE_URL,
                            'X-Requested-With: XMLHttpRequest',
                            'X-XSRF-TOKEN: ' . $xsrf,
                            'Sec-Fetch-Site: same-origin',
                            'Sec-Fetch-Mode: cors',
                            'Sec-Fetch-Dest: empty',
                        ],
                        CURLOPT_HEADERFUNCTION => $headerFn,
                    ]);
                $resp = curl_exec($ch);
                $pinfo = curl_getinfo($ch);
                $perr  = curl_error($ch);
                curl_close($ch);
                fclose($curlStderr);

                $dbg("POST code={$pinfo['http_code']} ct=".($pinfo['content_type'] ?? '')." err={$perr}");
                $dbg("POST headers:\n".implode('', $respHeaders));

                if ($resp === false) {
                    $lastError = 'Сетевая ошибка (POST): ' . $perr;
                    break;
                }

                $code  = (int)($pinfo['http_code'] ?? 0);
                $ctype = strtolower($pinfo['content_type'] ?? '');
                if ($code === 403) {
                    @file_put_contents($backoffF, (string)(time() + $BACKOFF_403_SECONDS));
                    echo json_encode(["status" => "error", "message" => "Доступ временно ограничен (403). Попробуйте позже."]);
                    return;
                }

                $isJson = (strpos($ctype, 'application/json') !== false);
                if ($code === 200 && $isJson) {
                    @file_put_contents($cacheF, $resp, LOCK_EX);
                    echo $resp;
                    return;
                }

                $needRetry = ($code === 419) || !$isJson;
                if ($needRetry && $attempt < $MAX_ATTEMPTS) {
                    $dbg("Retry reason: code=$code isJson=".($isJson?'1':'0'));
                    usleep(mt_rand(300, 600) * 1000);
                    continue;
                }

                $lastError = "HTTP $code".(!$isJson ? " (не JSON)" : "");
                break;
            }

            $dbg("FINAL ERROR: ".($lastError ?? 'unknown'));
            echo json_encode(["status" => "error", "message" => $lastError ?? "Неизвестная ошибка"]);
            return;

        } finally {
            if ($lockH) {
                flock($lockH, LOCK_UN);
                fclose($lockH);
            }
        }
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
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

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
        $pdo = App::db();

        // settings / меню / навигация
        $settings = $pdo->query("SELECT * FROM settings")->fetch();
        $navigations = $pdo->query("SELECT * FROM navigation")->fetchAll();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        // ====== селект города (только где есть данные) ======
        $selectedCitySlug = isset($_GET['city']) ? trim((string)$_GET['city']) : '';
        $selectedCityId = null;

        // типы топлива в нужном порядке
        $fuelTypes = $pdo->query("SELECT id, name FROM fuel_types ORDER BY id")
            ->fetchAll(\PDO::FETCH_KEY_PAIR); // id => name
        $fuelTypeNames = array_values($fuelTypes);

        // -------- дерево регион → город → компания (с учётом фильтра города) ----------
        $params = [];
        $whereSql = "WHERE cp.moderation_status !='pending' AND fc.moderation_status !='pending' AND fd.moderation_status !='pending'";
        if ($selectedCitySlug !== '') {
            $st = $pdo->prepare("SELECT id FROM cities WHERE slug = :slug LIMIT 1");
            $st->execute([':slug' => $selectedCitySlug]);
            if ($row = $st->fetch()) {
                $selectedCityId = (int)$row['id'];
                $whereSql = $whereSql . " AND c.id = :cityId";
                $params[':cityId'] = $selectedCityId;
            } else {
                $selectedCitySlug = '';
            }
        }
        $lang = 'name_' . (App::request()->getCookie('lang') ?: 'am');

        $sql = "
        SELECT
            r.id  AS region_id, r.slug AS region_slug, r.$lang AS region_name,
            c.id  AS city_id,   c.slug AS city_slug,   c.$lang AS city_name,
            fc.id AS company_id, fc.slug AS company_slug, fc.name AS company_name, fc.logo AS company_logo,
            ft.id AS fuel_type_id, ft.name AS fuel_type_name,
            MIN(fd.price) AS price,
            MAX(fd.updated_at) AS updated_at,
            MAX(CASE
          WHEN u.login IS NOT NULL AND u.login <> ''
           AND u.password IS NOT NULL AND u.password <> ''
          THEN 1 ELSE 0
        END) AS has_owner
        FROM regions r
        JOIN cities c          ON c.region_id = r.id
        JOIN company_points cp ON cp.city_id  = c.id
        JOIN fuel_companies fc ON fc.id       = cp.company_id
        JOIN fuel_data fd      ON fd.company_point_id = cp.id
        JOIN fuel_types ft     ON ft.id       = fd.fuel_type_id
        LEFT JOIN users u      ON u.company_id = fc.id
        $whereSql
        GROUP BY r.id, c.id, fc.id, ft.id
        ORDER BY r.$lang, c.$lang, fc.name, ft.id
    ";
        $st = $pdo->prepare($sql);
        $st->execute($params);

        $regions = [];         // дерево для аккордеонов

        while ($row = $st->fetch(\PDO::FETCH_ASSOC)) {
            $rid = (int)$row['region_id'];
            $cid = (int)$row['city_id'];
            $ft = (string)$row['fuel_type_name'];
            $price = (float)$row['price'];
            $updated = $row['updated_at'];

            if (!isset($regions[$rid])) {
                $regions[$rid] = [
                    'id' => $rid,
                    'slug' => $row['region_slug'],
                    'name' => $row['region_name'],
                    'best' => [],
                    'cities' => []
                ];
            }
            if (!isset($regions[$rid]['cities'][$cid])) {
                $regions[$rid]['cities'][$cid] = [
                    'id' => $cid,
                    'slug' => $row['city_slug'],
                    'name' => $row['city_name'],
                    'best' => [],
                    'companies' => []
                ];
            }
            if (!isset($regions[$rid]['cities'][$cid]['companies'][$row['company_id']])) {
                $regions[$rid]['cities'][$cid]['companies'][$row['company_id']] = [
                    'id' => (int)$row['company_id'],
                    'slug' => $row['company_slug'],
                    'name' => $row['company_name'],
                    'logo' => $row['company_logo'],
                    'verified' => ((int)$row['has_owner'] === 1),
                    'latest_update' => null,
                    'prices' => []
                ];
            }

            // цены компании по типам
            $company =& $regions[$rid]['cities'][$cid]['companies'][$row['company_id']];
            $company['prices'][$ft] = ['price' => $price, 'updated_at' => $updated];
            if ($updated && ($company['latest_update'] === null || $updated > $company['latest_update'])) {
                $company['latest_update'] = $updated;
            }

            // лучшие по городу
            $city =& $regions[$rid]['cities'][$cid];
            if (!isset($city['best'][$ft]) || $price < $city['best'][$ft]['price']) {
                $city['best'][$ft] = ['price' => $price, 'updated_at' => $updated];
            }
            // лучшие по региону
            if (!isset($regions[$rid]['best'][$ft]) || $price < $regions[$rid]['best'][$ft]['price']) {
                $regions[$rid]['best'][$ft] = ['price' => $price, 'updated_at' => $updated];
            }
        }

        // -------- «Лучшие» (без учёта фильтра города! глобально по стране) ----------
        $bestCompanies = []; // список компаний с их минимальными ценами по каждому типу
        $bestHeader = []; // глобально лучшая цена по типу топлива

        $sqlBest = "
        SELECT
            fc.id AS company_id, fc.slug AS company_slug, fc.name AS company_name, fc.logo AS company_logo,
            c.id  AS city_id, c.slug AS city_slug, c.$lang AS city_name,
            ft.id AS fuel_type_id, ft.name AS fuel_type_name,
            fd.price AS price, fd.updated_at AS updated_at,
            CASE WHEN EXISTS (
        SELECT 1 FROM users uu
         WHERE uu.company_id = fc.id
           AND uu.login IS NOT NULL AND uu.login <> ''
           AND uu.password IS NOT NULL AND uu.password <> ''
    ) THEN 1 ELSE 0 END AS has_owner
        FROM fuel_data fd
        JOIN company_points cp ON cp.id = fd.company_point_id
        JOIN cities c          ON c.id  = cp.city_id
        JOIN fuel_companies fc ON fc.id = cp.company_id
        JOIN fuel_types ft     ON ft.id = fd.fuel_type_id
        LEFT JOIN users u      ON u.company_id = fc.id
        $whereSql
    ";
        foreach ($pdo->query($sqlBest) as $row) {
            $cid = (int)$row['company_id'];
            $ft = (string)$row['fuel_type_name'];
            $price = (float)$row['price'];

            if (!isset($bestCompanies[$cid])) {
                $bestCompanies[$cid] = [
                    'id' => $cid,
                    'slug' => $row['company_slug'],
                    'name' => $row['company_name'],
                    'logo' => $row['company_logo'],
                    'latest_update' => null,
                    'verified' => ((int)$row['has_owner'] === 1),
                    'prices' => [] // fuel_type => ['price','updated_at','city_name','city_slug']
                ];
            }
            // сохраняем минимальную цену компании по типу и город-носитель минимума
            if (!isset($bestCompanies[$cid]['prices'][$ft]) || $price < $bestCompanies[$cid]['prices'][$ft]['price']) {
                $bestCompanies[$cid]['prices'][$ft] = [
                    'price' => $price,
                    'updated_at' => $row['updated_at'],
                    'city_name' => $row['city_name'],
                    'city_slug' => $row['city_slug'],
                ];
            }
            // глобальная лучшая цена по типу
            if (!isset($bestHeader[$ft]) || $price < $bestHeader[$ft]['price']) {
                $bestHeader[$ft] = ['price' => $price];
            }
        }
        // latest_update для «Лучших»
        foreach ($bestCompanies as &$bc) {
            foreach ($bc['prices'] as $p) {
                if ($p['updated_at'] && ($bc['latest_update'] === null || $p['updated_at'] > $bc['latest_update'])) {
                    $bc['latest_update'] = $p['updated_at'];
                }
            }
        }
        unset($bc);

        // -------- сортировки ----------
        $regions = array_values($regions);
        usort($regions, fn($a, $b) => strcmp($a['name'], $b['name']));
        foreach ($regions as &$r) {
            $r['cities'] = array_values($r['cities']);
            usort($r['cities'], fn($a, $b) => strcmp($a['name'], $b['name']));
            foreach ($r['cities'] as &$c) {
                $c['companies'] = array_values($c['companies']);
                usort($c['companies'], fn($a, $b) => strcmp($a['name'], $b['name']));
            }
            unset($c);
        }
        unset($r);
        $sqlCities = "
    SELECT DISTINCT c.id, c.slug, c.$lang as city_name
    FROM cities c
    JOIN company_points cp ON cp.city_id = c.id
    JOIN fuel_data fd ON fd.company_point_id = cp.id
    WHERE fd.moderation_status !='pending' AND cp.moderation_status !='pending'
    ORDER BY c.$lang
";
        $cities = App::db()->query($sqlCities)->fetchAll();


        // Сортировка компаний в «Лучших»
        $bestCompanies = array_values($bestCompanies);
        usort($bestCompanies, fn($a, $b) => strcmp($a['name'], $b['name']));

        return ['site/table_fuel', [
            'settings' => $settings,
            'menu' => $menu,
            'navigations' => $navigations,
            'fuelTypes' => $fuelTypeNames,
            'regionsTree' => $regions,
            'cities' => $cities,
            'selectedCitySlug' => $selectedCitySlug,
            'bestCompanies' => $bestCompanies,
            'bestHeader' => $bestHeader,
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

    private function buildSearchWhere(string $search, array &$params): string
    {
        $params = [];
        if ($search !== '') {
            $q = '%' . mb_strtolower($search, 'UTF-8') . '%';
            // Ищем и по имени семейства (folder), и по названию файла/шрифта (name)
            $params[] = $q; // folder
            $params[] = $q; // name
            return 'WHERE LOWER(folder) LIKE ? OR LOWER(name) LIKE ?';
        }
        return '';
    }

    protected function getGroupedFonts(int $offset, int $limit, string $search = ''): array
    {
        $pdo = App::db();
        $params = [];
        $where = $this->buildSearchWhere($search, $params);

        $query = "SELECT DISTINCT folder FROM fonts $where ORDER BY folder ASC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $folders = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $grouped = [];
        foreach ($folders as $folder) {
            $grouped[$folder] = $pdo->query(
                "SELECT * FROM fonts WHERE folder = " . $pdo->quote($folder) . " ORDER BY uploaded_at DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $grouped;
    }

    protected function actionFonts()
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        $pdo = App::db();
        $totalFamilies = (int)$pdo->query("SELECT COUNT(DISTINCT folder) FROM fonts")->fetchColumn();
        $groupedFonts = $this->getGroupedFonts(0, 24);
        $initialHasMore = $totalFamilies > count($groupedFonts);

        return [
            'site/fonts',
            [
                'settings' => $settings,
                'menu' => $menu,
                'groupedFonts' => $groupedFonts,
                'navigations' => $navigations,
                'initialHasMore' => $initialHasMore,
            ]
        ];
    }

    protected function actionFontsLoad()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $json = file_get_contents('php://input');
        $input = json_decode($json, true) ?? [];

        $offset = (int)($input['offset'] ?? 0);
        $search = trim((string)($input['search'] ?? ''));
        $previewText = $input['previewText'] ?? 'Դրամ.ամ';
        $fontSize = (int)($input['fontSize'] ?? 40);
        $limit = 24;

        $pdo = App::db();
        $params = [];
        $where = $this->buildSearchWhere($search, $params);

        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT folder) FROM fonts $where");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $groupedFonts = $this->getGroupedFonts($offset, $limit, $search);

        // Прокидываем переменные в partial
        ob_start();
        $fontSizeLocal = $fontSize;     // чтобы не потерять при include
        $previewTextLocal = $previewText;
        $fontSize = $fontSizeLocal;
        $previewText = $previewTextLocal;
        include __DIR__ . '/../../../view/site/partials/fonts-items.php';
        $html = ob_get_clean();

        $hasMore = ($offset + count($groupedFonts)) < $total;

        echo json_encode([
            'html' => $html,
            'hasMore' => $hasMore,
            'debug' => [
                'offset' => $offset,
                'limit' => $limit,
                'search' => $search,
                'total' => $total,
                'loadedCount' => count($groupedFonts)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function actionFontsSearch()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $json = file_get_contents('php://input');
        $input = json_decode($json, true) ?? [];

        $search = trim((string)($input['search'] ?? ''));
        $previewText = $input['previewText'] ?? 'Դրամ.ամ';
        $fontSize = (int)($input['fontSize'] ?? 40);
        $limit = 24;

        $pdo = App::db();
        $params = [];
        $where = $this->buildSearchWhere($search, $params);

        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT folder) FROM fonts $where");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // всегда с нулевого offset
        $groupedFonts = $this->getGroupedFonts(0, $limit, $search);

        ob_start();
        $fontSizeLocal = $fontSize;
        $previewTextLocal = $previewText;
        $fontSize = $fontSizeLocal;
        $previewText = $previewTextLocal;
        include __DIR__ . '/../../../view/site/partials/fonts-items.php';
        $html = ob_get_clean();

        $hasMore = (0 + count($groupedFonts)) < $total;

        echo json_encode([
            'html' => $html,
            'hasMore' => $hasMore,
            'debug' => [
                'offset' => 0,
                'limit' => $limit,
                'search' => $search,
                'total' => $total,
                'loadedCount' => count($groupedFonts)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function actionFontFamily($family)
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        // Use prepare and execute for the query
        $stmt = App::db()->prepare("SELECT * FROM fonts WHERE folder = ?");
        $stmt->execute([str_replace('+', ' ', $family)]);
        $fonts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Define variant order and weight mapping
        $variantOrder = [
            'Thin' => 1, 'ThinItalic' => 2,
            'ExtraLight' => 3, 'ExtraLightItalic' => 4,
            'Light' => 5, 'LightItalic' => 6,
            'Regular' => 7, 'Italic' => 8,
            'Medium' => 9, 'MediumItalic' => 10,
            'Book' => 11, 'BookItalic' => 12,
            'Semibold' => 13, 'SemiboldItalic' => 14,
            'Bold' => 15, 'BoldItalic' => 16,
            'Extrabold' => 17, 'ExtraboldItalic' => 18,
            'Black' => 19, 'BlackItalic' => 20,
        ];

        $weightMap = [
            'Thin' => 'Thin 100', 'ThinItalic' => 'Thin 100 Italic',
            'ExtraLight' => 'ExtraLight 200', 'ExtraLightItalic' => 'ExtraLight 200 Italic',
            'Light' => 'Light 300', 'LightItalic' => 'Light 300 Italic',
            'Regular' => 'Regular 400', 'Italic' => 'Regular 400 Italic',
            'Medium' => 'Medium 500', 'MediumItalic' => 'Medium 500 Italic',
            'Book' => 'Book', 'BookItalic' => 'Book Italic',
            'Semibold' => 'SemiBold 600', 'SemiboldItalic' => 'SemiBold 600 Italic',
            'Bold' => 'Bold 700', 'BoldItalic' => 'Bold 700 Italic',
            'Extrabold' => 'ExtraBold 800', 'ExtraboldItalic' => 'ExtraBold 800 Italic',
            'Black' => 'Black 900', 'BlackItalic' => 'Black 900 Italic',
        ];

        // Extract variant from name (e.g., "Mardoto-Thin" -> "Thin")
        foreach ($fonts as &$font) {
            $parts = explode('-', $font['name']);
            $variant = end($parts); // Get the last part as the variant
            $font['variant'] = $variant;
        }
        unset($font); // Unset reference

        // Sort fonts based on variant order
        usort($fonts, function ($a, $b) use ($variantOrder) {
            $orderA = $variantOrder[$a['variant']] ?? 999;
            $orderB = $variantOrder[$b['variant']] ?? 999;
            return $orderA <=> $orderB;
        });

        return [
            'site/font-family',
            [
                'settings' => $settings,
                'menu' => $menu,
                'fonts' => $fonts,
                'family' => $family,
                'navigations' => $navigations,
                'weightMap' => $weightMap,
            ]
        ];
    }

    protected function actionDownloadFontFamily($family)
    {
        $stmt = App::db()->prepare("SELECT * FROM fonts WHERE folder = ?");
        $stmt->execute([str_replace('+', ' ', $family)]);
        $fonts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $zip = new \ZipArchive();
        $zipFile = tempnam(sys_get_temp_dir(), 'fonts_');
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            foreach ($fonts as $font) {
                $filePath = __DIR__ . '/../../../../fonts/' . $font['folder'] . '/' . $font['filename'];
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $font['filename']);
                }
            }
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $family . '_fonts.zip"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            unlink($zipFile);
        } else {
            header('Location: /font-family/' . $family);
        }
        exit;
    }


    protected function actionAddStation()
    {
        $pdo = \App\App::db();

        // Базовые данные для шапки/меню
        $settings = $pdo->query("SELECT * FROM settings")->fetch();
        $navigations = $pdo->query("SELECT * FROM navigation")->fetchAll();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left'] = ['hidden' => $menuLeft['hidden']];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        // Режим формы
        $mode = (isset($_GET['mode']) && $_GET['mode'] === 'owner') ? 'owner' : 'driver';
        $isOwner = ($mode === 'owner');

        // Справочники для формы
        $regions = $pdo->query("SELECT id, name_ru FROM regions ORDER BY name_ru")->fetchAll(\PDO::FETCH_ASSOC);
        $cities = $pdo->query("SELECT id, region_id, name_ru FROM cities ORDER BY name_ru")->fetchAll(\PDO::FETCH_ASSOC);
        $fuelTypes = $pdo->query("SELECT id, name FROM fuel_types ORDER BY id")->fetchAll(\PDO::FETCH_ASSOC);

        // Лучшие цены (подсказки в UI)
        $bestPrices = [];
        $bx = $pdo->query("
        SELECT ft.id AS fuel_type_id, MIN(fd.price) AS min_price
          FROM fuel_types ft
          LEFT JOIN fuel_data fd ON ft.id = fd.fuel_type_id
          WHERE fd.moderation_status != 'pending'
         GROUP BY ft.id
    ");
        while ($r = $bx->fetch(\PDO::FETCH_ASSOC)) {
            $bestPrices[$r['fuel_type_id']] = $r['min_price'] ?: 'N/A';
        }

        $ok = !empty($_GET['ok']);

        // ===== Отправка формы =====
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_station'])) {
            $pdo->exec("PRAGMA busy_timeout = 30000");
            $pdo->exec("PRAGMA foreign_keys = ON");

            // Валидные ID типов топлива (для быстрой проверки)
            $validFuelTypeIds = array_flip(array_map(fn($r) => (int)$r['id'], $fuelTypes));

            // 1) Данные компании
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name === '') $name = 'Без названия';

            // Генерация уникального slug
            $nameAscii = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
            if ($nameAscii === false) $nameAscii = $name;
            $slug = preg_replace('~[^a-z0-9]+~i', '-', $nameAscii);
            $slug = strtolower(trim($slug, '-'));
            if ($slug === '') $slug = 'company';

            $suffix = '';
            while (true) {
                $st = $pdo->prepare("SELECT 1 FROM fuel_companies WHERE slug = ?");
                $st->execute([$slug . $suffix]);
                if (!$st->fetchColumn()) {
                    $slug .= $suffix;
                    break;
                }
                try {
                    $rand = bin2hex(random_bytes(6));
                } catch (\Throwable $e) {
                    $rand = sha1((string)microtime(true) . ':' . random_int(0, PHP_INT_MAX));
                }
                $suffix = '-' . substr($rand, 0, 6);
            }

            // 2) Город и адрес
            $cityId = (int)($_POST['company_city_id'] ?? 0);
            if ($cityId <= 0) {
                $cityId = (int)($pdo->query("SELECT id FROM cities ORDER BY id LIMIT 1")->fetchColumn() ?: 1);
            }
            $address = trim((string)($_POST['company_address'] ?? ''));
            $lat = ($_POST['company_latitude'] ?? '') !== '' ? (float)$_POST['company_latitude'] : null;
            $lng = ($_POST['company_longitude'] ?? '') !== '' ? (float)$_POST['company_longitude'] : null;

            $phones = $isOwner ? json_encode($_POST['phones'] ?? [], JSON_UNESCAPED_UNICODE) : json_encode([], JSON_UNESCAPED_UNICODE);
            $emails = $isOwner ? json_encode($_POST['emails'] ?? [], JSON_UNESCAPED_UNICODE) : json_encode([], JSON_UNESCAPED_UNICODE);
            $socials = $isOwner ? json_encode($_POST['socials'] ?? [], JSON_UNESCAPED_UNICODE) : json_encode([], JSON_UNESCAPED_UNICODE);
            $workingHours = [];
            if ($isOwner && !empty($_POST['working_days']) && !empty($_POST['working_times'])) {
                foreach ($_POST['working_days'] as $i => $day) {
                    $t = $_POST['working_times'][$i] ?? '';
                    if ($t !== '') $workingHours[$day] = $t;
                }
            }
            $workingHours = json_encode($workingHours, JSON_UNESCAPED_UNICODE);
            $website = $isOwner ? (string)($_POST['website'] ?? '') : '';

            try {
                $pdo->beginTransaction();

                $insC = $pdo->prepare("
                INSERT INTO fuel_companies (slug, name, moderation_status, created_at, updated_at)
                VALUES (?, ?, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
                $insC->execute([$slug, $name]);
                $companyId = (int)$pdo->lastInsertId();

                // INSERT: company_points (всегда pending)
                $insP = $pdo->prepare("
                INSERT INTO company_points
                    (company_id, city_id, address, phones, emails, working_hours, website, socials, latitude, longitude,
                     moderation_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
                $insP->execute([
                    $companyId, $cityId, $address,
                    $phones, $emails, $workingHours,
                    $website, $socials, $lat, $lng
                ]);
                $pointId = (int)$pdo->lastInsertId();

                // INSERT: fuel_data (всегда pending)
                if (!empty($_POST['fuel_type']) && !empty($_POST['fuel_price'])) {
                    $insF = $pdo->prepare("
                    INSERT INTO fuel_data (company_point_id, fuel_type_id, price, moderation_status, updated_at)
                    VALUES (?, ?, ?, 'pending', CURRENT_TIMESTAMP)
                ");
                    foreach ($_POST['fuel_type'] as $i => $ftId) {
                        $ftId = (int)$ftId;
                        $price = $_POST['fuel_price'][$i] ?? '';
                        if ($price === '' || $price === null) continue;
                        if (!isset($validFuelTypeIds[$ftId])) continue;
                        $insF->execute([$pointId, $ftId, (float)$price]);
                    }
                }

                $pdo->commit();
                header('Location: /add-station?mode=' . $mode . '&ok=1');
                return true;
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $this->error = 'Не удалось сохранить данные АЗС. Проверьте выбранный город и цены на топливо.';
            }
        }

        return ['site/add_station', [
            'settings' => $settings,
            'menu' => $menu,
            'navigations' => $navigations,
            'mode' => $mode,
            'fuelTypes' => $fuelTypes,
            'bestPrices' => $bestPrices,
            'regions' => $regions,
            'cities' => $cities,
            'ok' => $ok,
        ]];
    }

    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }
}
