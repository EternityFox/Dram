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
                            <span class="banner-ads-text"><?= $lang('реклама'); ?></span>
                        </div>
                    <?php endif; ?>
                    <section class="fuel-comparison">
                        <div class="fuel-table">
                            <table>
                                <thead>
                                <tr>
                                    <th class="sticky"></th>
                                    <?php foreach ($fuelTypes as $type): ?>
                                        <th>
                                            <div class="fuel-type-header">
                                                <?= $lang(htmlspecialchars($type)) ?>
                                                <div class="fuel-table-entry-item-input">
                                                    <span class="fuel-table-entry-item-input-placeholder"></span>
                                                    <input type="number" value="1" id="fuel-ursuminput">
                                                    <div class="fuel-table-entry-item-input-clear">
                                                        <img src="img/clear.svg" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($fuelCompanies as $c): ?>
                                    <tr>
                                        <td class="sticky">
                                            <a href="/fuel-company/<?= $c['id'] ?>">
                                                <div class="company-item">
                                                    <img src="img/fuel/<?= htmlspecialchars($c['logo']) ?>"
                                                         alt="<?= htmlspecialchars($c['name']) ?>">
                                                    <div class="company-text">
                                                        <div class="company-name"><?= htmlspecialchars($c['name']) ?></div>
                                                        <div class="company-updated"><?= htmlspecialchars(date('H:i d M', strtotime($c['latest_update'] ?? $c['company_updated']))) ?></div>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <?php foreach ($fuelTypes as $type): ?>
                                            <td>
                                                <div class="price-item-fuel"
                                                     data-base-price="<?= isset($fuelData[$c['slug']][$type]['price']) ? htmlspecialchars($fuelData[$c['slug']][$type]['price']) : '-' ?>">
                                                    <?php if (isset($fuelData[$c['slug']][$type]['price'])): ?>
                                                        <?= htmlspecialchars($fuelData[$c['slug']][$type]['price']) . ' ֏' ?>
                                                        <small class="date-added"><?= date('H:i d.m.Y', strtotime($fuelData[$c['slug']][$type]['updated_at'])) ?></small>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
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
        <script src="js/jquery-3.4.1.min.js"></script>
        <script src="js/fuel.js?<?= mt_rand() ?>"></script>
    </div>
</section>