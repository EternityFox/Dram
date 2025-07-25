<?php
$tableNames = ['Банки', 'Обменники', 'Обменники (Уст.)'];
$showHeaders = true;
?>

<div class="table-all d-flex flex-row">
    <div class="bank-info-all-item">
        <?php foreach ($table as $tableNum => $data): ?>
            <div class="bank-names-col p-0<?= (2 == $tableNum ? ' gray-bank' : '') ?>">
                <div class="table-row active table-bg">
                    <div class="table-item one head<?= (2 == $tableNum ? '' : ' active') ?>">
                        <div class="table-item-arrow">
                            <img src="img/table-arrow.png" alt="">
                        </div>
                        <div class="table-item-title">
                            <?= $lang($tableNames[$tableNum]) ?>
                            <?= (1 == $tableNum ? '<span style="padding: 1px 6px;border-radius:10px;background:#c00;color:#fff;margin-left:5px;font-size:11px;font-family:Arial;">new</span>' : '') ?>
                        </div>
                    </div>
                </div>
                <?php
                foreach ($data as $idx => $exch): ?>
                    <div class="table-row<?= (2 == $tableNum ? '' : ' active') ?>">
                        <div class="table-item one">
                            <div class="table-item-number">
                                <?= ++$idx ?>
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
                                <div class="d-flex flex-column">
                                    <?= (0 === $tableNum ? '<a href="/bank/' . $exch['id'] . '" data-maxlen="15">' . $lang("exchanger>{$exch['name']}") . '</a>'
                                        : '<a href="/exchanger/' . $exch['id'] . '" data-maxlen="15">' . $lang("exchanger>{$exch['name']}") . '</a>') ?>
                                    <span class="table-item-data"><?= $exch['date'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($idx === count($data) && $tableNum == 0): ?>
                        <?php
                        $bannerMid = random_elem($settings['banner_middle_1'] ?? '', $settings['banner_middle_2'] ?? '');
                        if (!empty($bannerMid)): ?>
                            <div class="banner def-box banner-mobile banner-middle">
                                <?= $bannerMid ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach ?>
            </div>
        <?php endforeach ?>
    </div>
    <div class="banks-info-wrap">
        <div class="table-bg scrollable-row px-0">
            <div class="table-row active scroller">
                <div class="table-flex-item" id="symbolPanel">
                    <div class="table-entry">
                        <?php foreach ($activeSymbols as $i => $symbol): ?>
                            <div class="table-entry-item">
                                <div class="table-entry-item-input">
                                    <span class="table-entry-item-input-placeholder"></span>
                                    <input type="number" value="1"
                                           data-symbol-num="<?= $i ?>" id="ursuminput" <?php if (0 === $i) {
                                        echo 'class="hovered-input"';
                                    }; ?>>
                                    <div class="table-entry-item-input-clear d-none">
                                        <img src="img/clear.svg" alt="">
                                    </div>
                                </div>
                                <div class="table-entry-item-inner">
                                    <div class="table-entry-item-inner-icon">
                                        <img src="img/currency/<?= $symbol->getImage() ?>" alt="">
                                    </div>
                                    <div class="table-entry-item-inner-text">
                                        <?= $symbol ?>
                                    </div>
                                    <div class="table-entry-item-inner-arrow">
                                        <img src="img/point-gray.svg" alt="">
                                    </div>
                                    <div class="table-entry-list">
                                        <?php foreach ($symbols as $subsymbol): ?>
                                            <?php if ($symbol === $subsymbol) continue; ?>
                                            <a href="#tableTop"
                                               class="table-entry-list-item-a<?= ($symbol === $subsymbol ? ' d-none' : '') ?>"
                                               data-symbol-change="<?= "{$i}_{$subsymbol}" ?>">
                                                <span class="table-entry-list-item-icon">
                                                    <img src="img/currency/<?= $subsymbol->getImage() ?>" alt="">
                                                </span>
                                                <span class="table-entry-list-item-text">
                                                    <?= $lang("currency>{$subsymbol->name}") ?>
                                                </span>
                                                <span class="table-entry-list-item-val">
                                                    <?= $subsymbol ?>
                                                </span>
                                            </a>
                                        <?php endforeach ?>
                                    </div>
                                </div>
                                <?php
                                $classMap = [['four', 'five'], ['six', 'seven'], ['eight', 'nine'], ['ten', 'eleven'], ['twelve', 'thirteen'],['fourteen','fifteen'],['sixteen','seventeen'],['eighteen','nineteen'],['twenty','twenty_one'],['twenty_two','twenty_three'],['twenty_four','twenty_five']];
                                $currentClass = $classMap[$i][0];
                                $nextClass = $classMap[$i][1];
                                ?>
                                <div class="table-items">
                                    <div class="table-item <?= $currentClass ?>"<?= ($showHeaders ? ' data-sort="' . $currentClass . '"' : '') ?>>
                                        <p class="table-item-text <?= ($showHeaders ? 'gray' : 'bold') ?>">
                                            <?= ($showHeaders ? ($i % 2 === 0 ? $lang('Куп.') : $lang('Прод.')) : '') ?>
                                        </p>
                                    </div>
                                    <div class="table-item <?= $nextClass ?>"<?= ($showHeaders ? ' data-sort="' . $nextClass . '"' : '') ?>>
                                        <p class="table-item-text <?= ($showHeaders ? 'gray' : 'bold') ?>">
                                            <?= ($showHeaders ? (($i + 1) % 2 === 0 ? $lang('Куп.') : $lang('Прод.')) : '') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="banks-info scrollable-row p-0">
            <?php foreach ($table as $tableNum => $data): ?>
                <div class="bank-value<?= (2 == $tableNum ? ' gray-bank' : '') ?>">
                    <?php if ($tableNum != 0): ?>
                        <div class="table-row active items-head">
                            <?php foreach ($activeSymbols as $i => $symbol): ?>
                                <?php
                                $classMap = [['four', 'five'], ['six', 'seven'], ['eight', 'nine'], ['ten', 'eleven'], ['twelve', 'thirteen'],['fourteen','fifteen'],['sixteen','seventeen'],['eighteen','nineteen'],['twenty','twenty_one'],['twenty_two','twenty_three'],['twenty_four','twenty_five']];
                                $currentClass = $classMap[$i][0];
                                $nextClass = $classMap[$i][1];
                                ?>
                                <div class="table-items">
                                    <div class="table-item <?= $currentClass ?>"<?= ($showHeaders ? ' data-sort="' . $currentClass . '"' : '') ?>>
                                        <p class="table-item-text <?= ($showHeaders ? 'gray' : 'bold') ?>">
                                            <?= ($showHeaders ? ($i % 2 === 0 ? $lang('Куп.') : $lang('Прод.')) : '') ?>
                                        </p>
                                    </div>
                                    <div class="table-item <?= $nextClass ?>"<?= ($showHeaders ? ' data-sort="' . $nextClass . '"' : '') ?>>
                                        <p class="table-item-text <?= ($showHeaders ? 'gray' : 'bold') ?>">
                                            <?= ($showHeaders ? (($i + 1) % 2 === 0 ? $lang('Куп.') : $lang('Прод.')) : '') ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif; ?>
                    <?php
                    foreach ($data as $idx => $exch): ?>
                        <div class="table-row<?= (2 == $tableNum ? '' : ' active') ?> table-items-row">
                            <?php $num = 0 ?>
                            <?php $nums = [['four', 'five'], ['six', 'seven'], ['eight', 'nine'], ['ten', 'eleven'], ['twelve', 'thirteen'], ['fourteen', 'fifteen'], ['sixteen', 'seventeen'], ['eighteen', 'nineteen'], ['twenty', 'twenty_one'], ['twenty_two', 'twenty_three'], ['twenty_four', 'twenty_five']] ?>
                            <?php foreach ($activeSymbols as $symbol): ?>
                                <div class="table-items">
                                    <div class="table-item <?= $nums[$num][0] ?>">
                                        <p class="table-item-number"
                                           data-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'buy']) ?>"
                                           data-reverse-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_buy']) ?>"
                                           data-reverse="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_buy']) ?>"
                                        ><?= arrayGet($exch, ['courses', $symbol->symbol, 'buy']) ?>
                                        </p>
                                    </div>
                                    <div class="table-item <?= $nums[$num][1] ?>">
                                        <p class="table-item-number"
                                           data-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'sell']) ?>"
                                           data-reverse-price="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_sell']) ?>"
                                           data-reverse="<?= arrayGet($exch, ['courses', $symbol->symbol, 'ws_sell']) ?>"
                                        ><?= arrayGet($exch, ['courses', $symbol->symbol, 'sell']) ?>
                                        </p>
                                    </div>
                                </div>
                                <?php ++$num ?>
                            <?php endforeach ?>
                        </div>
                        <?php if ($idx === count($data) - 1 && $tableNum == 0): ?>
                            <?php
                            $bannerMid = random_elem($settings['banner_middle_1'] ?? '', $settings['banner_middle_2'] ?? '');
                            if (!empty($bannerMid)): ?>
                                <div class="banner def-box banner-mobile banner-middle">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>