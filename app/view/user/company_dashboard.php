<section class="content">
    <div class="container dashboard-container modern">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h2 class="dashboard-title m-0">Личный кабинет компании</h2>
            <button class="btn btn-primary rounded-pill px-3" id="openCreateModal">+ Добавить адрес</button>
        </div>

        <?php if (!empty($_GET['saved'])): ?>
            <div class="alert alert-success">Сохранено.</div><?php endif; ?>
        <?php if (!empty($_GET['created'])): ?>
            <div class="alert alert-success">Адрес создан.</div><?php endif; ?>
        <?php if (!empty($_GET['deleted'])): ?>
            <div class="alert alert-warning">Адрес удалён.</div><?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <!-- 0. Выпадающий список + кнопка удаления -->
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-8">
                        <form id="pointSwitcher"
                              method="GET"
                              action="<?= empty($id) ? '/user/company' : '/user/company/' . (int)$id ?>">
                            <label class="form-label">Выберите адрес</label>
                            <select class="form-select form-select-lg" name="point_id" id="point_id"
                                    onchange="if(this.value){ this.form.submit(); }">
                                <option value="">— не выбирать —</option>
                                <?php foreach ($points as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" <?= ((int)$selectedPointId === (int)$p['id'] ? 'selected' : '') ?>>
                                        <?= htmlspecialchars(($p['city_name'] ?: ('Город #' . $p['city_id'])) . ' — ' . ($p['address'] ?: 'без адреса') . ' (#' . $p['id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-12 col-md-4">
                        <button type="button" class="btn btn-outline-danger w-100"
                                id="deletePointBtn" <?= empty($selectedPointId) ? 'disabled' : '' ?>>
                            Удалить выбранную
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основная форма -->
        <form action="<?= empty($id) ? '/user/company' : '/user/company/' . (int)$id ?>" method="POST"
              enctype="multipart/form-data">
            <input type="hidden" name="company_point_id" value="<?= (int)($selectedPointId ?? 0) ?>">

            <!-- 1.  Установка цен -->
            <div class="card-section" data-section="fuel-prices">
                <h3 class="section-toggle blue-background" onclick="toggleSection('fuel-prices')">
                    <span>1.  Установка цен</span>
                    <img src="/img/arrow-down.svg" alt="">
                </h3>
                <div class="section-content">
                    <?php if (empty($selectedPointId)): ?>
                        <div class="alert alert-info">Сначала создайте и/или выберите адрес.</div>
                    <?php else: ?>
                        <div class="price-table">
                            <div class="price-header">
                                <div>топливо</div>
                                <div>ваша цена</div>
                                <div>лучшая цена</div>
                                <div></div>
                            </div>

                            <div id="fuelPricesGroup" class="price-rows">
                                <?php if (!empty($fuelData)): ?>
                                    <?php foreach ($fuelData as $ftId => $price): ?>
                                        <div class="price-row">
                                            <select class="form-select fuel-type" name="fuel_type[]" required>
                                                <?php foreach ($fuelTypes as $opt): ?>
                                                    <option value="<?= (int)$opt['id'] ?>" <?= ((int)$opt['id'] === (int)$ftId ? 'selected' : '') ?>>
                                                        <?= htmlspecialchars($opt['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="number" class="form-control fuel-price" name="fuel_price[]"
                                                   value="<?= htmlspecialchars($price) ?>" placeholder="Цена в AMD"
                                                   step="0.01" required>
                                            <div class="best-cell"><span class="best-price">
                                                <?= isset($bestPrices[$ftId]) && $bestPrices[$ftId] !== 'N/A' ? htmlspecialchars($bestPrices[$ftId]) : 'N/A' ?>
                                            </span></div>
                                            <img src="/img/close-red.svg" class="remove-btn" alt="Удалить">
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="price-row">
                                        <select class="form-select fuel-type" name="fuel_type[]" required>
                                            <?php foreach ($fuelTypes as $opt): ?>
                                                <option value="<?= (int)$opt['id'] ?>"><?= htmlspecialchars($opt['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="number" class="form-control fuel-price" name="fuel_price[]"
                                               placeholder="Цена в AMD" step="0.01" required>
                                        <div class="best-cell"><span class="best-price">—</span></div>
                                        <img src="/img/close-red.svg" class="remove-btn" alt="Удалить">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-3 text-center">
                                <button type="button" class="add-btn" id="addFuelRow">Добавить топливо</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- скрытые поля для раздела 2 -->
            <input type="hidden" id="company_region_id" name="company_region_id" value="">
            <input type="hidden" id="company_city_id" name="company_city_id" value="">
            <input type="hidden" id="company_latitude" name="company_latitude"
                   value="<?= htmlspecialchars($point['latitude'] ?? 40.1772) ?>">
            <input type="hidden" id="company_longitude" name="company_longitude"
                   value="<?= htmlspecialchars($point['longitude'] ?? 44.5035) ?>">

            <!-- 2.  Данные про компанию -->
            <div class="card-section collapsed" data-section="company-core">
                <h3 class="section-toggle blue-background" onclick="toggleSection('company-core')">
                    <span>2.  Данные про компанию</span>
                    <img src="/img/arrow-down.svg" alt="">
                </h3>
                <div class="section-content">
                    <div class="mb-3 form-group">
                        <label for="companyName" class="form-label">Название компании</label>
                        <input type="text" class="form-control" id="companyName" name="name"
                               placeholder="Укажите название" value="<?= htmlspecialchars($company['name'] ?? '') ?>"
                               required>
                    </div>
                    <div class="mb-3 position-relative form-group">
                        <label for="companyAddress" class="form-label">Локация на карте</label>
                        <input type="text" class="form-control" id="companyAddress" name="company_address"
                               value="<?= htmlspecialchars($point['address'] ?? '') ?>" placeholder="Выберите на карте">
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

            <!-- 3.  Детальная информация (без карты) -->
            <div class="card-section collapsed" data-section="point-details">
                <h3 class="section-toggle blue-background" onclick="toggleSection('point-details')">
                    <span>3.  Детальная информация</span>
                    <img src="/img/arrow-down.svg" alt="">
                </h3>
                <div class="section-content">
                    <?php if (empty($selectedPointId)): ?>
                        <div class="alert alert-info">Сначала выберите адрес.</div>
                    <?php else: ?>
                        <div class="mb-3 form-group">
                            <label class="form-label">Логотип</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <?php if (!empty($company['logo'])): ?>
                                <img src="/img/fuel<?= DIRECTORY_SEPARATOR . htmlspecialchars($company['logo']) ?>"
                                     class="logo-preview mt-2" alt="">
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Телефоны</label>
                            <div class="dynamic-group" id="phoneGroup">
                                <?php $phones = $point && $point['phones'] ? json_decode($point['phones'], true) : [''];
                                foreach ($phones as $ph): ?>
                                    <div class="input-group mb-1">
                                        <input type="text" class="form-control phone-mask" name="phones[]"
                                               value="<?= htmlspecialchars($ph) ?>" placeholder="+374 00 000 000">
                                        <img src="/img/close-red.svg" class="remove-btn"
                                             onclick="this.parentElement.remove()">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="add-btn"
                                    onclick="addField('phoneGroup','phones[]','+374 00 000 000')">Добавить
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Email-ы</label>
                            <div class="dynamic-group" id="emailGroup">
                                <?php $emails = $point && $point['emails'] ? json_decode($point['emails'], true) : [''];
                                foreach ($emails as $em): ?>
                                    <div class="input-group mb-1">
                                        <input type="text" class="form-control" name="emails[]"
                                               value="<?= htmlspecialchars($em) ?>" placeholder="info@company.com">
                                        <img src="/img/close-red.svg" class="remove-btn"
                                             onclick="this.parentElement.remove()">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="add-btn"
                                    onclick="addField('emailGroup','emails[]','info@company.com')">Добавить
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Социальные сети</label>
                            <div class="dynamic-group" id="socialGroup">
                                <?php $socials = $point && $point['socials'] ? json_decode($point['socials'], true) : [''];
                                foreach ($socials as $sc): ?>
                                    <div class="input-group mb-1">
                                        <input type="text" class="form-control" name="socials[]"
                                               value="<?= htmlspecialchars($sc) ?>"
                                               placeholder="https://instagram.com/company">
                                        <img src="/img/close-red.svg" class="remove-btn"
                                             onclick="this.parentElement.remove()">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="add-btn"
                                    onclick="addField('socialGroup','socials[]','https://instagram.com/company')">
                                Добавить
                            </button>
                        </div>

                        <div class="form-group">
                            <label>График работы</label>
                            <small class="form-text text-muted">Добавьте только нужные дни</small>
                            <div class="working-hours" id="workingHoursGroup">
                                <?php
                                $workingHours = $point && $point['working_hours'] ? json_decode($point['working_hours'], true) : [];
                                $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                                foreach ($days as $d):
                                    $val = $workingHours[$d] ?? '';
                                    ?>
                                    <div class="working-row">
                                        <select class="form-select" name="working_days[]">
                                            <option value="<?= $d ?>"><?= $d ?></option>
                                        </select>
                                        <input type="text" class="form-control" name="working_times[]"
                                               value="<?= htmlspecialchars($val) ?>" placeholder="9:00-18:00">
                                        <img src="/img/close-red.svg" class="remove-btn"
                                             onclick="this.parentElement.remove()">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Сайт</label>
                            <input type="text" class="form-control" name="website"
                                   value="<?= htmlspecialchars($point['website'] ?? '') ?>">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" name="save_point"
                        class="btn btn-primary btn-lg save-button" <?= empty($selectedPointId) ? 'disabled' : '' ?>>
                    Сохранить данные
                </button>
            </div>
        </form>

        <!-- Модалка: подтверждение удаления -->
        <div class="modal-backdrop" id="deleteModal">
            <div class="modal-card" style="max-width:560px">
                <div class="modal-head">
                    <h3 class="m-0">Удалить адрес</h3>
                    <img src="/img/close-circle.svg" alt="Кнопка закрыть" class="modal-close" id="closeDeleteModal"
                         aria-label="Закрыть">
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Вы уверены, что хотите удалить выбранный адрес? Это действие необратимо и также удалит все цены,
                        связанные с этим адресом.
                    </p>
                    <form method="POST" id="deletePointForm" class="d-flex gap-2 justify-content-end">
                        <input type="hidden" name="company_point_id" id="deletePointId" value="">
                        <button type="button" class="btn btn-light" id="cancelDeleteBtn">Отмена</button>
                        <button type="submit" name="delete_point" class="btn btn-danger">Удалить</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Модалка: Создать точку -->
        <div class="modal-backdrop" id="createModal">
            <div class="modal-card">
                <div class="modal-head">
                    <h3>Создать новый адрес</h3>
                    <img src="/img/close-circle.svg" alt="Кнопка закрыть" class="modal-close" id="closeCreateModal"
                         aria-label="Закрыть">
                </div>
                <div class="modal-body">
                    <form method="POST" id="createPointForm">
                        <input type="hidden" name="create_point" value="1">
                        <input type="hidden" name="latitude" id="latCreate" value="">
                        <input type="hidden" name="longitude" id="lngCreate" value="">

                        <div class="row g-2 align-items-end">
                            <div class="col-6 col-md-2">
                                <label class="form-label">Регион</label>
                                <select class="form-select form-select-sm" id="regionCreate">
                                    <?php foreach ($regions as $r): ?>
                                        <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['name_ru']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Город</label>
                                <select class="form-select form-select-sm" name="city_id" id="cityCreate"
                                        required></select>
                            </div>
                            <div class="col-12 col-md-8 position-relative">
                                <label class="form-label">Адрес</label>
                                <input type="text" class="form-control" id="addressCreate" name="address"
                                       placeholder="Выберите или найдите">
                                <div id="modal-sugg-container" class="search-form-suggestions-container hide">
                                    <div id="modal-loading" class="loading-indicator" style="display:none;">
                                        <div class="spinner"></div>
                                    </div>
                                    <ul id="modal-suggestions-list" class="search-form-suggestions"></ul>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="map-container" id="mapCreate"></div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-light" id="closeCreateModal2">Отмена</button>
                            <button type="submit" class="btn btn-success">Создать адрес</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

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

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px
        }

        .modal-backdrop.show {
            display: flex;
            opacity: 1
        }

        .modal-card {
            background: #fff;
            border-radius: 16px;
            max-width: 980px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(2, 6, 23, .25);
            overflow: hidden
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #eef2f7
        }

        .modal-body {
            padding: 16px 20px
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
            d.innerHTML = `<input type="text" class="form-control ${isPhone ? 'phone-mask' : ''}" name="${name}" placeholder="${ph}">
                   <img src="/img/close-red.svg" class="remove-btn" onclick="this.parentElement.remove()">`;
            g.appendChild(d);
            if (isPhone) {
                IMask(d.querySelector('input'), {mask: '+{374} 00 000 000', lazy: false});
            }
        }

        const REGIONS = <?= json_encode($regions, JSON_UNESCAPED_UNICODE) ?>;
        const CITIES = <?= json_encode($cities, JSON_UNESCAPED_UNICODE) ?>;
        const FUEL_TYPES = <?= json_encode(array_values($fuelTypes), JSON_UNESCAPED_UNICODE) ?>;
        const BEST_PRICES = <?= json_encode($bestPrices, JSON_UNESCAPED_UNICODE) ?>;

        // ===== Удаление точки — модалка =====
        const deleteModal = document.getElementById('deleteModal');
        const deleteBtn = document.getElementById('deletePointBtn');
        const closeDel = document.getElementById('closeDeleteModal');
        const cancelDel = document.getElementById('cancelDeleteBtn');
        const deletePointId = document.getElementById('deletePointId');

        function openDeleteModal() {
            const curId = document.getElementById('point_id')?.value || '';
            if (!curId) return;
            deletePointId.value = curId;
            deleteModal.classList.add('show');
        }

        function closeDeleteModal() {
            deleteModal.classList.remove('show');
        }

        deleteBtn?.addEventListener('click', openDeleteModal);
        closeDel?.addEventListener('click', closeDeleteModal);
        cancelDel?.addEventListener('click', closeDeleteModal);
        deleteModal?.addEventListener('click', e => {
            if (e.target === deleteModal) closeDeleteModal();
        });

        // ===== Помощник: проставить hidden id региона/города из geoObject (секция 2) =====
        function setCompanyRegionCityFromGeoObject(geoObject) {
            try {
                const comps = geoObject?.properties?.get('metaDataProperty')?.GeocoderMetaData?.Address?.Components || [];
                let regionName = null, cityName = null;
                comps.forEach(c => {
                    if (c.kind === 'province' || c.kind === 'area') regionName = c.name;
                    if (c.kind === 'locality') cityName = c.name;
                });
                let regionId = null, cityId = null;
                if (cityName) {
                    const cc = CITIES.filter(x => x.name_ru.toLowerCase() === cityName.toLowerCase());
                    if (cc.length) {
                        cityId = cc[0].id;
                        regionId = cc[0].region_id;
                    } else {
                        const cc2 = CITIES.find(x => cityName.toLowerCase().includes(x.name_ru.toLowerCase()) || x.name_ru.toLowerCase().includes(cityName.toLowerCase()));
                        if (cc2) {
                            cityId = cc2.id;
                            regionId = cc2.region_id;
                        }
                    }
                }
                if (!regionId && regionName) {
                    const r = REGIONS.find(x => x.name_ru.toLowerCase() === regionName.toLowerCase());
                    if (r) regionId = r.id;
                }
                document.getElementById('company_region_id').value = regionId || '';
                document.getElementById('company_city_id').value = cityId || '';
            } catch (e) {
                console.warn('setCompanyRegionCityFromGeoObject error', e);
            }
        }

        // ===== Блок цен =====
        const rowsWrap = document.getElementById('fuelPricesGroup');
        const addBtn = document.getElementById('addFuelRow');

        function updateBestForRow(row) {
            const sel = row.querySelector('.fuel-type');
            const sp = row.querySelector('.best-price');
            const id = String(sel.value);
            const v = BEST_PRICES && BEST_PRICES[id] !== undefined ? BEST_PRICES[id] : 'N/A';
            sp.textContent = (v && v !== 'N/A') ? v : 'N/A';
            sp.style.color = (v && v !== 'N/A') ? '#22C55E' : '#6b7280';
            sp.style.fontWeight = (v && v !== 'N/A') ? '700' : '400';
        }

        function refreshSelectStates() {
            if (!rowsWrap) return;
            const selects = Array.from(rowsWrap.querySelectorAll('.fuel-type'));
            const used = new Set(selects.map(s => String(s.value)));
            selects.forEach(sel => {
                Array.from(sel.options).forEach(opt => {
                    opt.disabled = used.has(String(opt.value)) && String(opt.value) !== String(sel.value);
                });
            });
        }

        function createFuelSelect(prefId = null) {
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
            if (prefId !== null) s.value = String(prefId);
            return s;
        }

        function addFuelRow(prefId = null, price = '') {
            if (!rowsWrap) return;
            const row = document.createElement('div');
            row.className = 'price-row';
            const sel = createFuelSelect(prefId);
            const inp = document.createElement('input');
            inp.type = 'number';
            inp.name = 'fuel_price[]';
            inp.step = '0.01';
            inp.required = true;
            inp.placeholder = 'Цена в AMD';
            inp.className = 'form-control fuel-price';
            if (price !== '') inp.value = price;
            const best = document.createElement('div');
            best.className = 'best-cell';
            best.innerHTML = '<span class="best-price">—</span>';
            const del = document.createElement('img');
            del.className = 'remove-btn';
            del.src = '/img/close-red.svg';
            del.alt = 'Удалить';
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

        if (rowsWrap) {
            rowsWrap.querySelectorAll('.price-row').forEach(row => {
                row.querySelector('.fuel-type')?.addEventListener('change', () => {
                    updateBestForRow(row);
                    refreshSelectStates();
                });
                row.querySelector('.remove-btn')?.addEventListener('click', () => {
                    row.remove();
                    refreshSelectStates();
                });
                updateBestForRow(row);
            });
            refreshSelectStates();
            addBtn?.addEventListener('click', () => {
                const used = new Set(Array.from(rowsWrap.querySelectorAll('.fuel-type')).map(s => String(s.value)));
                const free = FUEL_TYPES.find(ft => !used.has(String(ft.id)));
                addFuelRow(free ? free.id : FUEL_TYPES[0]?.id || null, '');
            });
        }

        // ===== Модалка создания точки =====
        const createModal = document.getElementById('createModal');
        const openCreateModal = document.getElementById('openCreateModal');
        const closeCreateModal = document.getElementById('closeCreateModal');
        const closeCreateModal2 = document.getElementById('closeCreateModal2');

        function showCreateModal() {
            createModal.classList.add('show');
            setTimeout(initCreateUI, 10);
        }

        function hideCreateModal() {
            createModal.classList.remove('show');
        }

        openCreateModal?.addEventListener('click', showCreateModal);
        closeCreateModal?.addEventListener('click', hideCreateModal);
        closeCreateModal2?.addEventListener('click', hideCreateModal);
        createModal?.addEventListener('click', (e) => {
            if (e.target === createModal) hideCreateModal();
        });

        function fillCitiesCreate(regionId) {
            const citySelect = document.getElementById('cityCreate');
            const list = CITIES.filter(c => Number(c.region_id) === Number(regionId));
            citySelect.innerHTML = '';
            list.forEach(c => {
                const o = document.createElement('option');
                o.value = c.id;
                o.textContent = c.name_ru;
                citySelect.appendChild(o);
            });
        }

        function initCreateUI() {
            const rs = document.getElementById('regionCreate');
            const cs = document.getElementById('cityCreate');
            if (!rs || !cs) return;
            if (rs.value === '' && REGIONS[0]) rs.value = REGIONS[0].id;
            fillCitiesCreate(rs.value);
            rs.addEventListener('change', () => fillCitiesCreate(rs.value));
            initCreateMapAndSearch();
        }

        function setCreateRegionCityFromGeoObject(geoObject) {
            try {
                const comps = geoObject?.properties?.get('metaDataProperty')?.GeocoderMetaData?.Address?.Components || [];
                let regionName = null, cityName = null;
                comps.forEach(c => {
                    if (c.kind === 'province' || c.kind === 'area') regionName = c.name;
                    if (c.kind === 'locality') cityName = c.name;
                });
                let regionId = null, cityId = null;
                if (cityName) {
                    const cc = CITIES.filter(x => x.name_ru.toLowerCase() === cityName.toLowerCase());
                    if (cc.length) {
                        cityId = cc[0].id;
                        regionId = cc[0].region_id;
                    } else {
                        const cc2 = CITIES.find(x => cityName.toLowerCase().includes(x.name_ru.toLowerCase()) || x.name_ru.toLowerCase().includes(cityName.toLowerCase()));
                        if (cc2) {
                            cityId = cc2.id;
                            regionId = cc2.region_id;
                        }
                    }
                }
                if (!regionId && regionName) {
                    const r = REGIONS.find(x => x.name_ru.toLowerCase() === regionName.toLowerCase());
                    if (r) regionId = r.id;
                }
                const rs = document.getElementById('regionCreate');
                const cs = document.getElementById('cityCreate');
                if (regionId) {
                    rs.value = regionId;
                    fillCitiesCreate(regionId);
                }
                if (cityId) {
                    cs.value = cityId;
                }
            } catch (e) {
                console.warn('setCreateRegionCityFromGeoObject error', e);
            }
        }

        function initCreateMapAndSearch() {
            const $address = document.getElementById('addressCreate');
            const $list = document.getElementById('modal-suggestions-list');
            const $wrap = document.getElementById('modal-sugg-container');
            const $load = document.getElementById('modal-loading');
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

            $address?.addEventListener('input', debounce(async function () {
                const q = $address.value.trim();
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
                        $address.value = it.full;
                        hide();
                        document.getElementById('latCreate').value = it.coords[0];
                        document.getElementById('lngCreate').value = it.coords[1];
                        if (window._createPlacemark) window._createPlacemark.geometry.setCoordinates(it.coords);
                        if (window._createMap) window._createMap.setCenter(it.coords, 17);
                        const fake = new ymaps.GeoObject({geometry: {type: 'Point', coordinates: it.coords}});
                        fake.properties.set('metaDataProperty', it.geo.metaDataProperty);
                        setCreateRegionCityFromGeoObject(fake);
                    });
                    $list.appendChild(li);
                });
                show();
            }

            ymaps.ready(function () {
                const def = [40.1772, 44.5035];
                window._createMap = new ymaps.Map('mapCreate', {
                    center: def,
                    zoom: 12,
                    controls: ['zoomControl', 'geolocationControl']
                });
                window._createMap.controls.get('zoomControl').options.set({
                    size: 'small',
                    position: {top: '100px', right: '10px'}
                });
                window._createMap.controls.get('geolocationControl').options.set({
                    position: {
                        top: '50px',
                        right: '10px'
                    }
                });
                window._createPlacemark = new ymaps.Placemark(def, {}, {draggable: true});
                window._createMap.geoObjects.add(window._createPlacemark);

                function updateHiddenAndSelects(coords) {
                    document.getElementById('latCreate').value = coords[0];
                    document.getElementById('lngCreate').value = coords[1];
                    ymaps.geocode(coords).then(res => {
                        const o = res.geoObjects.get(0);
                        if (o) {
                            document.getElementById('addressCreate').value = o.getAddressLine();
                            setCreateRegionCityFromGeoObject(o);
                        }
                    });
                }

                window._createMap.events.add('click', e => {
                    const coords = e.get('coords');
                    window._createPlacemark.geometry.setCoordinates(coords);
                    updateHiddenAndSelects(coords);
                });
                window._createPlacemark.events.add('dragend', () => {
                    updateHiddenAndSelects(window._createPlacemark.geometry.getCoordinates());
                });
            });
        }

        // ===== Карта + поиск (секция 2: компания) =====
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
            map.controls.get('zoomControl').options.set({size: 'small', position: {top: '100px', right: '10px'}});
            map.controls.get('geolocationControl').options.set({position: {top: '50px', right: '10px'}});
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
                        setCompanyRegionCityFromGeoObject(o);
                    }
                });
            }

            map.events.add('click', function (e) {
                const coords = e.get('coords');
                placemark.geometry.setCoordinates(coords);
                updateByCoords(coords);
            });
            placemark.events.add('dragend', function () {
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
                        setCompanyRegionCityFromGeoObject(fake);
                    });
                    $list.appendChild(li);
                });
                show();
            }
        });
    </script>
</section>
