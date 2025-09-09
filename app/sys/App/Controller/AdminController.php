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

//        $this->addedTables();
        $this->migrateFuelSchemaToCompanyPoints();
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

        // === Уведомления (новые компании с ценами + новые заявки) ===
        $pdo = App::db();

// Кол-во компаний с любым ожиданием модерации (company/point/price)
        $sqlCountCompanies = "
    SELECT COUNT(DISTINCT fc.id)
    FROM fuel_companies fc
    LEFT JOIN company_points cp ON cp.company_id = fc.id
    LEFT JOIN fuel_data     fd ON fd.company_point_id = cp.id
    WHERE COALESCE(fc.moderation_status,'approved') = 'pending'
       OR COALESCE(cp.moderation_status,'approved') = 'pending'
       OR COALESCE(fd.moderation_status,'approved') = 'pending'
";
        $pendingCompaniesCount = (int)$pdo->query($sqlCountCompanies)->fetchColumn();

// Список последних компаний С ЦЕНАМИ (для предпросмотра)
        $sqlListCompanies = "
    SELECT
        fc.id,
        fc.name,
        MIN(cp.address) AS address,
        MIN(c.name_ru)  AS city,
        MIN(COALESCE(cp.created_at, fc.created_at)) AS created_at,
        REPLACE(
            GROUP_CONCAT(DISTINCT (ft.name || ' ' || printf('%.2f', fd.price))),
            ',', ' | '
        ) AS prices
    FROM fuel_companies fc
    JOIN company_points cp ON cp.company_id = fc.id
    JOIN fuel_data     fd  ON fd.company_point_id = cp.id
    LEFT JOIN fuel_types ft ON ft.id = fd.fuel_type_id
    LEFT JOIN cities     c  ON c.id = cp.city_id
    WHERE COALESCE(fc.moderation_status,'approved') = 'pending'
       OR COALESCE(cp.moderation_status,'approved') = 'pending'
       OR COALESCE(fd.moderation_status,'approved') = 'pending'
    GROUP BY fc.id, fc.name
    ORDER BY created_at DESC
    LIMIT 12
";
        $pendingCompaniesList = $pdo->query($sqlListCompanies)->fetchAll(PDO::FETCH_ASSOC);

// Новые заявки пользователей
        $newReqCount = (int)$pdo->query("SELECT COUNT(*) FROM user_requests WHERE status = 'new'")->fetchColumn();
        $newReqList = $pdo->query("
    SELECT ur.id,
           ur.user_id,
           ur.subject,
           ur.created_at,
           COALESCE(u.login, '') AS user_login,
           COALESCE(u.email, '') AS email
      FROM user_requests ur
      LEFT JOIN users u ON u.id = ur.user_id
     WHERE ur.status = 'new'
     ORDER BY ur.created_at DESC
     LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Собираем в один удобный массив и передаём во view
        $alerts = [
            'companies' => [
                'count' => $pendingCompaniesCount,
                'list' => $pendingCompaniesList,
            ],
            'requests' => [
                'count' => $newReqCount,
                'list' => $newReqList,
            ],
            'sum' => $newReqCount + $pendingCompaniesCount
        ];

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
            'admin/admin',
            [
                'settings' => $settings,
                'navigations' => $navigations,
                'menu' => $menu,
                'companies' => $companies,
                'fuelTypes' => $fuelTypes,
                'alerts' => $alerts,
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
                $slugify = function (string $s): string {
                    $s = trim($s);
                    if ($s === '') return substr(md5(uniqid('', true)), 0, 8);
                    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
                    $s = strtolower(preg_replace('~[^a-z0-9]+~', '-', $s));
                    $s = trim($s, '-');
                    return $s !== '' ? $s : substr(md5(uniqid('', true)), 0, 8);
                };
                $slug = trim($_POST['slug'] ?? '');
                if ($slug === '') $slug = $slugify($_POST['name']);
                $stmt = App::db()->prepare("INSERT INTO fuel_companies (name, slug) VALUES (?, ?)");
                $stmt->execute([$_POST['name'], $slug]);
            } elseif (isset($_POST['edit_company'])) {
                $companyId = $_POST['company_id'];
                $stmt = App::db()->prepare("UPDATE fuel_companies SET name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$_POST['name'], $companyId]);
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

        return ['admin/admin_manage_companies', ['settings' => $settings, 'menu' => $menu, 'companies' => $companies]];
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

        return ['admin/admin_manage_users', ['settings' => $settings, 'menu' => $menu, 'users' => $users, 'companies' => $companies]];
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
                'regular', 'italic', 'bold', 'semibold', 'light', 'black', 'medium', 'extrabold', 'ultrabold', 'thin',
                'extralight', 'demilight', 'demibold', 'heavy', 'book', 'roman', 'oblique', 'condensed', 'expanded',
                'narrow', 'compressed', 'display', 'caption', 'headline', 'text', 'mono',
                'r', 'i', 'b', 'bi', 'u', 'it', 'md', 'lt', 'bk', 'sb'
            ];
            while (!empty($tokens) && in_array(strtolower(end($tokens)), $styleTokens, true)) {
                array_pop($tokens);
            }
            if (empty($tokens)) $tokens = [$baseName];
            $family = implode(' ', $tokens);
            return _titleize_simple($family);
        };
        function _titleize_simple(string $s): string
        {
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
            'r' => 'Regular', 'reg' => 'Regular', 'regular' => 'Regular',
            'i' => 'Italic', 'it' => 'Italic', 'italic' => 'Italic', 'oblique' => 'Oblique',
            'b' => 'Bold', 'bold' => 'Bold',
            'md' => 'Medium', 'medium' => 'Medium',
            'lt' => 'Light', 'light' => 'Light', 'thin' => 'Thin', 'extralight' => 'ExtraLight',
            'sb' => 'SemiBold', 'semibold' => 'SemiBold', 'demibold' => 'SemiBold',
            'bk' => 'Book', 'book' => 'Book',
            'black' => 'Black', 'extrabold' => 'ExtraBold', 'ultrabold' => 'ExtraBold'
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
                    $allowedExts = ['ttf', 'otf', 'woff', 'woff2'];

                    foreach ($_FILES['font_files']['name'] as $key => $name) {
                        $tmpName = $_FILES['font_files']['tmp_name'][$key];
                        $fileSize = $_FILES['font_files']['size'][$key];
                        $fileExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));
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
                        $destPath = $folderPath . $stdFileName;

                        if (file_exists($destPath)) {
                            $i = 2;
                            $baseStd = pathinfo($stdFileName, PATHINFO_FILENAME);
                            while (file_exists($folderPath . $baseStd . "($i)." . $fileExt)) $i++;
                            $stdFileName = $baseStd . "($i)." . $fileExt;
                            $destPath = $folderPath . $stdFileName;
                        }

                        if (!move_uploaded_file($tmpName, $destPath)) {
                            $errors[] = "Ошибка загрузки файла: $name";
                            continue;
                        }

                        $woffName = '';
                        $woff2Name = '';
                        $displayName = $stdFileName;

                        if (in_array($fileExt, ['ttf', 'otf'], true)) {
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
                            } catch (\Throwable $e) {
                            }
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
                        return ['admin/admin_fonts', [
                            'settings' => $settings, 'menu' => $menu,
                            'groupedFonts' => $groupedFonts, 'errors' => $errors
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

        return ['admin/admin_fonts', ['settings' => $settings, 'menu' => $menu, 'groupedFonts' => $groupedFonts]];
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
                    return ['admin/admin_manage_fuel_types', ['settings' => $settings, 'menu' => $menu, 'fuelTypes' => $fuelTypes, 'error' => 'Название не может быть пустым']];
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

        return ['admin/admin_manage_fuel_types', ['settings' => $settings, 'menu' => $menu, 'fuelTypes' => $fuelTypes]];
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
            'admin/admin_bank',
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
            'admin/admin_exchanger',
            [
                'settings' => $settings,
                'menu' => $menu,
                'exchanger' => $exchangers[$id],
            ]
        ];
    }

    protected function actionManageGeo()
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

        // --- Меню, как в других экранах ---
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        $pdo = App::db();

        // Утилита: slugify (если slug не задан)
        $slugify = function (string $s): string {
            $s = trim($s);
            if ($s === '') return substr(md5(uniqid('', true)), 0, 8);
            // если есть английский — берём его, иначе пробуем упростить
            $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            $s = strtolower(preg_replace('~[^a-z0-9]+~', '-', $s));
            $s = trim($s, '-');
            return $s !== '' ? $s : substr(md5(uniqid('', true)), 0, 8);
        };

        // --- CRUD ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // REGION: CREATE
            if (isset($_POST['create_region'])) {
                $name_ru = trim($_POST['name_ru'] ?? '');
                $name_am = trim($_POST['name_am'] ?? '');
                $name_en = trim($_POST['name_en'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                if ($slug === '') $slug = $slugify($name_en ?: $name_ru);

                $stmt = $pdo->prepare("INSERT INTO regions (slug,name_ru,name_am,name_en,updated_at) VALUES (?,?,?,?,CURRENT_TIMESTAMP)");
                $stmt->execute([$slug, $name_ru, $name_am, $name_en]);
            }

            // REGION: UPDATE
            if (isset($_POST['edit_region'])) {
                $id = (int)$_POST['region_id'];
                $name_ru = trim($_POST['name_ru'] ?? '');
                $name_am = trim($_POST['name_am'] ?? '');
                $name_en = trim($_POST['name_en'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                if ($slug === '') $slug = $slugify($name_en ?: $name_ru);

                $stmt = $pdo->prepare("UPDATE regions SET slug=?, name_ru=?, name_am=?, name_en=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                $stmt->execute([$slug, $name_ru, $name_am, $name_en, $id]);
            }

            // REGION: DELETE (с удалением городов этого региона)
            if (isset($_POST['delete_region'])) {
                $id = (int)$_POST['region_id'];
                $pdo->beginTransaction();
                $pdo->prepare("DELETE FROM cities WHERE region_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM regions WHERE id=?")->execute([$id]);
                $pdo->commit();
            }

            // CITY: CREATE
            if (isset($_POST['create_city'])) {
                $region_id = (int)($_POST['region_id'] ?? 0);
                $name_ru = trim($_POST['name_ru'] ?? '');
                $name_am = trim($_POST['name_am'] ?? '');
                $name_en = trim($_POST['name_en'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $lat = $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
                $lng = $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;
                $capital = isset($_POST['is_capital']) ? 1 : 0;
                $center = isset($_POST['is_region_center']) ? 1 : 0;
                if ($slug === '') $slug = $slugify($name_en ?: $name_ru);

                $stmt = $pdo->prepare("
                INSERT INTO cities (region_id,slug,name_ru,name_am,name_en,lat,lng,is_capital,is_region_center,updated_at)
                VALUES (?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)
            ");
                $stmt->execute([$region_id, $slug, $name_ru, $name_am, $name_en, $lat, $lng, $capital, $center]);
            }

            // CITY: UPDATE
            if (isset($_POST['edit_city'])) {
                $id = (int)$_POST['city_id'];
                $region_id = (int)($_POST['region_id'] ?? 0);
                $name_ru = trim($_POST['name_ru'] ?? '');
                $name_am = trim($_POST['name_am'] ?? '');
                $name_en = trim($_POST['name_en'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $lat = $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
                $lng = $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;
                $capital = isset($_POST['is_capital']) ? 1 : 0;
                $center = isset($_POST['is_region_center']) ? 1 : 0;
                if ($slug === '') $slug = $slugify($name_en ?: $name_ru);

                $stmt = $pdo->prepare("
                UPDATE cities SET
                    region_id=?, slug=?, name_ru=?, name_am=?, name_en=?, lat=?, lng=?, is_capital=?, is_region_center=?, updated_at=CURRENT_TIMESTAMP
                WHERE id=?
            ");
                $stmt->execute([$region_id, $slug, $name_ru, $name_am, $name_en, $lat, $lng, $capital, $center, $id]);
            }

            // CITY: DELETE
            if (isset($_POST['delete_city'])) {
                $id = (int)$_POST['city_id'];
                $pdo->prepare("DELETE FROM cities WHERE id=?")->execute([$id]);
            }

            header('Location: /admin/manage-geo');
            return true;
        }

        // --- Данные для таблиц/селектов ---
        $regions = $pdo->query("SELECT * FROM regions ORDER BY name_ru")->fetchAll(PDO::FETCH_ASSOC);
        $cities = $pdo->query("
        SELECT c.*, r.name_ru AS region_name_ru
        FROM cities c
        LEFT JOIN regions r ON r.id = c.region_id
        ORDER BY r.name_ru, c.name_ru
    ")->fetchAll(PDO::FETCH_ASSOC);

        return ['admin/admin_manage_geo', [
            'settings' => $settings,
            'menu' => $menu,
            'regions' => $regions,
            'cities' => $cities
        ]];
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
            'admin/admin_pages',
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
                return ['admin/admin_create_page', ['error' => 'Обнаружены запрещенные PHP-функции']];
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
            'admin/admin_create_page',
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
                return ['admin/admin_edit_page', ['error' => 'Обнаружены запрещенные PHP-функции', 'page' => $page]];
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
            'admin/admin_edit_page',
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

    protected function guardAdmin()
    {
        // общий хелпер авторизации админа — такой же, как в manageUsers
        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $ok = false;
        if (!empty($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            if (self::hash($settings['login'] . $settings['password']) === $token) $ok = true;
        }
        if (!$ok) {
            header('Location: /login');
            return false;
        }

        // меню для админки
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        return ['settings' => $settings, 'menu' => $menu];
    }

    protected function actionRequests()
    {
        $guard = $this->guardAdmin();
        if ($guard === false) return false;

        // фильтры
        $q = trim((string)($_GET['q'] ?? ''));
        $status = (string)($_GET['status'] ?? '');
        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = "(ur.subject LIKE :q OR ur.message LIKE :q OR u.login LIKE :q)";
            $params[':q'] = "%{$q}%";
        }
        if (in_array($status, ['new', 'in_progress', 'done'], true)) {
            $where[] = "ur.status = :st";
            $params[':st'] = $status;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "
        SELECT ur.*, u.login
          FROM user_requests ur
          LEFT JOIN users u ON u.id = ur.user_id
          $whereSql
         ORDER BY ur.id DESC
         LIMIT 100
    ";
        $stmt = App::db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['admin/admin_requests', [
            'settings' => $guard['settings'],
            'menu' => $guard['menu'],
            'rows' => $rows,
            'q' => $q,
            'status' => $status,
        ]];
    }

    protected function actionRequestView($id)
    {
        $guard = $this->guardAdmin();
        if ($guard === false) return false;

        $id = (int)$id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $answer = trim((string)($_POST['answer'] ?? ''));
            $status = (string)($_POST['status'] ?? 'in_progress');
            if (!in_array($status, ['new', 'in_progress', 'done'], true)) $status = 'in_progress';

            $stmt = App::db()->prepare("
            UPDATE user_requests
               SET answer = ?, status = ?, answered_at = CASE WHEN ? <> '' THEN CURRENT_TIMESTAMP ELSE answered_at END
             WHERE id = ?
        ");
            $stmt->execute([$answer, $status, $answer, $id]);

            header('Location: /admin/request/' . $id . '?saved=1');
            return true;
        }

        // данные заявки
        $stmt = App::db()->prepare("
        SELECT ur.*, u.login, u.email
          FROM user_requests ur
          LEFT JOIN users u ON u.id = ur.user_id
         WHERE ur.id = ?
        LIMIT 1
    ");
        $stmt->execute([$id]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$req) {
            header('Location: /admin/requests');
            return true;
        }

        return ['admin/admin_request_view', [
            'settings' => $guard['settings'],
            'menu' => $guard['menu'],
            'r' => $req,
            'saved' => !empty($_GET['saved']),
        ]];
    }

    protected function actionRequestDelete($id)
    {
        $guard = $this->guardAdmin();
        if ($guard === false) return false;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/request/' . (int)$id);
            return true;
        }

        $id = (int)$id;
        // сносим файл, если был
        $stmt = App::db()->prepare("SELECT file_path FROM user_requests WHERE id = ?");
        $stmt->execute([$id]);
        $fp = $stmt->fetchColumn();
        if ($fp && is_file(__DIR__ . '/../../../../' . $fp)) {
            @unlink(__DIR__ . '/../../../../' . $fp);
        }

        $del = App::db()->prepare("DELETE FROM user_requests WHERE id = ?");
        $del->execute([$id]);

        header('Location: /admin/requests?deleted=1');
        return true;
    }

    protected function addedTables()
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

// Проверка и создание таблицы user_requests
        $checkTable = App::db()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user_requests'")->fetch();
        if (!$checkTable) {
            App::db()->query("
        CREATE TABLE user_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            subject TEXT NOT NULL,
            message TEXT NOT NULL,
            file_path TEXT,
            status TEXT NOT NULL DEFAULT 'new', -- new, in_progress, done
            answer TEXT,                        -- ответ администратора/поддержки
            answered_at TIMESTAMP,              -- когда дан ответ
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
            App::db()->query("CREATE INDEX IF NOT EXISTS idx_user_requests_user ON user_requests(user_id)");
            App::db()->query("CREATE INDEX IF NOT EXISTS idx_user_requests_status ON user_requests(status)");
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
        $pdo = App::db();

        /** 1) REGIONS */
        $existsTable = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='regions'")->fetch();
        if (!$existsTable) {
            $pdo->query("
        CREATE TABLE regions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            name_ru TEXT NOT NULL,
            name_am TEXT NOT NULL,
            name_en TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
        } else {
            // Добавим name_en, если его нет
            $cols = $pdo->query("PRAGMA table_info(regions)")->fetchAll(PDO::FETCH_ASSOC);
            $hasEng = false;
            foreach ($cols as $c) if (strtolower($c['name']) === 'name_en') {
                $hasEng = true;
                break;
            }
            if (!$hasEng) $pdo->query("ALTER TABLE regions ADD COLUMN name_en TEXT NOT NULL DEFAULT ''");
        }

        /** 2) CITIES */
        $existsTable = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='cities'")->fetch();
        if (!$existsTable) {
            $pdo->query("
        CREATE TABLE cities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            region_id INTEGER NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            name_ru TEXT NOT NULL,
            name_am TEXT NOT NULL,
            name_en TEXT NOT NULL,
            lat REAL,
            lng REAL,
            is_capital INTEGER NOT NULL DEFAULT 0,        -- столица страны
            is_region_center INTEGER NOT NULL DEFAULT 0,  -- центр марза
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (region_id) REFERENCES regions(id)
        )
    ");
            $pdo->query("CREATE INDEX IF NOT EXISTS idx_cities_region_name ON cities(region_id, name_ru)");
        } else {
            // Добавим name_en, если его нет
            $cols = $pdo->query("PRAGMA table_info(cities)")->fetchAll(PDO::FETCH_ASSOC);
            $hasEng = false;
            foreach ($cols as $c) if (strtolower($c['name']) === 'name_en') {
                $hasEng = true;
                break;
            }
            if (!$hasEng) $pdo->query("ALTER TABLE cities ADD COLUMN name_en TEXT NOT NULL DEFAULT ''");
        }

        /** 3) DATA: регионы (10 марзов + Ереван) */
        $regions = [
            ['slug' => 'yerevan', 'name_ru' => 'Ереван', 'name_am' => 'Երևան', 'name_en' => 'Yerevan'],
            ['slug' => 'aragatsotn', 'name_ru' => 'Арагацотн', 'name_am' => 'Արագածոտն', 'name_en' => 'Aragatsotn'],
            ['slug' => 'ararat', 'name_ru' => 'Арарат', 'name_am' => 'Արարատ', 'name_en' => 'Ararat'],
            ['slug' => 'armavir', 'name_ru' => 'Армавир', 'name_am' => 'Արմավիր', 'name_en' => 'Armavir'],
            ['slug' => 'gegharkunik', 'name_ru' => 'Гегаркуник', 'name_am' => 'Գեղարքունիք', 'name_en' => 'Gegharkunik'],
            ['slug' => 'kotayk', 'name_ru' => 'Котайк', 'name_am' => 'Կոտայք', 'name_en' => 'Kotayk'],
            ['slug' => 'lori', 'name_ru' => 'Лори', 'name_am' => 'Լոռի', 'name_en' => 'Lori'],
            ['slug' => 'shirak', 'name_ru' => 'Ширак', 'name_am' => 'Շիրակ', 'name_en' => 'Shirak'],
            ['slug' => 'syunik', 'name_ru' => 'Сюник', 'name_am' => 'Սյունիք', 'name_en' => 'Syunik'],
            ['slug' => 'tavush', 'name_ru' => 'Тавуш', 'name_am' => 'Թավուշ', 'name_en' => 'Tavush'],
            ['slug' => 'vayots-dzor', 'name_ru' => 'Вайоц Дзор', 'name_am' => 'Վայոց Ձոր', 'name_en' => 'Vayots Dzor'],
        ];

        $insReg = $pdo->prepare("INSERT OR IGNORE INTO regions (slug,name_ru,name_am,name_en) VALUES (:slug,:name_ru,:name_am,:name_en)");
        $updReg = $pdo->prepare("UPDATE regions SET name_ru=:name_ru, name_am=:name_am, name_en=:name_en WHERE slug=:slug");
        foreach ($regions as $r) {
            $insReg->execute($r);
            $updReg->execute($r);
        }

        /** 4) Карта region_id */
        $regionMap = [];
        foreach ($pdo->query("SELECT id, slug FROM regions")->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $regionMap[$row['slug']] = (int)$row['id'];
        }

        /** 5) DATA: города (основные города и центры марзов) */
        $cities = [
            // Yerevan (capital)
            ['slug' => 'yerevan', 'region' => 'yerevan', 'name_ru' => 'Ереван', 'name_am' => 'Երևան', 'name_en' => 'Yerevan', 'lat' => 40.1772, 'lng' => 44.5035, 'capital' => 1, 'center' => 1],

            // Aragatsotn
            ['slug' => 'ashtarak', 'region' => 'aragatsotn', 'name_ru' => 'Аштарак', 'name_am' => 'Աշտարակ', 'name_en' => 'Ashtarak', 'lat' => 40.299, 'lng' => 44.362, 'capital' => 0, 'center' => 1],
            ['slug' => 'aparan', 'region' => 'aragatsotn', 'name_ru' => 'Апаран', 'name_am' => 'Ապարան', 'name_en' => 'Aparan', 'lat' => 40.593, 'lng' => 44.358, 'capital' => 0, 'center' => 0],
            ['slug' => 'talin', 'region' => 'aragatsotn', 'name_ru' => 'Талин', 'name_am' => 'Թալին', 'name_en' => 'Talin', 'lat' => 40.391, 'lng' => 43.872, 'capital' => 0, 'center' => 0],

            // Ararat
            ['slug' => 'artashat', 'region' => 'ararat', 'name_ru' => 'Арташат', 'name_am' => 'Արտաշատ', 'name_en' => 'Artashat', 'lat' => 39.953, 'lng' => 44.549, 'capital' => 0, 'center' => 1],
            ['slug' => 'ararat-city', 'region' => 'ararat', 'name_ru' => 'Арарат', 'name_am' => 'Արարատ', 'name_en' => 'Ararat (city)', 'lat' => 39.830, 'lng' => 44.700, 'capital' => 0, 'center' => 0],
            ['slug' => 'masis', 'region' => 'ararat', 'name_ru' => 'Масис', 'name_am' => 'Մասիս', 'name_en' => 'Masis', 'lat' => 40.067, 'lng' => 44.434, 'capital' => 0, 'center' => 0],
            ['slug' => 'vedi', 'region' => 'ararat', 'name_ru' => 'Веди', 'name_am' => 'Վեդի', 'name_en' => 'Vedi', 'lat' => 39.913, 'lng' => 44.728, 'capital' => 0, 'center' => 0],

            // Armavir
            ['slug' => 'armavir-city', 'region' => 'armavir', 'name_ru' => 'Армавир', 'name_am' => 'Արմավիր', 'name_en' => 'Armavir (city)', 'lat' => 40.154, 'lng' => 44.039, 'capital' => 0, 'center' => 1],
            ['slug' => 'vagharshapat', 'region' => 'armavir', 'name_ru' => 'Вагаршапат', 'name_am' => 'Վաղարշապատ', 'name_en' => 'Vagharshapat (Etchmiadzin)', 'lat' => 40.169, 'lng' => 44.291, 'capital' => 0, 'center' => 0],
            ['slug' => 'metsamor', 'region' => 'armavir', 'name_ru' => 'Мецамор', 'name_am' => 'Մեծամոր', 'name_en' => 'Metsamor', 'lat' => 40.147, 'lng' => 44.133, 'capital' => 0, 'center' => 0],

            // Gegharkunik
            ['slug' => 'gavar', 'region' => 'gegharkunik', 'name_ru' => 'Гавар', 'name_am' => 'Գավառ', 'name_en' => 'Gavar', 'lat' => 40.354, 'lng' => 45.123, 'capital' => 0, 'center' => 1],
            ['slug' => 'sevan', 'region' => 'gegharkunik', 'name_ru' => 'Севан', 'name_am' => 'Սևան', 'name_en' => 'Sevan', 'lat' => 40.549, 'lng' => 44.948, 'capital' => 0, 'center' => 0],
            ['slug' => 'martuni', 'region' => 'gegharkunik', 'name_ru' => 'Мартуни', 'name_am' => 'Մարտունի', 'name_en' => 'Martuni', 'lat' => 40.135, 'lng' => 45.306, 'capital' => 0, 'center' => 0],
            ['slug' => 'vardenis', 'region' => 'gegharkunik', 'name_ru' => 'Варденис', 'name_am' => 'Վարդենիս', 'name_en' => 'Vardenis', 'lat' => 40.183, 'lng' => 45.730, 'capital' => 0, 'center' => 0],
            ['slug' => 'chambarak', 'region' => 'gegharkunik', 'name_ru' => 'Чамбарак', 'name_am' => 'Չամբարակ', 'name_en' => 'Chambarak', 'lat' => 40.595, 'lng' => 45.349, 'capital' => 0, 'center' => 0],

            // Kotayk
            ['slug' => 'hrazdan', 'region' => 'kotayk', 'name_ru' => 'Раздан', 'name_am' => 'Հրազդան', 'name_en' => 'Hrazdan', 'lat' => 40.497, 'lng' => 44.766, 'capital' => 0, 'center' => 1],
            ['slug' => 'abovyan', 'region' => 'kotayk', 'name_ru' => 'Абовян', 'name_am' => 'Աբովյան', 'name_en' => 'Abovyan', 'lat' => 40.271, 'lng' => 44.627, 'capital' => 0, 'center' => 0],
            ['slug' => 'charentsavan', 'region' => 'kotayk', 'name_ru' => 'Чаренцаван', 'name_am' => 'Չարենցավան', 'name_en' => 'Charentsavan', 'lat' => 40.402, 'lng' => 44.647, 'capital' => 0, 'center' => 0],
            ['slug' => 'byureghavan', 'region' => 'kotayk', 'name_ru' => 'Бюрегаван', 'name_am' => 'Բյուրեղավան', 'name_en' => 'Byureghavan', 'lat' => 40.374, 'lng' => 44.593, 'capital' => 0, 'center' => 0],
            ['slug' => 'nor-hachn', 'region' => 'kotayk', 'name_ru' => 'Нор-Ачин', 'name_am' => 'Նոր Հաճն', 'name_en' => 'Nor Hachn', 'lat' => 40.322, 'lng' => 44.586, 'capital' => 0, 'center' => 0],
            ['slug' => 'tsaghkadzor', 'region' => 'kotayk', 'name_ru' => 'Цахкадзор', 'name_am' => 'Ծաղկաձոր', 'name_en' => 'Tsaghkadzor', 'lat' => 40.532, 'lng' => 44.719, 'capital' => 0, 'center' => 0],
            ['slug' => 'yeghvard', 'region' => 'kotayk', 'name_ru' => 'Егвард', 'name_am' => 'Եղվարդ', 'name_en' => 'Yeghvard', 'lat' => 40.321, 'lng' => 44.486, 'capital' => 0, 'center' => 0],

            // Lori
            ['slug' => 'vanadzor', 'region' => 'lori', 'name_ru' => 'Ванадзор', 'name_am' => 'Վանաձոր', 'name_en' => 'Vanadzor', 'lat' => 40.8128, 'lng' => 44.4889, 'capital' => 0, 'center' => 1],
            ['slug' => 'alaverdi', 'region' => 'lori', 'name_ru' => 'Алаверди', 'name_am' => 'Ալավերդի', 'name_en' => 'Alaverdi', 'lat' => 41.097, 'lng' => 44.663, 'capital' => 0, 'center' => 0],
            ['slug' => 'spitak', 'region' => 'lori', 'name_ru' => 'Спитак', 'name_am' => 'Սպիտակ', 'name_en' => 'Spitak', 'lat' => 40.832, 'lng' => 44.267, 'capital' => 0, 'center' => 0],
            ['slug' => 'stepanavan', 'region' => 'lori', 'name_ru' => 'Степанаван', 'name_am' => 'Ստեփանավան', 'name_en' => 'Stepanavan', 'lat' => 41.009, 'lng' => 44.379, 'capital' => 0, 'center' => 0],
            ['slug' => 'tashir', 'region' => 'lori', 'name_ru' => 'Ташир', 'name_am' => 'Տաշիր', 'name_en' => 'Tashir', 'lat' => 41.121, 'lng' => 44.287, 'capital' => 0, 'center' => 0],
            ['slug' => 'akhtala', 'region' => 'lori', 'name_ru' => 'Ахтала', 'name_am' => 'Ախթալա', 'name_en' => 'Akhtala', 'lat' => 41.149, 'lng' => 44.750, 'capital' => 0, 'center' => 0],
            ['slug' => 'shamlugh', 'region' => 'lori', 'name_ru' => 'Шамлуг', 'name_am' => 'Շամլուղ', 'name_en' => 'Shamlugh', 'lat' => 41.173, 'lng' => 44.871, 'capital' => 0, 'center' => 0],

            // Shirak
            ['slug' => 'gyumri', 'region' => 'shirak', 'name_ru' => 'Гюмри', 'name_am' => 'Գյումրի', 'name_en' => 'Gyumri', 'lat' => 40.789, 'lng' => 43.847, 'capital' => 0, 'center' => 1],
            ['slug' => 'artik', 'region' => 'shirak', 'name_ru' => 'Артик', 'name_am' => 'Արթիկ', 'name_en' => 'Artik', 'lat' => 40.591, 'lng' => 43.980, 'capital' => 0, 'center' => 0],
            ['slug' => 'maralik', 'region' => 'shirak', 'name_ru' => 'Маралик', 'name_am' => 'Մարալիկ', 'name_en' => 'Maralik', 'lat' => 40.575, 'lng' => 43.869, 'capital' => 0, 'center' => 0],

            // Syunik
            ['slug' => 'kapan', 'region' => 'syunik', 'name_ru' => 'Капан', 'name_am' => 'Կապան', 'name_en' => 'Kapan', 'lat' => 39.207, 'lng' => 46.405, 'capital' => 0, 'center' => 1],
            ['slug' => 'goris', 'region' => 'syunik', 'name_ru' => 'Горис', 'name_am' => 'Գորիս', 'name_en' => 'Goris', 'lat' => 39.511, 'lng' => 46.338, 'capital' => 0, 'center' => 0],
            ['slug' => 'meghri', 'region' => 'syunik', 'name_ru' => 'Мегри', 'name_am' => 'Մեղրի', 'name_en' => 'Meghri', 'lat' => 38.906, 'lng' => 46.246, 'capital' => 0, 'center' => 0],
            ['slug' => 'agarak', 'region' => 'syunik', 'name_ru' => 'Агарак', 'name_am' => 'Ագարակ', 'name_en' => 'Agarak', 'lat' => 38.901, 'lng' => 46.545, 'capital' => 0, 'center' => 0],
            ['slug' => 'kajaran', 'region' => 'syunik', 'name_ru' => 'Каджаран', 'name_am' => 'Քաջարան', 'name_en' => 'Kajaran', 'lat' => 39.153, 'lng' => 46.146, 'capital' => 0, 'center' => 0],
            ['slug' => 'sisian', 'region' => 'syunik', 'name_ru' => 'Сисиан', 'name_am' => 'Սիսիան', 'name_en' => 'Sisian', 'lat' => 39.521, 'lng' => 46.019, 'capital' => 0, 'center' => 0],
            ['slug' => 'dastakert', 'region' => 'syunik', 'name_ru' => 'Дастакерт', 'name_am' => 'Դաստակերտ', 'name_en' => 'Dastakert', 'lat' => 39.383, 'lng' => 46.086, 'capital' => 0, 'center' => 0],

            // Tavush
            ['slug' => 'ijevan', 'region' => 'tavush', 'name_ru' => 'Иджеван', 'name_am' => 'Իջևան', 'name_en' => 'Ijevan', 'lat' => 40.879, 'lng' => 45.148, 'capital' => 0, 'center' => 1],
            ['slug' => 'dilijan', 'region' => 'tavush', 'name_ru' => 'Дилижан', 'name_am' => 'Դիլիջան', 'name_en' => 'Dilijan', 'lat' => 40.741, 'lng' => 44.863, 'capital' => 0, 'center' => 0],
            ['slug' => 'berd', 'region' => 'tavush', 'name_ru' => 'Берд', 'name_am' => 'Բերդ', 'name_en' => 'Berd', 'lat' => 40.882, 'lng' => 45.386, 'capital' => 0, 'center' => 0],
            ['slug' => 'noyemberyan', 'region' => 'tavush', 'name_ru' => 'Ноемберян', 'name_am' => 'Նոյեմբերյան', 'name_en' => 'Noyemberyan', 'lat' => 41.173, 'lng' => 44.998, 'capital' => 0, 'center' => 0],
            ['slug' => 'ayrum', 'region' => 'tavush', 'name_ru' => 'Айрум', 'name_am' => 'Այրում', 'name_en' => 'Ayrum', 'lat' => 41.017, 'lng' => 44.618, 'capital' => 0, 'center' => 0],

            // Vayots Dzor
            ['slug' => 'yeghegnadzor', 'region' => 'vayots-dzor', 'name_ru' => 'Ехегнадзор', 'name_am' => 'Եղեգնաձոր', 'name_en' => 'Yeghegnadzor', 'lat' => 39.763, 'lng' => 45.333, 'capital' => 0, 'center' => 1],
            ['slug' => 'jermuk', 'region' => 'vayots-dzor', 'name_ru' => 'Джермук', 'name_am' => 'Ջերմուկ', 'name_en' => 'Jermuk', 'lat' => 39.842, 'lng' => 45.672, 'capital' => 0, 'center' => 0],
            ['slug' => 'vayk', 'region' => 'vayots-dzor', 'name_ru' => 'Вайк', 'name_am' => 'Վայք', 'name_en' => 'Vayk', 'lat' => 39.688, 'lng' => 45.466, 'capital' => 0, 'center' => 0],
        ];

// upsert по slug
        $insCity = $pdo->prepare("
    INSERT OR IGNORE INTO cities
    (region_id, slug, name_ru, name_am, name_en, lat, lng, is_capital, is_region_center)
    VALUES (:region_id,:slug,:name_ru,:name_am,:name_en,:lat,:lng,:is_capital,:is_region_center)
");
        $updCity = $pdo->prepare("
    UPDATE cities SET
        region_id=:region_id, name_ru=:name_ru, name_am=:name_am, name_en=:name_en,
        lat=:lat, lng=:lng, is_capital=:is_capital, is_region_center=:is_region_center
    WHERE slug=:slug
");

        $pdo->beginTransaction();
        foreach ($cities as $c) {
            $regionId = $regionMap[$c['region']] ?? null;
            if (!$regionId) continue;
            $params = [
                ':region_id' => $regionId,
                ':slug' => $c['slug'],
                ':name_ru' => $c['name_ru'],
                ':name_am' => $c['name_am'],
                ':name_en' => $c['name_en'],
                ':lat' => $c['lat'],
                ':lng' => $c['lng'],
                ':is_capital' => $c['capital'],
                ':is_region_center' => $c['center'],
            ];
            $insCity->execute($params);
            $updCity->execute($params);
        }
        $pdo->commit();
    }

    protected function migrateFuelSchemaToCompanyPoints(): void
    {
        $pdo = App::db();

        // Дадим SQLite время подождать блокировки и выключим FK на время DDL
        $pdo->exec("PRAGMA busy_timeout = 30000");
        $pdo->exec("PRAGMA foreign_keys = OFF");

        // ==== УТИЛИТЫ ====
        $tableExists = function (string $name) use ($pdo): bool {
            for ($i = 0; $i < 5; $i++) {
                try {
                    $st = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=?");
                    $st->execute([$name]);
                    return (bool)$st->fetchColumn();
                } catch (\PDOException $e) {
                    usleep(100_000);
                    if ($i === 4) throw $e;
                }
            }
            return false;
        };
        $colNames = function (string $table) use ($pdo): array {
            $st = $pdo->query("PRAGMA table_info(" . $table . ")");
            $cols = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
            return array_map(fn($c) => strtolower($c['name']), $cols);
        };
        $fkList = function (string $table) use ($pdo): array {
            $st = $pdo->query("PRAGMA foreign_key_list(" . $table . ")");
            return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        };
        $begin = function () use ($pdo): array {
            if ($pdo->inTransaction()) {
                $name = 'mig_' . bin2hex(random_bytes(3));
                $pdo->exec("SAVEPOINT $name");
                return ['sp', $name];
            } else {
                $pdo->exec("BEGIN IMMEDIATE");
                return ['tx', null];
            }
        };
        $commit = function (array $ctx) use ($pdo): void {
            [$mode, $name] = $ctx;
            if ($mode === 'sp') $pdo->exec("RELEASE SAVEPOINT $name");
            else $pdo->exec("COMMIT");
        };
        $rollback = function (array $ctx) use ($pdo): void {
            [$mode, $name] = $ctx;
            if ($mode === 'sp') $pdo->exec("ROLLBACK TO SAVEPOINT $name");
            else $pdo->exec("ROLLBACK");
        };

        // ==== 1) company_points: создать, если нет ====
        if (!$tableExists('company_points')) {
            $ctx = $begin();
            try {
                $pdo->exec("
                CREATE TABLE company_points (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    company_id INTEGER NOT NULL,
                    city_id INTEGER NOT NULL,
                    address TEXT,
                    phones TEXT,
                    emails TEXT,
                    working_hours TEXT,
                    website TEXT,
                    socials TEXT,
                    latitude DECIMAL(10,8),
                    longitude DECIMAL(10,8),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (company_id) REFERENCES fuel_companies(id),
                    FOREIGN KEY (city_id) REFERENCES cities(id)
                )
            ");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_company_points_company ON company_points(company_id)");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_company_points_city    ON company_points(city_id)");
                $commit($ctx);
            } catch (\Throwable $e) {
                $rollback($ctx);
                $pdo->exec("PRAGMA foreign_keys = ON");
                throw $e;
            }
        }

        // ==== 2) Перенос «лишних» полей из fuel_companies в company_points (если companies ещё «старые») ====
        $companiesCols = $colNames('fuel_companies');
        $legacyFields = ['address', 'phones', 'emails', 'working_hours', 'website', 'socials', 'latitude', 'longitude'];
        $isLegacyCompanies = (bool)array_intersect($legacyFields, $companiesCols);

        $hasAnyPoints = false;
        if ($tableExists('company_points')) {
            $st = $pdo->query("SELECT 1 FROM company_points LIMIT 1");
            $hasAnyPoints = (bool)$st->fetchColumn();
        }

        if ($isLegacyCompanies && !$hasAnyPoints) {
            $ctx = $begin();
            try {
                $pdo->exec("
                INSERT INTO company_points (
                    company_id, city_id, address, phones, emails, working_hours, website, socials,
                    latitude, longitude, created_at, updated_at
                )
                SELECT
                    fc.id, 1, fc.address, fc.phones, fc.emails, fc.working_hours, fc.website, fc.socials,
                    fc.latitude, fc.longitude,
                    COALESCE(fc.created_at, CURRENT_TIMESTAMP),
                    COALESCE(fc.updated_at, CURRENT_TIMESTAMP)
                FROM fuel_companies fc
                WHERE NOT EXISTS (SELECT 1 FROM company_points cp WHERE cp.company_id = fc.id)
            ");
                $commit($ctx);
            } catch (\Throwable $e) {
                $rollback($ctx);
                $pdo->exec("PRAGMA foreign_keys = ON");
                throw $e;
            }
        }

        // ==== 3) Пересборка fuel_companies до минимальной схемы ====
        if ($isLegacyCompanies) {
            $oldName = 'fuel_companies__old';
            $needRename = !$tableExists($oldName);

            $ctx = $begin();
            try {
                if ($needRename) {
                    $pdo->exec("ALTER TABLE fuel_companies RENAME TO " . $oldName);
                }
                $pdo->exec("
                CREATE TABLE IF NOT EXISTS fuel_companies (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    slug TEXT NOT NULL UNIQUE,
                    name TEXT NOT NULL,
                    logo TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
                $pdo->exec("
                INSERT INTO fuel_companies (id, slug, name, logo, created_at, updated_at)
                SELECT o.id, o.slug, o.name, o.logo, o.created_at, o.updated_at
                  FROM {$oldName} o
                 WHERE NOT EXISTS (SELECT 1 FROM fuel_companies n WHERE n.id = o.id)
            ");
                $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_fuel_companies_slug ON fuel_companies(slug)");
                $commit($ctx);
            } catch (\Throwable $e) {
                $rollback($ctx);
                $pdo->exec("PRAGMA foreign_keys = ON");
                throw $e;
            }

            // Мягкий drop бэкапа, если получится
            try {
                $pdo->exec("DROP TABLE IF EXISTS {$oldName}");
            } catch (\PDOException $e) {
            }
        }

        // ==== 3.5) ПРОВЕРИТЬ и ПЕРЕСОБРАТЬ company_points, если FK указывает на fuel_companies__old ====
        if ($tableExists('company_points')) {
            $badFk = false;
            foreach ($fkList('company_points') as $fk) {
                if (strtolower($fk['table'] ?? '') === 'fuel_companies__old') {
                    $badFk = true;
                    break;
                }
            }

            if ($badFk) {
                // Пересобираем company_points с правильной FK на fuel_companies(id)
                $ctx = $begin();
                try {
                    // 1) переименуем старую таблицу
                    $pdo->exec("ALTER TABLE company_points RENAME TO company_points__old");

                    // 2) создадим новую таблицу с нужной FK
                    $pdo->exec("
                    CREATE TABLE company_points (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        company_id INTEGER NOT NULL,
                        city_id INTEGER NOT NULL,
                        address TEXT,
                        phones TEXT,
                        emails TEXT,
                        working_hours TEXT,
                        website TEXT,
                        socials TEXT,
                        latitude DECIMAL(10,8),
                        longitude DECIMAL(10,8),
                        moderation_status TEXT NOT NULL DEFAULT 'approved',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (company_id) REFERENCES fuel_companies(id),
                        FOREIGN KEY (city_id)    REFERENCES cities(id)
                    )
                ");

                    // 3) вычислим пересечение колонок и скопируем данные
                    $oldCols = $colNames('company_points__old');
                    $newCols = $colNames('company_points');

                    // обязательные, чтобы не зависеть от модификаций
                    $preferredOrder = [
                        'id', 'company_id', 'city_id', 'address', 'phones', 'emails', 'working_hours',
                        'website', 'socials', 'latitude', 'longitude', 'moderation_status', 'created_at', 'updated_at'
                    ];

                    $use = array_values(array_filter($preferredOrder, function ($c) use ($oldCols, $newCols) {
                        return in_array($c, $oldCols, true) && in_array($c, $newCols, true);
                    }));

                    $colsList = implode(',', $use);
                    $pdo->exec("
                    INSERT INTO company_points ($colsList)
                    SELECT $colsList FROM company_points__old
                ");

                    // 4) индексы
                    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_company_points_company ON company_points(company_id)");
                    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_company_points_city    ON company_points(city_id)");

                    // 5) удалить старую (оставим мягко закомментированным)
                    // $pdo->exec("DROP TABLE company_points__old");

                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    // Попробуем откатить обратно, чтобы не оставить систему в «полудо-режиме»
                    try {
                        $pdo->exec("DROP TABLE IF EXISTS company_points");
                    } catch (\Throwable $ee) {
                    }
                    try {
                        $pdo->exec("ALTER TABLE company_points__old RENAME TO company_points");
                    } catch (\Throwable $ee) {
                    }
                    $pdo->exec("PRAGMA foreign_keys = ON");
                    throw $e;
                }
            }
        }

        // ==== 4) fuel_data -> company_point_id (пересборка) ====
        if ($tableExists('fuel_data')) {
            $fuelDataCols = $colNames('fuel_data');
            $hasCompanyPointId = in_array('company_point_id', $fuelDataCols, true);
            $hasCompanyId = in_array('company_id', $fuelDataCols, true) || in_array('compani_id', $fuelDataCols, true);

            // 4.1 Добавляем новую колонку, если нет
            if (!$hasCompanyPointId) {
                $ctx = $begin();
                try {
                    $pdo->exec("ALTER TABLE fuel_data ADD COLUMN company_point_id INTEGER");
                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    $pdo->exec("PRAGMA foreign_keys = ON");
                    throw $e;
                }
            }

            // 4.2 Заполняем company_point_id по старому company_id/compani_id
            $companyIdCol = in_array('company_id', $fuelDataCols, true) ? 'company_id'
                : (in_array('compani_id', $fuelDataCols, true) ? 'compani_id' : null);

            if ($companyIdCol) {
                $ctx = $begin();
                try {
                    $pdo->exec("
                    UPDATE fuel_data
                       SET company_point_id = (
                           SELECT cp.id
                             FROM company_points cp
                            WHERE cp.company_id = fuel_data.$companyIdCol
                            ORDER BY cp.id
                            LIMIT 1
                       )
                     WHERE company_point_id IS NULL
                ");
                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    $pdo->exec("PRAGMA foreign_keys = ON");
                    throw $e;
                }
            }

            // ==== 5) Гарантируем наличие колонок moderation_status ====
            $addColIfMissing = function (string $table, string $col, string $def = 'approved') {
                $pdo = App::db();
                $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
                $names = array_map(fn($c) => strtolower($c['name']), $cols);
                if (!in_array(strtolower($col), $names, true)) {
                    $pdo->exec("ALTER TABLE $table ADD COLUMN $col TEXT NOT NULL DEFAULT '$def'");
                }
            };
            $addColIfMissing('fuel_companies', 'moderation_status');
            $addColIfMissing('company_points', 'moderation_status');
            $addColIfMissing('fuel_data', 'moderation_status');

            // Индексы по статусам модерации (на всякий)
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_fc_moderation ON fuel_companies(moderation_status)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_cp_moderation ON company_points(moderation_status)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_fd_moderation ON fuel_data(moderation_status)");

            // 4.3 Пересобираем fuel_data без старого столбца company_id/compani_id
            if ($hasCompanyId) {
                $oldFuel = 'fuel_data__old';
                $ctx = $begin();
                try {
                    if (!$tableExists($oldFuel)) {
                        $pdo->exec("ALTER TABLE fuel_data RENAME TO {$oldFuel}");
                    }
                    $pdo->exec("
                    CREATE TABLE IF NOT EXISTS fuel_data (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        company_point_id INTEGER NOT NULL,
                        fuel_type_id INTEGER NOT NULL,
                        price DECIMAL(10,2) NOT NULL,
                        moderation_status TEXT NOT NULL DEFAULT 'approved',
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (company_point_id) REFERENCES company_points(id) ON DELETE CASCADE,
                        FOREIGN KEY (fuel_type_id)     REFERENCES fuel_types(id)   ON DELETE CASCADE
                    )
                ");
                    $pdo->exec("
                    INSERT INTO fuel_data (id, company_point_id, fuel_type_id, price, moderation_status, updated_at)
                    SELECT o.id, o.company_point_id, o.fuel_type_id, o.price,
                           COALESCE(o.moderation_status, 'approved'),
                           o.updated_at
                      FROM {$oldFuel} o
                     WHERE o.company_point_id IS NOT NULL
                       AND NOT EXISTS (SELECT 1 FROM fuel_data n WHERE n.id = o.id)
                ");
                    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_fuel_data_point ON fuel_data(company_point_id)");
                    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_fuel_data_type  ON fuel_data(fuel_type_id)");
                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    $pdo->exec("PRAGMA foreign_keys = ON");
                    throw $e;
                }

                try {
                    $pdo->exec("DROP TABLE IF EXISTS {$oldFuel}");
                } catch (\PDOException $e) {
                }
            }
        }

        // ==== 6) Переименование name_eng -> name_en в regions и cities ====
        $renameNameEngToEn = function (string $table) use ($pdo, $colNames, $tableExists, $begin, $commit, $rollback) {
            if (!$tableExists($table)) return;

            $cols = $colNames($table);
            $hasOld = in_array('name_eng', $cols, true);
            $hasNew = in_array('name_en', $cols, true);

            if ($hasOld && !$hasNew) {
                // Пытаемся нативно переименовать столбец (SQLite >= 3.25)
                $ctx = $begin();
                try {
                    $pdo->exec("ALTER TABLE {$table} RENAME COLUMN name_eng TO name_en");
                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    // Фолбэк: добавим name_en, скопируем данные, попробуем удалить name_eng
                    $ctx2 = $begin();
                    try {
                        $pdo->exec("ALTER TABLE {$table} ADD COLUMN name_en TEXT");
                        $pdo->exec("UPDATE {$table} SET name_en = name_eng WHERE name_en IS NULL OR name_en = ''");
                        $commit($ctx2);
                    } catch (\Throwable $ee) {
                        $rollback($ctx2);
                        throw $ee;
                    }
                    // Попытка удалить старую колонку (SQLite >= 3.35)
                    try {
                        $pdo->exec("ALTER TABLE {$table} DROP COLUMN name_eng");
                    } catch (\Throwable $ignored) {
                    }
                }
            } elseif ($hasOld && $hasNew) {
                // Оба столбца есть — синхронизируем и пробуем удалить старый
                $ctx = $begin();
                try {
                    $pdo->exec("
                    UPDATE {$table}
                       SET name_en = CASE WHEN name_en IS NULL OR name_en = '' THEN name_eng ELSE name_en END
                ");
                    try {
                        $pdo->exec("ALTER TABLE {$table} DROP COLUMN name_eng");
                    } catch (\Throwable $ignored) {
                    }
                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    throw $e;
                }
            }
            // если есть только name_en — ничего не делаем
        };

        $renameNameEngToEn('regions');
        $renameNameEngToEn('cities');

        $renameNameHyToAm = function (string $table) use ($pdo, $colNames, $tableExists, $begin, $commit, $rollback) {
            if (!$tableExists($table)) return;

            $cols = $colNames($table);
            $hasOld = in_array('name_hy', $cols, true);
            $hasNew = in_array('name_am', $cols, true);

            if ($hasOld && !$hasNew) {
                // Пытаемся нативно переименовать столбец (SQLite >= 3.25)
                $ctx = $begin();
                try {
                    $pdo->exec("ALTER TABLE {$table} RENAME COLUMN name_hy TO name_am");
                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    // Фолбэк: добавим name_en, скопируем данные, попробуем удалить name_eng
                    $ctx2 = $begin();
                    try {
                        $pdo->exec("ALTER TABLE {$table} ADD COLUMN name_am TEXT");
                        $pdo->exec("UPDATE {$table} SET name_am = name_hy WHERE name_am IS NULL OR name_am = ''");
                        $commit($ctx2);
                    } catch (\Throwable $ee) {
                        $rollback($ctx2);
                        throw $ee;
                    }
                    // Попытка удалить старую колонку (SQLite >= 3.35)
                    try {
                        $pdo->exec("ALTER TABLE {$table} DROP COLUMN name_hy");
                    } catch (\Throwable $ignored) {
                    }
                }
            } elseif ($hasOld && $hasNew) {
                // Оба столбца есть — синхронизируем и пробуем удалить старый
                $ctx = $begin();
                try {
                    $pdo->exec("
                    UPDATE {$table}
                       SET name_en = CASE WHEN name_am IS NULL OR name_am = '' THEN name_hy ELSE name_am END
                ");
                    try {
                        $pdo->exec("ALTER TABLE {$table} DROP COLUMN name_hy");
                    } catch (\Throwable $ignored) {
                    }
                    $commit($ctx);
                } catch (\Throwable $e) {
                    $rollback($ctx);
                    throw $e;
                }
            }
            // если есть только name_en — ничего не делаем
        };

        $renameNameHyToAm('regions');
        $renameNameHyToAm('cities');

        // Вернём FK проверки
        $pdo->exec("PRAGMA foreign_keys = ON");
    }


}
