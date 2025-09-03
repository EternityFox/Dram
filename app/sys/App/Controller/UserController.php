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

        // --- auth ---
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

        // id в роуте — это company_id
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

        $pdo = App::db();

        // helpers
        $hasColumn = function (string $table, string $col) use ($pdo): bool {
            $st = $pdo->query("PRAGMA table_info(" . $table . ")");
            if (!$st) return false;
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $c) {
                if (strtolower($c['name']) === strtolower($col)) return true;
            }
            return false;
        };
        $idExists = function (string $table, int $id) use ($pdo): bool {
            $st = $pdo->prepare("SELECT 1 FROM {$table} WHERE id = ? LIMIT 1");
            $st->execute([$id]);
            return (bool)$st->fetchColumn();
        };

        $fcHasMod = $hasColumn('fuel_companies', 'moderation_status');
        $cpHasMod = $hasColumn('company_points', 'moderation_status');
        $fdHasMod = $hasColumn('fuel_data', 'moderation_status');

        // утверждения pending -> approved
        $approveCompanyIfPending = function (int $companyId) use ($pdo, $fcHasMod) {
            if ($fcHasMod) {
                $pdo->prepare("UPDATE fuel_companies SET moderation_status='approved' WHERE id=? AND moderation_status='pending'")
                    ->execute([$companyId]);
            }
        };
        $approveAllPointsIfPending = function (int $companyId) use ($pdo, $cpHasMod) {
            if ($cpHasMod) {
                $pdo->prepare("UPDATE company_points SET moderation_status='approved' WHERE company_id=? AND moderation_status='pending'")
                    ->execute([$companyId]);
            }
        };
        $approvePointIfPending = function (int $pointId) use ($pdo, $cpHasMod) {
            if ($cpHasMod) {
                $pdo->prepare("UPDATE company_points SET moderation_status='approved' WHERE id=? AND moderation_status='pending'")
                    ->execute([$pointId]);
            }
        };
        $approveFuelOfCompanyIfPending = function (int $companyId) use ($pdo, $fdHasMod) {
            if ($fdHasMod) {
                $pdo->prepare("
                UPDATE fuel_data
                   SET moderation_status='approved'
                 WHERE moderation_status='pending'
                   AND company_point_id IN (SELECT id FROM company_points WHERE company_id=?)
            ")->execute([$companyId]);
            }
        };
        $approveFuelOfPointIfPending = function (int $pointId) use ($pdo, $fdHasMod) {
            if ($fdHasMod) {
                $pdo->prepare("UPDATE fuel_data SET moderation_status='approved' WHERE company_point_id=? AND moderation_status='pending'")
                    ->execute([$pointId]);
            }
        };

        // --- company ---
        $stmt = $pdo->prepare("SELECT id, name, logo, created_at, updated_at FROM fuel_companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$company) {
            header('Location: /');
            return false;
        }

        // --- справочники ---
        $regions = $pdo->query("SELECT id, name_ru FROM regions ORDER BY name_ru")->fetchAll(PDO::FETCH_ASSOC);
        $cities = $pdo->query("SELECT id, region_id, name_ru FROM cities ORDER BY name_ru")->fetchAll(PDO::FETCH_ASSOC);

        // --- точки компании ---
        $stPoints = $pdo->prepare("
        SELECT cp.*, c.name_ru AS city_name
          FROM company_points cp
          LEFT JOIN cities c ON c.id = cp.city_id
         WHERE cp.company_id = ?
      ORDER BY cp.id DESC
    ");
        $stPoints->execute([$companyId]);
        $points = $stPoints->fetchAll(PDO::FETCH_ASSOC);

        // выбранная точка
        $selectedPointId = null;
        if (array_key_exists('point_id', $_GET) && $_GET['point_id'] !== '') {
            $selectedPointId = (int)$_GET['point_id'];
        } elseif (!empty($points)) {
            $selectedPointId = (int)$points[0]['id'];
        }

        $point = null;
        if ($selectedPointId) {
            $s = $pdo->prepare("SELECT * FROM company_points WHERE id = ? AND company_id = ?");
            $s->execute([$selectedPointId, $companyId]);
            $point = $s->fetch(PDO::FETCH_ASSOC);
            if (!$point) $selectedPointId = null;
        }

        // --- POST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // 0) Общие данные компании
            if (isset($_POST['save_company_common'])) {
                $newName = trim((string)($_POST['name'] ?? $company['name']));
                $logoPath = $company['logo'] ?? null;

                if (isset($_FILES['logo']) && is_array($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $ext = $ext ? ('.' . strtolower($ext)) : '';
                    $logoPath = uniqid('logo_', true) . $ext;
                    @move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../../../../img/fuel/' . $logoPath);
                }

                $u = $pdo->prepare("
                UPDATE fuel_companies
                   SET name = ?, logo = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?
            ");
                $u->execute([$newName, $logoPath, $companyId]);

                // аппрувы после успешного апдейта
                $approveCompanyIfPending($companyId);
                $approveAllPointsIfPending($companyId);
                $approveFuelOfCompanyIfPending($companyId);

                $redir = $isAdmin ? "/user/company/{$companyId}" : "/user/company";
                header("Location: {$redir}?saved=1" . ($selectedPointId ? "&point_id={$selectedPointId}" : ''));
                return true;
            }

            // 1) Создание новой точки
            if (isset($_POST['create_point'])) {
                $cityId = (int)($_POST['city_id'] ?? 0);
                if (!$idExists('cities', $cityId)) {
                    $cityId = (int)($pdo->query("SELECT id FROM cities ORDER BY id LIMIT 1")->fetchColumn() ?: 1);
                }

                $address = trim((string)($_POST['address'] ?? ''));
                $phones = json_encode($_POST['phones'] ?? [], JSON_UNESCAPED_UNICODE);
                $emails = json_encode($_POST['emails'] ?? [], JSON_UNESCAPED_UNICODE);
                $socials = json_encode($_POST['socials'] ?? [], JSON_UNESCAPED_UNICODE);

                $wh = [];
                if (!empty($_POST['working_days']) && !empty($_POST['working_times'])) {
                    foreach ($_POST['working_days'] as $i => $day) {
                        $t = $_POST['working_times'][$i] ?? '';
                        if ($t !== '') $wh[$day] = $t;
                    }
                }
                $workingHours = json_encode($wh, JSON_UNESCAPED_UNICODE);

                $lat = ($_POST['latitude'] ?? '') !== '' ? (float)$_POST['latitude'] : null;
                $lng = ($_POST['longitude'] ?? '') !== '' ? (float)$_POST['longitude'] : null;

                $pdo->beginTransaction();
                try {
                    $ins = $pdo->prepare("
                    INSERT INTO company_points
                        (company_id, city_id, address, phones, emails, working_hours, website, socials, latitude, longitude, created_at, updated_at)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ");
                    $ins->execute([
                        $companyId, $cityId, $address, $phones, $emails, $workingHours,
                        $_POST['website'] ?? '', $socials, $lat, $lng
                    ]);

                    $newPointId = (int)$pdo->lastInsertId();

                    // аппрувы после успешной вставки
                    $approveCompanyIfPending($companyId);
                    $approvePointIfPending($newPointId);

                    $pdo->commit();

                    $redir = $isAdmin ? "/user/company/{$companyId}" : "/user/company";
                    header("Location: {$redir}?created=1&point_id={$newPointId}");
                    return true;
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    throw $e;
                }
            }

            // 2) Сохранение выбранной точки и её цен
            if (isset($_POST['save_point'])) {
                $cpid = (int)($_POST['company_point_id'] ?? 0);

                // проверка принадлежности
                $chk = $pdo->prepare("SELECT * FROM company_points WHERE id = ? AND company_id = ?");
                $chk->execute([$cpid, $companyId]);
                $cp = $chk->fetch(PDO::FETCH_ASSOC);
                if (!$cp) {
                    header('Location: /user/company');
                    return false;
                }

                // поля точки
                $cityId = array_key_exists('company_city_id', $_POST) ? (int)$_POST['company_city_id'] : (int)$cp['city_id'];
                if (!$idExists('cities', $cityId)) {
                    $cityId = (int)$cp['city_id'];
                }
                $address = array_key_exists('company_address', $_POST) ? trim((string)$_POST['company_address']) : (string)($cp['address'] ?? '');
                $lat = array_key_exists('company_latitude', $_POST) && $_POST['company_latitude'] !== '' ? (float)$_POST['company_latitude'] : (isset($cp['latitude']) ? (float)$cp['latitude'] : null);
                $lng = array_key_exists('company_longitude', $_POST) && $_POST['company_longitude'] !== '' ? (float)$_POST['company_longitude'] : (isset($cp['longitude']) ? (float)$cp['longitude'] : null);

                $phones = json_encode($_POST['phones'] ?? (isset($cp['phones']) ? json_decode((string)$cp['phones'], true) : []), JSON_UNESCAPED_UNICODE);
                $emails = json_encode($_POST['emails'] ?? (isset($cp['emails']) ? json_decode((string)$cp['emails'], true) : []), JSON_UNESCAPED_UNICODE);
                $socials = json_encode($_POST['socials'] ?? (isset($cp['socials']) ? json_decode((string)$cp['socials'], true) : []), JSON_UNESCAPED_UNICODE);

                if (!empty($_POST['working_days']) && !empty($_POST['working_times'])) {
                    $wh = [];
                    foreach ($_POST['working_days'] as $i => $day) {
                        $t = $_POST['working_times'][$i] ?? '';
                        if ($t !== '') $wh[$day] = $t;
                    }
                } else {
                    $wh = isset($cp['working_hours']) ? (json_decode((string)$cp['working_hours'], true) ?: []) : [];
                }
                $workingHours = json_encode($wh, JSON_UNESCAPED_UNICODE);

                $pdo->beginTransaction();
                try {
                    // апдейт точки
                    $upd = $pdo->prepare("
                    UPDATE company_points
                       SET city_id = ?, address = ?, phones = ?, emails = ?, working_hours = ?,
                           website = ?, socials = ?, latitude = ?, longitude = ?, updated_at = CURRENT_TIMESTAMP
                     WHERE id = ? AND company_id = ?
                ");
                    $upd->execute([
                        $cityId, $address, $phones, $emails, $workingHours,
                        $_POST['website'] ?? ($cp['website'] ?? ''), $socials, $lat, $lng, $cpid, $companyId
                    ]);

                    // цены
                    $pdo->prepare("DELETE FROM fuel_data WHERE company_point_id = ?")->execute([$cpid]);

                    if (!empty($_POST['fuel_type']) && !empty($_POST['fuel_price'])) {
                        if ($fdHasMod) {
                            $ins = $pdo->prepare("
                            INSERT INTO fuel_data (company_point_id, fuel_type_id, price, moderation_status, updated_at)
                            VALUES (?, ?, ?, 'approved', CURRENT_TIMESTAMP)
                        ");
                        } else {
                            $ins = $pdo->prepare("
                            INSERT INTO fuel_data (company_point_id, fuel_type_id, price, updated_at)
                            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                        ");
                        }
                        foreach ($_POST['fuel_type'] as $i => $fuelId) {
                            $price = $_POST['fuel_price'][$i] ?? '';
                            if ($price === '' || $price === null) continue;
                            $ins->execute([$cpid, (int)$fuelId, (float)$price]);
                        }
                    }

                    // аппрувы после успешных операций
                    $approveCompanyIfPending($companyId);
                    $approvePointIfPending($cpid);
                    $approveFuelOfPointIfPending($cpid);

                    $pdo->commit();

                    $redir = $isAdmin ? "/user/company/{$companyId}" : "/user/company";
                    header("Location: {$redir}?saved=1&point_id={$cpid}");
                    return true;
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    throw $e;
                }
            }

            // 3) Удаление точки
            if (isset($_POST['delete_point'])) {
                $cpid = (int)($_POST['company_point_id'] ?? 0);
                $chk = $pdo->prepare("SELECT id FROM company_points WHERE id = ? AND company_id = ?");
                $chk->execute([$cpid, $companyId]);
                if ($chk->fetch()) {
                    $pdo->prepare("DELETE FROM fuel_data WHERE company_point_id = ?")->execute([$cpid]);
                    $pdo->prepare("DELETE FROM company_points WHERE id = ?")->execute([$cpid]);
                }
                $redir = $isAdmin ? "/user/company/{$companyId}" : "/user/company";
                header("Location: {$redir}?deleted=1");
                return true;
            }
        }

        // --- топливо и лучшие цены ---
        $fuelTypes = $pdo->query("SELECT id, name FROM fuel_types ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $bestPrices = [];
        $bx = $pdo->query("
        SELECT ft.id AS fuel_type_id, MIN(fd.price) AS min_price
          FROM fuel_types ft
          LEFT JOIN fuel_data fd ON ft.id = fd.fuel_type_id
      GROUP BY ft.id
    ");
        while ($r = $bx->fetch(PDO::FETCH_ASSOC)) {
            $bestPrices[$r['fuel_type_id']] = $r['min_price'] ?: 'N/A';
        }

        $fuelData = [];
        $lastUpdate = null;
        if ($selectedPointId) {
            $s = $pdo->prepare("SELECT fuel_type_id, price FROM fuel_data WHERE company_point_id = ?");
            $s->execute([$selectedPointId]);
            while ($r = $s->fetch(PDO::FETCH_ASSOC)) $fuelData[$r['fuel_type_id']] = $r['price'];

            $s = $pdo->prepare("SELECT updated_at FROM fuel_data WHERE company_point_id = ? ORDER BY updated_at DESC LIMIT 1");
            $s->execute([$selectedPointId]);
            $lastUpdate = $s->fetchColumn() ?: null;
        }

        // --- меню ---
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
            'regions' => $regions,
            'cities' => $cities,
            'points' => $points,
            'selectedPointId' => $selectedPointId,
            'point' => $point,
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
