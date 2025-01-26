<?php
declare(strict_types=1);
namespace App\Controller;

use Core\Controller,
    App\App;

class AdminController extends Controller
{
    protected function actionIndex()
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
        $md = false;

        if (isset($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            if (self::hash($settings['login'] . $settings['password']) == $token) {
                $md = true;
            }
        }

        if (!$md) {
            header('Location: /login');
            return false;
        }

        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='navigation'")->fetch();
        if (!$checkTable) {
            // Если таблица не существует, создаём её
            App::db()->query("
        CREATE TABLE navigation (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            link TEXT NOT NULL,
            title_ru TEXT NOT NULL,
            title_en TEXT NOT NULL,
            title_am TEXT NOT NULL,
            image TEXT NOT NULL
        )
    ");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            App::db()->query(
                "UPDATE settings
                 SET login = ?, password = ?, 
                     banner_head = ? , banner_head_2 = ? ,  banner_head_3 = ? ,  
                     banner_head_mobile = ? , banner_head_mobile_2 = ?,  
                     banner_side1 = ? , banner_side1_2 = ? ,  banner_side1_3 = ? , 
                     banner_side2 = ? , banner_side2_2 = ? ,  banner_side2_3 = ? , 
                     banner_side3 = ? , banner_side3_2 = ? ,  banner_side3_3 = ? , 
                     banner_footer = ?, banner_footer_2 = ? ,  banner_footer_3 = ? ,  
                     banner_footer_mobile = ?, banner_footer_mobile_2 = ? ,  
                     banner_footer_small = ?, banner_footer_small_2 = ? ,  banner_footer_small_3 = ?
                 WHERE true")
                ->execute([
                    $_POST['login'], $_POST['password'],
                    $_POST['banner_head'], $_POST['banner_head_2'], $_POST['banner_head_3'],
                    $_POST['banner_head_mobile'], $_POST['banner_head_mobile_2'],
                    $_POST['banner_side1'], $_POST['banner_side1_2'], $_POST['banner_side1_3'],
                    $_POST['banner_side2'], $_POST['banner_side2_2'], $_POST['banner_side2_3'],
                    $_POST['banner_side3'], $_POST['banner_side3_2'], $_POST['banner_side3_3'],
                    $_POST['banner_footer'], $_POST['banner_footer_2'], $_POST['banner_footer_3'],
                    $_POST['banner_footer_mobile'], $_POST['banner_footer_mobile_2'],
                    $_POST['banner_footer_small'], $_POST['banner_footer_small_2'], $_POST['banner_footer_small_3'],
                ]);

            file_put_contents(__DIR__ . '/../../../storage/lang/en/main.php', $_POST['english']);
            file_put_contents(__DIR__ . '/../../../storage/lang/am/main.php', $_POST['armenia']);

            file_put_contents(__DIR__ . '/../../../storage/site_title.txt', serialize($_POST['site_title']));

            file_put_contents(__DIR__ . '/../../../storage/static/about.txt', serialize($_POST['about']));
            file_put_contents(__DIR__ . '/../../../storage/static/faq.txt', serialize($_POST['faq']));
            file_put_contents(__DIR__ . '/../../../storage/static/contacts.txt', serialize($_POST['contacts']));
            file_put_contents(__DIR__ . '/../../../storage/static/advertising.txt', serialize($_POST['advertising']));

            file_put_contents(__DIR__ . '/../../../storage/menu/top.php', $_POST['menu']['top']);
            file_put_contents(__DIR__ . '/../../../storage/menu/left.php', $_POST['menu']['left']);

            $menuData = [];
            $navImages = $_FILES['nav-image'];
            $navTexts = $_POST['nav-text'];
            $navLinks = $_POST['nav-link'];

            foreach ($_POST['nav-title'] as $menuId => $titles) {
                if (isset($navImages['name'][$menuId]) && !empty($navImages['name'][$menuId])) {
                    $imageName = 'nav-icon/' . $navImages['name'][$menuId];
                    $imageTmp = $navImages['tmp_name'][$menuId];
                    $imagePath = __DIR__ . '/../../../../img/' . $imageName;
	                    move_uploaded_file($imageTmp, $imagePath);
                } else {
                    $imageName = $navTexts[$menuId];
                }
                if (substr($navLinks[$menuId], 0, 1) === '/') {
                    $navLink = $navLinks[$menuId];
                }else{
                    $navLink =  '/' . $navLinks[$menuId];
                }
                $menuData[] = [
                    'id' => $menuId,
                    'nav_link' => $navLink,
                    'title_ru' => $titles['ru'],
                    'title_en' => $titles['en'],
                    'title_am' => $titles['am'],
                    'image' => $imageName,
                ];
            }
            foreach ($menuData as $menuItem) {
                $stmt = App::db()->prepare("SELECT * FROM navigation WHERE id = ?");
                $stmt->execute([$menuItem['id']]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $stmt = App::db()->prepare(
                        "UPDATE navigation SET link = ?, title_ru = ?, title_en = ?, title_am = ?, image = ? WHERE id = ?"
                    );
                    $stmt->execute([
                        $menuItem['nav_link'],
                        $menuItem['title_ru'],
                        $menuItem['title_en'],
                        $menuItem['title_am'],
                        $menuItem['image'],
                        $menuItem['id']
                    ]);
                } else {
                    $stmt = App::db()->prepare(
                        "INSERT INTO navigation (id, link ,title_ru, title_en, title_am, image) VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $menuItem['id'],
                        $menuItem['nav_link'],
                        $menuItem['title_ru'],
                        $menuItem['title_en'],
                        $menuItem['title_am'],
                        $menuItem['image']
                    ]);
                }
            }


            header('Location: /admin/');
            return true;
        }

        $settings['english'] = file_get_contents(__DIR__ . '/../../../storage/lang/en/main.php');
        $settings['armenia'] = file_get_contents(__DIR__ . '/../../../storage/lang/am/main.php');

        $settings['site_title'] = unserialize(file_get_contents(__DIR__ . '/../../../storage/site_title.txt'));

        $settings['about'] = unserialize(file_get_contents(__DIR__ . '/../../../storage/static/about.txt'));
        $settings['faq'] = unserialize(file_get_contents(__DIR__ . '/../../../storage/static/faq.txt'));
        $settings['contacts'] = unserialize(file_get_contents(__DIR__ . '/../../../storage/static/contacts.txt'));
        $settings['advertising'] = unserialize(file_get_contents(__DIR__ . '/../../../storage/static/advertising.txt'));
        $settings['banks'] = unserialize(file_get_contents(__DIR__ . '/../../../storage/bank_info_pure.txt'));
        $settings['exchangers'] = unserialize(file_get_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt'));

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $settings['menu']['icons'] = array_diff(scandir(__DIR__ . '/../../../../img/menu'), array('.', '..'));

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();
        return [
            'site/admin',
            [
                'settings' => $settings,
                'navigations' => $navigations,
                'menu' => $menu,
            ]
        ];
    }

    protected function actionBank($id)
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
        $md = false;

        if (isset($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            if (self::hash($settings['login'] . $settings['password']) == $token) {
                $md = true;
            }
        }

        if (!$md) {
            header('Location: /login');
            return false;
        }

        $banks = unserialize(file_get_contents(__DIR__ . '/../../../storage/bank_info_pure.txt'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//            echo "<pre>";
//            print_r($_POST);
//            echo "</pre>";

            $bank = $_POST['bank'];

            $branches = [];

            foreach ($bank['baranches']['name']['ru'] as $key => $name) {
                $branches[] = [
//                    'name' => $name,
//                    'address' => $bank['baranches']['address'][$key],
//                    'phone' => $bank['baranches']['phone'][$key],
                    'name' => [
                        'ru' => $name,
                        'en' => $bank['baranches']['name']['en'][$key],
                        'am' => $bank['baranches']['name']['am'][$key]
                    ],
                    'address' => [
                        'ru' => $bank['baranches']['address']['ru'][$key],
                        'en' => $bank['baranches']['address']['en'][$key],
                        'am' => $bank['baranches']['address']['am'][$key]
                    ],
                    'phone' => [
                        'ru' => $bank['baranches']['phone']['ru'][$key],
                        'en' => $bank['baranches']['phone']['en'][$key],
                        'am' => $bank['baranches']['phone']['am'][$key]
                    ],
                ];
            }

            $bank['baranches'] = $branches;
            $bank['name'] = $banks[$id]['name'];

//            echo "<pre>";
//            print_r($bank);
//            echo "</pre>";
//
//            die(__FILE__ . ': ' . __LINE__);

            $banks[$id] = $bank;

            file_put_contents(__DIR__ . '/../../../storage/bank_info_pure.txt', serialize($banks));
        }

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

//        echo "<pre>";
//        print_r($banks);
//        echo "</pre>";
//
//        die(__FILE__ . ': ' . __LINE__);

//        foreach ($banks as $i => $bank) {
//            $banks[$i]['head_office'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['head_office']));
//            $banks[$i]['phone'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['phone']));
//            $banks[$i]['fax'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['fax']));
//            $banks[$i]['url'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['url']));
//        }
//
//        file_put_contents(__DIR__ . '/../../../storage/bank_info_pure.txt', serialize($banks));

        return [
            'site/admin_bank',
            [
                'settings' => $settings,
                'menu' => $menu,
                'bank' => $banks[$id],
            ]
        ];
    }

    protected function actionExchanger($id)
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
        $md = false;

        if (isset($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            if (self::hash($settings['login'] . $settings['password']) == $token) {
                $md = true;
            }
        }

        if (!$md) {
            header('Location: /login');
            return false;
        }

        $banks = unserialize(file_get_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//            echo "<pre>";
//            print_r($_POST);
//            echo "</pre>";

            $bank = $_POST['bank'];

            $branches = [];

            foreach ($bank['baranches']['name']['ru'] as $key => $name) {
                $branches[] = [
//                    'name' => $name,
//                    'address' => $bank['baranches']['address'][$key],
//                    'phone' => $bank['baranches']['phone'][$key],
                    'name' => [
                        'ru' => $name,
                        'en' => $bank['baranches']['name']['en'][$key],
                        'am' => $bank['baranches']['name']['am'][$key]
                    ],
                    'address' => [
                        'ru' => $bank['baranches']['address']['ru'][$key],
                        'en' => $bank['baranches']['address']['en'][$key],
                        'am' => $bank['baranches']['address']['am'][$key]
                    ],
                    'phone' => [
                        'ru' => $bank['baranches']['phone']['ru'][$key],
                        'en' => $bank['baranches']['phone']['en'][$key],
                        'am' => $bank['baranches']['phone']['am'][$key]
                    ],
                ];
            }

            $bank['baranches'] = $branches;
            $bank['name'] = $banks[$id]['name'];

//            echo "<pre>";
//            print_r($bank);
//            echo "</pre>";

//            die(__FILE__ . ': ' . __LINE__);

            $banks[$id] = $bank;

            file_put_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt', serialize($banks));
        }

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];

        unset($menuLeft['hidden']);

        $menu['left']['basic'] = $menuLeft;

//        echo "<pre>";
//        print_r($banks);
//        echo "</pre>";
//
//        die(__FILE__ . ': ' . __LINE__);

//        foreach ($banks as $i => $bank) {
//            $banks[$i]['head_office'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['head_office']));
//            $banks[$i]['phone'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['phone']));
//            $banks[$i]['fax'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['fax']));
//            $banks[$i]['url'] = trim(str_replace(['<td> ', ' </td>'], ' ', $bank['url']));
//        }
//
//        file_put_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt', serialize($banks));

        if (empty($banks[$id])) {
            $banks[$id] = [
                'name' => '',
            ];
        }

        return [
            'site/admin_exchanger',
            [
                'settings' => $settings,
                'menu' => $menu,
                'bank' => $banks[$id],
            ]
        ];
    }

    protected function actionLogin()
    {
        $md = false;
        $query = App::db()->query("SELECT login, password FROM settings");
        $cred = $query->fetch();


        if (isset($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            if (self::hash($cred['login'] . $cred['password']) == $token) {
                $md = true;
            }
        }

        if ($md) {
            header('Location: /admin/');
            return true;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($cred['login'] == $_POST['login'] && $cred['password'] == $_POST['password']) {
                setcookie('app_token', self::hash($cred['login'] . $cred['password']), time() + 60 * 60 * 24 * 30);
            } else {
                return ['site/login', ['error' => 'Invalid login or password']];
            }

            header('Location: /admin/');
        }

        return ['site/login'];
    }

    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }

}
