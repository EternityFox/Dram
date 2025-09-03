<section class="content fuel-content">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="left-block">
                    <?php $bannerDesktop = random_elem($settings['banner_head'], $settings['banner_head_2'], $settings['banner_head_3']); ?>
                    <?php if (!empty(trim($bannerDesktop))) : ?>
                        <div class="banner def-box banner-desktop"><?= $bannerDesktop ?></div>
                    <?php endif; ?>

                    <?php $bannerMobile = random_elem($settings['banner_head_mobile'], $settings['banner_head_mobile_2']); ?>
                    <?php if (!empty(trim($bannerMobile))) : ?>
                        <div class="banner def-box banner-mobile">
                            <?= $bannerMobile ?>
                            <span class="banner-ads-text"><?= $lang('реклама'); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- CTA -->
                    <div class="add-data-banner" id="addDataBanner">
                        <div class="d-flex gap-3">
                            <div class="add-data-icon" aria-hidden="true"><img src="img/fuel/fuel.svg" alt=""></div>
                            <div class="add-data-text">
                                <div class="add-data-title">Знаете где дешевле?</div>
                                <div class="add-data-sub">добавьте данные на сайт за 2 минуту</div>
                            </div>
                        </div>
                        <div class="add-data-actions">
                            <a href="/add-station?mode=driver" class="btn-pill btn-driver">Я водитель</a>
                            <a href="/add-station?mode=owner" class="btn-pill btn-owner">Я владелец заправки</a>
                        </div>
                        <img src="img/gray_close.svg" alt="закрыть" class="add-data-close">
                    </div>

                    <section class="fuel-comparison">
                        <div class="fuel-table" data-selected-city="<?= htmlspecialchars($selectedCitySlug) ?>">
                            <table>
                                <thead>
                                <tr class="thead-row">
                                    <th class="sticky">
                                        <div class="fuel-type-header-city">
                                            <div class="fuel-type-name">
                                                <h2 class="fuel-title">Сравнение топлива</h2>
                                            </div>
                                            <label class="city-select">
                                                <span class="city-pin" aria-hidden="true"><img src="img/pin.svg" alt=""></span>
                                                <select id="city-select" aria-label="Выбрать город">
                                                    <option value=""<?= empty($selectedCitySlug) ? ' selected' : '' ?>>
                                                        Все города
                                                    </option>
                                                    <?php foreach ($cities as $city): ?>
                                                        <option value="<?= htmlspecialchars($city['slug']) ?>"
                                                            <?= (!empty($selectedCitySlug) && $selectedCitySlug === $city['slug']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($city['name_ru']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <img src="img/arrow-down.svg" alt="" class="city-caret"
                                                     aria-hidden="true">
                                            </label>
                                        </div>
                                    </th>

                                    <?php foreach ($fuelTypes as $type): ?>
                                        <th>
                                            <div class="fuel-type-header">
                                                <div class="fuel-type-name"><?= $lang(htmlspecialchars($type)) ?></div>
                                                <div class="fuel-table-entry-item-input">
                                                    <span class="fuel-table-entry-item-input-placeholder"></span>
                                                    <input type="number" value="1" inputmode="numeric" min="1">
                                                    <div class="fuel-table-entry-item-input-clear"><img
                                                                src="img/clear.svg" alt=""></div>
                                                </div>
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                                </thead>

                                <tbody>

                                <!-- BEST (всегда первый) -->
                                <tr class="best-row">
                                    <td class="sticky">
                                        <a href="#" class="row-link best-toggle">
                                            <img src="img/blue-arrow-down.svg" alt="голубая стрелка вниз" class="caret">
                                            <div class="region-head"><span class="title">Лучшие</span></div>
                                        </a>
                                    </td>
                                    <?php foreach ($fuelTypes as $type): ?>
                                        <?php $bh = $bestHeader[$type]['price'] ?? null; ?>
                                        <td>
                                            <div class="price-item-fuel"
                                                 data-base-price="<?= $bh !== null ? htmlspecialchars($bh) : '-' ?>">
                                                <?= $bh !== null ? htmlspecialchars($bh) . ' ֏' : '-' ?>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>

                                <?php foreach ($bestCompanies as $bc): ?>
                                    <tr class="company-row best">
                                        <td class="sticky">
                                            <a href="/fuel-company/<?= (int)$bc['id'] ?>" class="row-link">
                                                <div class="company-item">
                                                    <img src="img/fuel/<?= htmlspecialchars($bc['logo']) ?: 'empty.png' ?>"
                                                         alt="<?= htmlspecialchars($bc['name']) ?>">
                                                </div>
                                                <div class="company-text">
                                                    <div class="company-name d-flex align-items-center">
                                                        <div class="company-name-text"><?= htmlspecialchars($bc['name']) ?></div>
                                                        <?php if (!empty($bc['verified'])): ?>
                                                            <img src="img/active.svg"
                                                                 alt="иконка подверждение"
                                                                 title="Данные заполнены представителем компании">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="company-updated">
                                                        <?= $bc['latest_update'] ? htmlspecialchars(date('H:i d M', strtotime($bc['latest_update']))) : '-' ?>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <?php foreach ($fuelTypes as $type): ?>
                                            <?php $cell = $bc['prices'][$type] ?? null;
                                            $base = $cell['price'] ?? '-'; ?>
                                            <td>
                                                <div class="price-item-fuel"
                                                     data-base-price="<?= $base !== '-' ? htmlspecialchars($base) : '-' ?>">
                                                    <?php if ($base !== '-'): ?>
                                                        <?= htmlspecialchars($base) . ' ֏' ?>
                                                        <small class="date-added">
                                                            <?php if (!empty($cell['updated_at'])): ?>
                                                                <?= date('H:i d.m.Y', strtotime($cell['updated_at'])) ?>
                                                            <?php endif; ?>
                                                            <?php if (!empty($cell['city_name'])): ?>
                                                                <span class="note">· <?= htmlspecialchars($cell['city_name']) ?></span>
                                                            <?php endif; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- РЕГИОНЫ / ГОРОДА / КОМПАНИИ -->
                                <?php foreach ($regionsTree as $region): ?>
                                    <tr class="region-row" data-region-id="<?= (int)$region['id'] ?>">
                                        <td class="sticky">
                                            <a href="#" class="row-link region-toggle">
                                                <img src="img/blue-arrow-down.svg" alt="голубая стрелка вниз"
                                                     class="caret">
                                                <div class="region-head"><span
                                                            class="title"><?= htmlspecialchars($region['name']) ?></span>
                                                </div>
                                            </a>
                                        </td>
                                        <?php foreach ($fuelTypes as $type): ?>
                                            <?php $cell = $region['best'][$type] ?? null;
                                            $base = $cell['price'] ?? '-'; ?>
                                            <td>
                                                <div class="price-item-fuel"
                                                     data-base-price="<?= $base !== '-' ? htmlspecialchars($base) : '-' ?>">
                                                    <?= $base !== '-' ? htmlspecialchars($base) . ' ֏' : '-' ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>

                                    <?php foreach ($region['cities'] as $city): ?>
                                        <tr class="city-row hidden"
                                            data-region-id="<?= (int)$region['id'] ?>"
                                            data-city-id="<?= (int)$city['id'] ?>"
                                            data-city-slug="<?= htmlspecialchars($city['slug']) ?>">
                                            <td class="sticky">
                                                <a href="#" class="row-link city-toggle">
                                                    <img src="img/blue-arrow-down.svg" alt="голубая стрелка вниз"
                                                         class="caret">
                                                    <div class="city-head"><span
                                                                class="title"><?= htmlspecialchars($city['name']) ?></span>
                                                    </div>
                                                </a>
                                            </td>
                                            <?php foreach ($fuelTypes as $type): ?>
                                                <?php $cell = $city['best'][$type] ?? null;
                                                $base = $cell['price'] ?? '-'; ?>
                                                <td>
                                                    <div class="price-item-fuel"
                                                         data-base-price="<?= $base !== '-' ? htmlspecialchars($base) : '-' ?>">
                                                        <?= $base !== '-' ? htmlspecialchars($base) . ' ֏' : '-' ?>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>

                                        <?php foreach ($city['companies'] as $c): ?>
                                            <tr class="company-row hidden"
                                                data-region-id="<?= (int)$region['id'] ?>"
                                                data-city-id="<?= (int)$city['id'] ?>">
                                                <td class="sticky">
                                                    <a href="/fuel-company/<?= (int)$c['id'] ?>" class="row-link">
                                                        <div class="company-item">
                                                            <img src="img/fuel/<?= htmlspecialchars($c['logo']) ?: 'empty.png' ?>"
                                                                 alt="<?= htmlspecialchars($c['name']) ?>">
                                                        </div>
                                                        <div class="company-text">
                                                            <div class="company-name d-flex align-items-center">
                                                                <div class="company-name-text"><?= htmlspecialchars($c['name']) ?></div>
                                                                <?php if (!empty($bc['verified'])): ?>
                                                                    <img src="img/active.svg"
                                                                         alt="иконка подверждение"
                                                                         title="Данные заполнены представителем компании">
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="company-updated">
                                                                <?= $c['latest_update'] ? htmlspecialchars(date('H:i d M', strtotime($c['latest_update']))) : '-' ?>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </td>
                                                <?php foreach ($fuelTypes as $type): ?>
                                                    <?php $cell = $c['prices'][$type] ?? null;
                                                    $base = $cell['price'] ?? '-'; ?>
                                                    <td>
                                                        <div class="price-item-fuel"
                                                             data-base-price="<?= $base !== '-' ? htmlspecialchars($base) : '-' ?>">
                                                            <?php if ($base !== '-'): ?>
                                                                <?= htmlspecialchars($base) . ' ֏' ?>
                                                                <?php if (!empty($cell['updated_at'])): ?>
                                                                    <small class="date-added"><?= date('H:i d.m.Y', strtotime($cell['updated_at'])) ?></small>
                                                                <?php endif; ?>
                                                            <?php else: ?>-<?php endif; ?>
                                                        </div>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>

                                    <?php endforeach; ?>
                                <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="right-block">
                    <div class="clue-container">
                        <div class="clue">
                            <div class="clue-inner">
                                <div class="clue-icon"><img src="img/clue-icon.png" alt=""></div>
                                <div class="clue-text"><?= $lang('Калькулятор') ?></div>
                            </div>
                            <div class="clue-close"><img src="img/clue-close.png" alt=""></div>
                            <a href="#" class="clue-link"><img src="img/clue-link.png" alt=""></a>
                        </div>
                    </div>

                    <div class="right-banner">
                        <div class="right-banner-item"><?= random_elem($settings['banner_side1'], $settings['banner_side1_2'], $settings['banner_side1_3']) ?></div>
                        <div class="right-banner-fixed">
                            <div class="right-banner-item"><?= random_elem($settings['banner_side2'], $settings['banner_side2_2'], $settings['banner_side2_3']) ?></div>
                            <div class="right-banner-item"><?= random_elem($settings['banner_side3'], $settings['banner_side3_2'], $settings['banner_side3_3']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Скрипты -->
        <script src="js/jquery-3.4.1.min.js"></script>
        <script src="js/fuel.js?<?= mt_rand() ?>"></script>
    </div>
</section>
