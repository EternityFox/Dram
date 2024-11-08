<?php declare(strict_types=1);

namespace App\Widget;
use Core\Widget,
    App\App,
    App\Model\Exrcourse;

class BestExchangers extends Widget
{

    /**
     * @var array
     */
    public array $bestCourses = [];

    /**
     * BestCourses constructor.
     */
    public function __construct()
    {
        $this->bestCourses = $this->createCourses();
    }

    /**
     * @return array
     */
    public function createCourses()
    {
        $ids = array_column(
            App::currency()->get($this->config('symbols')), 'id'
        );
        $ids = implode(',', $ids);
        $best = [];

        $query = App::db()->query(sprintf(Exrcourse::SQL_BEST_BUY_BANK, $ids));
        while ($res = $query->fetch()) {
            $symbol = App::currency()
                         ->findOne(['id' => (int) $res['cid']])['symbol'];
            $best[$symbol]['buy']['price'] = (float) $res['buy'];
            $best[$symbol]['buy'][] = $res['name'];
        }
        $query = App::db()->query(sprintf(Exrcourse::SQL_BEST_SELL_BANK, $ids));
        while ($res = $query->fetch()) {
            $symbol = App::currency()
                              ->findOne(['id' => (int) $res['cid']])['symbol'];
            $best[$symbol]['sell']['price'] = (float) $res['sell'];
            $best[$symbol]['sell'][] = $res['name'];
        }

        return $best;
    }

}
