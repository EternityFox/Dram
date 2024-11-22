<div class="table-flex-item table-flex-course scrollable-row" id="symbolPanel">
    <div class="table-entry scrollable-row scrollable-course">
        <?php foreach ($activeSymbols as $i => $symbol): ?>
            <div class="table-entry-item">
                <div class="table-entry-item-input">
                    <span class="table-entry-item-input-placeholder"></span>
                    <input type="number" value="1"
                           data-symbol-num="<?= $i ?>" id="ursuminput" <?php if (0 === $i) { echo 'class="hovered-input"'; }; ?>>
                </div>
                <div class="table-entry-item-inner">
                    <div class="table-entry-item-inner-icon">
                        <img src="img/currency/<?= $symbol->getImage() ?>" alt="">
                    </div>
                    <div class="table-entry-item-inner-text">
                        <?= $symbol ?>
                    </div>
                    <div class="table-entry-item-inner-arrow">
                        <img src="img/entry-arrow.png" alt="">
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
            </div>
        <?php endforeach ?>
    </div>
</div>