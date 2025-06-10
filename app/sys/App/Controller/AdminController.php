<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller,
    App\App;
use PDO;

class AdminController extends Controller
{
    protected function actionIndex()
    {
// Создание таблицы pages, если она не существует
        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='pages'")->fetch();
        if (!$checkTable) {
            App::db()->query("
            CREATE TABLE pages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,              
                slug TEXT NOT NULL UNIQUE,
                content TEXT NOT NULL,
                seo_title TEXT,
                seo_description TEXT,
                seo_keywords TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        }
        $query = App::db()->query("PRAGMA table_info(settings)");
        $columns = $query->fetchAll(PDO::FETCH_ASSOC);

        $columnExists = false;
        foreach ($columns as $column) {
            if ($column['name'] === 'img_logo') {
                $columnExists = true;
                break;
            }
        }

        if (!$columnExists) {
            App::db()->query("ALTER TABLE settings ADD COLUMN img_logo TEXT DEFAULT 'logo.svg'");
        }
        $columns = array_column($columns, 'name');
        if (!in_array('banner_middle_1', $columns)) {
            App::db()->query("ALTER TABLE settings ADD COLUMN banner_middle_1 TEXT DEFAULT ''");
        }
        if (!in_array('banner_middle_2', $columns)) {
            App::db()->query("ALTER TABLE settings ADD COLUMN banner_middle_2 TEXT DEFAULT ''");
        }
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
            if (isset($_FILES['logo-image']['name']) && !empty($_FILES['logo-image']['name'])) {
                $imageName = $_FILES['logo-image']['name'];
                $imageTmp = $_FILES['logo-image']['tmp_name'];
                $imagePath = __DIR__ . '/../../../../img/' . $imageName;
                move_uploaded_file($imageTmp, $imagePath);
            } else {
                $query = App::db()->query("SELECT img_logo FROM settings LIMIT 1");
                $imageName = $query->fetchColumn();
            }
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
                     banner_footer_small = ?, banner_footer_small_2 = ? ,  banner_footer_small_3 = ?,
                     banner_middle_1 = ?, banner_middle_2 = ?,
                     img_logo = ?
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
                    $_POST['banner_middle_1'], $_POST['banner_middle_2'],
                    $imageName
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
                } else {
                    $navLink = '/' . $navLinks[$menuId];
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
            $existingMenus = App::db()->query("SELECT * FROM navigation")->fetchAll();
            $existingMenuIds = array_column($existingMenus, 'id');
            $menuIdsInRequest = array_column($menuData, 'id');
            foreach ($existingMenus as $existingMenu) {
                if (!in_array($existingMenu['id'], $menuIdsInRequest)) {
                    $imagePath = __DIR__ . '/../../../../img/' . $existingMenu['image'];
                    if (file_exists($imagePath) && is_file($imagePath)) {
                        unlink($imagePath);
                    }
                    $stmt = App::db()->prepare("DELETE FROM navigation WHERE id = ?");
                    $stmt->execute([$existingMenu['id']]);
                }
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
            $bank = $_POST['bank'];
            //$bank['name'] = $banks[$id]['name']; // не даём перезаписать название вручную (если не хочешь — убери эту строку)
            $bank['manual'] = true;
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

        $exchangers = unserialize(file_get_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $exchanger = $_POST['exchanger'];
            $bank['manual'] = true;
            //$exchanger['name'] = $exchangers[$id]['name']; // можно убрать, если редактирование имени нужно

            $exchangers[$id] = $exchanger;

            file_put_contents(__DIR__ . '/../../../storage/exchanger_info_pure.txt', serialize($exchangers));
        }

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');

        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        return [
            'site/admin_exchanger',
            [
                'settings' => $settings,
                'menu' => $menu,
                'exchanger' => $exchangers[$id],
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

    // Отображение списка страниц
    protected function actionPages()
    {
        $query = App::db()->query("SELECT * FROM pages ORDER BY created_at DESC");
        $pages = $query->fetchAll();

        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/admin_pages',
            [
                'pages' => $pages,
                'settings' => $settings,
                'menu' => $menu,
                'navigations' => $navigations,
            ]
        ];
    }

    protected function actionCreatePage()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $slug = $_POST['slug'];
            $content = $_POST['content']; // Сохраняем содержимое без фильтрации PHP
            $seo_title = $_POST['seo_title'];
            $seo_description = $_POST['seo_description'];
            $seo_keywords = $_POST['seo_keywords'];

            // Базовая санитизация (например, проверка на опасные конструкции)
            if (preg_match('/(eval|system|exec|shell_exec|passthru|phpinfo)/i', $content)) {
                return ['site/admin_create_page', ['error' => 'Обнаружены запрещенные PHP-функции']];
            }

            $stmt = App::db()->prepare("INSERT INTO pages (slug, content, seo_title, seo_description, seo_keywords) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $content, $seo_title, $seo_description, $seo_keywords]);

            header('Location: /admin/pages');
            return true;
        }

        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/admin_create_page',
            [
                'settings' => $settings,
                'menu' => $menu,
                'navigations' => $navigations,
            ]
        ];
    }

    // Редактирование страницы
    protected function actionEditPage($id)
    {
        $stmt = App::db()->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch();

        if (!$page) {
            return $this->actionNotFound();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $slug = $_POST['slug'];
            $content = $_POST['content']; // Сохраняем содержимое без фильтрации PHP
            $seo_title = $_POST['seo_title'];
            $seo_description = $_POST['seo_description'];
            $seo_keywords = $_POST['seo_keywords'];

            // Базовая санитизация
            if (preg_match('/(eval|system|exec|shell_exec|passthru|phpinfo)/i', $content)) {
                return ['site/admin_edit_page', ['error' => 'Обнаружены запрещенные PHP-функции', 'page' => $page]];
            }

            $stmt = App::db()->prepare("UPDATE pages SET slug = ?, content = ?, seo_title = ?, seo_description = ?, seo_keywords = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$slug, $content, $seo_title, $seo_description, $seo_keywords, $id]);

            header('Location: /admin/pages');
            return true;
        }

        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/admin_edit_page',
            [
                'page' => $page,
                'settings' => $settings,
                'menu' => $menu,
                'navigations' => $navigations,
            ]
        ];
    }

    // Удаление страницы
    protected function actionDeletePage($id)
    {
        $stmt = App::db()->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: /admin/pages');
        return true;
    }

    // Метод для обработки 404
    protected function actionNotFound()
    {
        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();

        return [
            'site/404',
            [
                'settings' => $settings,
                'menu' => $menu,
                'navigations' => $navigations,
            ]
        ];
    }
}