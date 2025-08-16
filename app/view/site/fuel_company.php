<section class="content">
    <div class="container fuel-companion">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3 gap-lg-4 mb-4">
            <div>
                <a href="/"
                   class="btn btn-light d-flex align-items-center gap-2 px-3 py-2 rounded">
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
                    <div class="row justify-content-between flex-wrap gap-3 p-3">
                        <div class="col background-gray p-4">
                            <div class="d-flex gap-4 flex-column">
                                <div class="d-flex flex-row gap-3 align-items-center">
                                    <img src="img/fuel/<?= htmlspecialchars($fuelCompanyInfo['logo']) ?>"
                                         alt="<?= htmlspecialchars($fuelCompanyInfo['name']) ?>"
                                         style="width: 64px; height: 64px;" class="rounded-3">
                                    <div class="d-flex flex-column gap-2 align-items-start">
                                        <span class="name-branch"><?= htmlspecialchars($fuelCompanyInfo['name']) ?></span>
                                        <div class="d-flex flex-row gap-2 align-items-center">
                                            <svg viewBox="0 0 24 24" width="40px" height="40px" fill="none">
                                                <path d="M15 10.5C15 11.2956 14.6839 12.0587 14.1213 12.6213C13.5587 13.1839 12.7956 13.5 12 13.5C11.2044 13.5 10.4413 13.1839 9.87868 12.6213C9.31607 12.0587 9 11.2956 9 10.5C9 9.70435 9.31607 8.94129 9.87868 8.37868C10.4413 7.81607 11.2044 7.5 12 7.5C12.7956 7.5 13.5587 7.81607 14.1213 8.37868C14.6839 8.94129 15 9.70435 15 10.5Z"
                                                      stroke="#0155eb" stroke-width="1.5" stroke-linecap="round"
                                                      stroke-linejoin="round"></path>
                                                <path d="M19.5 10.5C19.5 17.642 12 21.75 12 21.75C12 21.75 4.5 17.642 4.5 10.5C4.5 8.51088 5.29018 6.60322 6.6967 5.1967C8.10322 3.79018 10.0109 3 12 3C13.9891 3 15.8968 3.79018 17.3033 5.1967C18.7098 6.60322 19.5 8.51088 19.5 10.5Z"
                                                      stroke="#0155eb" stroke-width="1.5" stroke-linecap="round"
                                                      stroke-linejoin="round"></path>
                                            </svg>
                                            <span class="branches-address"><?= htmlspecialchars($address) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($phones as $phone): ?>
                                        <?php
                                        // Удаляем пробелы и плюс из номера телефона для href
                                        $cleanPhone = preg_replace('/[\s\+]/', '', $phone);
                                        ?>
                                        <a class="d-flex items-center gap-2"
                                           href="tel:<?= htmlspecialchars($cleanPhone) ?>">
                                            <svg viewBox="0 0 24 24" width="24px" height="24px" fill="none">
                                                <path d="M3 7.15385C3 14.8006 9.19938 21 16.8462 21H18.9231C19.4739 21 20.0022 20.7812 20.3917 20.3917C20.7812 20.0022 21 19.4739 21 18.9231V17.6566C21 17.1803 20.676 16.7649 20.2135 16.6495L16.1308 15.6286C15.7246 15.5271 15.2982 15.6794 15.048 16.0135L14.1526 17.2071C13.8923 17.5542 13.4428 17.7074 13.0357 17.5578C11.5245 17.0023 10.1521 16.1249 9.01364 14.9864C7.87515 13.8479 6.99773 12.4755 6.44215 10.9643C6.29262 10.5572 6.44585 10.1077 6.79292 9.84738L7.98646 8.952C8.32154 8.70185 8.47292 8.27446 8.37138 7.86923L7.35046 3.78646C7.29428 3.56187 7.16467 3.3625 6.9822 3.22C6.79974 3.07751 6.57489 3.00008 6.34338 3H5.07692C4.52609 3 3.99782 3.21882 3.60832 3.60832C3.21882 3.99782 3 4.52609 3 5.07692V7.15385Z"
                                                      stroke="#0155eb" stroke-width="1.5" stroke-linecap="round"
                                                      stroke-linejoin="round"></path>
                                            </svg>
                                            <span class="branches-phone"><?= htmlspecialchars($phone) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php foreach ($emails as $email): ?>
                                        <a class="d-flex items-center gap-2"
                                           href="mailto:<?= htmlspecialchars($email) ?>">
                                            <svg viewBox="0 0 24 24" width="24px" height="24px" fill="none">
                                                <path d="M21.75 6.75V17.25C21.75 17.8467 21.5129 18.419 21.091 18.841C20.669 19.2629 20.0967 19.5 19.5 19.5H4.5C3.90326 19.5 3.33097 19.2629 2.90901 18.841C2.48705 18.419 2.25 17.8467 2.25 17.25V6.75M21.75 6.75C21.75 6.15326 21.5129 5.58097 21.091 5.15901C20.669 4.73705 20.0967 4.5 19.5 4.5H4.5C3.90326 4.5 3.33097 4.73705 2.90901 5.15901C2.48705 5.58097 2.25 6.15326 2.25 6.75M21.75 6.75V6.993C21.75 7.37715 21.6517 7.75491 21.4644 8.0903C21.2771 8.42569 21.0071 8.70754 20.68 8.909L13.18 13.524C12.8252 13.7425 12.4167 13.8582 12 13.8582C11.5833 13.8582 11.1748 13.7425 10.82 13.524L3.32 8.91C2.99292 8.70854 2.72287 8.42669 2.53557 8.0913C2.34827 7.75591 2.24996 7.37815 2.25 6.994V6.75"
                                                      stroke="#0155eb" stroke-width="1.5" stroke-linecap="round"
                                                      stroke-linejoin="round"></path>
                                            </svg>
                                            <span class="branches-email"><?= htmlspecialchars($email) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (!empty($website)): ?>
                                        <a class="d-flex items-center gap-2" href="<?= htmlspecialchars($website) ?>"
                                           target="_blank">
                                            <svg viewBox="0 0 24 24" width="24px" height="24px" fill="none">
                                                <path d="M12 21C13.995 20.9999 15.9335 20.3372 17.511 19.116C19.0886 17.8948 20.2159 16.1843 20.716 14.253M12 21C10.005 20.9999 8.06654 20.3372 6.48898 19.116C4.91141 17.8948 3.78408 16.1843 3.284 14.253M12 21C14.485 21 16.5 16.97 16.5 12C16.5 7.03 14.485 3 12 3M12 21C9.515 21 7.5 16.97 7.5 12C7.5 7.03 9.515 3 12 3M20.716 14.253C20.901 13.533 21 12.778 21 12C21.0025 10.4521 20.6039 8.92999 19.843 7.582M20.716 14.253C18.0492 15.7314 15.0492 16.5048 12 16.5C8.838 16.5 5.867 15.685 3.284 14.253M3.284 14.253C3.09475 13.517 2.99933 12.76 3 12C3 10.395 3.42 8.887 4.157 7.582M12 3C13.5962 2.99933 15.1639 3.42336 16.5422 4.22856C17.9205 5.03377 19.0597 6.19117 19.843 7.582M12 3C10.4038 2.99933 8.83608 3.42336 7.45781 4.22856C6.07954 5.03377 4.94031 6.19117 4.157 7.582"
                                                      stroke="#0155eb" stroke-width="1.5" stroke-linecap="round"
                                                      stroke-linejoin="round"></path>
                                            </svg>
                                            <span class="branches-of_site"><?= htmlspecialchars($website) ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($socials)): ?>
                                        <div class="d-flex align-items-center justify-content-start gap-3 mt-3">
                                            <?php foreach ($socials as $social): ?>
                                                <?php
                                                $icon = '';
                                                switch (true) {
                                                    case strpos($social, 'instagram.com') !== false:
                                                        $icon = '<svg viewBox="0 0 24 24" width="24px" height="24px" fill="none"><path d="M16.517 2H8.447C6.87015 2.00185 5.35844 2.62914 4.24353 3.74424C3.12862 4.85933 2.50159 6.37115 2.5 7.948L2.5 16.018C2.50185 17.5948 3.12914 19.1066 4.24424 20.2215C5.35933 21.3364 6.87115 21.9634 8.448 21.965H16.518C18.0948 21.9631 19.6066 21.3359 20.7215 20.2208C21.8364 19.1057 22.4634 17.5938 22.465 16.017V7.947C22.4631 6.37015 21.8359 4.85844 20.7208 3.74353C19.6057 2.62862 18.0938 2.00159 16.517 2V2ZM20.457 16.017C20.457 16.5344 20.3551 17.0468 20.1571 17.5248C19.9591 18.0028 19.6689 18.4371 19.303 18.803C18.9371 19.1689 18.5028 19.4591 18.0248 19.6571C17.5468 19.8551 17.0344 19.957 16.517 19.957H8.447C7.40222 19.9567 6.40032 19.5415 5.66165 18.8026C4.92297 18.0638 4.508 17.0618 4.508 16.017V7.947C4.50827 6.90222 4.92349 5.90032 5.66235 5.16165C6.40122 4.42297 7.40322 4.008 8.448 4.008H16.518C17.5628 4.00827 18.5647 4.42349 19.3034 5.16235C20.042 5.90122 20.457 6.90322 20.457 7.948V16.018V16.017Z" fill="#1f1f1f"></path><path d="M12.482 6.81897C11.1134 6.82109 9.80154 7.36576 8.83391 8.33358C7.86627 9.3014 7.32186 10.6134 7.32001 11.982C7.32159 13.3509 7.86603 14.6633 8.83391 15.6314C9.80179 16.5994 11.1141 17.1441 12.483 17.146C13.8521 17.1444 15.1647 16.5998 16.1328 15.6317C17.1008 14.6636 17.6454 13.3511 17.647 11.982C17.6449 10.6131 17.0999 9.30085 16.1317 8.33316C15.1634 7.36547 13.8509 6.82129 12.482 6.81997V6.81897ZM12.482 15.138C11.6452 15.138 10.8428 14.8056 10.2511 14.2139C9.65941 13.6222 9.32701 12.8197 9.32701 11.983C9.32701 11.1462 9.65941 10.3437 10.2511 9.75205C10.8428 9.16037 11.6452 8.82797 12.482 8.82797C13.3188 8.82797 14.1213 9.16037 14.7129 9.75205C15.3046 10.3437 15.637 11.1462 15.637 11.983C15.637 12.8197 15.3046 13.6222 14.7129 14.2139C14.1213 14.8056 13.3188 15.138 12.482 15.138Z" fill="#1f1f1f"></path><path d="M17.656 8.09497C18.3392 8.09497 18.893 7.54115 18.893 6.85797C18.893 6.1748 18.3392 5.62097 17.656 5.62097C16.9728 5.62097 16.419 6.1748 16.419 6.85797C16.419 7.54115 16.9728 8.09497 17.656 8.09497Z" fill="#1f1f1f"></path></svg>';
                                                        break;
                                                    case strpos($social, 'facebook.com') !== false:
                                                        $icon = '<svg viewBox="0 0 24 24" width="24px" height="24px" fill="none"><path d="M9.54601 5.865V8.613H7.53201V11.973H9.54601V21.959H13.68V11.974H16.455C16.455 11.974 16.715 10.363 16.841 8.601H13.697V6.303C13.697 5.96 14.147 5.498 14.593 5.498H16.847V2H13.783C9.44301 2 9.54601 5.363 9.54601 5.865Z" fill="#1f1f1f"></path></svg>';
                                                        break;
                                                    case strpos($social, 'linkedin.com') !== false:
                                                        $icon = '<svg viewBox="0 0 24 24" width="24px" height="24px" fill="none"><path d="M22.459 13.719V21.098H18.181V14.213C18.181 12.483 17.562 11.303 16.014 11.303C14.832 11.303 14.128 12.099 13.819 12.868C13.706 13.143 13.677 13.526 13.677 13.911V21.098H9.397C9.397 21.098 9.455 9.438 9.397 8.229H13.677V10.053L13.649 10.095H13.677V10.053C14.245 9.178 15.26 7.927 17.533 7.927C20.348 7.927 22.459 9.767 22.459 13.719ZM4.921 2.026C3.458 2.026 2.5 2.986 2.5 4.249C2.5 5.484 3.43 6.473 4.865 6.473H4.893C6.386 6.473 7.313 5.484 7.313 4.249C7.287 2.986 6.387 2.026 4.922 2.026H4.921ZM2.754 21.098H7.032V8.229H2.754V21.098Z" fill="#1f1f1f"></path></svg>';
                                                        break;
                                                    case strpos($social, 'twitter.com') !== false:
                                                        $icon = '<svg viewBox="0 0 24 24" width="24px" height="24px" fill="none"><path d="M14.2145 10.6226L20.9161 3H19.3282L13.5066 9.61719L8.86025 3H3.5L10.5278 13.0073L3.5 21H5.08786L11.2319 14.0104L16.1398 21H21.5L14.2145 10.6226ZM12.039 13.0951L11.3258 12.098L5.66048 4.17132H8.09976L12.6732 10.5708L13.3834 11.5679L19.3275 19.8857H16.8882L12.039 13.0951Z" fill="#1f1f1f"></path></svg>';
                                                        break;
                                                    default:
                                                        $icon = '<i class="bi bi-globe"></i>';
                                                }
                                                ?>
                                                <a href="<?= htmlspecialchars($social) ?>" target="_blank">
                                                    <?= $icon ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="ya-share2 d-flex justify-content-end" data-curtain
                                         data-color-scheme="blackwhite" data-limit="0" data-more-button-type="short"
                                         data-services="vkontakte,telegram,twitter,whatsapp"></div>

                                </div>
                            </div>
                        </div>

                        <div class="col background-gray p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex flex-row gap-2 align-items-center">
                                    <svg viewBox="0 0 24 24" width="24px" height="24px" fill="none">
                                        <path d="M12.333 6V12H16.833M21.333 12C21.333 13.1819 21.1002 14.3522 20.6479 15.4442C20.1956 16.5361 19.5327 17.5282 18.697 18.364C17.8612 19.1997 16.8691 19.8626 15.7772 20.3149C14.6852 20.7672 13.5149 21 12.333 21C11.1511 21 9.98079 20.7672 8.88886 20.3149C7.79693 19.8626 6.80477 19.1997 5.96905 18.364C5.13332 17.5282 4.47038 16.5361 4.01809 15.4442C3.5658 14.3522 3.33301 13.1819 3.33301 12C3.33301 9.61305 4.28122 7.32387 5.96905 5.63604C7.65687 3.94821 9.94606 3 12.333 3C14.72 3 17.0091 3.94821 18.697 5.63604C20.3848 7.32387 21.333 9.61305 21.333 12Z"
                                              stroke="#0155eb" stroke-width="1.5" stroke-linecap="round"
                                              stroke-linejoin="round"></path>
                                    </svg>
                                    <span class="name-branch"><?= $lang("Рабочие часы") ?></span>
                                </div>
                                <div class="d-flex justify-content-between flex-column gap-2">
                                    <?php
                                    $dayMapping = [
                                        'Пн' => 'Понедельник',
                                        'Вт' => 'Вторник',
                                        'Ср' => 'Среда',
                                        'Чт' => 'Четверг',
                                        'Пт' => 'Пятница',
                                        'Сб' => 'Суббота',
                                        'Вс' => 'Воскресенье'
                                    ];
                                    foreach ($dayMapping as $shortDay => $fullDay): ?>
                                        <span class="d-flex align-items-center justify-content-between">
                    <span><?= $lang(htmlspecialchars($fullDay)) ?></span>
                    <span class="font-bold"><?= htmlspecialchars($workingHours[$shortDay] ?? $lang('Не указано')) ?></span>
                </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 background-gray p-4">
                            <h4 class="text-primary fw-bold mb-3"><?= $lang('Цены на топливо') ?></h4>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                <?php foreach ($fuelPrices as $price): ?>
                                    <div class="col">
                                        <div class="price-card bg-white p-3 rounded-3 shadow-sm border border-light">
                                            <h6 class="text-muted mb-2"><?= $lang(htmlspecialchars($price['name'])) ?></h6>
                                            <div class="d-flex align-items-baseline gap-2">
                                                <span class="display-6 fw-bold text-success"><?= htmlspecialchars($price['price']) ?> ֏</span>
                                            </div>
                                            <small class="text-secondary"><?= $lang('Обновлено') . ": " . date('H:i d.m.Y', strtotime($price['updated_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($fuelPrices)): ?>
                                    <div class="col-12 text-center text-muted"><?= $lang('Цены не указаны') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-12 background-gray p-4 mt-3">
                            <?php
                            $mapId = 'yandex-map-' . md5($address);
                            $hasCoords = !empty($fuelCompanyInfo['latitude']) && !empty($fuelCompanyInfo['longitude']);
                            ?>
                            <div id="<?= $mapId ?>"
                                <?= $hasCoords
                                    ? 'data-lat="' . htmlspecialchars($fuelCompanyInfo['latitude']) . '" data-lng="' . htmlspecialchars($fuelCompanyInfo['longitude']) . '"'
                                    : 'data-address="' . htmlspecialchars($address, ENT_QUOTES) . '"' ?>
                                 style="width: 100%; height: 400px;">
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
    </div>
</section>

<script>
    ymaps.ready(function () {
        const mapContainer = document.querySelector('[id^="yandex-map"]');
        if (!mapContainer) return;

        const lat = mapContainer.getAttribute('data-lat');
        const lng = mapContainer.getAttribute('data-lng');
        const address = mapContainer.getAttribute('data-address');

        if (lat && lng) {
            const coords = [parseFloat(lat), parseFloat(lng)];
            const map = new ymaps.Map(mapContainer.id, {
                center: coords,
                zoom: 16,
                controls: ['zoomControl', 'fullscreenControl']
            });
            const placemark = new ymaps.Placemark(coords, {
                balloonContent: address || 'Топливная компания'
            }, {
                preset: 'islands#orangeDotIcon'
            });
            map.geoObjects.add(placemark);
        } else if (address) {
            ymaps.geocode(address).then(function (res) {
                const coords = res.geoObjects.get(0).geometry.getCoordinates();
                const map = new ymaps.Map(mapContainer.id, {
                    center: coords,
                    zoom: 16,
                    controls: ['zoomControl', 'fullscreenControl']
                });
                const placemark = new ymaps.Placemark(coords, {
                    balloonContent: address
                }, {
                    preset: 'islands#orangeDotIcon'
                });
                map.geoObjects.add(placemark);
            }, function (err) {
                console.error('Ошибка геокодирования:', err);
            });
        }
    });
</script>