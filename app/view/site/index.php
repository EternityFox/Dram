<section class="content">
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
                        </div>
                    <?php endif; ?>

                    <?= $mainTable->render() ?>

                    <div class="widget  def-box">
                        <?php $bestExchangers->render() ?>

                        <div class="extra-block def-box">
                            <div class="extra-block-item">
                                <a href="<?= App::$url ?>/converter">
                                    <div class="extra-block-item-icon">
                                        <img src="img/extra/extra-icon-one.png" alt="">
                                    </div>
                                    <p class="extra-block-item-title">
                                        <?= $lang('Конвертер') ?>
                                    </p>
                                </a>
                            </div>
                            <div class="extra-block-item">
                                <a href="<?= App::$url ?>/charts">
                                    <div class="extra-block-item-icon">
                                        <img src="img/extra/extra-icon-two.png" alt="">
                                    </div>
                                    <p class="extra-block-item-title">
                                        <?= $lang('Графики') ?>
                                    </p>
                                </a>
                            </div>
                            <div class="extra-block-item">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-three.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Города') ?>
                                </p>
                            </div>
                            <div class="extra-block-item">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-four.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Архив') ?>
                                </p>
                            </div>
                            <div class="extra-block-item five">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-five.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Мир') ?>
                                </p>
                            </div>
                            <div class="extra-block-item six">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-six.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Биткоин') ?>
                                </p>
                            </div>
                            <div class="extra-block-item seven">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-seven.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Связь') ?>
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="banner banner-two def-box banner-desktop">
                        <?= random_elem($settings['banner_footer'], $settings['banner_footer_2'], $settings['banner_footer_3']) ?>
                    </div>
                    <div class="banner banner-two def-box banner-mobile">
                        <?= random_elem($settings['banner_footer_mobile'], $settings['banner_footer_mobile_2'],) ?>
                    </div>
                    <?php $converter->render() ?>

                    <?php $intlCourses->render() ?>

                </div>
            </div>

            <div class="col-lg-3">
                <div class="right-block">
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
