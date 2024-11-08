<section class="content">
    <div class="container">
        <h1>Панель управления</h1>

        <form action="" method="POST" class="mt-4">

            <div class="card admin-banners toggle">
                <h5 class="card-header">Баннеры</h5>
                <div class="card-body" style="display: none;">
                    <div class="card admin-banners toggle">
                        <h5 class="card-header">Сверху</h5>
                        <div class="card-body" style="display: none;">


                            <div class="form-group">
                                <label>Баннер топ (220x275):</label>
                                <textarea class="form-control" name="banner_head"
                                          rows="3"><?= $settings['banner_head'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер топ (2) (220x275):</label>
                                <textarea class="form-control" name="banner_head_2"
                                          rows="3"><?= $settings['banner_head_2'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер топ (3) (720x90):</label>
                                <textarea class="form-control" name="banner_head_3"
                                          rows="3"><?= $settings['banner_head_3'] ?></textarea>
                                <div class="form-banner mt-2">
                                    <?= $settings['banner_head'] ?>
                                    <?= $settings['banner_head_2'] ?>
                                    <?= $settings['banner_head_3'] ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Сохранить</button>
                            </div>
                        </div>
                    </div>

                    <div class="card admin-banners toggle mt-4">
                        <h5 class="card-header">Сайдбар</h5>
                        <div class="card-body" style="display: none;">

                            <div class="form-group">
                                <label>Баннер сайдбар 1 (220x275):</label>
                                <textarea class="form-control" name="banner_side1"
                                          rows="3"><?= $settings['banner_side1'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер сайдбар 1 (2) (220x275):</label>
                                <textarea class="form-control" name="banner_side1_2"
                                          rows="3"><?= $settings['banner_side1_2'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер сайдбар 1 (3) (220x275):</label>
                                <textarea class="form-control" name="banner_side1_3"
                                          rows="3"><?= $settings['banner_side1_3'] ?></textarea>
                                <div class="form-banner mt-2">
                                    <?= $settings['banner_side1'] ?>
                                    <?= $settings['banner_side1_2'] ?>
                                    <?= $settings['banner_side1_3'] ?>
                                </div>
                            </div>

                            <hr>


                            <div class="form-group">
                                <label>Баннер сайдбар 2 (220x275):</label>
                                <textarea class="form-control" name="banner_side2"
                                          rows="3"><?= $settings['banner_side2'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер сайдбар 2 (2) (220x275):</label>
                                <textarea class="form-control" name="banner_side2_2"
                                          rows="3"><?= $settings['banner_side2_2'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер сайдбар 2 (3) (220x275):</label>
                                <textarea class="form-control" name="banner_side2_3"
                                          rows="3"><?= $settings['banner_side2_3'] ?></textarea>
                                <div class="form-banner mt-2">
                                    <?= $settings['banner_side2'] ?>
                                    <?= $settings['banner_side2_2'] ?>
                                    <?= $settings['banner_side2_3'] ?>
                                </div>
                            </div>
                            <hr>

                            <div class="form-group">
                                <label>Баннер сайдбар 3 (220x275):</label>
                                <textarea class="form-control" name="banner_side3"
                                          rows="3"><?= $settings['banner_side3'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер сайдбар 3 (2) (220x275):</label>
                                <textarea class="form-control" name="banner_side3_2"
                                          rows="3"><?= $settings['banner_side3_2'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер сайдбар 3 (3) (220x275):</label>
                                <textarea class="form-control" name="banner_side3_3"
                                          rows="3"><?= $settings['banner_side3_3'] ?></textarea>
                                <div class="form-banner mt-2">
                                    <?= $settings['banner_side3'] ?>
                                    <?= $settings['banner_side3_2'] ?>
                                    <?= $settings['banner_side3_3'] ?>
                                </div>
                            </div>


                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Сохранить</button>
                            </div>
                        </div>
                    </div>
                    <div class="card admin-banners toggle mt-4">
                        <h5 class="card-header">Низ</h5>
                        <div class="card-body" style="display: none;">

                            <div class="form-group">
                                <label>Баннер подвал (220x275):</label>
                                <textarea class="form-control" name="banner_footer"
                                          rows="3"><?= $settings['banner_footer'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер подвал 2 (2) (220x275):</label>
                                <textarea class="form-control" name="banner_footer_2"
                                          rows="3"><?= $settings['banner_footer_2'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер подвал (3) (720x80):</label>
                                <textarea class="form-control" name="banner_footer_3"
                                          rows="3"><?= $settings['banner_footer_3'] ?></textarea>
                                <div class="form-banner mt-2">
                                    <?= $settings['banner_footer'] ?>
                                    <?= $settings['banner_footer_2'] ?>
                                    <?= $settings['banner_footer_3'] ?>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label>Баннер подвал (малый) (220x275):</label>
                                <textarea class="form-control" name="banner_footer_small"
                                          rows="3"><?= $settings['banner_footer_small'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер подвал (малый) 2 (2) (220x275):</label>
                                <textarea class="form-control" name="banner_footer_small_2"
                                          rows="3"><?= $settings['banner_footer_small_2'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Баннер подвал (малый) (3) (720x80):</label>
                                <textarea class="form-control" name="banner_footer_small_3"
                                          rows="3"><?= $settings['banner_footer_small_3'] ?></textarea>
                                <div class="form-banner mt-2">
                                    <?= $settings['banner_footer_small'] ?>
                                    <?= $settings['banner_footer_small_2'] ?>
                                    <?= $settings['banner_footer_small_3'] ?>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary mt-2">Сохранить</button>
                    </div>
                </div>
            </div>

            <div class="card admin-banners toggle mt-4">
                <h5 class="card-header">Мобильная версия (ширина дисплея < 600px)</h5>
                <div class="card-body" style="display: none;">


                    <div class="form-group">
                        <label>Баннер топ (220x275):</label>
                        <textarea class="form-control" name="banner_head_mobile"
                                  rows="3"><?= $settings['banner_head_mobile'] ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Баннер топ (2) (220x275):</label>
                        <textarea class="form-control" name="banner_head_mobile_2"
                                  rows="3"><?= $settings['banner_head_mobile_2'] ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Баннер низ (720x90):</label>
                        <textarea class="form-control" name="banner_footer_mobile"
                                  rows="3"><?= $settings['banner_footer_mobile'] ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Баннер низ (2) (720x90):</label>
                        <textarea class="form-control" name="banner_footer_mobile_2"
                                  rows="3"><?= $settings['banner_footer_mobile_2'] ?></textarea>
                    </div>

                    <div class="form-banner mt-2">
                        <?= $settings['banner_head_mobile'] ?>
                        <?= $settings['banner_head_mobile_2'] ?>
                        <?= $settings['banner_footer_mobile'] ?>
                        <?= $settings['banner_footer_mobile_2'] ?>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary mt-2">Сохранить</button>
                    </div>
                </div>
            </div>

            <div class="card mt-4 toggle hide">
                <h5 class="card-header">Логин и пароль</h5>
                <div class="card-body" style="display: none;">
                    <div class="form-group">
                        <label>Логин:</label>
                        <input type="text" class="form-control" name="login" value="<?= $settings['login'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Пароль:</label>
                        <input type="password" class="form-control" name="password"
                               value="<?= $settings['password'] ?>">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary mt-2">Сохранить</button>
                    </div>
                </div>
            </div>

            <div class="card mt-4 toggle hide">
                <h5 class="card-header">English</h5>
                <div class="card-body" style="display: none;">
                    <div class="form-group">
                        <label>Файл перевода:</label>
                        <textarea type="text" class="form-control monospace" rows="10"
                                  name="english"><?= $settings['english'] ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary mt-2">Сохранить</button>
                    </div>
                </div>
            </div>

            <div class="card mt-4 toggle hide">
                <h5 class="card-header">Հայերեն</h5>
                <div class="card-body" style="display: none;">
                    <div class="form-group">
                        <label>Файл перевода:</label>
                        <textarea type="text" class="form-control monospace" rows="10"
                                  name="armenia"><?= $settings['armenia'] ?></textarea>
                    </div>

                </div>
            </div>

            <div class="card mt-4 toggle">
                <h5 class="card-header">Название и заголовок сайта</h5>
                <div class="card-body" style="display: none;">
                    <div class="form-group">
                        <ul class="nav nav-tabs" style="border-bottom: none;">
                            <li class="nav-item head">
                                <div class="nav-link tabs active" data-target="#site_title_ru">Ру</div>
                            </li>
                            <li class="nav-item head">
                                <div class="nav-link tabs" data-target="#site_title_en">EN</div>
                            </li>
                            <li class="nav-item head">
                                <div class="nav-link tabs" data-target="#site_title_am">Հա</div>
                            </li>
                        </ul>
                        <textarea id="site_title_ru" class="form-control collapse show" name="site_title[ru]"
                                  rows="3"><?= $settings['site_title']['ru'] ?></textarea>
                        <textarea id="site_title_en" class="form-control collapse" name="site_title[en]"
                                  rows="3"><?= $settings['site_title']['en'] ?></textarea>
                        <textarea id="site_title_am" class="form-control collapse" name="site_title[am]"
                                  rows="3"><?= $settings['site_title']['am'] ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mt-4 toggle">
                <h5 class="card-header">Статичные страницы</h5>
                <div class="card-body" style="display: none;">
                    <div class="card toggle">
                        <h5 class="card-header">О нас</h5>
                        <div class="card-body" style="display: none;">

                            <div class="form-group">
                                <label>Заголовок</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#about_title_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#about_title_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#about_title_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="about_title_ru" class="form-control collapse show" name="about[title][ru]"
                                          rows="3"><?= $settings['about']['title']['ru'] ?></textarea>
                                <textarea id="about_title_en" class="form-control collapse" name="about[title][en]"
                                          rows="3"><?= $settings['about']['title']['en'] ?></textarea>
                                <textarea id="about_title_am" class="form-control collapse" name="about[title][am]"
                                          rows="3"><?= $settings['about']['title']['am'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Текст</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#about_text_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#about_text_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#about_text_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="about_text_ru" class="form-control collapse show" name="about[text][ru]"
                                          rows="3"><?= $settings['about']['text']['ru'] ?></textarea>
                                <textarea id="about_text_en" class="form-control collapse" name="about[text][en]"
                                          rows="3"><?= $settings['about']['text']['en'] ?></textarea>
                                <textarea id="about_text_am" class="form-control collapse" name="about[text][am]"
                                          rows="3"><?= $settings['about']['text']['am'] ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Сохранить</button>
                            </div>
                        </div>
                    </div>

                    <div class="card toggle mt-4">
                        <h5 class="card-header">ЧаВО</h5>
                        <div class="card-body" style="display: none;">

                            <div class="form-group">
                                <label>Заголовок</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#faq_title_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#faq_title_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#faq_title_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="faq_title_ru" class="form-control collapse show" name="faq[title][ru]"
                                          rows="3"><?= $settings['faq']['title']['ru'] ?></textarea>
                                <textarea id="faq_title_en" class="form-control collapse" name="faq[title][en]"
                                          rows="3"><?= $settings['faq']['title']['en'] ?></textarea>
                                <textarea id="faq_title_am" class="form-control collapse" name="faq[title][am]"
                                          rows="3"><?= $settings['faq']['title']['am'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Текст</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#faq_text_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#faq_text_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#faq_text_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="faq_text_ru" class="form-control collapse show" name="faq[text][ru]"
                                          rows="3"><?= $settings['faq']['text']['ru'] ?></textarea>
                                <textarea id="faq_text_en" class="form-control collapse" name="faq[text][en]"
                                          rows="3"><?= $settings['faq']['text']['en'] ?></textarea>
                                <textarea id="faq_text_am" class="form-control collapse" name="faq[text][am]"
                                          rows="3"><?= $settings['faq']['text']['am'] ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Сохранить</button>
                            </div>
                        </div>
                    </div>

                    <div class="card toggle mt-4">
                        <h5 class="card-header">Контакты</h5>
                        <div class="card-body" style="display: none;">

                            <div class="form-group">
                                <label>Заголовок</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#contacts_title_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#contacts_title_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#contacts_title_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="contacts_title_ru" class="form-control collapse show" name="contacts[title][ru]"
                                          rows="3"><?= $settings['contacts']['title']['ru'] ?></textarea>
                                <textarea id="contacts_title_en" class="form-control collapse" name="contacts[title][en]"
                                          rows="3"><?= $settings['contacts']['title']['en'] ?></textarea>
                                <textarea id="contacts_title_am" class="form-control collapse" name="contacts[title][am]"
                                          rows="3"><?= $settings['contacts']['title']['am'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Текст</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#contacts_text_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#contacts_text_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#contacts_text_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="contacts_text_ru" class="form-control collapse show" name="contacts[text][ru]"
                                          rows="3"><?= $settings['contacts']['text']['ru'] ?></textarea>
                                <textarea id="contacts_text_en" class="form-control collapse" name="contacts[text][en]"
                                          rows="3"><?= $settings['contacts']['text']['en'] ?></textarea>
                                <textarea id="contacts_text_am" class="form-control collapse" name="contacts[text][am]"
                                          rows="3"><?= $settings['contacts']['text']['am'] ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Сохранить</button>
                            </div>
                        </div>
                    </div>

                    <div class="card toggle mt-4">
                        <h5 class="card-header">Реклама</h5>
                        <div class="card-body" style="display: none;">

                            <div class="form-group">
                                <label>Заголовок</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#advertising_title_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#advertising_title_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#advertising_title_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="advertising_title_ru" class="form-control collapse show" name="advertising[title][ru]"
                                          rows="3"><?= $settings['advertising']['title']['ru'] ?></textarea>
                                <textarea id="advertising_title_en" class="form-control collapse" name="advertising[title][en]"
                                          rows="3"><?= $settings['advertising']['title']['en'] ?></textarea>
                                <textarea id="advertising_title_am" class="form-control collapse" name="advertising[title][am]"
                                          rows="3"><?= $settings['advertising']['title']['am'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Текст</label>
                                <ul class="nav nav-tabs" style="border-bottom: none;">
                                    <li class="nav-item head">
                                        <div class="nav-link tabs active" data-target="#advertising_text_ru">Ру</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#advertising_text_en">EN</div>
                                    </li>
                                    <li class="nav-item head">
                                        <div class="nav-link tabs" data-target="#advertising_text_am">Հա</div>
                                    </li>
                                </ul>
                                <textarea id="advertising_text_ru" class="form-control collapse show" name="advertising[text][ru]"
                                          rows="3"><?= $settings['advertising']['text']['ru'] ?></textarea>
                                <textarea id="advertising_text_en" class="form-control collapse" name="advertising[text][en]"
                                          rows="3"><?= $settings['advertising']['text']['en'] ?></textarea>
                                <textarea id="advertising_text_am" class="form-control collapse" name="advertising[text][am]"
                                          rows="3"><?= $settings['advertising']['text']['am'] ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Сохранить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 toggle">
                <h5 class="card-header">Меню</h5>
                <div class="card-body" style="display: none;">
                    <div class="card toggle">
                        <h5 class="card-header">Верхнее</h5>
                        <div class="card-body" style="display: none;">
                            <div class="form-group">
                                <textarea class="form-control monospace" name="menu[top]"
                                          rows="10"><?= $settings['menu']['top'] ?></textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Сохранить</button>
                            </div>
                        </div>
                    </div>

                    <div class="card toggle mt-4">
                        <h5 class="card-header">Левое</h5>
                        <div class="card-body" style="display: none;">
                            <div class="form-group">
                                <?php foreach ($settings['menu']['icons'] as $icon): ?>
                                    <div>
                                        <img src="/img/menu/<?= $icon ?>" style="opacity: 0.5;">
                                        <?= $icon ?>
                                    </div>
                                <?php endforeach ?>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control monospace" name="menu[left]"
                                          rows="30"><?= $settings['menu']['left'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 toggle">
                <h5 class="card-header">Банки</h5>
                <div class="card-body" style="display: none;">
                    <?php
//                        echo "<pre>";
//                        print_r($settings['banks']);
//                        echo "</pre>";
                    ?>
                    <?php $num = 1; ?>
                    <?php foreach ($settings['banks'] as $id => $bank): ?>
                        <div class="mb-2">
                            <?= $num ?>. <img src="img/exchanger/<?= $bank['raid'] ?>.png" alt=""
                                              style="width: 32px; height: 16px;">
                            <a href="/admin/bank/<?= $id ?>"><?= $bank['name'] ?></a>
                        </div>
                        <?php $num++; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mt-4 toggle">
                <h5 class="card-header">Обменники</h5>
                <div class="card-body" style="display: none;">
                    <?php
//                        echo "<pre>";
//                        print_r($settings['banks']);
//                        echo "</pre>";
                    ?>
                    <?php $num = 1; ?>
                    <?php foreach ($settings['exchangers'] as $id => $bank): ?>
                        <div class="mb-2">
                            <?= $num ?>. <img src="img/exchanger/<?= $bank['raid'] ?>.png" alt=""
                                              style="width: 32px; height: 16px;">
                            <a href="/admin/exchanger/<?= $id ?>"><?= $bank['name'] ?></a>
                        </div>
                        <?php $num++; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-primary mt-4">Сохранить</button>
            </div>
        </form>
    </div>
</section>