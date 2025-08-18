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
        // Проверка и создание таблицы users
        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
        if (!$checkTable) {
            App::db()->query("
                CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    login TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    email TEXT,
                    company_id INTEGER,
                    role TEXT NOT NULL DEFAULT 'user',
                    app_token TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (company_id) REFERENCES fuel_companies(id)
                )
            ");
        }

        // Проверка и создание таблицы fuel_types
        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='fuel_types'")->fetch();
        if (!$checkTable) {
            App::db()->query("
                CREATE TABLE fuel_types (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    description TEXT
                )
            ");
        }

        // Проверка и создание таблицы fuel_companies
        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='fuel_companies'")->fetch();
        if (!$checkTable) {
            App::db()->query("
                CREATE TABLE fuel_companies (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    slug TEXT NOT NULL UNIQUE,                   
                    name TEXT NOT NULL,
                    address TEXT,
                    phones TEXT,
                    emails TEXT,
                    working_hours TEXT,
                    website TEXT,
                    socials TEXT,
                    latitude DECIMAL(10, 8),
                    longitude DECIMAL(10, 8),
                    logo TEXT, -- Добавлено поле для логотипа
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }

        // Проверка и создание таблицы fuel_data
        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='fuel_data'")->fetch();
        if (!$checkTable) {
            App::db()->query("
                CREATE TABLE fuel_data (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    company_id INTEGER NOT NULL,
                    fuel_type_id INTEGER NOT NULL,
                    price DECIMAL(10, 2) NOT NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (company_id) REFERENCES fuel_companies(id),
                    FOREIGN KEY (fuel_type_id) REFERENCES fuel_types(id)
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
        $companies = App::db()->query("SELECT * FROM fuel_companies")->fetchAll(PDO::FETCH_ASSOC);
        $fuelTypes = App::db()->query("SELECT * FROM fuel_types")->fetchAll(PDO::FETCH_ASSOC);
        $navigations = App::db()->query("SELECT * FROM navigation")->fetchAll();
        return [
            'site/admin',
            [
                'settings' => $settings,
                'navigations' => $navigations,
                'menu' => $menu,
                'companies' => $companies,
                'fuelTypes' => $fuelTypes,
            ]
        ];
    }

    protected function actionManageCompanies()
    {
        $md = false;
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
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

        $companies = App::db()->query("SELECT * FROM fuel_companies")->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['create_company'])) {
                $phones = json_encode($_POST['phones'] ?? [], JSON_UNESCAPED_UNICODE);
                $emails = json_encode($_POST['emails'] ?? [], JSON_UNESCAPED_UNICODE);
                $workingHours = json_encode($_POST['working_hours'] ?? [], JSON_UNESCAPED_UNICODE);
                $socials = json_encode($_POST['socials'] ?? [], JSON_UNESCAPED_UNICODE);
                $stmt = App::db()->prepare("INSERT INTO fuel_companies (name, slug, address, phones, emails, working_hours, website, socials, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['name'], $_POST['slug'], $_POST['address'], $phones, $emails, $workingHours, $_POST['website'], $socials, $_POST['latitude'], $_POST['longitude']]);
            } elseif (isset($_POST['edit_company'])) {
                $companyId = $_POST['company_id'];
                $phones = json_encode($_POST['phones'] ?? [], JSON_UNESCAPED_UNICODE);
                $emails = json_encode($_POST['emails'] ?? [], JSON_UNESCAPED_UNICODE);
                $workingHours = json_encode($_POST['working_hours'] ?? [], JSON_UNESCAPED_UNICODE);
                $socials = json_encode($_POST['socials'] ?? [], JSON_UNESCAPED_UNICODE);
                $stmt = App::db()->prepare("UPDATE fuel_companies SET name = ?, slug = ?, address = ?, phones = ?, emails = ?, working_hours = ?, website = ?, socials = ?, latitude = ?, longitude = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['slug'], $_POST['address'], $phones, $emails, $workingHours, $_POST['website'], $socials, $_POST['latitude'], $_POST['longitude'], $companyId]);
            } elseif (isset($_POST['delete_company'])) {
                $companyId = (int)$_POST['company_id'];
                $stmt = App::db()->prepare("DELETE FROM fuel_companies WHERE id = ?");
                $stmt->execute([$companyId]);
                $stmt = App::db()->prepare("DELETE FROM users WHERE company_id = ?");
                $stmt->execute([$companyId]);
                $stmt = App::db()->prepare("DELETE FROM fuel_data WHERE company_id = ?");
                $stmt->execute([$companyId]);
            }
            header('Location: /admin/manage-companies');
            return true;
        }

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        return ['site/admin_manage_companies', ['settings' => $settings, 'menu' => $menu, 'companies' => $companies]];
    }

    protected function actionManageUsers()
    {
        $md = false;
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
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

        $users = App::db()->query("SELECT u.*, fc.name AS company_name FROM users u LEFT JOIN fuel_companies fc ON u.company_id = fc.id")->fetchAll(PDO::FETCH_ASSOC);
        $companies = App::db()->query("SELECT id, name FROM fuel_companies")->fetchAll(PDO::FETCH_ASSOC);
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['create_user'])) {
                $password = self::hash($_POST['password']);
                $companyId = $_POST['company_id'] === '' ? null : (int)$_POST['company_id'];
                $appToken = self::hash($_POST['login'] . $_POST['password']);
                $stmt = App::db()->prepare("INSERT INTO users (login, password, email, role, company_id, app_token) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['login'], $password, $_POST['email'], $_POST['role'], $companyId, $appToken]);
            } elseif (isset($_POST['edit_user'])) {
                $userId = (int)$_POST['user_id'];
                $password = $_POST['password'] ? self::hash($_POST['password']) : null;
                if (!$_POST['password']) {
                    $stmt = App::db()->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $password = $stmt->fetchColumn();
                }
                $companyId = $_POST['company_id'] === '' ? null : (int)$_POST['company_id'];
                $appToken = self::hash($_POST['login'] . ($_POST['password'] ?: $password));
                $stmt = App::db()->prepare("UPDATE users SET login = ?, password = ?, email = ?, role = ?, company_id = ?, app_token = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$_POST['login'], $password, $_POST['email'], $_POST['role'], $companyId, $appToken, $userId]);
            } elseif (isset($_POST['delete_user'])) {
                $userId = (int)$_POST['user_id'];
                $stmt = App::db()->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
            }
            header('Location: /admin/manage-users');
            return true;
        }

        return ['site/admin_manage_users', ['settings' => $settings, 'menu' => $menu, 'users' => $users, 'companies' => $companies]];
    }

    protected function actionFontsList()
    {
        $md = false;
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
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

        // Проверка и создание таблицы fonts
        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='fonts'")->fetch();
        if (!$checkTable) {
            App::db()->query("
            CREATE TABLE fonts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filename TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                size INTEGER NOT NULL,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                folder TEXT NOT NULL,
                woff_filename TEXT,
                woff2_filename TEXT,
                display_filename TEXT
            )
        ");
        }

        $fonts = App::db()->query("SELECT * FROM fonts ORDER BY folder ASC, uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);

        // Группировка шрифтов по папкам (семействам)
        $groupedFonts = [];
        foreach ($fonts as $font) {
            $groupedFonts[str_replace('\'', '', $font['folder'])][] = $font;
        }

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        // Хелпер для имени семейной папки
        $detectFamilyFolder = function (string $baseName): string {
            $baseName = preg_replace('/\s+/', ' ', trim($baseName));
            if (strpos($baseName, '-') !== false) {
                $family = explode('-', $baseName)[0];
                return _titleize_simple(str_replace('_', ' ', $family));
            }
            $baseName = str_replace('_', ' ', $baseName);
            $tokens = preg_split('/\s+/', $baseName);
            $styleTokens = [
                'regular','italic','bold','semibold','light','black','medium','extrabold','ultrabold','thin',
                'extralight','demilight','demibold','heavy','book','roman','oblique','condensed','expanded',
                'narrow','compressed','display','caption','headline','text','mono',
                'r','i','b','bi','u','it','md','lt','bk','sb'
            ];
            while (!empty($tokens) && in_array(strtolower(end($tokens)), $styleTokens, true)) {
                array_pop($tokens);
            }
            if (empty($tokens)) $tokens = [$baseName];
            $family = implode(' ', $tokens);
            return _titleize_simple($family);
        };
        function _titleize_simple(string $s): string {
            $s = mb_strtolower($s, 'UTF-8');
            $words = preg_split('/\s+/', $s);
            foreach ($words as &$w) {
                if ($w === '') continue;
                $w = mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($w, 1, null, 'UTF-8');
            }
            return implode(' ', $words);
        }

        // ======== новые хелперы для стандартизации имён ========
        $__STYLE_MAP = [
            'r'=>'Regular','reg'=>'Regular','regular'=>'Regular',
            'i'=>'Italic','it'=>'Italic','italic'=>'Italic','oblique'=>'Oblique',
            'b'=>'Bold','bold'=>'Bold',
            'md'=>'Medium','medium'=>'Medium',
            'lt'=>'Light','light'=>'Light','thin'=>'Thin','extralight'=>'ExtraLight',
            'sb'=>'SemiBold','semibold'=>'SemiBold','demibold'=>'SemiBold',
            'bk'=>'Book','book'=>'Book',
            'black'=>'Black','extrabold'=>'ExtraBold','ultrabold'=>'ExtraBold'
        ];
        $parseFamilyAndStyle = function (string $baseName) use ($__STYLE_MAP): array {
            $name = preg_replace('/(?:[ _-])?u$/i', '', $baseName);
            $name = preg_replace('/[ _-]+/u', ' ', trim($name));
            $compoundPattern = '(Thin|ExtraLight|Light|Book|Regular|Medium|SemiBold|Bold|ExtraBold|Black)';
            if (preg_match('/\b' . $compoundPattern . '(Italic|Oblique)?$/i', $name, $m)) {
                $style = ucfirst(strtolower($m[1])) . (!empty($m[2]) ? ucfirst(strtolower($m[2])) : '');
                $familyRaw = trim(preg_replace('/[ _-]*' . preg_quote($m[0], '/') . '$/i', '', $name));
                if ($familyRaw === '') $familyRaw = $name;
                return [$familyRaw, $style];
            }
            if (preg_match('/\b(r|reg|regular|i|it|italic|oblique|b|bold|md|medium|lt|light|thin|extralight|sb|semibold|demibold|bk|book|black|extrabold|ultrabold)$/i', $name, $m)) {
                $key = strtolower($m[1]);
                $style = $__STYLE_MAP[$key] ?? 'Regular';
                $familyRaw = trim(preg_replace('/[ _-]*' . preg_quote($m[0], '/') . '$/i', '', $name));
                if ($familyRaw === '') $familyRaw = $name;
                return [$familyRaw, $style];
            }
            return [$name, 'Regular'];
        };
        $buildFileName = function (string $familyRaw, string $style, string $ext): string {
            $familyTokenized = preg_replace('/\s+/u', '_', trim($familyRaw));
            $styleCompact = preg_replace('/\s+/u', '', $style);
            return $familyTokenized . '-' . $styleCompact . '.' . strtolower($ext);
        };
        // =======================================================

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['upload_fonts'])) {
                if (isset($_FILES['font_files']) && !empty($_FILES['font_files']['name'][0])) {
                    $baseUploadDir = __DIR__ . '/../../../../fonts/';
                    if (!is_dir($baseUploadDir)) {
                        mkdir($baseUploadDir, 0755, true);
                    }

                    require_once __DIR__ . '/../../../lib/sfnt2woff.php';
                    $errors = [];
                    $allowedExts = ['ttf','otf','woff','woff2'];

                    foreach ($_FILES['font_files']['name'] as $key => $name) {
                        $tmpName  = $_FILES['font_files']['tmp_name'][$key];
                        $fileSize = $_FILES['font_files']['size'][$key];
                        $fileExt  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $baseName = pathinfo($name, PATHINFO_FILENAME);

                        if (!in_array($fileExt, $allowedExts, true)) {
                            $errors[] = "Недопустимый формат файла: $name (допустимы: " . implode(', ', $allowedExts) . ")";
                            continue;
                        }

                        [$familyRaw, $style] = $parseFamilyAndStyle($baseName);
                        $folderName = preg_replace('/u$/i', '', $detectFamilyFolder($familyRaw));
                        $folderPath = $baseUploadDir . $folderName . '/';
                        if (!is_dir($folderPath)) mkdir($folderPath, 0755, true);

                        $stdFileName = $buildFileName($familyRaw, $style, $fileExt);
                        $destPath    = $folderPath . $stdFileName;

                        if (file_exists($destPath)) {
                            $i = 2;
                            $baseStd = pathinfo($stdFileName, PATHINFO_FILENAME);
                            while (file_exists($folderPath . $baseStd . "($i)." . $fileExt)) $i++;
                            $stdFileName = $baseStd . "($i)." . $fileExt;
                            $destPath    = $folderPath . $stdFileName;
                        }

                        if (!move_uploaded_file($tmpName, $destPath)) {
                            $errors[] = "Ошибка загрузки файла: $name";
                            continue;
                        }

                        $woffName = '';
                        $woff2Name = '';
                        $displayName = $stdFileName;

                        if (in_array($fileExt, ['ttf','otf'], true)) {
                            try {
                                $sfnt2woff = new \xenocrat\sfnt2woff();
                                $sfnt = file_get_contents($destPath);
                                $sfnt2woff->import($sfnt);
                                $sfnt2woff->compression_level = 9;
                                $woffData = $sfnt2woff->export();

                                $stdBase = pathinfo($stdFileName, PATHINFO_FILENAME);
                                $woffName = $stdBase . '.woff';
                                $destWoff = $folderPath . $woffName;
                                if (file_exists($destWoff)) {
                                    $i = 2;
                                    while (file_exists($folderPath . $stdBase . "($i).woff")) $i++;
                                    $woffName = $stdBase . "($i).woff";
                                    $destWoff = $folderPath . $woffName;
                                }
                                file_put_contents($destWoff, $woffData);
                                $displayName = $woffName;
                            } catch (\Throwable $e) { }
                        }

                        $stmt = App::db()->prepare("
                        INSERT INTO fonts (filename, name, size, folder, woff_filename, woff2_filename, display_filename)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                        $stmt->execute([
                            $stdFileName,
                            pathinfo($stdFileName, PATHINFO_FILENAME),
                            $fileSize,
                            $folderName,
                            $woffName,
                            $woff2Name,
                            $displayName
                        ]);
                    }

                    if (!empty($errors)) {
                        return ['site/admin_fonts', [
                            'settings'=>$settings,'menu'=>$menu,
                            'groupedFonts'=>$groupedFonts,'errors'=>$errors
                        ]];
                    }
                    header('Location: /admin/fonts-list');
                    return true;
                }
            } elseif (isset($_POST['delete_font'])) {
                $fontId = (int)$_POST['font_id'];
                $stmt = App::db()->prepare("SELECT filename, folder, woff_filename, woff2_filename, display_filename FROM fonts WHERE id = ?");
                $stmt->execute([$fontId]);
                $font = $stmt->fetch();

                if ($font) {
                    $filePath = __DIR__ . '/../../../../fonts/' . $font['folder'] . '/' . $font['filename'];
                    if (file_exists($filePath)) unlink($filePath);
                    if ($font['woff_filename'] && file_exists(__DIR__ . '/../../../../fonts/' . $font['folder'] . '/' . $font['woff_filename'])) unlink(__DIR__ . '/../../../../fonts/' . $font['folder'] . '/' . $font['woff_filename']);
                    if ($font['woff2_filename'] && file_exists(__DIR__ . '/../../../../fonts/' . $font['folder'] . '/' . $font['woff2_filename'])) unlink(__DIR__ . '/../../../../fonts/' . $font['folder'] . '/' . $font['woff2_filename']);

                    $folderPath = __DIR__ . '/../../../../fonts/' . $font['folder'] . '/';
                    if (is_dir($folderPath) && count(scandir($folderPath)) <= 2) rmdir($folderPath);

                    $stmt = App::db()->prepare("DELETE FROM fonts WHERE id = ?");
                    $stmt->execute([$fontId]);
                }
                header('Location: /admin/fonts-list');
                return true;
            }
        }

        return ['site/admin_fonts', ['settings'=>$settings,'menu'=>$menu,'groupedFonts'=>$groupedFonts]];
    }


    protected function actionManageFuelTypes()
    {
        $md = false;
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
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

        $fuelTypes = App::db()->query("SELECT * FROM fuel_types")->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['create_fuel_type'])) {
                $stmt = App::db()->prepare("INSERT INTO fuel_types (name, description) VALUES (?, ?)");
                $stmt->execute([$_POST['name'], $_POST['description']]);
            } elseif (isset($_POST['edit_fuel_type'])) {
                $fuelTypeId = $_POST['fuel_type_id'];
                $name = $_POST['name'] ?? ''; // Ensure name is provided
                if (empty($name)) {
                    return ['site/admin_manage_fuel_types', ['settings' => $settings, 'menu' => $menu, 'fuelTypes' => $fuelTypes, 'error' => 'Название не может быть пустым']];
                }
                $stmt = App::db()->prepare("UPDATE fuel_types SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['description'], $fuelTypeId]);
            } elseif (isset($_POST['delete_fuel_type'])) {
                $fuelTypeId = (int)$_POST['fuel_type_id']; // Ensure integer conversion
                $stmt = App::db()->prepare("DELETE FROM fuel_types WHERE id = ?");
                $stmt->execute([$fuelTypeId]);
                $stmt = App::db()->prepare("DELETE FROM fuel_data WHERE fuel_type_id = ?");
                $stmt->execute([$fuelTypeId]);
            }
            header('Location: /admin/manage-fuel-types');
            return true;
        }

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        return ['site/admin_manage_fuel_types', ['settings' => $settings, 'menu' => $menu, 'fuelTypes' => $fuelTypes]];
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