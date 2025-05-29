<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description"
          content="dram.am - Финансовый маркетплейс в Армении (Курсы валют, Кредиты, Вклады, Банковские карты).">
    <meta name="keywords" content="Курс валют в Армении, kurs rubli dram dollar, exchange in Armenia">
    <meta property="og:image" content="img/site_preview_s.jpg"/>
    <meta name="robots" content="">
    <meta name="Author" content="Copyright by Georgi Selmidis">
    <meta name="Copyright" content="Copyright by Georgi Selmidis">
    <meta name="Address" content="Армения, г. Севан, ул. Наирян 164/408">

    <base href="/">
    <link type="image/x-icon" rel="shortcut icon" href="img/favicon/Favicon.png">
    <link type="image/png" sizes="16x16" rel="icon" href="img/favicon/Favicon 16x16.png">
    <link type="image/png" sizes="32x32" rel="icon" href="img/favicon/Favicon 32x32.png">
    <link type="image/png" sizes="96x96" rel="icon" href="img/favicon/Favicon 96x96.png">
    <link type="image/png" sizes="120x120" rel="icon" href="img/favicon/Favicon 120x120.png">
    <link type="image/png" sizes="192x192" rel="icon" href="img/favicon/Favicon 192x192.png">
    <link type="image/png" sizes="512x512" rel="icon" href="img/favicon/Favicon 512x512.png">


    <link sizes="57x57" rel="apple-touch-icon" href="img/favicon/Favicon Apple 57x57.png">
    <link sizes="60x60" rel="apple-touch-icon" href="img/favicon/Favicon Apple 60x60.png">
    <link sizes="72x72" rel="apple-touch-icon" href="img/favicon/Favicon Apple 72x72.png">
    <link sizes="76x76" rel="apple-touch-icon" href="img/favicon/Favicon Apple 76x76.png">
    <link sizes="114x114" rel="apple-touch-icon" href="img/favicon/Favicon Apple 114x114.png">
    <link sizes="120x120" rel="apple-touch-icon" href="img/favicon/Favicon Apple 120x120.png">
    <link sizes="144x144" rel="apple-touch-icon" href="img/favicon/Favicon Apple 144x144.png">
    <link sizes="152x152" rel="apple-touch-icon" href="img/favicon/Favicon Apple 152x152.png">
    <link sizes="180x180" rel="apple-touch-icon" href="img/favicon/Favicon Apple 180x180.png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/slick/slick.css">
    <link rel="stylesheet" href="dist/slick/slick-theme.css">
    <link rel="stylesheet" href="css/main.css?<?= mt_rand(1, 999999) ?>">

    <title><?= $title[$lang->getLang()] ?></title>

    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2241706448393061"
            crossorigin="anonymous"></script>
    <!-- Yandex.RTB -->
    <script>window.yaContextCb = window.yaContextCb || []</script>
    <script src="https://yandex.ru/ads/system/context.js" async></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=93e925f4-cf14-4f88-b5a7-38bbb050f665&lang=ru_RU"
            type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

</head>

<body>
<div class="wrapper">
    <header class="header my-3">
        <div class="header-row">
            <div class="header-bars">
                <img src="img/menu.svg" alt="">
            </div>
            <div class="container d-flex flex-row justify-content-between align-items-center">
                <div class="header-logo">
                    <a href="/" class="logo">
                        <img src="img/<?= $settings['img_logo'] ?>" alt="Логотип">
                    </a>
                </div>
                <div class="header-place accordion">
                    <div class="header-place-content accordion-content">
                        <div class="accordion-close">
                            <img src="img/close-circle.svg" alt="">
                        </div>
                        <div class="header-place-block">
                            <div class="header-place-block-icon">
                                <img src="img/place.png" alt="">
                            </div>
                            <div class="header-place-block-title">
                                <?= $lang('Выберите город') ?>
                            </div>
                        </div>
                        <div class="header-place-list">
                            <a href="#" class="header-place-list-item header-place-list-item-head ">
                                <p>
                                    <?= $lang('Ереван') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Ачапняк') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Арабкир') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Аван') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Давидашен') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Эребуни') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Зейтун Канакер') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Кентрон') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Малация Себастия') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item header-place-list-item-head">
                                <p>
                                    <?= $lang('Арагацотн') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Аштарак') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Бюракан') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Ошакан') ?>
                                </p>
                            </a>
                            <a href="#" class="header-place-list-item">
                                <p>
                                    <?= $lang('Талин') ?>
                                </p>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-lang accordion">
                    <div class="header-lang-header accordion-header">
                        <img src="img/lang/<?= $lang ?>.svg" alt="">
                    </div>
                    <div class="header-lang-content accordion-content">
                        <div class="header-lang-block">

                            <?php foreach ($languages as $lng => $lngFull): ?>
                                <a href="<?= $lng ?>" class="header-lang-block-item">
                                <span class="header-lang-block-item-icon">
                                    <img src="img/lang/<?= $lng ?>.png" alt="<?= $lng ?>">
                                </span>
                                    <span class="header-lang-block-item-text">
                                <?= $lngFull ?>
                            </span>
                                </a>
                            <?php endforeach ?>

                        </div>
                    </div>
                </div>
                <a href="#" class="header-profile">
                    <img src="img/profile.png" alt="">
                </a>
            </div>
        </div>
        <!--        <div class="header-list scroll-slider lg-hide">-->
        <!--            <a href="#" class="header-list-item active">-->
        <!--                --><?php //= $lang('Главная') ?>
        <!--            </a>-->
        <!--            <a href="#" class="header-list-item">-->
        <!--                --><?php //= $lang('Валюты') ?>
        <!--            </a>-->
        <!--            <a href="#" class="header-list-item">-->
        <!--                --><?php //= $lang('Кредиты') ?>
        <!--            </a>-->
        <!--            <a href="#" class="header-list-item">-->
        <!--                --><?php //= $lang('Депозиты') ?>
        <!--            </a>-->
        <!--            <a href="#" class="header-list-item">-->
        <!--                --><?php //= $lang('Карты') ?>
        <!--            </a>-->
        <!--            <a href="#" class="header-list-item">-->
        <!--                --><?php //= $lang('Моб. связь') ?>
        <!--            </a>-->
        <!--        </div>-->
        <div id="top-menu" class="header-list scroll-slider lg-hide">
            <?php foreach ($menu['top'] as $href => $text): ?>
                <a href="<?= $href ?>" class="header-list-item">
                    <?= $lang($text) ?>
                </a>
            <?php endforeach ?>
        </div>
    </header>

    <?php $this->render('widget/Menu', ['menu' => $menu['left']]) ?>
    <?php if (isset($navigations) && !empty($navigations)): ?>
        <div class="container pt-3">
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
        </div>
    <?php endif; ?>
    <?php if (isset($right_template)): ?>
        <section class="content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <?php include __DIR__ . "/{$template}.php" ?>
                    </div>
                    <div class="col-lg-3">
                        <?php include __DIR__ . "/{$right_template}.php" ?>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <?php include __DIR__ . "/{$template}.php" ?>
    <?php endif; ?>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="f-logo-block">
                        <a href="#" class="f-logo">
                            <img src="img/<?= $settings['img_logo'] ?>" alt="Логотип">
                        </a>
                    </div>
                </div>
                <div class="col-md-8 col-sm-6">
                    <div class="f-menu-block">
                        <ul class="f-menu">
                            <li class="f-menu-item">
                                <a href="/about" class="f-menu-link">
                                    <?= $lang('О нас') ?>
                                </a>
                            </li>
                            <li class="f-menu-item">
                                <a href="/faq" class="f-menu-link">
                                    <?= $lang('ЧаВО') ?>
                                </a>
                            </li>
                            <li class="f-menu-item">
                                <a href="/contacts" class="f-menu-link">
                                    <?= $lang('Контакты') ?>
                                </a>
                            </li>
                            <li class="f-menu-item">
                                <a href="/advertising" class="f-menu-link">
                                    <?= $lang('Реклама') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row f-row">
                <div class="col-12">
                    <div class="f-inner">
                        <div class="f-inner-text">
                            <?= $lang('Присоединяйтесь к нам с соц сетях') ?>
                        </div>
                        <div class="f-soc">
                            <iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fwww.dram.am&width=20&layout&action&size&share=true&height=35&appId"
                                    width="280" height="26" style="border:none;overflow:hidden" scrolling="no"
                                    frameborder="0" allowfullscreen="true"
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                            <!--<a href="#" class="f-soc-item">
                                <img src="img/facebook.png" alt="">
                            </a>
                            <a href="#" class="f-soc-item">
                                <img src="img/instagram.png" alt="">
                            </a>
                            <a href="#" class="f-soc-item">
                                <img src="img/linkedin.png" alt="">
                            </a>
                            <a href="#" class="f-soc-item">
                                <img src="img/youtube.png" alt="">
                            </a>
                            <a href="#" class="f-soc-item">
                                <img src="img/twitter.png" alt="">
                            </a>-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="f-text-block">
                        <p class="f-text">
                            <?= $lang('footer_slogan') ?>
                        </p>
                        <p class="f-text mt-2 mb-2">
                            <!--LiveInternet counter--><a href="https://www.liveinternet.ru/click"
                                                          target="_blank"><img id="licntB307" width="31" height="31"
                                                                               style="border:0"
                                                                               title="LiveInternet"
                                                                               src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7"
                                                                               alt=""/></a>
                            <script>(function (d, s) {
                                    d.getElementById("licntB307").src =
                                        "https://counter.yadro.ru/hit?t44.6;r" + escape(d.referrer) +
                                        ((typeof (s) == "undefined") ? "" : ";s" + s.width + "*" + s.height + "*" +
                                            (s.colorDepth ? s.colorDepth : s.pixelDepth)) + ";u" + escape(d.URL) +
                                        ";h" + escape(d.title.substring(0, 150)) + ";" + Math.random()
                                })
                                (document, screen)</script><!--/LiveInternet-->
                        </p>
                        <p class="f-text mt-0">
                            © 2020 - <?= date('Y'); ?> “dram.am” by
                            <a href="https://www.behance.net/selmidis" target="_blank">Selmidis</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/jquery.sort-elements.js"></script>
<script src="dist/slick/slick.min.js"></script>
<script src="js/typed.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/js.cookie.min.js"></script>
<script src="js/main.js?<?= mt_rand(0, 99999) ?>"></script>
<script src="js/draglist.js?<?= mt_rand(0, 99999) ?>"></script>
<script src="js/table.js?<?= mt_rand(0, 99999) ?>"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ZQBN7XLQME"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('config', 'G-ZQBN7XLQME');
</script>
</body>
</html>
