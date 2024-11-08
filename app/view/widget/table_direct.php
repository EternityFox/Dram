<?php
$tableNames = ['Банки', 'Обменники', 'Обменники (Уст.)'];
$showHeaders = true;
?>

<div class="table-all">
<?php foreach ($table as $tableNum => $data): ?>
    <div class="table-all-item">
        <div class="table-row active">
            <div class="table-item one head active">
                <div class="table-item-arrow">
                    <img src="img/table-arrow.png" alt="">
                </div>
                <div class="table-item-title">
                    <?= $lang($tableNames[$tableNum]) ?>
                    <?= (1 == $tableNum ? '<span style="padding: 1px 6px;border-radius:10px;background:#c00;color:#fff;margin-left:5px;font-size:11px;font-family:Arial;">new</span>' : '') ?>
                </div>
            </div>
            <div class="table-item two">
                <?= ($showHeaders ? '<img src="img/place.png" alt="">' : '') ?>
            </div>
            <div class="table-item three">
                <p class="table-item-text<?= ($showHeaders ? ' blue' : '') ?>">
                    <?= ($showHeaders ? $lang('Дата') : '') ?>
                </p>
            </div>
            <div class="table-item four"<?= ($showHeaders ? ' data-sort="four"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Куп.') : '') ?>
                </p>
            </div>
            <div class="table-item five"<?= ($showHeaders ? ' data-sort="five"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Прод.') : '') ?>
                </p>
            </div>
            <div class="table-item six"<?= ($showHeaders ? ' data-sort="six"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Куп.') : '') ?>
                </p>
            </div>
            <div class="table-item seven"<?= ($showHeaders ? ' data-sort="seven"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Прод.') : '') ?>
                </p>
            </div>
            <div class="table-item eight"<?= ($showHeaders ? ' data-sort="eight"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Куп.') : '') ?>
                </p>
            </div>
            <div class="table-item nine"<?= ($showHeaders ? ' data-sort="nine"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Прод.') : '') ?>
                </p>
            </div>
            <div class="table-item ten"<?= ($showHeaders ? ' data-sort="ten"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Куп.') : '') ?>
                </p>
            </div>
            <div class="table-item eleven"<?= ($showHeaders ? ' data-sort="eleven"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? $lang('Прод.') : '') ?>
                </p>
            </div>
            <div class="zero d-none"></div>
            <?php $showHeaders = false ?>
        </div>

        <?php $i = 0; foreach ($data as $exch): ?>
            <div class="table-row<?= (0 === $tableNum || 1 === $tableNum ? ' active"'
                : (2 === $tableNum ? '" style="opacity: .6" data-outdated="1"' : '"')) ?>>
                <div class="table-item one">
                    <div class="table-item-number">
                        <?= ++$i ?>
                    </div>
                    <div class="table-item-icon">
                        <?php
                        $img = str_replace('32x32', 'exchanger-icon', $exch['logo']);
                        $img = str_replace('svg', 'webp', $exch['logo'])
                        ?>
                        <img src="img/exchanger/<?= $img ?>" alt=""
                             style="width: 24px; height: 24px;">
                    </div>
                    <div class="table-item-slogan">
                        <?= (0 === $tableNum ? '<a href="/bank/' . $exch['id'] . '" data-maxlen="15">' . $lang("exchanger>{$exch['name']}") . '</a>'
                            : '<a href="/exchanger/' . $exch['id'] . '" data-maxlen="15">' . $lang("exchanger>{$exch['name']}") . '</a>') ?>
                    </div>
                </div>
                <div class="table-item two">
                    <div class="table-item-slogan">
                        <?= $exch['branches'] ?>
                    </div>
                </div>
                <div class="table-item three">
                    <p class="table-item-text" data-raw-date="<?= $exch['raw_date'] ?>">
                        <?= $exch['date'] ?>
                    </p>
                </div>

                <?php $num = 0 ?>
                <?php $nums = [['four', 'five'], ['six', 'seven'], ['eight', 'nine'], ['ten', 'eleven']] ?>
                <?php foreach ($activeSymbols as $symbol): ?>
                    <div class="table-item <?= $nums[$num][0] ?>">
                        <p class="table-item-number"
                            data-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'buy']) ?>"
                            data-reverse-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_buy']) ?>"
                           data-reverse="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_buy']) ?>"
                        ><?= arrayGet($exch, ['courses', $symbol->symbol, 'buy']) ?></p>
                    </div>
                    <div class="table-item <?= $nums[$num][1] ?>">
                        <p class="table-item-number"
                            data-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'sell']) ?>"
                            data-reverse-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_sell']) ?>"
                            data-reverse="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_sell']) ?>"
                        ><?= arrayGet($exch, ['courses', $symbol->symbol, 'sell']) ?></p>
                    </div>
                    <?php ++$num ?>
                <?php endforeach ?>

            <div class="zero d-none"><?= $i ?></div>
        </div>
        <?php endforeach ?>
    </div>
<?php endforeach ?>
</div>
