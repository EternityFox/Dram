<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use App\App;
use PDO;

class AccountController extends Controller
{
    private function requireUser(): ?array
    {
        if (empty($_COOKIE['app_token'])) {
            header('Location: /login');
            return null;
        }
        $token = $_COOKIE['app_token'];

        // 1) обычный пользователь
        $stmt = App::db()->prepare("SELECT * FROM users WHERE app_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // 2) админ из settings — в личный кабинет не пускаем, только на /admin
            $adminRow = App::db()->query("SELECT login, password FROM settings")->fetch();
            if ($adminRow && self::hash($adminRow['login'] . $adminRow['password']) === $token) {
                header('Location: /admin/');
                return null;
            }
            header('Location: /login');
            return null;
        }
        return $user;
    }

    protected function actionIndex()
    {
        $user = $this->requireUser();
        if (!$user) return false;

        $settings = App::db()->query("SELECT * FROM settings")->fetch();
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        // последние 10 заявок с ответами
        $stmt = App::db()->prepare("
            SELECT id, subject, status, created_at, answer, answered_at
              FROM user_requests
             WHERE user_id = ?
             ORDER BY id DESC
             LIMIT 10
        ");
        $stmt->execute([$user['id']]);
        $lastRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['user/account', [
            'settings' => $settings,
            'user' => $user,
            'lastRequests' => $lastRequests
        ]];
    }

    protected function actionChangePassword()
    {
        $user = $this->requireUser();
        if (!$user) return false;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/account');
            return true;
        }

        $old = (string)($_POST['old_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $rep = (string)($_POST['new_password_confirm'] ?? '');

        $errors = [];
        if ($new !== $rep) $errors[] = 'Пароли не совпадают.';
        if (mb_strlen($new) < 6) $errors[] = 'Новый пароль должен быть не короче 6 символов.';
        if (self::hash($old) !== $user['password']) $errors[] = 'Текущий пароль указан неверно.';

        if ($errors) {
            header('Location: /user/account?pwd_error=' . urlencode(implode(' ', $errors)));
            return true;
        }

        $newHash = self::hash($new);
        $upd = App::db()->prepare("UPDATE users SET password=?, app_token=? WHERE id=?");
        $newToken = self::hash($user['login'] . $new);
        $upd->execute([$newHash, $newToken, $user['id']]);

        setcookie('app_token', $newToken, time() + 60 * 60 * 24 * 30, '/');
        header('Location: /user/account?pwd_ok=1');
        return true;
    }

    protected function actionCreateRequest()
    {
        $user = $this->requireUser();
        if (!$user) return false;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/account');
            return true;
        }

        $subject = trim((string)($_POST['subject'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($subject === '' || $message === '') {
            header('Location: /user/account?req_error=Заполните тему и сообщение');
            return true;
        }

        $filePath = null;
        if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safe = preg_replace('~[^A-Za-z0-9_.-]~', '_', pathinfo($_FILES['file']['name'], PATHINFO_FILENAME));
            $name = $safe . '_' . time() . ($ext ? ('.' . $ext) : '');
            $dst = __DIR__ . '/../../../../storage/uploads/requests';
            if (!is_dir($dst)) @mkdir($dst, 0775, true);
            $full = $dst . '/' . $name;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $full)) {
                $filePath = 'storage/uploads/requests/' . $name;
            }
        }

        $ins = App::db()->prepare("INSERT INTO user_requests (user_id, subject, message, file_path) VALUES (?, ?, ?, ?)");
        $ins->execute([$user['id'], $subject, $message, $filePath]);

        header('Location: /user/account');
        return true;
    }

    protected function actionDeleteAccount()
    {
        $user = $this->requireUser();
        if (!$user) return false;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/account');
            return true;
        }

        $confirm = (string)($_POST['confirm_password'] ?? '');
        if (self::hash($confirm) !== $user['password']) {
            header('Location: /user/account?del_error=' . urlencode('Пароль не совпадает. Удаление отменено.'));
            return true;
        }

        $del = App::db()->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$user['id']]);

        setcookie('app_token', '', time() - 3600, '/');
        header('Location: /');
        return true;
    }

    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }
}
