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
                case 'user':
                    header('Location: /user/');
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
                    case 'user':
                        header('Location: /user/');
                        break;
                    default:
                        header('Location: /');
                        break;
                }
                return true;
            }

            return ['site/login', ['error' => 'Неверный логин или пароль', 'settings' => $settings]];
        }

        return ['site/login', ['settings' => $settings]];
    }

    public static function hash($str): ?string
    {
        return hash('sha512', $str . '*@#^$&');
    }
}