<?php declare(strict_types=1);

namespace App\Model;
use Core\Model;

/**
 * @property int $id
 * @property int $cid
 * @property float $price
 * @property int $date_at
 */
class Course extends Model
{

    /**
     * @inheritDoc
     */
    static protected function struct(): array
    {
        return [
            'id' => self::FIELD_INT,
            'cid' => self::FIELD_INT,
            'price' => self::FIELD_FLOAT,
            'date_at' => self::FIELD_INT
        ];
    }

}
