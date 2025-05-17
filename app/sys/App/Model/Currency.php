<?php declare(strict_types=1);

namespace App\Model;
use Core\Model;

/**
 * @property int $id
 * @property string $symbol
 * @property int $type
 * @property string $name
 * @property string $img
 * @property int $pos
 */
class Currency extends Model
{

    /**
     * Национальная валюта
     */
    const TYPE_NATIONAL = 1;
    /**
     * Криптовалюта
     */
    const TYPE_CRYPTO = 2;

    /**
     * @inheritDoc
     */
    static protected function struct(): array
    {
        return [
            'id' => self::FIELD_INT,
            'symbol' => self::FIELD_STRING,
            'type' => self::FIELD_INT,
            'name' => self::FIELD_STRING,
            'img' => self::FIELD_STRING,
            'pos' => self::FIELD_INT
        ];
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->img
            ?: ($this->symbol . (2 === $this->type ? '.svg' : '.png'));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->fields['symbol'];
    }

}
