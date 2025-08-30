<section class="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="left-block">
                    <div class="banner def-box banner-desktop">
                        <?= random_elem($settings['banner_head'], $settings['banner_head_2'], $settings['banner_head_3']) ?>
                    </div>
                    <div class="banner def-box banner-mobile">
                        <?= random_elem($settings['banner_head_mobile'], $settings['banner_head_mobile_2'],) ?>
                        <span class="banner-ads-text"><?= $lang('реклама'); ?></span>
                    </div>
<!--                    <h1>title</h1>-->

                    <div class="mt-4">
                        <?php $widget->render() ?>
                    </div>

                    <div class="banner banner-two def-box banner-desktop">
                        <?= random_elem($settings['banner_footer'], $settings['banner_footer_2'], $settings['banner_footer_3']) ?>
                    </div>
                    <div class="banner banner-two def-box banner-mobile">
                        <?= random_elem($settings['banner_footer_mobile'], $settings['banner_footer_mobile_2'],) ?>
                        <span class="banner-ads-text"><?= $lang('реклама'); ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="right-block">
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
