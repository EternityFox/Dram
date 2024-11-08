<section class="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="left-block tab def-box">
                    <?php
//                    echo "<pre>";
//                    print_r($bankInfo);
//                    echo "</pre>";
                    ?>
                    <div class="table-item-icon mb-3 h3">
                        <img src="img/exchanger/<?= $model->getLogo() ?>" alt=""
                             style="width: 32px; height: 16px;">
                        <?= $lang("exchanger>{$bankInfo['name']}") ?>
                    </div>

                    <table class="bank-info mb-3">
                        <tr>
                            <th class="bank-info-th"><?= $lang("Центральный офис") ?></th>
                            <td><?= $bankInfo['head_office'][$lang->getLang()] ?></td>
                        </tr>
                        <tr>
                            <th class="bank-info-th"><?= $lang("Телефон(ы)") ?></th>
                            <td><?= $bankInfo['phone'][$lang->getLang()] ?></td>
                        </tr>
                        <tr>
                            <th class="bank-info-th"><?= $lang("Факс") ?></th>
                            <td><?= $bankInfo['fax'][$lang->getLang()] ?></td>
                        </tr>
                        <tr>
                            <th class="bank-info-th"><?= $lang("Сайт") ?></th>
                            <td><?= $bankInfo['url'][$lang->getLang()] ?></td>
                        </tr>
                    </table>

                    <table class="branches">
                        <tr class="branches-th">
                            <th></th>
                            <th><?= $lang("Отделение") ?></th>
                            <th><?= $lang("Адрес") ?></th>
                            <th><?= $lang("Телефон(ы)") ?></th>
                        </tr>
                        <?php foreach ($bankInfo['baranches'] as $key => $branch): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><?= $branch['name'][$lang->getLang()] ?></td>
                                <td><?= $branch['address'][$lang->getLang()] ?></td>
                                <td><?= $branch['phone'][$lang->getLang()] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="right-block">
                    <div class="soc-block def-box">
                        <p class="soc-block-title">
                            <?= $lang('Мы в соц сетях') ?>
                        </p>
                        <div class="soc-block-row">
                            <a href="#" class="soc-block-row-item">
                                <img src="img/facebook.png" alt="">
                            </a>
                            <a href="#" class="soc-block-row-item">
                                <img src="img/instagram.png" alt="">
                            </a>
                            <a href="#" class="soc-block-row-item">
                                <img src="img/linkedin.png" alt="">
                            </a>
                            <a href="#" class="soc-block-row-item">
                                <img src="img/youtube.png" alt="">
                            </a>
                            <a href="#" class="soc-block-row-item">
                                <img src="img/twitter.png" alt="">
                            </a>
                        </div>
                    </div>

                    <div class="clue-container">
                        <div class="clue">
                            <div class="clue-inner">
                                <div class="clue-icon">
                                    <img src="img/clue-icon.png" alt="">
                                </div>
                                <div class="clue-text">
                                    <?= $lang('Калькулятор') ?>
                                </div>
                            </div>
                            <div class="clue-close">
                                <img src="img/clue-close.png" alt="">
                            </div>
                            <a href="#" class="clue-link">
                                <img src="img/clue-link.png" alt="">
                            </a>
                        </div>
                    </div>

                    <div class="right-banner">
                        <div class="right-banner-item">
                            <?= random_elem($settings['banner_side1'], $settings['banner_side1_2'], $settings['banner_side1_3']) ?>
                        </div>
                        <div class="right-banner-fixed">
                            <div class="right-banner-item">
                                <?= random_elem($settings['banner_side2'], $settings['banner_side2_2'], $settings['banner_side2_3']) ?>
                            </div>
                            <div class="right-banner-item">
                                <?= random_elem($settings['banner_side3'], $settings['banner_side3_2'], $settings['banner_side3_3']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
