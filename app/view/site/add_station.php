<section class="content">
    <div class="container dashboard-container modern">

        <div class="d-flex align-items-center gap-4 gap-md-5 mb-4">
            <a href="javascript:history.back()">
                <img src="img/back-circle.svg" alt="Иконка назад" class="back-arrow">
            </a>
            <h2 class="m-0"><?= $lang('Добавление заправки'); ?></h2>
        </div>

        <div class="d-flex gap-2 my-4 justify-content-around">
            <a class="btn rounded-pill p-3 col <?= $mode === 'driver' ? 'btn-primary blue-btn' : 'btn-light gray-btn' ?>"
               href="/add-station?mode=driver"><?= $lang('Я водитель'); ?></a>
            <a class="btn rounded-pill p-3 col <?= $mode === 'owner' ? 'btn-primary blue-btn' : 'btn-light gray-btn' ?>"
               href="/add-station?mode=owner"><?= $lang('Я владелец заправки'); ?></a>
        </div>

        <?php if (!empty($ok)): ?>
            <div class="alert alert-success">
                <?= $lang('Заявка отправлена. Запись появится на сайте'); ?> <b><?= $lang('после модерации'); ?></b>.
                <?php if ($mode === 'owner'): ?>
                    <br><?= $lang('Чтобы получить логин и пароль для личного кабинета владельца — напишите на'); ?> <a
                            href="mailto:selmidis.com@gmail.com">selmidis.com@gmail.com</a>.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="/add-station?mode=<?= htmlspecialchars($mode) ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="submit_station" value="1">
            <input type="hidden" id="company_region_id" name="company_region_id" value="">
            <input type="hidden" id="company_city_id" name="company_city_id" value="">
            <input type="hidden" id="company_latitude" name="company_latitude" value="40.1772">
            <input type="hidden" id="company_longitude" name="company_longitude" value="44.5035">

            <!-- 1. Установка цен -->
            <div class="card-section" data-section="fuel-prices">
                <h3 class="section-toggle blue-background" onclick="toggleSection('fuel-prices')">
                    <span>1. <?= $lang('Установка цен'); ?></span><img src="/img/arrow-down.svg" alt="">
                </h3>
                <div class="section-content">
                    <div class="price-table">
                        <div class="price-header">
                            <div><?= $lang('топливо'); ?></div>
                            <div><?= $lang('ваша цена'); ?></div>
                            <div><?= $lang('лучшая цена'); ?></div>
                            <div></div>
                        </div>
                        <div id="fuelPricesGroup" class="price-rows">
                            <div class="price-row">
                                <select class="form-select fuel-type" name="fuel_type[]" required>
                                    <?php foreach ($fuelTypes as $opt): ?>
                                        <option value="<?= (int)$opt['id'] ?>"><?= htmlspecialchars($opt['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" class="form-control fuel-price" name="fuel_price[]"
                                       placeholder="<?= $lang('Цена в AMD'); ?>" step="0.01" required>
                                <div class="best-cell"><span class="best-price"><?= $bestPrices[1]; ?></span></div>
                                <img src="/img/close-red.svg" class="remove-btn" alt="<?= $lang('Удалить'); ?>">
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <button type="button" class="add-btn"
                                    id="addFuelRow"><?= $lang('Добавить топливо'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-section" data-section="company-core">
                <h3 class="section-toggle blue-background" onclick="toggleSection('company-core')">
                    <span>2. <?= $lang('Данные про компанию'); ?></span><img src="/img/arrow-down.svg" alt="">
                </h3>
                <div class="section-content">
                    <div class="mb-3 form-group">
                        <label class="form-label"><?= $lang('Название компании'); ?></label>
                        <input type="text" class="form-control" name="name"
                               placeholder="<?= $lang('Укажите название'); ?>" required>
                    </div>

                    <div class="mb-3 position-relative form-group">
                        <label class="form-label"><?= $lang('Локация на карте'); ?></label>
                        <input type="text" class="form-control" id="companyAddress" name="company_address"
                               placeholder="<?= $lang('Введите на карте или начните ввод'); ?>">
                        <div id="company-sugg-container" class="search-form-suggestions-container hide">
                            <div id="company-loading" class="loading-indicator" style="display:none;">
                                <div class="spinner"></div>
                            </div>
                            <ul id="company-suggestions-list" class="search-form-suggestions"></ul>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="map-container" id="mapCompany"></div>
                    </div>
                </div>
            </div>

            <!-- 3. Детальная информация -->
            <div class="card-section collapsed" data-section="point-details">
                <h3 class="section-toggle blue-background" onclick="toggleSection('point-details')">
                    <span>3. <?= $lang('Детальная информация'); ?></span><img src="/img/arrow-down.svg" alt="">
                </h3>
                <div class="section-content">
                    <?php if ($mode === 'driver'): ?>
                        <div class="alert alert-info">
                            <?= $lang('Этот блок может редактировать только'); ?>
                            <b><?= $lang('владелец заправки'); ?></b>.
                            <?= $lang('Переключитесь на «Я владелец заправки», если вы действительно владелец'); ?>.
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label><?= $lang('Телефоны'); ?></label>
                            <div class="dynamic-group" id="phoneGroup">
                            </div>
                            <button type="button" class="add-btn"
                                    onclick="addField('phoneGroup','phones[]','+374 00 000 000')"><?= $lang('Добавить'); ?>
                            </button>
                        </div>

                        <div class="form-group">
                            <label><?= $lang('Email-ы'); ?></label>
                            <div class="dynamic-group" id="emailGroup">
                                <div class="input-group mb-1">
                                </div>
                            </div>
                            <button type="button" class="add-btn"
                                    onclick="addField('emailGroup','emails[]','info@company.com')"><?= $lang('Добавить'); ?>
                            </button>
                        </div>

                        <div class="form-group">
                            <label><?= $lang('Социальные сети'); ?></label>
                            <div class="dynamic-group" id="socialGroup">
                                <div class="input-group mb-1">
                                </div>
                            </div>
                            <button type="button" class="add-btn"
                                    onclick="addField('socialGroup','socials[]','https://instagram.com/company')">
                                <?= $lang('Добавить'); ?>
                            </button>
                        </div>

                        <div class="form-group">
                            <label><?= $lang('График работы'); ?></label>
                            <small class="form-text text-muted"><?= $lang('Добавьте только нужные дни'); ?></small>
                            <div class="working-hours" id="workingHoursGroup">
                                <?php foreach (['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $d): ?>
                                    <div class="working-row">
                                        <select class="form-select" name="working_days[]">
                                            <option value="<?= $d ?>"><?= $lang($d); ?></option>
                                        </select>
                                        <input type="text" class="form-control" name="working_times[]"
                                               placeholder="9:00-18:00">
                                        <img src="/img/close-red.svg" class="remove-btn"
                                             onclick="this.parentElement.remove()">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?= $lang('Сайт'); ?></label>
                            <input type="text" class="form-control" name="website" placeholder="https://…">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary btn-lg save-button"><?= $lang('Отправить'); ?></button>
                <div class="text-muted mt-2" style="font-size:13px">
                    <?= $lang('После отправки ваша заявка попадёт на модерацию. Публикация произойдёт после проверки'); ?>.
                    <?php if ($mode === 'owner'): ?>
                        <br><?= $lang('Хотите получить логин и пароль владельца? Пишите на'); ?> <a href="mailto:selmidis.com@gmail.com">selmidis.com@gmail.com</a>.
                    <?php endif; ?>
                </div>
            </div>
        </form>

    </div>

    <!-- кусок CSS + JS можно переиспользовать из company_dashboard.php -->
    <style>
        .map-container {
            width: 100%;
            height: 340px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-top: 8px
        }

        @media (max-width: 768px) {
            .map-container {
                height: 260px
            }
        }
    </style>

    <script src="/js/imask.min.js"></script>
    <script>
        function toggleSection(id) {
            const s = document.querySelector(`[data-section="${id}"]`);
            if (s) s.classList.toggle('collapsed');
        }

        function addField(groupId, name, ph) {
            const g = document.getElementById(groupId);
            const d = document.createElement('div');
            const isPhone = name === 'phones[]';
            d.className = 'input-group mb-1';
            d.innerHTML = `<input type="text" class="form-control ${isPhone ? 'phone-mask' : ''}" name="${name}" placeholder="${ph}"><img src="/img/close-red.svg" class="remove-btn" onclick="this.parentElement.remove()">`;
            g.appendChild(d);
            if (isPhone) {
                IMask(d.querySelector('input'), {mask: '+{374} 00 000 000', lazy: false});
            }
        }

        const FUEL_TYPES = <?= json_encode(array_values($fuelTypes), JSON_UNESCAPED_UNICODE) ?>;
        const BEST_PRICES = <?= json_encode($bestPrices, JSON_UNESCAPED_UNICODE) ?>;

        // блок цен
        const rowsWrap = document.getElementById('fuelPricesGroup');

        function updateBestForRow(row) {
            const sel = row.querySelector('.fuel-type');
            const sp = row.querySelector('.best-price');
            const id = String(sel.value);
            const v = (BEST_PRICES && BEST_PRICES[id] !== undefined) ? BEST_PRICES[id] : 'N/A';
            sp.textContent = (v && v !== 'N/A') ? v : '—';
            sp.style.color = (v && v !== 'N/A') ? '#22C55E' : '#6b7280';
            sp.style.fontWeight = (v && v !== 'N/A') ? '700' : '400';
        }

        function refreshSelectStates() {
            const sels = [...rowsWrap.querySelectorAll('.fuel-type')];
            const used = new Set(sels.map(s => String(s.value)));
            sels.forEach(sel => {
                [...sel.options].forEach(o => {
                    o.disabled = used.has(String(o.value)) && String(o.value) !== String(sel.value);
                });
            });
        }

        function createSelect() {
            const s = document.createElement('select');
            s.name = 'fuel_type[]';
            s.required = true;
            s.className = 'form-select fuel-type';
            FUEL_TYPES.forEach(ft => {
                const o = document.createElement('option');
                o.value = ft.id;
                o.textContent = ft.name;
                s.appendChild(o);
            });
            return s;
        }

        function addRow() {
            const row = document.createElement('div');
            row.className = 'price-row';
            const sel = createSelect();
            const inp = document.createElement('input');
            inp.type = 'number';
            inp.name = 'fuel_price[]';
            inp.step = '0.01';
            inp.required = true;
            inp.placeholder = "<?= $lang('Цена в AMD'); ?>";
            inp.className = 'form-control fuel-price';
            const best = document.createElement('div');
            best.className = 'best-cell';
            best.innerHTML = '<span class="best-price">—</span>';
            const del = document.createElement('img');
            del.className = 'remove-btn';
            del.src = '/img/close-red.svg';
            del.alt = "<?= $lang('Удалить'); ?>";
            row.append(sel, inp, best, del);
            rowsWrap.appendChild(row);
            sel.addEventListener('change', () => {
                updateBestForRow(row);
                refreshSelectStates();
            });
            del.addEventListener('click', () => {
                row.remove();
                refreshSelectStates();
            });
            updateBestForRow(row);
            refreshSelectStates();
        }

        rowsWrap.querySelector('.price-row .fuel-type')?.addEventListener('change', function () {
            updateBestForRow(this.closest('.price-row'));
            refreshSelectStates();
        });
        rowsWrap.querySelector('.price-row .remove-btn')?.addEventListener('click', function () {
            this.closest('.price-row').remove();
            refreshSelectStates();
        });
        document.getElementById('addFuelRow')?.addEventListener('click', addRow);
    </script>

    <!-- Яндекс-карты и поиск адреса (короткая версия из company_dashboard.php) -->
    <script>
        const CITIES = <?= json_encode($cities, JSON_UNESCAPED_UNICODE) ?>;
        const REGIONS = <?= json_encode($regions, JSON_UNESCAPED_UNICODE) ?>;

        function setRegionCityFromGeoObject(geoObject) {
            try {
                const comps = geoObject?.properties?.get('metaDataProperty')?.GeocoderMetaData?.Address?.Components || [];
                let regionName = null, cityName = null;
                comps.forEach(c => {
                    if (c.kind === 'province' || c.kind === 'area') regionName = c.name;
                    if (c.kind === 'locality') cityName = c.name;
                });
                let regionId = null, cityId = null;
                if (cityName) {
                    const cc = CITIES.filter(x => x.city_name.toLowerCase() === cityName.toLowerCase());
                    if (cc.length) {
                        cityId = cc[0].id;
                        regionId = cc[0].region_id;
                    }
                }
                if (!regionId && regionName) {
                    const r = REGIONS.find(x => x.city_name.toLowerCase() === regionName.toLowerCase());
                    if (r) regionId = r.id;
                }
                document.getElementById('company_region_id').value = regionId || '';
                document.getElementById('company_city_id').value = cityId || '';
            } catch (e) {
                console.warn(e);
            }
        }

        ymaps.ready(function () {
            let lat = parseFloat(document.getElementById('company_latitude').value || '40.1772');
            let lng = parseFloat(document.getElementById('company_longitude').value || '44.5035');
            if (isNaN(lat) || isNaN(lng)) {
                lat = 40.1772;
                lng = 44.5035;
            }
            const map = new ymaps.Map('mapCompany', {
                center: [lat, lng],
                zoom: 12,
                controls: ['zoomControl', 'geolocationControl']
            });
            let placemark = new ymaps.Placemark([lat, lng], {}, {draggable: true});
            map.geoObjects.add(placemark);

            const $addr = document.getElementById('companyAddress');
            const $wrap = document.getElementById('company-sugg-container');
            const $list = document.getElementById('company-suggestions-list');
            const $load = document.getElementById('company-loading');
            const cache = {};
            const show = () => {
                $wrap.classList.remove('hide');
                $wrap.classList.add('show');
            };
            const hide = () => {
                $wrap.classList.remove('show');
                $wrap.classList.add('hide');
            };
            const on = () => {
                $load.style.display = 'block';
            };
            const off = () => {
                $load.style.display = 'none';
            };

            function debounce(fn, t) {
                let tm;
                return (...a) => {
                    clearTimeout(tm);
                    tm = setTimeout(() => fn(...a), t);
                };
            }

            function updateByCoords(coords) {
                document.getElementById('company_latitude').value = coords[0];
                document.getElementById('company_longitude').value = coords[1];
                ymaps.geocode(coords).then(res => {
                    const o = res.geoObjects.get(0);
                    if (o) {
                        $addr.value = o.getAddressLine();
                        setRegionCityFromGeoObject(o);
                    }
                });
            }

            map.events.add('click', e => {
                const coords = e.get('coords');
                placemark.geometry.setCoordinates(coords);
                updateByCoords(coords);
            });
            placemark.events.add('dragend', () => {
                updateByCoords(placemark.geometry.getCoordinates());
            });

            $addr.addEventListener('input', debounce(async function () {
                const q = $addr.value.trim();
                if (q.length < 3) {
                    hide();
                    return;
                }
                if (cache[q]) {
                    render(cache[q]);
                    return;
                }
                try {
                    on();
                    const bbox = '43.4,38.8~46.6,41.3';
                    const resp = await fetch(`https://geocode-maps.yandex.ru/1.x/?apikey=93e925f4-cf14-4f88-b5a7-38bbb050f665&format=json&lang=ru_RU&geocode=${encodeURIComponent(q)}&bbox=${bbox}&rspn=1&kind=house`);
                    const data = await resp.json();
                    const arr = (data.response?.GeoObjectCollection?.featureMember) || [];
                    const items = arr.map(it => ({
                        full: it.GeoObject.metaDataProperty.GeocoderMetaData.text,
                        coords: it.GeoObject.Point.pos.split(' ').map(Number).reverse(),
                        geo: it.GeoObject
                    }));
                    cache[q] = items;
                    render(items);
                } catch (e) {
                    console.error(e);
                } finally {
                    off();
                }
            }, 300));

            function render(items) {
                $list.innerHTML = '';
                if (!items.length) {
                    $list.innerHTML = '<li>Нет подходящих адресов</li>';
                    show();
                    return;
                }
                items.forEach(it => {
                    const li = document.createElement('li');
                    li.textContent = it.full;
                    li.addEventListener('click', () => {
                        $addr.value = it.full;
                        hide();
                        document.getElementById('company_latitude').value = it.coords[0];
                        document.getElementById('company_longitude').value = it.coords[1];
                        placemark.geometry.setCoordinates(it.coords);
                        map.setCenter(it.coords, 17);
                        const fake = new ymaps.GeoObject({geometry: {type: 'Point', coordinates: it.coords}});
                        fake.properties.set('metaDataProperty', it.geo.metaDataProperty);
                        setRegionCityFromGeoObject(fake);
                    });
                    $list.appendChild(li);
                });
                show();
            }
        });
    </script>
</section>