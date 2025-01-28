<section class="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="left-block">
                    <?php if (isset($navigations) && !empty($navigations)): ?>
                        <div class="nav-icons">
                            <?php foreach ($navigations as $navigation): ?>
                                <a href="<?= $navigation['link'] ?>">
                                    <div class="nav-icon<?= (explode('?', $_SERVER['REQUEST_URI'])[0] === $navigation['link'] ? ' active' : '') ?>">
                                        <img src="img/<?= $navigation['image'] ?>"
                                             alt="<?= $navigation["title_" . $lang->getLang()] ?>">
                                        <span class="nav-icon-text"><?= $navigation["title_" . $lang->getLang()] ?></span>
                                    </div>
                                </a>
                            <?php endforeach ?>
                        </div>
                    <?php endif; ?>
                    <div class="banner def-box banner-desktop">
                        <?= random_elem($settings['banner_head'], $settings['banner_head_2'], $settings['banner_head_3']) ?>
                    </div>
                    <div class="banner def-box banner-mobile">
                        <?= random_elem($settings['banner_head_mobile'], $settings['banner_head_mobile_2'],) ?>
                    </div>

                    <?= $mainTable->render() ?>

                    <div class="extra">
                        <div class="extra-title">
                            <div>
                                <?= $lang('Смотрите так же') ?><!-- Դիտեք նաև -->
                            </div>
                            <div class="extra-title-icon">
                                <img src="img/information.png" alt="">
                            </div>
                        </div>
                        <div class="extra-block def-box">
                            <div class="extra-block-item">
                                <a href="<?= App::$url ?>/converter">
                                    <div class="extra-block-item-icon">
                                        <img src="img/extra/extra-icon-one.png" alt="">
                                    </div>
                                    <p class="extra-block-item-title">
                                        <?= $lang('Конвертер') ?><!-- հաշվիչ -->
                                    </p>
                                </a>
                            </div>
                            <div class="extra-block-item">
                                <a href="<?= App::$url ?>/charts">
                                    <div class="extra-block-item-icon">
                                        <img src="img/extra/extra-icon-two.png" alt="">
                                    </div>
                                    <p class="extra-block-item-title">
                                        <?= $lang('Графики') ?><!-- Գրաֆիկա -->
                                    </p>
                                </a>
                            </div>
                            <div class="extra-block-item">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-three.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Города') ?><!-- Քաղաքներ -->
                                </p>
                            </div>
                            <div class="extra-block-item">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-four.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Архив') ?><!-- Արխիվ -->
                                </p>
                            </div>
                            <div class="extra-block-item">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-five.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Мир') ?><!-- Աշխարհ -->
                                </p>
                            </div>
                            <div class="extra-block-item">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-six.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Биткоин') ?>
                                </p>
                            </div>
                            <div class="extra-block-item">
                                <div class="extra-block-item-icon">
                                    <img src="img/extra/extra-icon-seven.png" alt="">
                                </div>
                                <p class="extra-block-item-title">
                                    <?= $lang('Связь') ?><!-- Միացում -->
                                </p>
                            </div>
                        </div>
                    </div>

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
                    <div class="soc-block def-box" style="padding:15px 5px">
                        <p class="soc-block-title">
                            <?= $lang('Мы в соц сетях') ?>
                        </p>
                        <div class="soc-block-row">
                            <iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fwww.dram.am&width=20&layout&action&size&share=true&height=35&appId"
                                    width="180" height="20" style="border:none;overflow:hidden" scrolling="no"
                                    frameborder="0" allowfullscreen="true"
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                            <!--<a href="#" class="soc-block-row-item">
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
                            </a>-->
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
