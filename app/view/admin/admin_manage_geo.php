<section class="content">
    <div class="container geo">
        <?php
        // сгруппируем города по региону
        $citiesByRegion = [];
        foreach (($cities ?? []) as $c) { $citiesByRegion[$c['region_id']][] = $c; }
        ?>

        <header class="geo-header">
            <div>
                <h1>Регионы и города</h1>
            </div>
            <div class="geo-actions">
                <a href="#" id="btnShowRegion" class="btn btn-primary-light">+ Регион</a>
                <a href="#" id="btnShowCity" class="btn btn-primary">+ Город</a>
            </div>
        </header>

        <!-- СОЗДАТЬ РЕГИОН (скрыто) -->
        <div class="card full hidden" id="createRegionCard">
            <div class="card-head row-between">
                <h3>Создать регион</h3>
                <button class="btn btn-secondary" id="btnHideRegion">Закрыть</button>
            </div>
            <form action="/admin/manage-geo" method="POST" class="card-body form-grid-2">
                <label class="form-field">
                    <span>Slug</span>
                    <input type="text" name="slug" class="form-control" placeholder="например, kotayk">
                    <small class="hint">Если пусто — slug сформируется автоматически из ENG.</small>
                </label>
                <label class="form-field">
                    <span>Название (RU)</span>
                    <input type="text" name="name_ru" class="form-control" required>
                </label>
                <label class="form-field">
                    <span>Название (HY)</span>
                    <input type="text" name="name_hy" class="form-control" required>
                </label>
                <label class="form-field">
                    <span>Название (ENG)</span>
                    <input type="text" name="name_eng" class="form-control" required>
                </label>
                <div class="form-actions">
                    <button type="submit" name="create_region" class="btn btn-primary">Создать регион</button>
                </div>
            </form>
        </div>

        <!-- СОЗДАТЬ ГОРОД (скрыто) -->
        <div class="card full hidden" id="createCityCard">
            <div class="card-head row-between">
                <h3>Создать город</h3>
                <button class="btn btn-secondary" id="btnHideCity">Закрыть</button>
            </div>
            <form action="/admin/manage-geo" method="POST" class="card-body form-grid-3">
                <label class="form-field">
                    <span>Регион</span>
                    <select name="region_id" class="form-control" required>
                        <option value="">— выберите регион —</option>
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name_ru']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="form-field">
                    <span>Slug</span>
                    <input type="text" name="slug" class="form-control" placeholder="например, gyumri">
                    <small class="hint">Если пусто — slug сформируется автоматически из ENG.</small>
                </label>
                <label class="form-field">
                    <span>Название (RU)</span>
                    <input type="text" name="name_ru" class="form-control" required>
                </label>
                <label class="form-field">
                    <span>Название (HY)</span>
                    <input type="text" name="name_hy" class="form-control" required>
                </label>
                <label class="form-field">
                    <span>Название (ENG)</span>
                    <input type="text" name="name_eng" class="form-control" required>
                </label>

                <label class="form-field"><span>Lat</span>
                    <input type="number" step="0.000001" name="lat" id="createCityLat" class="form-control">
                </label>
                <label class="form-field"><span>Lng</span>
                    <input type="number" step="0.000001" name="lng" id="createCityLng" class="form-control">
                </label>

                <!-- Поиск адреса + карта в форме создания -->
                <label class="form-field full position-relative">
                    <span>Адрес / поиск на карте</span>
                    <input type="text" id="createCityAddress" class="form-control" placeholder="Введите адрес или кликните на карте">
                    <div class="search-form-suggestions-container hide" id="createCitySugContainer">
                        <div id="createCityLoading" class="loading-indicator" style="display:none;"><div class="spinner"></div></div>
                        <ul id="createCitySugList" class="search-form-suggestions"></ul>
                    </div>
                </label>
                <div class="form-field full">
                    <div class="map-container" id="createCityMap"></div>
                </div>

                <div class="form-field checkbox-row">
                    <label class="checkbox"><input type="checkbox" name="is_capital"> Столица</label>
                    <label class="checkbox"><input type="checkbox" name="is_region_center"> Центр региона</label>
                </div>
                <div class="form-actions">
                    <button type="submit" name="create_city" class="btn btn-primary">Создать город</button>
                </div>
            </form>
        </div>

        <!-- ОБЪЕДИНЁННЫЙ СПИСОК -->
        <div class="card full">
            <div class="card-head row-between">
                <h3>Регионы и города</h3>
                <div class="toolbar">
                    <input id="globalSearch" type="search" class="form-control small" placeholder="Поиск по региону, городу">
                </div>
            </div>

            <div class="card-body no-pad">
                <div class="accordion" id="geoAccordion">
                    <?php $i=0; foreach ($regions as $r): $i++; $open = ($i===1); $rid=(int)$r['id']; $list=$citiesByRegion[$rid]??[]; ?>
                        <section class="acc-item<?= $open ? ' open' : '' ?>" data-type="region">
                            <header class="acc-header" data-toggle>
                                <div class="acc-title">
                                    <span class="caret" aria-hidden="true"></span>
                                    <div class="acc-text">
                                        <strong><?= htmlspecialchars($r['name_ru']) ?></strong>
                                        <span class="muted">/ <?= htmlspecialchars($r['name_eng']) ?> • <?= htmlspecialchars($r['name_hy']) ?></span>
                                        <span class="muted slug">[<?= htmlspecialchars($r['slug']) ?>]</span>
                                    </div>
                                </div>
                                <div class="acc-actions">
                                    <span class="chip"><?= count($list) ?> город(ов)</span>
                                    <button
                                            class="btn btn-ghost edit-region"
                                            data-id="<?= $rid ?>"
                                            data-slug="<?= htmlspecialchars($r['slug']) ?>"
                                            data-ru="<?= htmlspecialchars($r['name_ru']) ?>"
                                            data-hy="<?= htmlspecialchars($r['name_hy']) ?>"
                                            data-eng="<?= htmlspecialchars($r['name_eng']) ?>"
                                    >Редактировать</button>
                                    <form action="/admin/manage-geo" method="POST" class="inline" onsubmit="return confirm('Удалить регион и все его города?');">
                                        <input type="hidden" name="region_id" value="<?= $rid ?>">
                                        <button type="submit" name="delete_region" class="btn btn-danger-ghost">Удалить</button>
                                    </form>
                                </div>
                            </header>

                            <div class="acc-panel">
                                <?php if (!$list): ?>
                                    <div class="empty">Города не добавлены.</div>
                                <?php else: ?>
                                    <div class="table-wrap">
                                        <table class="table table-clean">
                                            <thead>
                                            <tr>
                                                <th>ID</th><th>Slug</th><th>Город</th><th>Lat</th><th>Lng</th><th>Флаги</th><th class="right">Действия</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($list as $c): ?>
                                                <tr data-type="city">
                                                    <td><?= $c['id'] ?></td>
                                                    <td><?= htmlspecialchars($c['slug']) ?></td>
                                                    <td>
                                                        <div class="stack">
                                                            <strong><?= htmlspecialchars($c['name_ru']) ?></strong>
                                                            <small class="muted"><?= htmlspecialchars($c['name_eng']) ?></small>
                                                            <small class="muted"><?= htmlspecialchars($c['name_hy']) ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($c['lat']) ?></td>
                                                    <td><?= htmlspecialchars($c['lng']) ?></td>
                                                    <td>
                                                        <?php if ($c['is_capital']): ?><span class="chip info">Столица</span><?php endif; ?>
                                                        <?php if ($c['is_region_center']): ?><span class="chip">Центр</span><?php endif; ?>
                                                    </td>
                                                    <td class="right">
                                                        <button
                                                                class="btn btn-ghost edit-city"
                                                                data-id="<?= $c['id'] ?>"
                                                                data-region-id="<?= $rid ?>"
                                                                data-slug="<?= htmlspecialchars($c['slug']) ?>"
                                                                data-ru="<?= htmlspecialchars($c['name_ru']) ?>"
                                                                data-hy="<?= htmlspecialchars($c['name_hy']) ?>"
                                                                data-eng="<?= htmlspecialchars($c['name_eng']) ?>"
                                                                data-lat="<?= htmlspecialchars($c['lat']) ?>"
                                                                data-lng="<?= htmlspecialchars($c['lng']) ?>"
                                                                data-cap="<?= (int)$c['is_capital'] ?>"
                                                                data-center="<?= (int)$c['is_region_center'] ?>"
                                                        >Редактировать</button>
                                                        <form action="/admin/manage-geo" method="POST" class="inline" onsubmit="return confirm('Удалить город?');">
                                                            <input type="hidden" name="city_id" value="<?= $c['id'] ?>">
                                                            <button type="submit" name="delete_city" class="btn btn-danger-ghost">Удалить</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Редактировать регион -->
        <div class="modal fade" id="regionModal" tabindex="-1" aria-hidden="true"
             data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title mb-0">Редактировать регион</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>

                    <form action="/admin/manage-geo" method="POST" class="modal-body row g-3">
                        <input type="hidden" name="region_id" id="editRegionId">
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="editRegionSlug" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Название (RU)</label>
                            <input type="text" name="name_ru" id="editRegionNameRu" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Название (HY)</label>
                            <input type="text" name="name_hy" id="editRegionNameHy" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Название (ENG)</label>
                            <input type="text" name="name_eng" id="editRegionNameEng" class="form-control" required>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" name="edit_region" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Редактировать город -->
        <div class="modal fade" id="cityModal" tabindex="-1" aria-hidden="true"
             data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title mb-0">Редактировать город</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>

                    <form action="/admin/manage-geo" method="POST" class="modal-body">
                        <input type="hidden" name="city_id" id="editCityId">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Регион</label>
                                <select name="region_id" id="editCityRegionId" class="form-select" required>
                                    <?php foreach ($regions as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name_ru']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="editCitySlug" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Название (RU)</label>
                                <input type="text" name="name_ru" id="editCityNameRu" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Название (HY)</label>
                                <input type="text" name="name_hy" id="editCityNameHy" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Название (ENG)</label>
                                <input type="text" name="name_eng" id="editCityNameEng" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Lat</label>
                                <input type="number" step="0.000001" name="lat" id="editCityLat" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Lng</label>
                                <input type="number" step="0.000001" name="lng" id="editCityLng" class="form-control">
                            </div>

                            <!-- Адрес + подсказки (как в company_dashboard) -->
                            <div class="col-12 position-relative">
                                <label class="form-label">Адрес / поиск на карте</label>
                                <input type="text" id="cityAddress" class="form-control" placeholder="Введите адрес или кликните на карте">
                                <div class="search-form-suggestions-container hide" id="citySugContainer">
                                    <div id="cityLoading" class="loading-indicator" style="display:none;">
                                        <div class="spinner"></div>
                                    </div>
                                    <ul id="citySugList" class="search-form-suggestions list-unstyled mb-0"></ul>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="map-container" id="cityMap"></div>
                            </div>

                            <div class="col-12 d-flex gap-3">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_capital" id="editCityIsCapital">
                                    <span class="form-check-label">Столица</span>
                                </label>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_region_center" id="editCityIsCenter">
                                    <span class="form-check-label">Центр региона</span>
                                </label>
                            </div>

                            <div class="col-12 d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="submit" name="edit_city" class="btn btn-primary">Сохранить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // -------------------------
        // Bootstrap модалки
        // -------------------------
        let regionModalBS, cityModalBS;

        document.addEventListener('DOMContentLoaded', () => {
            const rm = document.getElementById('regionModal');
            const cm = document.getElementById('cityModal');
            if (rm) regionModalBS = new bootstrap.Modal(rm, { backdrop: 'static', keyboard: false });
            if (cm) cityModalBS   = new bootstrap.Modal(cm, { backdrop: 'static', keyboard: false });
        });

        // -------------------------
        // Показ/скрытие форм создания
        // -------------------------
        function toggleCard(id, show) {
            const el = document.getElementById(id);
            if (!el) return;
            const toShow = (show === true) ? true : (show === false ? false : el.classList.contains('hidden'));
            el.classList.toggle('hidden', !toShow);
            if (toShow) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.getElementById('btnShowRegion')?.addEventListener('click', (e) => {
            e.preventDefault();
            toggleCard('createRegionCard', true);
            toggleCard('createCityCard', false);
        });

        document.getElementById('btnShowCity')?.addEventListener('click', (e) => {
            e.preventDefault();
            toggleCard('createCityCard', true);
            toggleCard('createRegionCard', false);
            // лениво инициализируем карту/поиск для формы создания
            loadYmaps(() => {
                if (!window._createMapInited) {
                    initCreateMap();
                    bindCreateSearch();
                    window._createMapInited = true;
                }
            });
        });

        document.getElementById('btnHideRegion')?.addEventListener('click', () => toggleCard('createRegionCard', false));
        document.getElementById('btnHideCity')?.addEventListener('click', () => toggleCard('createCityCard', false));

        // -------------------------
        // Аккордеон
        // -------------------------
        document.querySelectorAll('.acc-item .acc-header').forEach((h) => {
            h.addEventListener('click', (e) => {
                if (e.target.closest('button') || e.target.closest('form')) return; // не разворачиваем при клике на действия
                h.parentElement.classList.toggle('open');
            });
        });

        const globalSearch = document.getElementById('globalSearch');


        function doSearch() {
            const q = (globalSearch?.value || '').trim().toLowerCase();
            document.querySelectorAll('.acc-item').forEach((item) => {
                const regionTxt = item.querySelector('.acc-header')?.textContent.toLowerCase() || '';
                const matchRegion = q ? regionTxt.includes(q) : true;
                let matchAnyCity = false;

                item.querySelectorAll('tbody tr').forEach((tr) => {
                    const showRow = !q || tr.textContent.toLowerCase().includes(q) || matchRegion;
                    tr.classList.toggle('hidden', !showRow);
                    if (showRow) matchAnyCity = true;
                });

                const showItem = !q || matchRegion || matchAnyCity;
                item.classList.toggle('hidden', !showItem);
                if (q && showItem) item.classList.add('open');
            });
        }

        globalSearch?.addEventListener('input', doSearch);

        // -------------------------
        // Открытие модалки Региона (Bootstrap)
        // -------------------------
        document.querySelectorAll('.edit-region').forEach((btn) => {
            btn.addEventListener('click', (ev) => {
                ev.preventDefault();
                editRegionId.value      = btn.dataset.id;
                editRegionSlug.value    = btn.dataset.slug;
                editRegionNameRu.value  = btn.dataset.ru;
                editRegionNameHy.value  = btn.dataset.hy;
                editRegionNameEng.value = btn.dataset.eng;
                regionModalBS?.show();
            });
        });

        // =========================================================
        // Яндекс.Карты + подсказки (как в company_dashboard.php)
        // =========================================================
        let cityMap, cityPlacemark, citySugCache = {};
        let createMap, createPlacemark, createSugCache = {};
        const YMAPS_KEY = '93e925f4-cf14-4f88-b5a7-38bbb050f665'; // при необходимости замени

        function loadYmaps(cb) {
            if (window.ymaps) { ymaps.ready(cb); return; }
            const s = document.createElement('script');
            s.src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=' + YMAPS_KEY;
            s.onload = () => ymaps.ready(cb);
            document.head.appendChild(s);
        }

        // ВАЖНО: debounce сохраняет this, иначе this.value будет undefined
        function debounce(fn, ms) {
            let t;
            return function (...args) {
                const ctx = this;
                clearTimeout(t);
                t = setTimeout(() => fn.apply(ctx, args), ms);
            };
        }

        // ---------- Утилиты подсказок
        async function fetchGeo(query, cache) {
            if (cache[query]) return cache[query];
            const bbox = '43.4,38.8~46.6,41.3';
            const url  = `https://geocode-maps.yandex.ru/1.x/?apikey=${YMAPS_KEY}&format=json&lang=ru_RU&geocode=${encodeURIComponent(query)}&bbox=${bbox}&rspn=1&kind=house`;
            const res  = await fetch(url);
            const data = await res.json();
            const items = (data.response?.GeoObjectCollection?.featureMember || [])
                .map((i) => {
                    const g = i.GeoObject;
                    const kind = g.metaDataProperty.GeocoderMetaData.kind;
                    if (!(kind === 'house' || kind === 'street' || kind === 'locality')) return null;
                    return {
                        text: g.metaDataProperty.GeocoderMetaData.text,
                        coords: g.Point.pos.split(' ').map(Number).reverse() // [lat, lng]
                    };
                })
                .filter(Boolean);
            cache[query] = items;
            return items;
        }

        // =========================================================
        // Модалка "Редактировать город"
        // =========================================================
        function initCityMap() {
            const lat = parseFloat(editCityLat.value) || 40.1772;
            const lng = parseFloat(editCityLng.value) || 44.5035;

            cityMap = new ymaps.Map('cityMap', {
                center: [lat, lng],
                zoom: 12,
                controls: ['zoomControl', 'geolocationControl']
            });

            cityPlacemark = new ymaps.Placemark([lat, lng], {}, { draggable: true });
            cityMap.geoObjects.add(cityPlacemark);

            const updateFromCoords = (c) => {
                editCityLat.value = c[0].toFixed(6);
                editCityLng.value = c[1].toFixed(6);
                ymaps.geocode(c).then((res) => {
                    const g = res.geoObjects.get(0);
                    if (g) cityAddress.value = g.getAddressLine();
                });
            };

            cityPlacemark.events.add('dragend', () => updateFromCoords(cityPlacemark.geometry.getCoordinates()));
            cityMap.events.add('click', (e) => {
                const c = e.get('coords');
                cityPlacemark.geometry.setCoordinates(c);
                updateFromCoords(c);
            });
        }

        function bindCitySearch() {
            const input = document.getElementById('cityAddress');
            if (!input || input.dataset.bound === '1') return;

            const ul     = document.getElementById('citySugList');
            const box    = document.getElementById('citySugContainer');
            const loader = document.getElementById('cityLoading');

            const showBox   = () => { box.classList.add('show'); box.classList.remove('hide'); };
            const hideBox   = () => { box.classList.add('hide'); box.classList.remove('show'); };
            const showLoad  = (v) => { loader.style.display = v ? 'block' : 'none'; };

            input.addEventListener('input', debounce(async function () {
                const q = (this.value || '').trim();
                if (q.length < 3) { hideBox(); return; }
                showLoad(true);
                const items = await fetchGeo(q, citySugCache);
                showLoad(false);

                ul.innerHTML = items.length ? '' : '<li class="px-2 py-1">Нет подходящих адресов</li>';
                items.forEach((it) => {
                    const li = document.createElement('li');
                    li.className = 'px-2 py-1';
                    li.textContent = it.text;
                    li.addEventListener('click', () => {
                        input.value = it.text;
                        hideBox();
                        cityPlacemark.geometry.setCoordinates(it.coords);
                        cityMap.setCenter(it.coords, 16);
                        editCityLat.value = it.coords[0].toFixed(6);
                        editCityLng.value = it.coords[1].toFixed(6);
                    });
                    ul.appendChild(li);
                });
                showBox();
            }, 300));

            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const first = ul.querySelector('li');
                    if (first && !first.textContent.includes('Нет')) first.click();
                }
            });

            // закрытие подсказок по клику вне
            document.addEventListener('click', (e) => {
                if (!box.contains(e.target) && e.target !== input) hideBox();
            });

            input.dataset.bound = '1';
        }

        // Кнопки "Редактировать" для городов
        document.querySelectorAll('.edit-city').forEach((btn) => {
            btn.addEventListener('click', (ev) => {
                ev.preventDefault();

                editCityId.value        = btn.dataset.id;
                editCityRegionId.value  = btn.dataset.regionId;
                editCitySlug.value      = btn.dataset.slug;
                editCityNameRu.value    = btn.dataset.ru;
                editCityNameHy.value    = btn.dataset.hy;
                editCityNameEng.value   = btn.dataset.eng;
                editCityLat.value       = btn.dataset.lat || '';
                editCityLng.value       = btn.dataset.lng || '';
                editCityIsCapital.checked = btn.dataset.cap === '1';
                editCityIsCenter.checked  = btn.dataset.center === '1';

                cityModalBS?.show();

                // После показа модалки — инициализируем карту и подсказки (контейнер уже имеет размеры)
                const modalEl = document.getElementById('cityModal');
                modalEl.addEventListener('shown.bs.modal', () => {
                    document.getElementById('cityMap').innerHTML = '';
                    loadYmaps(() => { initCityMap(); bindCitySearch(); });
                }, { once: true });
            });
        });

        // =========================================================
        // Карта/подсказки в форме "Создать город"
        // =========================================================
        function initCreateMap() {
            const lat = parseFloat(document.getElementById('createCityLat')?.value) || 40.1772;
            const lng = parseFloat(document.getElementById('createCityLng')?.value) || 44.5035;

            createMap = new ymaps.Map('createCityMap', {
                center: [lat, lng],
                zoom: 12,
                controls: ['zoomControl', 'geolocationControl']
            });

            createPlacemark = new ymaps.Placemark([lat, lng], {}, { draggable: true });
            createMap.geoObjects.add(createPlacemark);

            const updateFromCoords = (c) => {
                createCityLat.value = c[0].toFixed(6);
                createCityLng.value = c[1].toFixed(6);
                ymaps.geocode(c).then((res) => {
                    const g = res.geoObjects.get(0);
                    if (g) createCityAddress.value = g.getAddressLine();
                });
            };

            createPlacemark.events.add('dragend', () => updateFromCoords(createPlacemark.geometry.getCoordinates()));
            createMap.events.add('click', (e) => {
                const c = e.get('coords');
                createPlacemark.geometry.setCoordinates(c);
                updateFromCoords(c);
            });
        }

        function bindCreateSearch() {
            const input = document.getElementById('createCityAddress');
            if (!input || input.dataset.bound === '1') return;

            const ul     = document.getElementById('createCitySugList');
            const box    = document.getElementById('createCitySugContainer');
            const loader = document.getElementById('createCityLoading');

            const showBox   = () => { box.classList.add('show'); box.classList.remove('hide'); };
            const hideBox   = () => { box.classList.add('hide'); box.classList.remove('show'); };
            const showLoad  = (v) => { loader.style.display = v ? 'block' : 'none'; };

            input.addEventListener('input', debounce(async function () {
                const q = (this.value || '').trim();
                if (q.length < 3) { hideBox(); return; }
                showLoad(true);
                const items = await fetchGeo(q, createSugCache);
                showLoad(false);

                ul.innerHTML = items.length ? '' : '<li class="px-2 py-1">Нет подходящих адресов</li>';
                items.forEach((it) => {
                    const li = document.createElement('li');
                    li.className = 'px-2 py-1';
                    li.textContent = it.text;
                    li.addEventListener('click', () => {
                        input.value = it.text;
                        hideBox();
                        createPlacemark.geometry.setCoordinates(it.coords);
                        createMap.setCenter(it.coords, 16);
                        createCityLat.value = it.coords[0].toFixed(6);
                        createCityLng.value = it.coords[1].toFixed(6);
                    });
                    ul.appendChild(li);
                });
                showBox();
            }, 300));

            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const first = ul.querySelector('li');
                    if (first && !first.textContent.includes('Нет')) first.click();
                }
            });

            // закрытие подсказок по клику вне
            document.addEventListener('click', (e) => {
                if (!box.contains(e.target) && e.target !== input) hideBox();
            });

            input.dataset.bound = '1';
        }
    </script>



    <style>
        /* header */
        .geo-header{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:16px}
        .geo-actions{display:flex;gap:8px;flex-wrap:wrap}
        .muted{color:#6b7280}

        /* cards */
        .card{background:#fff;border:1px solid #eaeaea;border-radius:14px;box-shadow:0 4px 14px rgba(0,0,0,.05);margin-bottom:16px}
        .card.full{width:100%}
        .card-head{padding:14px 16px;border-bottom:1px solid #eee}
        .card-body{padding:14px 16px}
        .card-body.no-pad{padding:0}
        .row-between{display:flex;align-items:center;justify-content:space-between;gap:12px}

        /* toolbar / search */
        .toolbar{display:flex;gap:10px;align-items:center;margin-left:auto}
        .counter{background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:6px 10px;font-weight:700}

        /* forms */
        .form-grid-2{display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:12px}
        .form-grid-3{display:grid;grid-template-columns:repeat(3,minmax(220px,1fr));gap:12px}
        .form-field{display:flex;flex-direction:column;gap:6px}
        .form-field>span{font-weight:600}
        .form-field.full{grid-column:1/-1}
        .form-actions{grid-column:1/-1;display:flex;gap:10px;justify-content:flex-end}
        .checkbox-row{grid-column:1/-1;display:flex;gap:16px;align-items:center}
        .checkbox input{margin-right:6px}
        .hint{color:#98a2b3}
        .hidden{display:none!important}

        /* controls */
        .btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:10px;padding:10px 14px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap}
        .btn-primary{background:#3B82F6;color:#fff;border-color:#3B82F6}
        .btn-primary-light{background:#EEF2FF;color:#3B82F6;border-color:#E0E7FF}
        .btn-secondary{background:#F3F4F6;color:#111827;border-color:#E5E7EB}
        .btn-ghost{background:#fff;border-color:#E5E7EB;color:#111827}
        .btn-danger-ghost{background:#fff;border-color:#FEE2E2;color:#B91C1C}
        .btn:hover{filter:brightness(.98)}
        .inline{display:inline}
        .form-control{width:100%;border:1px solid #E5E7EB;border-radius:10px;padding:10px 12px;outline:none}
        .form-control.small{padding:8px 10px;border-radius:999px}

        /* chips & accordion */
        .chip{display:inline-block;padding:3px 8px;border-radius:999px;background:#E5E7EB;color:#111827;font-size:12px;margin-right:6px}
        .chip.info{background:#DBEAFE;color:#1D4ED8}

        .accordion{width:100%}
        .acc-item{border-top:1px solid #f0f0f0}
        .acc-item:first-child{border-top:0}
        .acc-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;cursor:pointer}
        .acc-header:hover{background:#fafafa}
        .acc-title{display:flex;align-items:center;gap:10px}
        .acc-text{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
        .caret{width:10px;height:10px;border-right:2px solid #6b7280;border-bottom:2px solid #6b7280;transform:rotate(-45deg);transition:transform .2s}
        .acc-item.open .caret{transform:rotate(45deg)}
        .acc-actions{display:flex;gap:8px;align-items:center}
        .slug{opacity:.7}
        .acc-panel{display:none;border-top:1px solid #f2f2f2}
        .acc-item.open .acc-panel{display:block}

        /* tables */
        .table-wrap{width:100%;overflow:auto}
        .table-clean{width:100%;border-collapse:separate;border-spacing:0}
        .table-clean thead th{position:sticky;top:0;background:#fafafa;border-bottom:1px solid #eee;padding:10px 12px;font-weight:700;text-align:left;z-index:1}
        .table-clean tbody td{border-bottom:1px solid #f2f2f2;padding:10px 12px;vertical-align:top}
        .table-clean tbody tr:hover{background:#fcfcff}
        .table-clean .right{text-align:right}
        .stack{display:flex;flex-direction:column;gap:2px}

        /* Подсказки в модалке поверх всего и без обрезания */
        .modal-content{ overflow: visible; }
        .modal-body{ overflow: visible; }


        /* карта и подсказки */
        .map-container{width:100%;height:320px;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden}

        @media (max-width:1024px){
            .form-grid-2,.form-grid-3{grid-template-columns:1fr}
            .acc-actions{flex-wrap:wrap;justify-content:flex-end}
        }
    </style>
</section>
