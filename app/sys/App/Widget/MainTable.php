<?php declare(strict_types=1);

namespace App\Widget;
use Core\Widget,
    App\App,
    App\Model\Exchanger,
    App\Model\Exrcourse;

class MainTable extends Widget
{

    /**
     * @var array
     */
    static protected array $courseTypes = ['direct', 'cross'];
    /**
     * @var array
     */
    static protected array $exchangeTypes = [
        'cash' => Exrcourse::TYPE_CASH,
        'noncash' => Exrcourse::TYPE_NONCASH,
        'card' => Exrcourse::TYPE_CARD
    ];

    /**
     * @var string
     */
    public string $courseType;
    /**
     * @var string
     */
    public string $exchangeType;
    /**
     * @var int
     */
    public int $typeId;
    /**
     * @var array
     */
    public array $activeSymbols = [];
    /**
     * @var array
     */
    protected array $crossSymbols = [];
    /**
     * @var array|null
     */
    protected ?array $table = null;

    protected string $refreshTime;

    /**
     * @param string|null $course
     * @param string|null $type
     */
    public function __construct(
        ?string $course = null, ?string $type = null
    )
    {
        if (!$course || !in_array($course, static::$courseTypes))
            $course = 'direct';
        if (!isset(static::$exchangeTypes[$type]))
            $type = 'cash';

        $this->courseType = $course;
        /** @var string $type */
        $this->exchangeType = $type;
        $this->typeId = static::$exchangeTypes[$type];
        $this->activeSymbols = $this->createSymbols();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function createDirectTable(): array
    {
        $symbols = App::currency()
                           ->new(['symbol' => ['in', $this->activeSymbols]])
                           ->column('symbol', 'id');
        $upd_field = "upd_{$this->exchangeType}";

        $actualTime = (time() - $this->config('actualSec'));
        $removeTime = (time() - $this->config('removeSec'));
        $months = App::lang()->params('month') ?? [];
        $table = [0 => [], 1 => []];
        $tableRefs = [];

        $exchangers = App::createHdbk(
            Exchanger::class, 'id', '*', null, ['upd_cash' => 'DESC']
        );

        $maxRefreshTime = 0;
        $dates = [];

        foreach ($exchangers as $exch) {
            if ($exch->is_bank)
                $to = 0;
            elseif ($exch->upd_cash < $removeTime)
                continue;
            elseif ($exch->upd_cash < $actualTime)
                $to = 2;
            else
                $to = 1;

            $date = ($exch[$upd_field]
                ? strtr(date('d M H:i', $exch[$upd_field]), $months) : '');

            $table[$to][] = [
                'id' => $exch['id'],
                'name' => $exch->name,
                'branches' => $exch->branches,
                'logo' => $exch->getLogo(),
                'date' => $date,
                'raw_date' => $exch[$upd_field],
            ];

            $dates[$exch[$upd_field]] = $date;
            if ($exch[$upd_field] > $maxRefreshTime) {
                $maxRefreshTime = $exch[$upd_field];
                $this->refreshTime = $date;
            }

            $tableRefs[$exch['id']] = &$table[$to][array_key_last($table[$to])];
        }

        $query = Exrcourse::select(
            '*',
            'cid IN(' . implode(', ', array_keys($symbols)) . ')'
            . ' AND type & ' . static::$exchangeTypes[$this->exchangeType],
        );
        while ($res = $query->fetch()) {
            $symbol = $symbols[$res['cid']];
            $exch = &$tableRefs[$res['eid']];
            $exch['courses'][$symbol] = [
                'buy' => (float) $res['buy'],
                'sell' => (float) $res['sell'],
                'ws_buy' => (float) $res['ws_buy'],
                'ws_sell' => (float) $res['ws_sell']
            ];
        }

        foreach ($table as $i => $data) {
            foreach ($data as $ii => $values) {
                if (empty($values['courses']))
                    unset($table[$i][$ii]);
            }

            if (empty($table[$i]))
                unset($table[$i]);
        }

        return $table;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function createCrossTable(): array
    {
        if (!$this->crossSymbols)
            $this->crossSymbols = $this->createCrossSymbols();

        $table = $this->createDirectTable();
        foreach ($table as $tableNum => $data) {
            foreach ($data as $num => $exch) {
                $courses = [];
                foreach ($this->crossSymbols as $symbol) {
                    $key = "{$symbol[0]}/{$symbol[1]}";

                    $s0 = arrayGet($exch['courses'], [$symbol[0], 'buy']);
                    $s1 = arrayGet($exch['courses'], [$symbol[1], 'sell']);
                    $ws0 = arrayGet($exch['courses'], [$symbol[0], 'ws_buy'])
                           ?? $s0;
                    $ws1 = arrayGet($exch['courses'], [$symbol[1], 'ws_sell'])
                           ?? $s1;

                    if (!$s0 && !$s1 && !$ws0 && !$ws1)
                        continue;
                    if ($s0 && $s1)
                        $courses[$key]['price'] = round(($s0 / $s1), 4);
                    if ($ws0 && $ws1)
                        $courses[$key]['ws_price'] = round(($ws0 / $ws1), 4);
                }

                if (!$courses)
                    unset($table[$tableNum][$num]);
                else
                    $table[$tableNum][$num]['courses'] = $courses;
            }
        }

        return $table;
    }

    /**
     * @return array
     */
    protected function createSymbols(): array
    {
        $symbols = [];

        if (($cookie = App::request()->getCookie('tableSymbols'))) {
            $cookie = array_unique(explode(',', $cookie));
            foreach ($cookie as $symbol) {
                if (($symbol = App::currency()->get($symbol))) {
                    $symbols[] = $symbol['symbol'];
                }
            }
        }

        if (4 !== count($symbols)) {
            foreach ($this->config('baseSymbols') as $name) {
                if (!in_array($name, $symbols)) {
                    $symbols[] = $name;
                    if (4 === count($symbols))
                        break;
                }
            }
        }

        return $symbols;
    }

    /**
     * @return array
     */
    protected function createCrossSymbols(): array
    {
        $symbols = [];
        foreach ($this->activeSymbols as $i => $symbol) {
            $next = $this->activeSymbols[($i + 1)] ?? $this->activeSymbols[0];
            $symbols[] = [$symbol, $next];
            $symbols[] = [$next, $symbol];
        }

        return $symbols;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        if (!$this->table)
            $this->table = ('cross' === $this->courseType)
                ? $this->createCrossTable() : $this->createDirectTable();

        $data = parent::getData();
        $data['activeSymbols'] = array_values(
            App::currency()->get($data['activeSymbols'])
        );
        $data['symbols'] = App::currency();

        return $data;
    }

    /**
     * @param bool $toString
     *
     * @return string|null
     */
    public function renderTable(bool $toString = false)
    {
        return static::render("table_{$this->courseType}", $toString);
    }

    /**
     * @param bool $toString
     *
     * @return string|null
     */
    public function renderSymbolPanel(bool $toString = false)
    {
        return static::render("table_symbol_panel", $toString);
    }

}
