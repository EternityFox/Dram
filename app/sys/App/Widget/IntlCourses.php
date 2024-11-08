<?php declare(strict_types=1);

namespace App\Widget;
use Core\Widget,
    App\App,
    Core\Utils\Hdbk,
    App\Model\Course,
    DateTime;

class IntlCourses extends Widget
{

    /**
     * @var array
     */
    public array $intlCourses = [];
    /**
     * @var mixed|null
     */
    private $settings;

    public function __construct($settings = null)
    {
        $cb = $this->cbCourses();
        $crypto = $this->cryptoCourses();
        $metall = $this->metallCourses();
        $this->settings = $settings;

        $pop = array_flip($this->config('topSymbols'));
        foreach ($pop as $key => $val) {
            if (isset($cb[$key]))
                $pop[$key] = $cb[$key];
            elseif (isset($crypto[$key]))
                $pop[$key] = $crypto[$key];
            elseif (isset($metall[$key]))
                $pop[$key] = $metall[$key];
            else
                unset($pop[$key]);
        }

        $this->intlCourses = [$pop, $cb, $crypto];
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $data = parent::getData();
        $data['settings'] = $this->settings;

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function cbCourses(): array
    {
        $data = [];
        $end = (new \DateTime('now 00:00:00'))->getTimestamp();
        $start = $end - 86400;
        $ids = implode(', ', App::currency()->column('id'));
        $ids .= ', 186, 187';

        $sql = 'SELECT *,'
               . ' (SELECT avg(price)'
               . ' FROM course c2'
               . ' WHERE cid = c1.cid'
               . " AND date_at >= {$start} AND date_at <= {$end}"
               . ') as sr'
               . ' FROM course c1'
               . " WHERE cid IN({$ids})"
               . ' GROUP BY cid'
               . ' HAVING date_at = MAX(date_at)';
        $query = App::db()->query($sql);

        while ($res = $query->fetch()) {
            $symbol = App::currency()->findOne(['id' => (int) $res['cid']]);
            if (!$symbol)
                $symbol = App::otherCurrency()->findOne(['id' => (int) $res['cid']]);

            $data[$symbol->symbol] = [
                'symbol' => $symbol,
                'price' => $res['price'],
                'diff' => ($res['sr'] && $res['sr'] !== $res['price'])
                    ? round(($res['price'] - $res['sr']), 4) : '0.00'
            ];
        }
        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function metallCourses(): array
    {
        $data = [];
        $end = (new \DateTime('now 00:00:00'))->getTimestamp();
        $start = $end - 86400;
        $ids = implode(', ', App::metall()->column('id'));

        $sql = 'SELECT *,'
               . ' (SELECT avg(price)'
               . ' FROM course c2'
               . ' WHERE cid = c1.cid'
               . " AND date_at >= {$start} AND date_at <= {$end}"
               . ') as sr'
               . ' FROM course c1'
               . " WHERE cid IN({$ids})"
               . ' GROUP BY cid'
               . ' HAVING date_at = MAX(date_at)';
        $query = App::db()->query($sql);

        while ($res = $query->fetch()) {
            $symbol = App::metall()->findOne(['id' => (int) $res['cid']]);

            $data[$symbol->symbol] = [
                'symbol' => $symbol,
                'price' => $res['price'],
                'diff' => ($res['sr'] && $res['sr'] !== $res['price'])
                    ? round(($res['price'] - $res['sr']), 4) : '0.00'
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function cryptoCourses(): array
    {
        $data = [];
        $end = (new \DateTime('now 00:00:00'))->getTimestamp();
        $start = $end - 86400;
        $ids = implode(', ', App::crypto()->column('id'));

        $sql = 'SELECT *,'
               . ' (SELECT avg(price)'
               . ' FROM course c2'
               . ' WHERE cid = c1.cid'
               . " AND date_at >= {$start} AND date_at <= {$end}"
               . ') as sr'
               . ' FROM course c1'
               . " WHERE cid IN({$ids})"
               . ' GROUP BY cid'
               . ' HAVING date_at = MAX(date_at)';
        $query = App::db()->query($sql);

        while ($res = $query->fetch()) {
            $symbol = App::crypto()->findOne(['id' => (int) $res['cid']]);
            $data[$symbol->symbol] = [
                'symbol' => $symbol,
                'price' => $res['price'],
                'diff' => ($res['sr'] && $res['sr'] !== $res['price'])
                    ? round(($res['price'] - $res['sr']), 4) : '0.00'
            ];
        }

        return $data;
    }

    /**
     * @param int $type
     * @param int|null $limit
     *
     * @return array
     */
    protected function getCourses(int $type, int $limit = null)
    {
        $today = (new DateTime('now 00:00:00'))->getTimestamp();
        $yestoday = $today - 86400;
        $ids = '';
        $sql = 'SELECT MIN(price) as min, MAX(price) as max, '
               . '(SELECT price FROM course as sub'
               . ' WHERE sub.cid = course.cid'
               . ' ORDER BY date_at DESC LIMIT 1) as price'
               . ' FROM course'
               . " WHERE cid IN({$ids})"
               . " AND date_at > {$yestoday} AND date_at < {$today}";
        if ($limit)
            $sql .= " LIMIT {$limit}";

        return [];
    }
}