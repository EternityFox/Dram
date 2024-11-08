<section class="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="n-found">
                    <div class="n-found-img">
                        <img src="img/404-logo.jpg" alt="">
                    </div>
                    <a href="/" class="n-found-link">
                        <?= $lang('Go to home page') ?>
                    </a>
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
