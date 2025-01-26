<?php
$ctNames = ['direct' => 'Прямой', 'cross' => 'Кросс-курс'];
$etNames = ['cash' => 'Наличный', 'noncash' => 'Безналичный', 'card' => 'По картам'];
?>
<div class="table" id="table">
    <!--    <p class="table-title">-->
    <!--        --><?php //= $lang('Курсы валют') ?>
    <!--    </p>-->
    <!--    <div class="table-flex table-title">-->
    <div class="row table-title">
        <div class="col-7 text-right refresh-time-container">
            <a href="/"><?= $lang('Обновить') ?></a>
            <div id="refresh-time" class="d-none"><?= $refreshTime ?></div>
            <span class="refresh-time"><?= $refreshTime ?></span>
        </div>
    </div>
    <div class="table-flex">
        <div class="table-flex-item">
            <div class="table-point clue-mob d-lg-none" style="display: none;">
                <div class="table-point-container">
                    <div class="table-point-item active">
                        <div class="clue-mob-close">
                            <img src="img/clue-close.png" alt="">
                        </div>
                        <!--                        <a href="--><?php //= App::$url ?><!--/converter">-->
                        <span>
                                <?= $lang('Конвертер') ?>
                            </span>
                        <div class="clue-mob-link">
                            <img src="img/clue-mob-link.png" alt="">
                        </div>
                        <!--                        </a>-->
                    </div>
                </div>
            </div>

            <div class="table-point d-lg-flex">
                <div id="exchangeType"
                     class="table-point-container"
                     data-active="<?= $exchangeType ?>">
                    <div class="table-point-item active">
                        <span>
                            <?= $lang($etNames[$exchangeType]) ?>
                        </span>
                        <div class="table-point-item-icon">
                            <img src="img/point-gray.png" alt="">
                            <img src="img/point-white.png" alt="">
                        </div>
                    </div>

                    <div class="table-point-item-list">
                        <a href="#tableTop" class="table-point-item-list-item"
                           data-type="cash"
                        ><?= $lang('Наличный') ?></a>
                        <a href="#tableTop" class="table-point-item-list-item"
                           data-type="noncash"
                        ><?= $lang('Безналичный') ?></a>
                        <a href="#tableTop" class="table-point-item-list-item"
                           data-type="card"
                        ><?= $lang('По картам') ?></a>

                        <div class="table-point-item-list-item">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="wholeSale">
                                <label class="custom-control-label" for="wholeSale">
                                    <?= $lang('Оптовые цены') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="courseType"
                     class="table-point-container"
                     data-active="<?= $courseType ?>">
                    <div class="table-point-item">
                        <span>
                            <?= $lang($ctNames[$courseType]) ?>
                        </span>
                        <div class="table-point-item-icon">
                            <img src="img/point-gray.png" alt="">
                            <img src="img/point-white.png" alt="">
                        </div>
                    </div>
                    <div class="table-point-item-list">
                        <a href="#tableTop" class="table-point-item-list-item"
                           data-type="direct"
                        ><?= $lang('Прямой') ?></a>
                        <a href="#tableTop" class="table-point-item-list-item"
                           data-type="cross"
                        ><?= $lang('Кросс-курс') ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="table-flex-item clue-mob d-lg-none d-block">
            <div class="clue-mob-close">X</div>
            <div class="clue-mob-text">
                <?= $lang('Конвертер') ?>
            </div>
            <div class="clue-mob-arrow">
                <img src="img/point-white.png" alt="">
            </div>
        </div> -->
    </div>
    <div class="row table-text-row d-none">
        <div class="table-text-row-text">
            <p>
                Текст
            </p>
        </div>
        <div class="table-text-row-text">
            <p>
                Текст
            </p>
        </div>
        <div class="table-text-row-text">
            <p>
                Текст
            </p>
        </div>
    </div>

    <?php $widget->renderTable() ?>

</div>
