<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use App\App;
use PDO;

class LoginController extends Controller
{
    protected function actionLogout()
    {
        if (isset($_COOKIE['app_token'])) {
            setcookie('app_token', '', time() - 3600);
        }
        header('Location: /login');
        return true;
    }

    protected function actionLogin()
    {
        $md = false;
        $query = App::db()->query("SELECT login, password FROM settings");
        $adminCred = $query->fetch();
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        if (isset($_COOKIE['app_token'])) {
            $token = $_COOKIE['app_token'];
            $stmt = App::db()->prepare("SELECT id, login, password, role FROM users WHERE app_token = ?");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && self::hash($user['login'] . $user['password']) == $token) {
                $md = true;
            }
            if (self::hash($adminCred['login'] . $adminCred['password']) == $token) {
                $md = true;
            }
        }

        if ($md) {
            switch ($user['role']) {
                case 'admin':
                    header('Location: /admin/');
                    break;
                case 'company':
                    header('Location: /user/company/');
                    break;
                default:
                    header('Location: /');
                    break;
            }
            return true;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $login = $_POST['login'];
            $password = $_POST['password'];
            if ($adminCred['login'] == $login && $adminCred['password'] == $password) {
                setcookie('app_token', self::hash($adminCred['login'] . $adminCred['password']), time() + 60 * 60 * 24 * 30);
                header('Location: /admin/');
                return true;
            }

            $stmt = App::db()->prepare("SELECT id, login, password, role FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && self::hash($password) === $user['password']) {
                setcookie('app_token', self::hash($user['login'] . $password), time() + 60 * 60 * 24 * 30);

                switch ($user['role']) {
                    case 'admin':
                        header('Location: /admin/');
                        break;
                    case 'company':
                        header('Location: /user/company');
                        break;
                    default:
                        header('Location: /');
                        break;
                }
                return true;
            }

            return ['auth/login', ['error' => 'Неверный логин или пароль', 'settings' => $settings]];
        }

        return ['auth/login', ['settings' => $settings]];
    }

    protected function actionRegister()
    {
        $query = App::db()->query("SELECT * FROM settings");
        $settings = $query->fetch();
        $settings['menu']['top'] = file_get_contents(__DIR__ . '/../../../storage/menu/top.php');
        $settings['menu']['left'] = file_get_contents(__DIR__ . '/../../../storage/menu/left.php');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['auth/register', ['settings' => $settings]];
        }

        $login = trim((string)($_POST['login'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $password_confirm = (string)($_POST['password_confirm'] ?? '');

        $role = 'user';

        // Простая валидация
        $errors = [];

        if ($login === '' || mb_strlen($login) < 3) {
            $errors[] = 'Логин должен быть не короче 3 символов.';
        }
        // Разрешим буквы/цифры/._-
        if (!preg_match('/^[a-zA-Z0-9._-]+$/u', $login)) {
            $errors[] = 'Разрешены только латинские буквы, цифры и символы . _ -';
        }
        if (mb_strlen($password) < 6) {
            $errors[] = 'Пароль должен быть не короче 6 символов.';
        }
        if ($password !== $password_confirm) {
            $errors[] = 'Пароли не совпадают.';
        }

        // Проверка уникальности логина
        $stmt = App::db()->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetchColumn()) {
            $errors[] = 'Пользователь с таким логином уже существует.';
        }

        if (!empty($errors)) {
            return ['auth/register', [
                'settings' => $settings,
                'error' => implode('<br>', array_map('htmlspecialchars', $errors)),
                'old' => ['login' => $login]
            ]];
        }

        // Хеш пароля: совместим с вашей авторизацией
        $passwordHash = self::hash($password);

        // Вставка пользователя
        $ins = App::db()->prepare("
            INSERT INTO users (login, password, role, app_token, created_at)
            VALUES (?, ?, ?, NULL, datetime('now'))
        ");
        $ok = $ins->execute([$login, $passwordHash, $role]);

        if (!$ok) {
            return ['auth/register', [
                'settings' => $settings,
                'error' => 'Не удалось создать пользователя. Повторите попытку позже.',
                'old' => ['login' => $login]
            ]];
        }

        $token = self::hash($login . $password);
        $upd = App::db()->prepare("UPDATE users SET app_token = ? WHERE login = ?");
        $upd->execute([$token, $login]);

        setcookie('app_token', $token, time() + 60 * 60 * 24 * 30, '/');
        header('Location: /');
        return true;
    }

    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }
}