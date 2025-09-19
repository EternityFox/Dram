<section class="content credit-block">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="left-block">
                    <?php $bannerDesktop = random_elem($settings['banner_head'], $settings['banner_head_2'], $settings['banner_head_3']); ?>
                    <?php if (!empty(trim($bannerDesktop))) : ?>
                        <div class="banner def-box banner-desktop">
                            <?= $bannerDesktop ?>
                        </div>
                    <?php endif; ?>

                    <?php $bannerMobile = random_elem($settings['banner_head_mobile'], $settings['banner_head_mobile_2']); ?>
                    <?php if (!empty(trim($bannerMobile))) : ?>
                        <div class="banner def-box banner-mobile">
                            <?= $bannerMobile ?>
                            <span class="banner-ads-text"><?= $lang('реклама'); ?></span>
                        </div>
                    <?php endif; ?>

                </div>
            </div>


            <!-- Сюда свой код


            !-->


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