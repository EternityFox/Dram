<section class="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="left-block">
                    <h1><?= $content['title'][$lang->getLang()] ?></h1>

                    <div class="mt-4"><?= $content['text'][$lang->getLang()] ?></div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="right-block">
                    <div class="clue-container">
                        <div class="clue">
                            <div class="clue-inner">
                                <a href="<?= App::$url ?>/converter">
                                    <div class="clue-icon">
                                        <img src="img/clue-icon.png" alt="">
                                    </div>
                                    <div class="clue-text">
                                        <?= $lang('Конвертер') ?>
                                    </div>
                                </a>
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
