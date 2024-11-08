<?php declare(strict_types=1);

namespace App\Model;
use Core\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $branches
 * @property bool $is_bank
 * @property int $upd_cash
 * @property int $upd_nocash
 * @property int $upd_card
 * @property string $raid
 */
class Exchanger extends Model
{

    /**
     * @inheritDoc
     */
    static protected function struct(): array
    {
        return [
            'id' => self::FIELD_INT,
            'name' => self::FIELD_STRING,
            'branches' => self::FIELD_INT,
            'is_bank' => self::FIELD_BOOL,
            'upd_cash' => self::FIELD_INT,
            'upd_noncash' => self::FIELD_INT,
            'upd_card' => self::FIELD_INT,
            'raid' => self::FIELD_STRING
        ];
    }

    public function getLogo(): string
    {
        return $this->is_bank
            ? ($this->raid ?: $this->id) . '.svg'
            : './../32x32.png';
    }

}
