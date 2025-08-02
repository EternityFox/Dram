<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use App\App;
use PDO;

class UserController extends Controller
{
    protected function actionCompanyDashboard()
    {
        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $md = false;
        if (isset($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            $stmt = App::db()->prepare("SELECT id, login, password, role, company_id FROM users WHERE app_token = ?");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $md = true;
                if ($user['role'] !== 'company') {
                    header('Location: /');
                    return false;
                }
            }
        }

        if (!$md) {
            header('Location: /login');
            return false;
        }

        $company = null;
        if ($user) {
            $stmt = App::db()->prepare("SELECT * FROM fuel_companies WHERE id = ?");
            $stmt->execute([$user['company_id']]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_company'])) {
            $companyId = $user['company_id'];
            $phones = json_encode($_POST['phones'] ?? [], JSON_UNESCAPED_UNICODE);
            $emails = json_encode($_POST['emails'] ?? [], JSON_UNESCAPED_UNICODE);
            $socials = json_encode($_POST['socials'] ?? [], JSON_UNESCAPED_UNICODE);

            // Преобразуем график работы в JSON
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
                move_uploaded_file($_FILES['logo']['tmp_name'],$imagePath);
            }

            // Обновляем адрес и координаты с карты
            $address = $_POST['address'] ?? $company['address'];
            $latitude = $_POST['latitude'] ?? $company['latitude'];
            $longitude = $_POST['longitude'] ?? $company['longitude'];

            $stmt = App::db()->prepare("UPDATE fuel_companies SET name = ?, address = ?, phones = ?, emails = ?, working_hours = ?, website = ?, socials = ?, latitude = ?, longitude = ?, logo = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $address, $phones, $emails, $workingHours, $_POST['website'], $socials, $latitude, $longitude, $logoPath, $companyId]);

            // Сохранение цен на топливо в таблицу fuel_data
            $stmt = App::db()->prepare("DELETE FROM fuel_data WHERE company_id = ?");
            $stmt->execute([$companyId]);
            if (!empty($_POST['fuel_type']) && !empty($_POST['fuel_price'])) {
                foreach ($_POST['fuel_type'] as $index => $fuelId) {
                    if (!empty($_POST['fuel_price'][$index])) {
                        $stmt = App::db()->prepare("INSERT INTO fuel_data (company_id, fuel_type_id, price) VALUES (?, ?, ?)");
                        $stmt->execute([$companyId, $fuelId, (float)$_POST['fuel_price'][$index]]);
                    }
                }
            }

            header('Location: /user/company');
            return true;
        }

        // Получение типов топлива, отсортированных по id
        $fuelTypes = App::db()->query("SELECT id, name FROM fuel_types ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Получение текущих цен
        $fuelData = [];
        if ($company) {
            $stmt = App::db()->prepare("SELECT fuel_type_id, price FROM fuel_data WHERE company_id = ?");
            $stmt->execute([$company['id']]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $fuelData[$row['fuel_type_id']] = $row['price'];
            }
        }

        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');
        $menu['top'] = include_once(__DIR__ . '/../../../storage/menu/top.php');
        $menuLeft = include_once(__DIR__ . '/../../../storage/menu/left.php');
        $menu['left']['hidden'] = $menuLeft['hidden'];
        unset($menuLeft['hidden']);
        $menu['left']['basic'] = $menuLeft;

        return ['user/company_dashboard', ['settings' => $settings, 'menu' => $menu, 'company' => $company, 'fuelTypes' => $fuelTypes, 'fuelData' => $fuelData]];
    }

    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }
}