<section class="content">
    <div class="container fuel-companion">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3 gap-lg-4 mb-4">
            <div>
                <a href="/" class="btn btn-light d-flex align-items-center gap-2 px-3 py-2 rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                         stroke="black" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 4.5L3 12M3 12L10.5 19.5M3 12H21"/>
                    </svg>
                    <span class="ml-2"><?= $lang("Назад") ?></span>
                </a>
            </div>
            <h1 class="h4 m-0 fw-bold ml-4"><?= htmlspecialchars($fuelCompanyInfo['name']) ?></h1>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <div class="left-block tab def-box">

                    <?php if (!empty($canEdit) && !empty($editUrl)): ?>
                        <div class="ms-lg-auto w-25 mb-3">
                            <a href="<?= htmlspecialchars($editUrl) ?>"
                               class="btn btn-primary d-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 17.25V21h3.75L19.81 7.94l-3.75-3.75L3 17.25zM21 6.75a.996.996 0 0 0 0-1.41l-2.34-2.34a.996.996 0 0 0-1.41 0l-1.83 1.83 3.75 3.75L21 6.75z"
                                          fill="currentColor"/>
                                </svg>
                                <span><?= $lang('Редактировать') ?></span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="row justify-content-between flex-wrap gap-3 p-3">
                        <div class="col-12 p-2 mt-3">
                            <div class="accordion" id="regionsAccordion">
                                <?php
                                $hasAny = !empty($regionsTree);
                                if (!$hasAny): ?>
                                    <div class="alert alert-secondary mb-0"><?= $lang('Для компании пока не добавлены точки.') ?></div>
                                <?php else:
                                    $regionIndex = 0;
                                    foreach ($regionsTree as $region):
                                        $regionIndex++;
                                        $rId = (int)$region['id'];
                                        $rName = htmlspecialchars($region['name']);
                                        $rCollapse = "r-{$rId}";
                                        $rShow = ($regionIndex === 1) ? 'show' : '';
                                        $rExpanded = ($regionIndex === 1) ? 'true' : 'false';
                                        ?>
                                        <div class="accordion-item mb-2">
                                            <h2 class="accordion-header" id="h-<?= $rCollapse ?>">
                                                <button class="accordion-button button-transparent <?= $rShow ? '' : 'collapsed' ?>"
                                                        type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#<?= $rCollapse ?>"
                                                        aria-expanded="<?= $rExpanded ?>"
                                                        aria-controls="<?= $rCollapse ?>">
                                                    <span class="accordion-header-text"><?= $rName ?></span>
                                                </button>
                                            </h2>
                                            <div id="<?= $rCollapse ?>"
                                                 class="accordion-collapse collapse <?= $rShow ?>"
                                                 aria-labelledby="h-<?= $rCollapse ?>"
                                                 data-bs-parent="#regionsAccordion">
                                                <div class="accordion-body">

                                                    <?php
                                                    $cities = $region['cities'] ?? [];
                                                    if (empty($cities)): ?>
                                                        <div class="text-muted"><?= $lang('Нет городов в регионе') ?></div>
                                                    <?php else:
                                                        $cityIndex = 0;
                                                        ?>
                                                        <div class="accordion" id="cities-acc-<?= $rId ?>">
                                                            <?php foreach ($cities as $city):
                                                                $cityIndex++;
                                                                $cId = (int)$city['id'];
                                                                $cName = htmlspecialchars($city['name']);
                                                                $cCollapse = "c-{$cId}-r{$rId}";
                                                                $cShow = ($regionIndex === 1 && $cityIndex === 1) ? 'show' : '';
                                                                $cExpanded = ($cShow ? 'true' : 'false');
                                                                ?>
                                                                <div class="accordion-item mb-2">
                                                                    <h2 class="accordion-header"
                                                                        id="h-<?= $cCollapse ?>">
                                                                        <button class="accordion-button button-transparent <?= $cShow ? '' : 'collapsed' ?>"
                                                                                type="button"
                                                                                data-bs-toggle="collapse"
                                                                                data-bs-target="#<?= $cCollapse ?>"
                                                                                aria-expanded="<?= $cExpanded ?>"
                                                                                aria-controls="<?= $cCollapse ?>">
                                                                            <span class="accordion-header-text"><?= $cName ?></span>
                                                                        </button>
                                                                    </h2>
                                                                    <div id="<?= $cCollapse ?>"
                                                                         class="accordion-collapse collapse <?= $cShow ?>"
                                                                         aria-labelledby="h-<?= $cCollapse ?>"
                                                                         data-bs-parent="#cities-acc-<?= $rId ?>">
                                                                        <div class="accordion-body">
                                                                            <?php
                                                                            $points = $city['points'] ?? [];
                                                                            if (empty($points)): ?>
                                                                                <div class="text-muted"><?= $lang('Нет точек в этом городе') ?></div>
                                                                            <?php else:
                                                                                $firstPointDone = false;
                                                                                foreach ($points as $p):
                                                                                    $pid = (int)$p['id'];
                                                                                    $phones = $p['phones'] ?? [];
                                                                                    $emails = $p['emails'] ?? [];
                                                                                    $website = $p['website'] ?? '';
                                                                                    $socials = $p['socials'] ?? [];
                                                                                    $wh = $p['working_hours'] ?? [];
                                                                                    $lat = $p['latitude'];
                                                                                    $lng = $p['longitude'];
                                                                                    $prices = $p['prices'] ?? [];
                                                                                    $address = $p['address'] ?: $lang('Адрес не указан');
                                                                                    $mapId = 'yandex-map-' . $pid;
                                                                                    ?>
                                                                                    <div class="row justify-content-between flex-wrap gap-3 mb-3">
                                                                                        <div class="col background-gray p-4">
                                                                                            <div class="d-flex gap-4 flex-column">
                                                                                                <div class="d-flex flex-row gap-3 align-items-center">
                                                                                                    <img src="img/fuel/<?= htmlspecialchars($fuelCompanyInfo['logo']) ?: 'empty.png' ?>"
                                                                                                         alt="<?= htmlspecialchars($fuelCompanyInfo['name']) ?>"
                                                                                                         style="width: 64px; height: 64px;"
                                                                                                         class="rounded-3">
                                                                                                    <div class="d-flex flex-column gap-2 align-items-start">
                                                                                                        <span class="name-branch"><?= htmlspecialchars($fuelCompanyInfo['name']) ?></span>
                                                                                                        <div class="d-flex flex-row gap-2 align-items-center">
                                                                                                            <svg viewBox="0 0 24 24"
                                                                                                                 width="40"
                                                                                                                 height="40"
                                                                                                                 fill="none">
                                                                                                                <path d="M15 10.5c0 .8-.32 1.56-.88 2.12A2.99 2.99 0 0 1 12 13.5c-.8 0-1.56-.32-2.12-.88A2.99 2.99 0 0 1 9 10.5c0-.8.32-1.56.88-2.12A2.99 2.99 0 0 1 12 7.5c.8 0 1.56.32 2.12.88.56.56.88 1.32.88 2.12Z"
                                                                                                                      stroke="#0155eb"
                                                                                                                      stroke-width="1.5"
                                                                                                                      stroke-linecap="round"
                                                                                                                      stroke-linejoin="round"></path>
                                                                                                                <path d="M19.5 10.5C19.5 17.642 12 21.75 12 21.75S4.5 17.642 4.5 10.5A7.5 7.5 0 0 1 12 3a7.5 7.5 0 0 1 7.5 7.5Z"
                                                                                                                      stroke="#0155eb"
                                                                                                                      stroke-width="1.5"
                                                                                                                      stroke-linecap="round"
                                                                                                                      stroke-linejoin="round"></path>
                                                                                                            </svg>
                                                                                                            <span class="branches-address"><?= htmlspecialchars($address) ?></span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="d-flex flex-column gap-3">
                                                                                                    <?php foreach ($phones as $phone): ?>
                                                                                                        <?php $clean = preg_replace('/[\s\+()-]/', '', $phone); ?>
                                                                                                        <a class="d-flex items-center gap-2"
                                                                                                           href="tel:<?= htmlspecialchars($clean) ?>">
                                                                                                            <svg viewBox="0 0 24 24"
                                                                                                                 width="24"
                                                                                                                 height="24"
                                                                                                                 fill="none">
                                                                                                                <path d="M3 7.154C3 14.8 9.199 21 16.846 21h2.077c.55 0 1.078-.219 1.468-.608.389-.39.608-.918.608-1.469v-1.266c0-.476-.324-.891-.787-1.007l-4.082-1.02a1.02 1.02 0 0 0-1.083.385l-.895 1.194a1 1 0 0 1-1.117.351 13.96 13.96 0 0 1-4.022-2.571 13.96 13.96 0 0 1-2.572-4.022 1 1 0 0 1 .35-1.117l1.193-.895a1.02 1.02 0 0 0 .2-1.083L7.35 3.786A1.02 1.02 0 0 0 6.343 3H5.077c-.55 0-1.079.219-1.468.608A2.077 2.077 0 0 0 3 5.077V7.154Z"
                                                                                                                      stroke="#0155eb"
                                                                                                                      stroke-width="1.5"
                                                                                                                      stroke-linecap="round"
                                                                                                                      stroke-linejoin="round"></path>
                                                                                                            </svg>
                                                                                                            <span class="branches-phone"><?= htmlspecialchars($phone) ?></span>
                                                                                                        </a>
                                                                                                    <?php endforeach; ?>

                                                                                                    <?php foreach ($emails as $email): ?>
                                                                                                        <a class="d-flex items-center gap-2"
                                                                                                           href="mailto:<?= htmlspecialchars($email) ?>">
                                                                                                            <svg viewBox="0 0 24 24"
                                                                                                                 width="24"
                                                                                                                 height="24"
                                                                                                                 fill="none">
                                                                                                                <path d="M21.75 6.75V17.25A2.25 2.25 0 0 1 19.5 19.5H4.5A2.25 2.25 0 0 1 2.25 17.25V6.75m19.5 0c0-.596-.237-1.168-.659-1.59A2.25 2.25 0 0 0 19.5 4.5H4.5c-.596 0-1.168.237-1.59.659A2.25 2.25 0 0 0 2.25 6.75m19.5 0V6.993c0 .384-.098.762-.285 1.097-.187.336-.457.618-.784.819l-7.5 4.615a2.25 2.25 0 0 1-2.18 0L3.32 8.91A2.25 2.25 0 0 1 2.25 6.994V6.75"
                                                                                                                      stroke="#0155eb"
                                                                                                                      stroke-width="1.5"
                                                                                                                      stroke-linecap="round"
                                                                                                                      stroke-linejoin="round"></path>
                                                                                                            </svg>
                                                                                                            <span class="branches-email"><?= htmlspecialchars($email) ?></span>
                                                                                                        </a>
                                                                                                    <?php endforeach; ?>

                                                                                                    <?php if (!empty($website)): ?>
                                                                                                        <a class="d-flex items-center gap-2"
                                                                                                           href="<?= htmlspecialchars($website) ?>"
                                                                                                           target="_blank"
                                                                                                           rel="nofollow noopener">
                                                                                                            <svg viewBox="0 0 24 24"
                                                                                                                 width="24"
                                                                                                                 height="24"
                                                                                                                 fill="none">
                                                                                                                <path d="M12 21c2 0 3.94-.66 5.51-1.88A8.99 8.99 0 0 0 20.716 14.253M12 21c-2 0-3.94-.66-5.51-1.88A8.99 8.99 0 0 1 3.284 14.253M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3"
                                                                                                                      stroke="#0155eb"
                                                                                                                      stroke-width="1.5"
                                                                                                                      stroke-linecap="round"
                                                                                                                      stroke-linejoin="round"></path>
                                                                                                            </svg>
                                                                                                            <span class="branches-of_site"><?= htmlspecialchars($website) ?></span>
                                                                                                        </a>
                                                                                                    <?php endif; ?>

                                                                                                    <?php if (!empty($socials)): ?>
                                                                                                        <div class="d-flex align-items-center justify-content-start gap-3 mt-1">
                                                                                                            <?php foreach ($socials as $social): ?>
                                                                                                                <a href="<?= htmlspecialchars($social) ?>"
                                                                                                                   target="_blank"
                                                                                                                   rel="nofollow noopener"><i
                                                                                                                            class="bi bi-globe"></i></a>
                                                                                                            <?php endforeach; ?>
                                                                                                        </div>
                                                                                                    <?php endif; ?>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="col background-gray p-4">
                                                                                            <div class="d-flex flex-column gap-3">
                                                                                                <div class="d-flex flex-row gap-2 align-items-center">
                                                                                                    <svg viewBox="0 0 24 24"
                                                                                                         width="24"
                                                                                                         height="24"
                                                                                                         fill="none">
                                                                                                        <path d="M12.333 6V12H16.833M21.333 12A9.333 9.333 0 1 1 3.333 12 9.333 9.333 0 0 1 21.333 12Z"
                                                                                                              stroke="#0155eb"
                                                                                                              stroke-width="1.5"
                                                                                                              stroke-linecap="round"
                                                                                                              stroke-linejoin="round"></path>
                                                                                                    </svg>
                                                                                                    <span class="name-branch"><?= $lang("Рабочие часы") ?></span>
                                                                                                </div>
                                                                                                <div class="d-flex justify-content-between flex-column gap-2">
                                                                                                    <?php
                                                                                                    $dayMapping = ['Пн' => 'Понедельник', 'Вт' => 'Вторник', 'Ср' => 'Среда', 'Чт' => 'Четверг', 'Пт' => 'Пятница', 'Сб' => 'Суббота', 'Вс' => 'Воскресенье'];
                                                                                                    foreach ($dayMapping as $short => $full): ?>
                                                                                                        <span class="d-flex align-items-center justify-content-between">
                                                                                                    <span><?= $lang($full) ?></span>
                                                                                                    <span class="font-bold"><?= htmlspecialchars($wh[$short] ?? $lang('Не указано')) ?></span>
                                                                                                </span>
                                                                                                    <?php endforeach; ?>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <?php if (!empty($prices)): ?>
                                                                                            <div class="col-12 background-gray p-4">
                                                                                                <h6 class="text-primary fw-bold mb-3"><?= $lang('Цены на топливо') ?></h6>
                                                                                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                                                                                    <?php foreach ($prices as $pr): ?>
                                                                                                        <div class="col">
                                                                                                            <div class="price-card bg-white p-3 rounded-3 shadow-sm border border-light">
                                                                                                                <h6 class="text-muted mb-2"><?= $lang(htmlspecialchars($pr['name'])) ?></h6>
                                                                                                                <div class="d-flex align-items-baseline gap-2">
                                                                                                                    <span class="display-6 fw-bold text-success"><?= htmlspecialchars($pr['price']) ?> ֏</span>
                                                                                                                </div>
                                                                                                                <small class="text-secondary"><?= $lang('Обновлено') . ': ' . date('H:i d.m.Y', strtotime($pr['updated_at'])) ?></small>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    <?php endforeach; ?>
                                                                                                </div>
                                                                                            </div>
                                                                                        <?php endif; ?>

                                                                                        <div class="col-md-12 background-gray p-4">
                                                                                            <div id="<?= $mapId ?>"
                                                                                                <?= (!empty($lat) && !empty($lng))
                                                                                                    ? 'data-lat="' . htmlspecialchars($lat) . '" data-lng="' . htmlspecialchars($lng) . '"'
                                                                                                    : 'data-address="' . htmlspecialchars($address, ENT_QUOTES) . '"' ?>
                                                                                                <?= (!$firstPointDone && $regionIndex === 1 && $cityIndex === 1) ? 'data-initial="true"' : '' ?>
                                                                                                 style="width:100%;height:400px;">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <?php
                                                                                    $firstPointDone = true;
                                                                                endforeach;
                                                                            endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

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

<script>
    ymaps.ready(function () {
        const maps = {};
        const initMap = (el) => {
            if (!el || maps[el.id]) return;
            const lat = el.getAttribute('data-lat');
            const lng = el.getAttribute('data-lng');
            const address = el.getAttribute('data-address');

            const create = (coords, balloon) => {
                const map = new ymaps.Map(el.id, {
                    center: coords,
                    zoom: 16,
                    controls: ['zoomControl', 'fullscreenControl']
                });
                map.geoObjects.add(new ymaps.Placemark(coords, {balloonContent: balloon || '<?= $lang('Точка компании') ?>'}, {preset: 'islands#orangeDotIcon'}));
                maps[el.id] = map;
            };

            if (lat && lng) create([parseFloat(lat), parseFloat(lng)], address || '');
            else if (address) ymaps.geocode(address).then(res => {
                const obj = res.geoObjects.get(0);
                if (!obj) return;
                create(obj.geometry.getCoordinates(), address);
            });
        };

        // первая карта
        setTimeout(() => {
            const initial = document.querySelector('[data-initial="true"]');
            if (initial) initMap(initial);
        }, 300);

        // ленивое создание/удаление при раскрытии
        document.querySelectorAll('.accordion-collapse').forEach(acc => {
            acc.addEventListener('shown.bs.collapse', () => {
                const el = acc.querySelector('[id^="yandex-map-"]');
                if (el) initMap(el);
            });
            acc.addEventListener('hide.bs.collapse', () => {
                const el = acc.querySelector('[id^="yandex-map-"]');
                if (el && maps[el.id]) {
                    maps[el.id].destroy();
                    delete maps[el.id];
                }
            });
        });
    });
</script>
