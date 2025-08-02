<section class="content">
    <div class="container">
        <section class="fuel-comparison">
            <div class="fuel-table">
                <table>
                    <thead>
                    <tr>
                        <th class="sticky"></th>
                        <?php foreach ($fuelTypes as $type): ?>
                            <th>
                                <div class="fuel-type-header">
                                    <?= htmlspecialchars($type) ?>
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
                                            <div class="company-updated"><?= htmlspecialchars(date('H:i d M', strtotime($c['updated']))) ?></div>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <?php foreach ($fuelTypes as $type): ?>
                                <td>
                                    <div class="price-item-fuel"
                                         data-base-price="<?= isset($fuelData[$c['slug']][$type]) ? htmlspecialchars($fuelData[$c['slug']][$type]) : 'N/A' ?>">
                                        <?= isset($fuelData[$c['slug']][$type]) ? htmlspecialchars($fuelData[$c['slug']][$type]) . ' AMD' : 'N/A' ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <script src="js/jquery-3.4.1.min.js"></script>
        <script src="js/fuel.js?<?= mt_rand() ?>"></script>
    </div>
</section>