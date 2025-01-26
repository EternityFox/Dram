<?php declare(strict_types=1);

namespace App;

use Core\App as BaseApp,
    Core\Lang,
    Core\Viewer,
    App\Model\Currency,
    App\Model\Exchanger,
    Core\Utils\Hdbk,
    PDO;

/**
 * @method static \Core\Http\ServerRequest request()
 * @method static \Core\Http\Router router()
 * @method static \Core\Controller|null controller()
 * @method static \Core\Viewer viewer()
 * @method static \Core\Lang lang()
 * @method static \PDO db()
 * @method static \Core\Utils\Hdbk currency()
 * @method static \Core\Utils\Hdbk crypto()
 * @method static \Core\Utils\Hdbk exchanger()
 */
class App extends BaseApp
{

    /**
     * URL адрес сайта
     * @var string
     */
    static public string $url = '';
    /**
     * Название сайта
     * @var string
     */
    static public string $site = '';
    /**
     * Заголовок страниц по умолчанию
     * @var string
     */
    static public string $title = '';
    /**
     * Языки сайта
     * @var array<string, string>
     */
    static public array $languages = ['am'];
    /**
     * @var array<string, string>
     */
    static public array $currencySymbols = [];

    static public string $redirect;

    /**
     * @param string $method
     * @param array $args
     *
     * @return object
     * @throws \Exception
     */
    static function __callStatic(string $method, array $args): object
    {
        return static::get($method);
    }

    /**
     * @param array $routes
     */
    protected function setRoutes(array $routes)
    {
        static::$components['router'] = new Router($routes);
    }

    /**
     * @return \PDO
     */
    static protected function createDb(): PDO
    {
        $db = new PDO('sqlite:' . static::path('sqlite'));
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA encoding = "UTF-8"');
        $db->exec('PRAGMA journal_mode = OFF');
        $db->exec('PRAGMA synchronous = 0');
        $db->exec('PRAGMA foreign_keys = ON');

        return $db;
    }

    /**
     * @return \Core\Lang
     */
    static protected function createLang(): Lang
    {
        $request = static::request();
        $lang = explode('/', $request->getTarget())[1] ?? null;

//        if (self::config('home') !== $_SERVER['HTTP_REFERER']) {
//            self::$redirect = $_SERVER['HTTP_REFERER'];
//        }

        if (isset(static::$languages[$lang])) {
            if ($lang !== $request->getCookie('lang'))
                $request->setCookie('lang', $lang);
        } elseif (!($lang = $request->getCookie('lang'))
            || !isset(static::$languages[$lang])
        ) {
            $languages = ['am'] + acceptLangs($request->getHeader('accept-language'));
            foreach ($languages as $lng) {
                if (isset(static::$languages[$lng])) {
                    $lang = $lng;
                    break;
                }
            }

            if (empty($lang))
                $lang = array_key_first(static::$languages);
        }

        Lang::$defaultLang = 'am';
        Lang::$defaultDir = static::path('lang/am');

        return new Lang(
            static::path("lang/{$lang}"), $lang,
            ['main', 'exchanger' => 'exchanger']
        );
    }

    /**
     * @return \Core\Viewer
     */
    static protected function createViewer(): Viewer
    {
        $viewer = parent::createViewer();
        $viewer->set([
            'url' => static::$url,
            'site' => static::$site,
//            'title' => static::$title,
            'title' => unserialize(file_get_contents(__DIR__ . '/../../storage/site_title.txt')),
            'languages' => static::$languages,
            'lang' => static::lang()
        ]);

        return $viewer;
    }

    /**
     * @return \Core\Utils\Hdbk
     * @throws \Exception
     */
    static protected function createCurrency(): Hdbk
    {
        return static::createHdbk(
            Currency::class, 'symbol', '*',
            ['type' => 1], ['pos' => 'ASC', 'symbol' => 'ASC']
        );
    }

    /**
     * @return \Core\Utils\Hdbk
     * @throws \Exception
     */
    static protected function createCrypto(): Hdbk
    {
        return static::createHdbk(
            Currency::class, 'symbol', '*',
            ['type' => 2], ['pos' => 'ASC', 'symbol' => 'ASC'], 15
        );
    }

    /**
     * @return \Core\Utils\Hdbk
     * @throws \Exception
     */
    static protected function createOtherCurrency(): Hdbk
    {
        return static::createHdbk(
            Currency::class, 'symbol', '*',
            ['type' => 3], ['pos' => 'ASC', 'symbol' => 'ASC']
        );
    }

    /**
     * @return \Core\Utils\Hdbk
     * @throws \Exception
     */
    static protected function createMetall(): Hdbk
    {
        return static::createHdbk(
            Currency::class, 'symbol', '*',
            ['type' => 4], ['pos' => 'ASC', 'symbol' => 'ASC']
        );
    }

    /**
     * @return \Core\Utils\Hdbk
     * @throws \Exception
     */
    static protected function createExchanger(): Hdbk
    {
        return static::createHdbk(
            Exchanger::class, 'id', '*', null, ['is_bank' => 'DESC']
        );
    }

    /**
     * @param string $model
     * @param string $index
     * @param array|null $fields
     * @param string|array|null $filter
     * @param string|array|null $sort
     * @param string|array|null $limit
     *
     * @return \Core\Utils\Hdbk
     * @throws \Exception
     */
    static public function createHdbk(
        string $model,
        string $index,
               $fields = null,
               $filter = null,
               $sort = null,
               $limit = null
    ): Hdbk
    {
        /** @var \Core\Model $model */
        if (is_null($fields)) {
            $fields = '*';
            $type = 0;
        } elseif ('*' === $fields) {
            $type = 0;
        } elseif (!in_array($index, $fields)) {
            $type = 2;
            $fields[] = $index;
        } else {
            $type = 1;
        }

        $table = [];
        $query = $model::select($fields, $filter, $sort, $limit);
        while ($res = $query->fetch()) {
if($res['id'] == 121) continue;
            $key = $res[$index];

            if (0 === $type)
                $res = new $model($model::decodeFields($res), false);
            elseif (2 === $type)
                unset($res[$index]);

            $table[$key] = $res;
        }

        return new Hdbk($table, ($type ? null : 'fields'));
    }

}
