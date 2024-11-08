<?php declare(strict_types=1);

namespace App\Model;

use Core\Model;

/**
 * @property float $id
 * @property int $type
 * @property int $eid
 * @property int $cid
 * @property float $buy
 * @property float $sell
 * @property float $ws_buy
 * @property float $ws_sell
 */
class Exrcourse extends Model
{

    /**
     * Наличный
     */
    const TYPE_CASH = 1;
    /**
     * Безналичный
     */
    const TYPE_NONCASH = 2;
    /**
     * По картам
     */
    const TYPE_CARD = 4;
    /**
     * Смешанный (нал/безнал)
     */
    const TYPE_CASH_NONCASH = 3;

    /**
     * @inheritDoc
     */
    static protected function struct(): array
    {
        return [
            'id' => self::FIELD_FLOAT,
            'type' => self::FIELD_INT,
            'eid' => self::FIELD_INT,
            'cid' => self::FIELD_INT,
            'buy' => self::FIELD_FLOAT,
            'sell' => self::FIELD_FLOAT,
            'ws_buy' => self::FIELD_FLOAT,
            'ws_sell' => self::FIELD_FLOAT
        ];
    }

    /**
     * @param int $eid
     * @param int $cid
     * @param int $tid
     *
     * @return float
     */
    static public function generateId(int $eid, int $cid, int $tid): float
    {
        return $eid * 1000 + $cid + ($tid / 10);
    }

    const SQL_BEST_BUY_BANK =
        <<<SQL
SELECT *, (SELECT name FROM exchanger WHERE id = exrcourse.eid) as name
    FROM exrcourse
    LEFT JOIN
        (SELECT cid, MAX(buy) as best
            FROM exrcourse
            WHERE cid IN(%s) AND type = 1
            GROUP BY cid
        ) as best
    WHERE exrcourse.cid = best.cid
        AND exrcourse.type = 1
        AND exrcourse.buy = best.best
    ORDER BY exrcourse.cid
SQL;

    const SQL_BEST_SELL_BANK =
        <<<SQL
SELECT *, (SELECT name FROM exchanger WHERE id = exrcourse.eid) as name
    FROM exrcourse
    LEFT JOIN
        (SELECT cid, MIN(sell) as best
            FROM exrcourse
            WHERE cid IN(%s) AND type = 1
            GROUP BY cid
        ) as best
    WHERE exrcourse.cid = best.cid
        AND exrcourse.type = 1
        AND exrcourse.sell = best.best
    ORDER BY exrcourse.cid
SQL;

}
