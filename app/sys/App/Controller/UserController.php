<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use App\App;
use PDO;

class UserController extends Controller
{
    protected function actionCompanyDashboard($id = null)
    {
        $settings = App::db()->query("SELECT * FROM settings")->fetch();

        if (empty($_COOKIE['app_token'])) {
            header('Location: /login');
            return false;
        }
        $token = $_COOKIE['app_token'];

        $stmt = App::db()->prepare("SELECT id, login, password, role, company_id FROM users WHERE app_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $adminRow = App::db()->query("SELECT login, password FROM settings")->fetch();
        $isSettingsAdmin = $adminRow && self::hash($adminRow['login'] . $adminRow['password']) === $token;

        if (!$user && !$isSettingsAdmin) {
            header('Location: /login');
            return false;
        }

        $isAdmin = ($user && $user['role'] === 'admin') || $isSettingsAdmin;
        $isCompany = $user && $user['role'] === 'company';

        if ($isCompany) {
            $companyId = (int)$user['company_id'];
            if ($id !== null && (int)$id !== $companyId) {
                header('Location: /user/company');
                return false;
            }
        } elseif ($isAdmin) {
            if ($id === null) {
                header('Location: /admin/manage-companies');
                return false;
            }
            $companyId = (int)$id;
        } else {
            header('Location: /');
            return false;
        }

        // ===== Получаем компанию =====
        $stmt = App::db()->prepare("SELECT * FROM fuel_companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$company) {
            header('Location: /');
            return false;
        }

        // ===== Сохранение =====
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_company'])) {
            $phones = json_encode($_POST['phones'] ?? [], JSON_UNESCAPED_UNICODE);
            $emails = json_encode($_POST['emails'] ?? [], JSON_UNESCAPED_UNICODE);
            $socials = json_encode($_POST['socials'] ?? [], JSON_UNESCAPED_UNICODE);

            $workingHours = [];
            if (!empty($_POST['working_days']) && !empty($_POST['working_times'])) {
                foreach ($_POST['working_days'] as $index => $day) {
                    if (!empty($_POST['working_times'][$index])) {
                        $workingHours[$day] = $_POST['working_times'][$index];
                    }
                }
            }
            $workingHours = json_encode($workingHours, JSON_UNESCAPED_UNICODE);

            $logoPath = $company['logo'] ?? null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logoPath = uniqid() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $imagePath = __DIR__ . '/../../../../img/fuel/' . $logoPath;
                move_uploaded_file($_FILES['logo']['tmp_name'], $imagePath);
            }

            $address = $_POST['address'] ?? $company['address'];
            $latitude = $_POST['latitude'] ?? $company['latitude'];
            $longitude = $_POST['longitude'] ?? $company['longitude'];

            $stmt = App::db()->prepare("
            UPDATE fuel_companies
               SET name = ?, address = ?, phones = ?, emails = ?, working_hours = ?, website = ?, socials = ?, latitude = ?, longitude = ?, logo = ?
             WHERE id = ?
        ");
            $stmt->execute([$_POST['name'], $address, $phones, $emails, $workingHours, $_POST['website'], $socials, $latitude, $longitude, $logoPath, $companyId]);

            // Цены
            $stmt = App::db()->prepare("DELETE FROM fuel_data WHERE company_id = ?");
            $stmt->execute([$companyId]);
            if (!empty($_POST['fuel_type']) && !empty($_POST['fuel_price'])) {
                foreach ($_POST['fuel_type'] as $index => $fuelId) {
                    if ($_POST['fuel_price'][$index] !== '' && $_POST['fuel_price'][$index] !== null) {
                        $ins = App::db()->prepare("INSERT INTO fuel_data (company_id, fuel_type_id, price, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
                        $ins->execute([$companyId, (int)$fuelId, (float)$_POST['fuel_price'][$index]]);
                    }
                }
            }

            // После сохранения: компания — на /user/company; админ — на /user/company/{id}
            if ($isAdmin) {
                header('Location: /user/company/' . $companyId);
            } else {
                header('Location: /user/company');
            }
            return true;
        }

        // ===== Данные для формы =====
        $fuelTypes = App::db()->query("SELECT id, name FROM fuel_types ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $fuelData = [];
        $lastUpdate = null;
        $stmt = App::db()->prepare("SELECT fuel_type_id, price, updated_at FROM fuel_data WHERE company_id = ? ORDER BY updated_at DESC LIMIT 1");
        $stmt->execute([$companyId]);
        $lastUpdateRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($lastUpdateRow) $lastUpdate = $lastUpdateRow['updated_at'];

        $stmt = App::db()->prepare("SELECT fuel_type_id, price FROM fuel_data WHERE company_id = ?");
        $stmt->execute([$companyId]);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $fuelData[$row['fuel_type_id']] = $row['price'];
        }

        $bestPrices = [];
        $stmt = App::db()->query("
        SELECT ft.id AS fuel_type_id, MIN(fd.price) AS min_price
          FROM fuel_types ft
          LEFT JOIN fuel_data fd ON ft.id = fd.fuel_type_id
         GROUP BY ft.id
    ");
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $bestPrices[$row['fuel_type_id']] = $row['min_price'] ?: 'N/A';
        }

        // Меню
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        return ['user/company_dashboard', [
            'settings' => $settings,
            'menu' => $menu,
            'company' => $company,
            'fuelTypes' => $fuelTypes,
            'fuelData' => $fuelData,
            'bestPrices' => $bestPrices,
            'lastUpdate' => $lastUpdate,
            'id' => $id
        ]];
    }


    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }
}