<section class="content">
    <div class="container dashboard-container modern">
        <h2 class="dashboard-title">Личный кабинет компании</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/user/company" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="save_company" value="1">
            <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($company['latitude'] ?? 40.1772) ?>">
            <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($company['longitude'] ?? 44.5035) ?>">

            <div class="card-section" data-section="company-data">
                <h3 class="section-toggle" onclick="toggleSection('company-data')">
                    <i class="fas fa-chevron-right"></i> Данные компании
                </h3>
                <div class="section-content">
                    <div class="form-group">
                        <label for="companyName">Название компании</label>
                        <input type="text" class="form-control" id="companyName" name="name" value="<?= htmlspecialchars($company['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="companyAddress">Адрес</label>
                        <input type="text" class="form-control" id="companyAddress" name="address" value="<?= htmlspecialchars($company['address'] ?? '') ?>" placeholder="Введите адрес или выберите на карте">
                        <div class="search-form-suggestions-container hide">
                            <div id="loading-indicator" class="loading-indicator" style="display: none;">
                                <div class="spinner"></div>
                            </div>
                            <ul id="suggestions-list" class="search-form-suggestions"></ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="map-container" id="map"></div>
                        <div class="map-container-balloon" id="map-container-balloon">
                            <div class="map-balloon-content">
                                <span class="close-btn">&times;</span>
                                <div class="map-balloon-body" id="map-balloon-body"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Телефоны</label>
                        <div class="dynamic-group" id="phoneGroup">
                            <?php $phones = $company['phones'] ? json_decode($company['phones'], true) : ['']; foreach ($phones as $index => $phone): ?>
                                <div class="input-group">
                                    <input type="text" class="form-control phone-mask" name="phones[]" value="<?= htmlspecialchars($phone) ?>" placeholder="+374 00 000 000" required>
                                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addField('phoneGroup', 'phones[]', '+374 00 000 000')">Добавить</button>
                    </div>
                    <div class="form-group">
                        <label>Email-ы</label>
                        <div class="dynamic-group" id="emailGroup">
                            <?php $emails = $company['emails'] ? json_decode($company['emails'], true) : ['']; foreach ($emails as $index => $email): ?>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="emails[]" value="<?= htmlspecialchars($email) ?>" placeholder="info@company.com" required>
                                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addField('emailGroup', 'emails[]', 'info@company.com')">Добавить</button>
                    </div>
                    <div class="form-group">
                        <label>Социальные сети</label>
                        <div class="dynamic-group" id="socialGroup">
                            <?php $socials = $company['socials'] ? json_decode($company['socials'], true) : ['']; foreach ($socials as $index => $social): ?>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="socials[]" value="<?= htmlspecialchars($social) ?>" placeholder="https://instagram.com/company" required>
                                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addField('socialGroup', 'socials[]', 'https://instagram.com/company')">Добавить</button>
                    </div>
                    <div class="form-group">
                        <label>График работы</label>
                        <small class="form-text text-muted">Добавьте только нужные дни</small>
                        <div class="working-hours" id="workingHoursGroup">
                            <?php $workingHours = $company['working_hours'] ? json_decode($company['working_hours'], true) : []; $days = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']; ?>
                            <?php foreach ($days as $day): if (!isset($workingHours[$day])) continue; ?>
                                <div class="working-row">
                                    <select class="form-select" name="working_days[]" disabled>
                                        <option value="<?= $day ?>"><?= $day ?></option>
                                    </select>
                                    <input type="text" class="form-control" name="working_times[]" value="<?= htmlspecialchars($workingHours[$day]) ?>" placeholder="9:00-18:00" required>
                                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>
                                </div>
                            <?php endforeach; ?>
                            <?php if (isset($workingHours)): ?>
                                <?php for ($i = count($workingHours); $i < 7; $i++): $day = $days[$i]; ?>
                                    <div class="working-row">
                                        <select class="form-select" name="working_days[]">
                                            <option value="<?= $day ?>"><?= $day ?></option>
                                        </select>
                                        <input type="text" class="form-control" name="working_times[]" placeholder="9:00-18:00">
                                        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>
                                    </div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addWorkingHour()">Добавить день</button>
                    </div>
                    <div class="form-group">
                        <label for="website">Сайт</label>
                        <input type="text" class="form-control" id="website" name="website" value="<?= htmlspecialchars($company['website'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="logo">Логотип</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <?php if ($company['logo']): ?>
                            <img src="img/fuel/<?= htmlspecialchars($company['logo']) ?>" alt="Логотип" class="logo-preview">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-section" data-section="fuel-prices">
                <h3 class="section-toggle" onclick="toggleSection('fuel-prices')">
                    <i class="fas fa-chevron-down"></i> Установка цен
                </h3>
                <div class="section-content">
                    <div class="form-group">
                        <div id="fuelPricesGroup">
                            <?php foreach ($fuelTypes as $index => $fuelType): ?>
                                <div class="input-group">
                                    <select class="form-select" name="fuel_type[]" required>
                                        <option value="<?= $fuelType['id'] ?>" <?= isset($fuelData[$fuelType['id']]) ? 'selected' : '' ?>><?= htmlspecialchars($fuelType['name']) ?></option>
                                    </select>
                                    <input type="number" class="form-control" name="fuel_price[]" value="<?= htmlspecialchars($fuelData[$fuelType['id']] ?? '') ?>" placeholder="Цена в AMD" step="0.01" required>
                                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addFuelPrice()">Добавить</button>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg save-button">Сохранить изменения</button>
            </div>
        </form>
    </div>

    <script src="js/imask.min.js"></script>
    <script>
        // Form handling functions
        function toggleSection(sectionId) {
            const section = document.querySelector(`[data-section="${sectionId}"]`);
            section.classList.toggle('collapsed');
            const icon = section.querySelector('h3 i');
            icon.classList.toggle('fa-chevron-right');
            icon.classList.toggle('fa-chevron-down');
        }

        function applyPhoneMasks() {
            const phoneInputs = document.querySelectorAll('.phone-mask');
            phoneInputs.forEach(input => {
                if (!input._mask) {
                    input._mask = IMask(input, {
                        mask: '+{374} 00 000 000',
                        lazy: false
                    });
                }
            });
        }

        function addField(groupId, name, placeholder) {
            const group = document.getElementById(groupId);
            const div = document.createElement('div');
            const isPhone = name === 'phones[]';
            div.className = 'input-group mb-1';
            div.innerHTML = `<input type="text" class="form-control ${isPhone ? 'phone-mask' : ''}" name="${name}" placeholder="${placeholder}" required><button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>`;
            group.appendChild(div);
            if (isPhone) applyPhoneMasks();
        }

        function addWorkingHour() {
            const group = document.getElementById('workingHoursGroup');
            const days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
            const usedDays = Array.from(group.querySelectorAll('select[name="working_days[]"]')).map(select => select.value);
            const availableDays = days.filter(day => !usedDays.includes(day) || usedDays.indexOf(day) === usedDays.lastIndexOf(day));
            if (availableDays.length === 0) return;
            const div = document.createElement('div');
            div.className = 'working-row';
            div.innerHTML = `<select class="form-select" name="working_days[]">${availableDays.map(day => `<option value="${day}">${day}</option>`).join('')}</select><input type="text" class="form-control" name="working_times[]" placeholder="9:00-18:00"><button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>`;
            group.appendChild(div);
        }

        function addFuelPrice() {
            const group = document.getElementById('fuelPricesGroup');
            const div = document.createElement('div');
            div.className = 'input-group mb-1';
            const options = <?= json_encode(array_column($fuelTypes, 'name', 'id')) ?>;
            div.innerHTML = `<select class="form-select" name="fuel_type[]">${Object.entries(options).map(([id, name]) => `<option value="${id}">${name}</option>`).join('')}</select><input type="number" class="form-control" name="fuel_price[]" placeholder="Цена в AMD" step="0.01" required><button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖</button>`;
            group.appendChild(div);
        }

        // Yandex Maps initialization
        ymaps.ready(function () {
            let userCoords = null;
            const lat = parseFloat(document.getElementById('latitude').value) || 40.1772;
            const lng = parseFloat(document.getElementById('longitude').value) || 44.5035;

            // Try to get user's location
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userCoords = [position.coords.latitude, position.coords.longitude];
                    initializeMap(userCoords);
                },
                (error) => {
                    console.error('Geolocation error:', error.message);
                    initializeMap([lat, lng]);
                },
                { maximumAge: 60000, timeout: 5000, enableHighAccuracy: true }
            );

            function initializeMap(centerCoords) {
                const map = new ymaps.Map('map', {
                    center: centerCoords,
                    zoom: 12,
                    controls: ['zoomControl', 'geolocationControl']
                });

                // Customize controls position
                map.controls.get('zoomControl').options.set({
                    size: 'small',
                    position: { top: '100px', right: '10px' }
                });
                map.controls.get('geolocationControl').options.set({
                    position: { top: '50px', right: '10px' }
                });

                let placemark = new ymaps.Placemark(centerCoords, {}, {
                    draggable: true
                });
                map.geoObjects.add(placemark);

                const $address = document.getElementById('companyAddress');
                const $suggestionsList = document.getElementById('suggestions-list');
                const $suggestionsContainer = document.querySelector('.search-form-suggestions-container');
                const suggestionsCache = {};

                function debounce(func, delay) {
                    let timer;
                    return function (...args) {
                        clearTimeout(timer);
                        timer = setTimeout(() => func.apply(this, args), delay);
                    };
                }

                // Handle address input
                $address.addEventListener('input', debounce(async function () {
                    const query = $address.value;
                    if (query.length > 3) {
                        showLoader();
                        await fetchSuggestions(query);
                        hideLoader();
                    } else {
                        hideContainer($suggestionsContainer);
                    }
                }, 300));

                $address.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const first = $suggestionsList.querySelector('li');
                        if (first && !first.textContent.includes("Нет подходящих")) {
                            first.click();
                        }
                    }
                });

                async function fetchSuggestions(query) {
                    if (suggestionsCache[query]) {
                        displaySuggestions(suggestionsCache[query]);
                        return;
                    }
                    try {
                        const bbox = '43.4,38.8~46.6,41.3'; // Corrected bbox for Armenia
                        const response = await fetch(`https://geocode-maps.yandex.ru/1.x/?apikey=93e925f4-cf14-4f88-b5a7-38bbb050f665&format=json&lang=ru_RU&geocode=${encodeURIComponent(query)}&bbox=${bbox}&rspn=1&kind=house`);
                        const data = await response.json();
                        const suggestions = data.response.GeoObjectCollection.featureMember;
                        const filteredSuggestions = suggestions.filter(item => {
                            const kind = item.GeoObject.metaDataProperty.GeocoderMetaData.kind;
                            return kind === 'house' || kind === 'street' || kind === 'locality';
                        });

                        suggestionsCache[query] = filteredSuggestions.map(item => ({
                            ...item,
                            coords: item.GeoObject.Point.pos.split(' ').map(Number).reverse() // Yandex returns [lon, lat], Maps expects [lat, lon]
                        }));
                        displaySuggestions(suggestionsCache[query]);
                    } catch (error) {
                        console.error('Ошибка автодополнения:', error);
                    }
                }

                function formatAddress(fullAddress) {
                    const parts = fullAddress.split(',').map(part => part.trim());
                    return parts.length >= 4 ? parts.slice(-4).join(', ') : fullAddress;
                }

                function displaySuggestions(suggestions) {
                    $suggestionsList.innerHTML = '';
                    if (!suggestions || suggestions.length === 0) {
                        $suggestionsList.innerHTML = '<li>Нет подходящих адресов в Армении</li>';
                        showContainer($suggestionsContainer);
                        return;
                    }
                    suggestions.forEach(item => {
                        const fullAddress = item.GeoObject.metaDataProperty.GeocoderMetaData.text;
                        const formattedAddress = formatAddress(fullAddress);
                        const li = document.createElement('li');
                        li.textContent = formattedAddress;
                        li.addEventListener('click', () => selectSuggestion(fullAddress, item.coords));
                        $suggestionsList.appendChild(li);
                    });
                    showContainer($suggestionsContainer);
                }

                function selectSuggestion(address, coords) {
                    $address.value = address;
                    hideContainer($suggestionsContainer);
                    placemark.geometry.setCoordinates(coords);
                    document.getElementById('latitude').value = coords[0];
                    document.getElementById('longitude').value = coords[1];
                    map.setCenter(coords, 17); // Zoom to building level
                }

                function showContainer(element) {
                    if (element.classList.contains('hide')) {
                        element.classList.remove('hide');
                        element.classList.add('show');
                    }
                }

                function hideContainer(element) {
                    if (element.classList.contains('show')) {
                        element.classList.remove('show');
                        element.classList.add('hide');
                    }
                }

                function showLoader() {
                    document.getElementById('loading-indicator').style.display = 'block';
                }

                function hideLoader() {
                    document.getElementById('loading-indicator').style.display = 'none';
                }

                // Update coordinates and address
                function updateAddress(coords) {
                    document.getElementById('latitude').value = coords[0];
                    document.getElementById('longitude').value = coords[1];
                    ymaps.geocode(coords).then(function (res) {
                        const firstGeoObject = res.geoObjects.get(0);
                        if (firstGeoObject) {
                            $address.value = firstGeoObject.getAddressLine();
                        }
                    });
                }

                // Handle map click for building selection
                map.events.add('click', function (e) {
                    const coords = e.get('coords');
                    ymaps.geocode(coords, { kind: 'house', results: 1 }).then(function (res) {
                        const firstGeoObject = res.geoObjects.get(0);
                        if (firstGeoObject && map.getZoom() >= 15) {
                            const address = firstGeoObject.getAddressLine();
                            map.balloon.open(coords, {
                                content: `
                                    <div>
                                        <p>Установить адрес: <br><strong>${address}</strong>?</p>
                                        <button id="set-address">Установить</button>
                                    </div>
                                `
                            });
                            document.addEventListener('click', function setAddressHandler(e) {
                                if (e.target.id === 'set-address') {
                                    e.preventDefault();
                                    placemark.geometry.setCoordinates(coords);
                                    updateAddress(coords);
                                    map.balloon.close();
                                    document.removeEventListener('click', setAddressHandler);
                                }
                            }, { once: true });
                        }
                    });
                });

                // Handle placemark drag
                placemark.events.add('dragend', function () {
                    const coords = placemark.geometry.getCoordinates();
                    updateAddress(coords);
                });

                // Close balloon
                document.querySelector('.map-container-balloon .close-btn').addEventListener('click', function () {
                    document.getElementById('map-container-balloon').style.display = 'none';
                });

                // Remove "Loading map..." text
                document.getElementById('map').classList.add('loaded');
            }
        });
    </script>
</section>