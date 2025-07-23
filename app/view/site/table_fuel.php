<section class="content">
    <div class="container">
        <?php
        $fuelCompanies = [
            ['key' => 'SevGaz', 'name' => 'SevGaz', 'icon' => 'sevgaz.png', 'updated' => '20:54 30 Сент.'],
            ['key' => 'GasOil', 'name' => 'Gas Oil', 'icon' => 'gasoil.png', 'updated' => '16:30 30 Сент.'],
            ['key' => 'Fresh', 'name' => 'Fresh', 'icon' => 'fresh.png', 'updated' => '15:11 30 Сент.'],
        ];

        $fuelTypes = [
            'Бензин 92',
            'Бензин 95',
            'Бензин 98',
            'Дизель',
            'LPG (автогаз)'
        ];

        $fuelData = [
            'SevGaz' => [190, 175, 160, 150, 140],
            'GasOil' => [210, 180, 170, 160, 150],
            'Fresh' => [255, 200, 190, 180, 170],
        ];
        ?>

        <section class="fuel-comparison">
            <!-- Фиксированная колонка с компаниями -->
            <div class="companies-column">
                <?php foreach ($fuelCompanies as $c): ?>
                    <div class="company-item">
                        <img src="img/<?= $c['icon'] ?>" alt="<?= $c['name'] ?>">
                        <div class="company-text">
                            <div class="company-name"><?= $c['name'] ?></div>
                            <div class="company-updated"><?= $c['updated'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Скроллируемая область с данными -->
            <div class="data-column">
                <!-- Заголовки типов топлива -->
                <div class="header-row">
                    <?php foreach ($fuelTypes as $type): ?>
                        <div class="fuel-type-header"><?= $type ?></div>
                    <?php endforeach; ?>
                    <div class="fuel-type-header">Кол-во</div>
                </div>

                <!-- Строки с ценами -->
                <?php foreach ($fuelCompanies as $c): ?>
                    <div class="data-row" data-company="<?= $c['key'] ?>">
                        <?php foreach ($fuelData[$c['key']] as $price): ?>
                            <div class="price-item" data-base-price="<?= $price ?>">
                                <?= $price ?> AMD
                            </div>
                        <?php endforeach; ?>
                        <div class="quantity-cell">
                            <input type="number" class="quantity" value="1" min="1">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <script src="js/fuel.js?<?= mt_rand() ?>"></script>
    </div>
</section>