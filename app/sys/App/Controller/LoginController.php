<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use App\App;
use PDO;

class LoginController extends Controller
{
    private static function normLogin(string $s): string
    {
        return strtolower(trim($s));
    }

    private static function makeUserToken(string $loginOriginal, string $passwordHash): string
    {
        return self::hash(self::normLogin($loginOriginal) . $passwordHash);
    }

    private static function makeAdminToken(string $adminLogin, string $adminPlainPassword): string
    {
        return self::hash(self::normLogin($adminLogin) . $adminPlainPassword);
    }

    private static function setAuthCookie(string $token): void
    {
        $params = [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        setcookie('app_token', $token, $params);
    }

    private static function clearAuthCookie(): void
    {
        $params = [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        setcookie('app_token', '', $params);
    }

    private static function redirectByRole(string $role): void
    {
        switch ($role) {
            case 'admin':
                header('Location: /admin/');
                break;
            case 'company':
                header('Location: /user/company');
                break;
            case 'user':
                header('Location: /user/account');
                break;
            default:
                header('Location: /');
                break;
        }
        exit;
    }

    private static function authFromCookie(?array $adminCred): ?array
    {
        if (empty($_COOKIE['app_token'])) {
            return null;
        }
        $token = $_COOKIE['app_token'];
        if ($adminCred) {
            $adminToken = self::makeAdminToken((string)$adminCred['login'], (string)$adminCred['password']);
            if (hash_equals($adminToken, $token)) {
                return ['id' => 0, 'login' => $adminCred['login'], 'role' => 'admin'];
            }
        }
        $st = App::db()->prepare("SELECT id, login, password, role, app_token FROM users WHERE app_token = ? LIMIT 1");
        $st->execute([$token]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $shouldBe = self::makeUserToken($user['login'], $user['password']); // password тут уже хэш
            if (hash_equals($shouldBe, $token)) {
                return $user;
            }
        }
        return null;
    }

    protected function actionLogout()
    {
        if (!empty($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            $st = App::db()->prepare("UPDATE users SET app_token = NULL WHERE app_token = ?");
            $st->execute([$token]);
        }
        self::clearAuthCookie();
        header('Location: /login');
        return true;
    }

    protected function actionLogin()
    {
        $q = App::db()->query("SELECT login, password FROM settings");
        $adminCred = $q->fetch();
        $q = App::db()->query("SELECT * FROM settings");
        $settings = $q->fetch();
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        if ($auth = self::authFromCookie($adminCred)) {
            self::redirectByRole($auth['role']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['auth/login', ['settings' => $settings]];
        }

        $loginRaw = (string)($_POST['login'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $login = self::normLogin($loginRaw);

        $adminLoginNorm = self::normLogin((string)$adminCred['login'] ?? '');
        if ($adminLoginNorm !== '' && $login === $adminLoginNorm && $password === (string)$adminCred['password']) {
            $token = self::makeAdminToken($adminCred['login'], $adminCred['password']);
            self::setAuthCookie($token);
            self::redirectByRole('admin');
        }

        $st = App::db()->prepare("SELECT id, login, password, role FROM users WHERE lower(login) = lower(?) LIMIT 1");
        $st->execute([$loginRaw]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if ($user && hash_equals(self::hash($password), $user['password'])) {
            $token = self::makeUserToken($user['login'], $user['password']);

            $upd = App::db()->prepare("UPDATE users SET app_token = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$token, $user['id']]);

            self::setAuthCookie($token);
            self::redirectByRole($user['role']);
        }

        return ['auth/login', ['error' => 'Неверный логин или пароль', 'settings' => $settings]];
    }

    protected function actionRegister()
    {
        $q = App::db()->query("SELECT * FROM settings");
        $settings = $q->fetch();
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['auth/register', ['settings' => $settings]];
        }

        $loginRaw = trim((string)($_POST['login'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $role = 'user';

        $errors = [];
        if ($loginRaw === '' || mb_strlen($loginRaw) < 3) {
            $errors[] = 'Логин должен быть не короче 3 символов.';
        }
        if (!preg_match('/^[a-zA-Z0-9._-]+$/u', $loginRaw)) {
            $errors[] = 'Разрешены только латинские буквы, цифры и символы . _ -';
        }
        if (mb_strlen($password) < 6) {
            $errors[] = 'Пароль должен быть не короче 6 символов.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Пароли не совпадают.';
        }

        $st = App::db()->prepare("SELECT id FROM users WHERE lower(login) = lower(?) LIMIT 1");
        $st->execute([$loginRaw]);
        if ($st->fetchColumn()) {
            $errors[] = 'Пользователь с таким логином уже существует.';
        }

        if ($errors) {
            return ['auth/register', [
                'settings' => $settings,
                'error' => implode('<br>', array_map('htmlspecialchars', $errors)),
                'old' => ['login' => $loginRaw],
            ]];
        }

        $passwordHash = self::hash($password);

        $ins = App::db()->prepare("
            INSERT INTO users (login, password, role, created_at, updated_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $ok = $ins->execute([$loginRaw, $passwordHash, $role]);
        if (!$ok) {
            return ['auth/register', [
                'settings' => $settings,
                'error' => 'Не удалось создать пользователя. Повторите попытку позже.',
                'old' => ['login' => $loginRaw],
            ]];
        }

        $uid = (int)App::db()->lastInsertId();
        $token = self::makeUserToken($loginRaw, $passwordHash);
        $upd = App::db()->prepare("UPDATE users SET app_token = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $upd->execute([$token, $uid]);

        self::setAuthCookie($token);
        header('Location: /user/account');
        return true;
    }

    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }
}
