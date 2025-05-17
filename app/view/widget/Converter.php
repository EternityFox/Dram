<div class="tab def-box converter">
    <?php
//    echo "<pre>";
//    print_r($banksAndExchangersSorted);
//    echo "</pre>";
    ?>
    <div class="tab-block">
        <div class="tab-block-item">
            <p class="exchange-course-title">
                <?= $lang('Курсы') ?>
            </p>
            <div class="exchange-course">
                <?php $active = false; ?>
                <?php $num = 0; ?>
                <?php foreach ($converter as $data) : ?>
                    <?php ++$num ?>
                    <?php
                    if (is_string($data['diff']))
                        $class = '';
                    else
                        [$class, $data['diff']] = 0 > $data['diff']
                            ? [' red', $data['diff']]
                            : [' green', "+{$data['diff']}"];
                    ?>
                    <div class="tab-course exchange-course-inner-item<?= $active ? ' active' : '' ?><?= $num > 6 ? ' last-item' : '' ?>" data-currency="<?= $data['symbol'] ?>" data-price="<?= $data['price'] ?>">
                        <div class="tab-inner-icon">
                            <img src="<?= App::$url ?>/img/currency/<?= $data['symbol'] ?>.svg" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                <?= $data['symbol'] ?>
                            </p>
                            <p class="tab-inner-center-text">
                                <?= $lang("{$data['symbol']->name}") ?>
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                <?= $data['price'] ?> <?= \App\App::$currencySymbols[$data['symbol']->symbol] ?? '' ?>
                            </p>
                            <p class="tab-inner-right-text<?= $class ?>">
                                <?= $data['diff'] ?>
                            </p>
                        </div>
                    </div>
                    <?php $active = false; ?>
                <?php endforeach ?>
            </div>
            <div class="exchange-course-btn">
                <div class="exchange-course-btn-title">
                    <?= $lang('Показать всё') ?>
                </div>
                <div class="exchange-course-btn-title d-none">
                    <?= $lang('Скрыть') ?>
                </div>
                <div class="exchange-course-btn-icon">
                    <img src="img/exchange-arrow.svg" alt="">
                </div>
            </div>
        </div>

        <div class="tab-block-item exchange-block">
            <div class="exchange-block-header">
                <div class="exchange-block-title">
                    <?= $lang("Конвертер валют") ?>
                </div>
                <div class="exchange-block-list drop">
                    <div class="exchange-block-list-header drop-header">
                        <div class="exchange-block-list-item drop-item">
                            <?= $lang("Наличные") ?>
                            <input type="hidden" name="type" value="cash">
                        </div>
                        <div class="exchange-block-list-header-arrow drop-arrow">
                            <img src="img/exchange-arrow.svg" alt="">
                        </div>
                    </div>
                    <div class="exchange-block-list-footer drop-footer" style="display: none;">
                        <div class="exchange-block-list-item drop-item">
                            <?= $lang("Безналичные") ?>
                            <input type="hidden" name="type" value="noncash">
                        </div>
                        <div class="exchange-block-list-item drop-item">
                            <?= $lang("По картам") ?>
                            <input type="hidden" name="type" value="card">
                        </div>
                    </div>
                </div>
            </div>
            <div class="exchange-inner">
                <div class="exchange-inner-row">
                    <div class="exchange-inner-item">
                        <p class="exchange-inner-item-title">
                            <?= $lang("У меня есть") ?>
                        </p>
                        <div class="exchange-inner-item-input">
                            <input class="i-have" data-exchange-type="i-have" type="text" data-currency="" value="1" autocomplete="off">
                        </div>
                    </div>
                    <div class="exchange-inner-item">
                        <p class="exchange-inner-item-title">
                            <?= $lang("Получу") ?>
                        </p>
                        <div class="exchange-inner-item-input">
                            <input class="i-get" data-exchange-type="i-get" type="text" data-currency="" value="" autocomplete="off">
                        </div>
                    </div>
                    <div class="exchange-inner-object">
                        <img src="img/exchange-icon.svg" alt="">
                    </div>
                </div>
<!--                <div class="exchange-inner-row">-->
<!--                    <div class="exchange-list drop">-->
<!--                        <div class="exchange-list-header first drop-header">-->
<!--                            <div class="exchange-list-header-item drop-item exchange-currency-i-have">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="<?= App::$url ?>/img/currency/usa.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Американский Доллар-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val" data-currency-val="USD">-->
<!--                                    USD-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-arrow drop-arrow">-->
<!--                                <img src="img/exchange-arrow.svg" alt="">-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="exchange-list-footer first drop-footer" style="display: none;">-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="<?= App::$url ?>/img/currency/armenia.png" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Драм-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val" data-currency-val="AMD">-->
<!--                                    AMD-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="<?= App::$url ?>/img/currency/euro.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Евро-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    EUR-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-rub.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Российский Рубль-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    RUB-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-uk.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Британский Фунт-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    GBR-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-gel.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Грузинский Лари-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    GEL-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-chf.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Швейцарский Франк-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    CHF-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-cad.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Канадский Доллар-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    CAD-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-aed.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Дирхам ОАЭ-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    aed-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-chy.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Китайский Юань-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    chy-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-aud.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Австралийский Доллар-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    aud-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-jpy.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Японская Йена-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    jpy-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-sek.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Шведская Крона-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    sek-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="exchange-list drop">-->
<!--                        <div class="exchange-list-header second drop-header">-->
<!--                            <div class="exchange-list-header-item drop-item exchange-currency-i-get">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="<?= App::$url ?>/img/currency/armenia.png" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Драм-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val" data-currency-val="AMD">-->
<!--                                    AMD-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-arrow drop-arrow">-->
<!--                                <img src="img/exchange-arrow.svg" alt="">-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="exchange-list-footer second drop-footer" style="display: none;">-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="<?= App::$url ?>/img/currency/euro.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Евро-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    EUR-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-usa.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Американский Доллар-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val" data-currency-val="USD">-->
<!--                                    USD-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-rub.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Российский Рубль-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    RUB-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-uk.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Британский Фунт-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    GBR-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-gel.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Грузинский Лари-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    GEL-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-chf.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Швейцарский Франк-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    CHF-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-cad.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Канадский Доллар-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    CAD-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-aed.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Дирхам ОАЭ-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    aed-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-chy.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Китайский Юань-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    chy-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-aud.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Австралийский Доллар-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    aud-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-jpy.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Японская Йена-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    jpy-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="exchange-list-header-item drop-item">-->
<!--                                <div class="exchange-list-header-item-icon">-->
<!--                                    <img src="img/mini-sek.svg" alt="">-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-title">-->
<!--                                    Шведская Крона-->
<!--                                </div>-->
<!--                                <div class="exchange-list-header-item-val">-->
<!--                                    sek-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="exchange-inner-row">
                    <div class="exchange-list drop exchange-currency-i-have-list">
                        <div class="exchange-list-header first drop-header">
                            <div class="exchange-list-header-item drop-item exchange-currency-i-have">
                                <div class="exchange-list-header-item-icon">
                                    <img src="<?= App::$url ?>/img/currency/<?= $converter['USD']['symbol'] ?>.svg" alt="">
                                </div>
                                <div class="exchange-list-header-item-title">
                                    <?= $lang("{$converter['USD']['symbol']->name}") ?>
                                </div>
                                <div class="exchange-list-header-item-val" data-currency-val="<?= $converter['USD']['symbol'] ?>">
                                    <?= $converter['USD']['symbol'] ?>
                                </div>
                            </div>
                            <div class="exchange-list-header-arrow drop-arrow">
                                <img src="img/exchange-arrow.svg" alt="">
                            </div>
                        </div>
                        <div class="exchange-list-footer first drop-footer" style="display: none;">
                            <div class="exchange-list-header-item drop-item d-none">
<!--                            <div class="exchange-list-header-item drop-item">-->
                                <div class="exchange-list-header-item-icon">
                                    <img src="<?= App::$url ?>/img/currency/AMD.svg" alt="">
                                </div>
                                <div class="exchange-list-header-item-title">
                                    <?= $lang("Драм") ?>
                                </div>
                                <div class="exchange-list-header-item-val" data-currency-val="AMD">
                                    AMD
                                </div>
                            </div>
                            <?php
                                $firstConverter = $converter;
                                unset($firstConverter['USD']);
                            ?>
                            <?php foreach ($firstConverter as $data) : ?>
                                <div class="exchange-list-header-item drop-item">
                                    <div class="exchange-list-header-item-icon">
                                        <img src="<?= App::$url ?>/img/currency/<?= $data['symbol'] ?>.svg" alt="">
                                    </div>
                                    <div class="exchange-list-header-item-title">
                                        <?= $lang("{$data['symbol']->name}") ?>
                                    </div>
                                    <div class="exchange-list-header-item-val" data-currency-val="<?= $data['symbol'] ?>">
                                        <?= $data['symbol'] ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="exchange-list drop exchange-currency-i-get-list">
                        <div class="exchange-list-header second drop-header">
                            <div class="exchange-list-header-item drop-item exchange-currency-i-get">
                                <div class="exchange-list-header-item-icon">
                                    <img src="<?= App::$url ?>/img/currency/AMD.svg" alt="">
                                </div>
                                <div class="exchange-list-header-item-title">
                                    <?= $lang("Драм") ?>
                                </div>
                                <div class="exchange-list-header-item-val" data-currency-val="AMD">
                                    AMD
                                </div>
                            </div>
                            <div class="exchange-list-header-arrow drop-arrow">
                                <img src="img/exchange-arrow.svg" alt="">
                            </div>
                        </div>
                        <div class="exchange-list-footer second drop-footer" style="display: none;">
                            <?php foreach ($converter as $data) : ?>
                                <div class="exchange-list-header-item drop-item<?= 'USD' == $data['symbol'] ? ' d-none' : '' ?>">
<!--                                <div class="exchange-list-header-item drop-item">-->
                                    <div class="exchange-list-header-item-icon">
                                        <img src="<?= App::$url ?>/img/currency/<?= $data['symbol'] ?>.svg" alt="">
                                    </div>
                                    <div class="exchange-list-header-item-title">
                                        <?= $lang("{$data['symbol']->name}") ?>
                                    </div>
                                    <div class="exchange-list-header-item-val" data-currency-val="<?= $data['symbol'] ?>">
                                        <?= $data['symbol'] ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="exchange-table">
                <?php
                    include(getcwd() . '/app/view/widget/Converter_table.php');
                ?>
            </div>
        </div>
    </div>
</div>