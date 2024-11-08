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
                    <?= ($showHeaders ? "{$crossSymbols[0][0]}/{$crossSymbols[0][1]}" : '') ?>
                </p>
            </div>
            <div class="table-item five"<?= ($showHeaders ? ' data-sort="five"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? "{$crossSymbols[1][0]}/{$crossSymbols[1][1]}" : '') ?>
                </p>
            </div>
            <div class="table-item six"<?= ($showHeaders ? ' data-sort="six"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? "{$crossSymbols[2][0]}/{$crossSymbols[2][1]}" : '') ?>
                </p>
            </div>
            <div class="table-item seven"<?= ($showHeaders ? ' data-sort="seven"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? "{$crossSymbols[3][0]}/{$crossSymbols[3][1]}" : '') ?>
                </p>
            </div>
            <div class="table-item eight"<?= ($showHeaders ? ' data-sort="eight"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? "{$crossSymbols[4][0]}/{$crossSymbols[4][1]}" : '') ?>
                </p>
            </div>
            <div class="table-item nine"<?= ($showHeaders ? ' data-sort="nine"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? "{$crossSymbols[5][0]}/{$crossSymbols[5][1]}" : '') ?>
                </p>
            </div>
            <div class="table-item ten"<?= ($showHeaders ? ' data-sort="ten"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? "{$crossSymbols[6][0]}/{$crossSymbols[6][1]}" : '') ?>
                </p>
            </div>
            <div class="table-item eleven"<?= ($showHeaders ? ' data-sort="eleven"' : '') ?>>
                <p class="table-item-text <?= ($showHeaders ? 'blue' : 'bold') ?>">
                    <?= ($showHeaders ? "{$crossSymbols[7][0]}/{$crossSymbols[7][1]}" : '') ?>
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
    <?php $nums = ['four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven'] ?>
    <?php foreach ($crossSymbols as $symbol): ?>
        <?php $symbol = "{$symbol[0]}/{$symbol[1]}" ?>
        <div class="table-item <?= $nums[$num] ?>">
            <p class="table-item-number"
               data-price="<?= arrayGet($exch, ['courses', $symbol, 'price']) ?>"
               data-reverse-price="<?= arrayGet($exch, ['courses', $symbol, 'ws_price']) ?>"
               data-reverse="<?= arrayGet($exch, ['courses', $symbol, 'ws_price']) ?>"
            ><?= arrayGet($exch, ['courses', $symbol, 'price']) ?></p>
        </div>
        <?php ++$num ?>
    <?php endforeach ?>

        <div class="zero d-none"><?= $i ?></div>
    </div>
    <?php endforeach ?>
    </div>
<?php endforeach ?>
</div>
