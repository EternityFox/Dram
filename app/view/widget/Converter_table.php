<?php
    $currencyPrices = $exchangersSorted ? $banksAndExchangersSorted : $banksSorted;
//    echo "<pre>";
//    print_r($currencyPrices);
//    echo "</pre>";

    $currencyPricesData = [];

    foreach ($currencyPrices as $currency => $exch) {
        $currencyPricesData[$currency] = $exch[0]['price'];
    }

    echo "<input type='hidden' name='currencyPrices' data-currency-prices='" . json_encode($currencyPricesData) . "'>";

    if ('cross' == $tableType) {
        $currencyPricesCrossSell = $exchangersSortedCrossSell ? $banksAndExchangersSortedCrossSell : $banksSortedCrossSell;
//    echo "<pre>";
//    print_r($currencyPrices);
//    echo "</pre>";

        $currencyPricesData = [];

        foreach ($currencyPricesCrossSell as $currency => $exch) {
            $currencyPricesData[$currency] = $exch[0]['price'];
        }

        echo "<input type='hidden' name='currencyPricesCrossSell' data-currency-prices-cross-sell='" . json_encode($currencyPricesData) . "'>";
    }

//    $currencyPricesSell = $exchangersSortedSell ? $banksAndExchangersSortedSell : $banksSortedSell;
////        echo "<pre>";
////        print_r($currencyPricesSell);
////        echo "</pre>";
//
//    $currencyPricesSellData = [];
//
//    foreach ($currencyPricesSell as $currency => $exch) {
//        $currencyPricesSellData[$currency] = $exch[0]['price'];
//    }
//
//    echo "<input type='hidden' name='currencyPricesSell' data-currency-prices-sell='" . json_encode($currencyPricesSellData) . "'>";
?>
<?php if ($exchangersSorted): ?>
    <div class="exchange-table-container active">
        <div class="exchange-table-item head">
            <div class="exchange-table-item-arrow">
                <img src="<?= App::$url ?>/img/table-arrow-black.png" alt="">
            </div>
            <div class="exchange-table-item-title">
                <?= $lang("Лучшие курсы") ?>
            </div>
            <!--                        <div class="exchange-table-item-text">-->
            <!--                            --><?php //= $banksAndExchangersSorted[$tableCurrency][0]['price'] ?>
            <!--                        </div>-->
        </div>
        <?php $num = 0; ?>
        <?php foreach ($banksAndExchangersSorted[$tableCurrency] as $data) : ?>

            <?php $num++; ?>
            <div class="exchange-table-item">
                <div class="exchange-table-item-number">
                    <?= $num ?>
                </div>

                <div class="exchange-table-item-img">
                    <?php
                        $img = str_replace('svg', 'webp', $data['logo']);
                    ?>
                    <img src="<?= App::$url ?>/img/exchanger/<?= $img ?>" alt="">
                </div>

                <div class="exchange-table-item-sub-title table-item-slogan">
                    <?= $data['name'] ?>
                    <?php if ('exchanger' == $data['type']) : ?>
                        <span> (<?= $lang("Обменник") ?>)</span>
                    <?php endif ?>
                </div>

                <!--                            --><?php //if ('exchanger' == $data['type']) : ?>
                <!--                                <div class="exchange-table-item-slogan">-->
                <!--                                    (--><?php //= $lang("Обменник") ?><!--)-->
                <!--                                </div>-->
                <!--                            --><?php //endif ?>

                <div class="exchange-table-item-text" data-price="<?= $data['price'] ?>">
                    <?= $data['price'] ?>
                </div>
            </div>
        <?php endforeach ?>
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
    </div>
<?php endif ?>

<div class="exchange-table-container<?= !$exchangersSorted ? ' active' : '' ?>">
    <div class="exchange-table-item head">
        <div class="exchange-table-item-arrow">
            <img src="<?= App::$url ?>/img/table-arrow-black.png" alt="">
        </div>
        <div class="exchange-table-item-title">
            <?= $lang("Банки") ?>
        </div>
        <!--                        <div class="exchange-table-item-num">-->
        <!--                            200-->
        <!--                        </div>-->
    </div>

    <?php $num = 0; ?>
    <?php foreach ($banksSorted[$tableCurrency] as $data) : ?>
        <?php $num++; ?>
        <div class="exchange-table-item">
            <div class="exchange-table-item-number">
                <?= $num ?>
            </div>

            <div class="exchange-table-item-img">
                <?php
                $img = str_replace('svg', 'webp', $data['logo']);
                ?>
                <img src="<?= App::$url ?>/img/exchanger/<?= $img ?>" alt="666">
            </div>

            <div class="exchange-table-item-sub-title table-item-slogan">
                <?= $data['name'] ?>
            </div>

            <div class="exchange-table-item-text" data-price="<?= $data['price'] ?>">
                <?= $data['price'] ?>
            </div>
        </div>
    <?php endforeach ?>
</div>
<?php if ('cross' == $tableType) : ?>
    <div class="exchange-table-container<?= !$exchangersSorted ? ' active' : '' ?> cross d-none">
        <div class="exchange-table-item head">
            <div class="exchange-table-item-arrow">
                <img src="<?= App::$url ?>/img/table-arrow-black.png" alt="">
            </div>
            <div class="exchange-table-item-title">
                <?= $lang("Банки") ?>
            </div>
            <!--                        <div class="exchange-table-item-num">-->
            <!--                            200-->
            <!--                        </div>-->
        </div>

        <?php $num = 0; ?>
        <?php foreach ($banksSortedCrossSell[$tableCrossCurrency] as $data) : ?>
            <?php $num++; ?>
            <div class="exchange-table-item">
                <div class="exchange-table-item-number">
                    <?= $num ?>
                </div>

                <div class="exchange-table-item-img">
                    <img src="<?= App::$url ?>/img/exchanger/<?= $data['logo'] ?>" alt="">
                </div>

                <div class="exchange-table-item-sub-title table-item-slogan">
                    <?= $data['name'] ?>
                </div>

                <div class="exchange-table-item-text" data-price="<?= $data['price'] ?>">
                    <?= $data['price'] ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>

<?php if (isset($exchangersSorted[$tableCurrency])): ?>
    <div class="exchange-table-container">
        <div class="exchange-table-item head">
            <div class="exchange-table-item-arrow">
                <img src="<?= App::$url ?>/img/table-arrow-black.png" alt="">
            </div>
            <div class="exchange-table-item-title">
                <?= $lang("Обменные курсы") ?>
            </div>
            <!--                        <div class="exchange-table-item-num">-->
            <!--                            200-->
            <!--                        </div>-->
        </div>

        <?php $num = 0; ?>
        <?php foreach ($exchangersSorted[$tableCurrency] as $data) : ?>
            <?php $num++; ?>
            <div class="exchange-table-item">
                <div class="exchange-table-item-number">
                    <?= $num ?>
                </div>

                <div class="exchange-table-item-img">
                    <img src="<?= App::$url ?>/img/exchanger/<?= $data['logo'] ?>" alt="">
                </div>

                <div class="exchange-table-item-sub-title table-item-slogan">
                    <?= $data['name'] ?>
                </div>

                <div class="exchange-table-item-text" data-price="<?= $data['price'] ?>">
                    <?= $data['price'] ?>
                </div>
            </div>
        <?php endforeach ?>
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="exchange-table-item">-->
        <!--                        <div class="exchange-table-item-number">-->
        <!--                            1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-img">-->
        <!--                            <img src="img/table-icon.png" alt="">-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-sub-title">-->
        <!--                            Bank 1-->
        <!--                        </div>-->
        <!--                        <div class="exchange-table-item-slogan">-->
        <!--                            100-->
        <!--                        </div>-->
        <!--                    </div>-->
    </div>
<?php endif ?>
