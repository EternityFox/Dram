<!-- fonts.php (updated) -->
<section class="content font-container-block">
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="left-block">
                    <?php $bannerDesktop = random_elem($settings['banner_head'], $settings['banner_head_2'], $settings['banner_head_3']); ?>
                    <?php if (!empty(trim($bannerDesktop))) : ?>
                        <div class="banner def-box banner-desktop mb-4">
                            <?= $bannerDesktop ?>
                        </div>
                    <?php endif; ?>

                    <?php $bannerMobile = random_elem($settings['banner_head_mobile'], $settings['banner_head_mobile_2']); ?>
                    <?php if (!empty(trim($bannerMobile))) : ?>
                        <div class="banner def-box banner-mobile mb-4">
                            <?= $bannerMobile ?>
                            <span class="banner-ads-text"><?= $lang('реклама'); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="card card-custom mb-5">
                        <div class="card-header card-header-custom">
                            <?= $lang('Поиск армянских шрифтов') ?>
                        </div>
                        <div class="card-body">
                            <div class="font-toolbar mb-3">
                                <div class="font-search-wrap">
                                    <span class="fsw-icon" aria-hidden="true">
                                        <img src="img/write.svg" alt="Icon write">
                                    </span>
                                    <input type="text" id="previewText" class="fsw-input"
                                           placeholder="<?= $lang('Напишите тут') ?>"
                                           value="Դրամ.ամ"
                                           aria-label="<?= $lang('Напишите тут') ?>">
                                    <img src="img/close.svg" alt="Icon close" id="clearFontSearch" class="fsw-clear"
                                         aria-label="Close">
                                </div>
                                <div class="col-12 mt-3">
                                    <label for="fontSizeSlider" class="form-label"><?= $lang('Размер шрифта') ?>:
                                        <span id="fontSizeValue">40px</span>
                                    </label>
                                    <input type="range" class="form-range" id="fontSizeSlider" min="10" max="100"
                                           value="40">
                                </div>

                                <button class="fsw-more my-3" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#detailsPanel"
                                        aria-expanded="false" aria-controls="detailsPanel">
                                    <?= $lang('Подробнее') ?>
                                    <span class="fsw-caret">
                                        <img src="img/arrow-down.svg" alt="Icon arrow down">
                                    </span>
                                </button>
                            </div>
                            <div id="detailsPanel" class="collapse">
                                <div class="row g-3 align-items-center mb-3 p-3">
                                    <div class="col-12">
                                        <div class="font-search-wrap">
                                            <span class="fsw-icon" aria-hidden="true">
                                                <img src="img/write.svg" alt="Icon write">
                                            </span>
                                            <input type="text" id="fontSearch" class="fsw-input"
                                                   placeholder="<?= $lang('Поиск армянских шрифтов') ?>"
                                                   aria-label=" <?= $lang('Поиск армянских шрифтов') ?>">
                                            <img src="img/close.svg" alt="Icon close" id="clearPreview"
                                                 class="fsw-clear"
                                                 aria-label="Close">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="fontsContainer">
                                <?php
                                $previewText = 'Դրամ.ամ';
                                $fontSize = 40;
                                include 'partials/fonts-items.php';
                                ?>
                            </div>
                            <div class="text-center mt-4" id="loadMoreContainer"
                                 <?php if (!$initialHasMore): ?>style="display: none;"<?php endif; ?>>
                                <button id="showMore" class="btn btn-primary"><?= $lang('Показать ещё') ?></button>
                                <div id="loader" class="spinner-border text-primary ml-2" style="display: none;"
                                     role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
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
    document.addEventListener('DOMContentLoaded', function () {
        const fontSearch = document.getElementById('fontSearch');
        const clearFontSearch = document.getElementById('clearFontSearch');
        const previewText = document.getElementById('previewText');
        const clearPreview = document.getElementById('clearPreview');
        const fontSizeSlider = document.getElementById('fontSizeSlider');
        const fontSizeValue = document.getElementById('fontSizeValue');
        let fontItems = document.querySelectorAll('.font-item');
        const fontsContainer = document.getElementById('fontsContainer');
        const showMore = document.getElementById('showMore');
        const loader = document.getElementById('loader');
        const loadMoreContainer = document.getElementById('loadMoreContainer');

        let currentOffset = document.querySelectorAll('.font-item').length || 24;
        let currentSearch = '';
        let isLoading = false;
        let hasMore = <?php echo json_encode($initialHasMore); ?>;
        let searchTimer;

        function updatePreviewText() {
            const text = (previewText ? previewText.value.trim() : '') || 'Դրամ.ամ';
            fontItems.forEach(item => {
                const preview = item.querySelector('[id^="preview-"]');
                if (!preview) return;
                preview.style.opacity = '0';
                setTimeout(() => {
                    preview.textContent = text;
                    preview.style.transition = 'opacity 0.3s ease';
                    preview.style.opacity = '1';
                }, 100);
            });
        }

        function updateFontSize() {
            if (!fontSizeSlider) return;
            const size = fontSizeSlider.value + 'px';
            if (fontSizeValue) fontSizeValue.textContent = size;
            fontItems.forEach(item => {
                const preview = item.querySelector('[id^="preview-"]');
                if (preview) preview.style.fontSize = size;
            });
        }

        // Универсальный helper для POST JSON
        async function postJSON(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            // читаем как текст и пробуем парсить — чтобы не падать на случайном выводе
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON from', url, text);
                throw e;
            }
        }

        // Загрузка следующей страницы (использует текущую строку поиска)
        function loadFonts(offset) {
            const data = {
                offset: offset,
                search: fontSearch.value.trim(),
                previewText: (previewText ? previewText.value.trim() : '') || 'Դրամ.ամ',
                fontSize: fontSizeSlider ? parseInt(fontSizeSlider.value) : 40
            };
            return postJSON('/ajax/fonts/load', data).then(data => {
                if (offset === 0) {
                    fontsContainer.innerHTML = data.html;
                } else {
                    fontsContainer.insertAdjacentHTML('beforeend', data.html);
                }
                fontItems = document.querySelectorAll('.font-item');
                hasMore = !!data.hasMore;
                loadMoreContainer.style.display = hasMore ? '' : 'none';
                showMore.disabled = !hasMore;

                updatePreviewText();
                updateFontSize();
                isLoading = false;
                loader.style.display = 'none';

                // пересчитываем offset по фактическому количеству карточек
                currentOffset = document.querySelectorAll('.font-item').length;
            }).catch(err => {
                console.error(err);
                isLoading = false;
                showMore.disabled = false;
                loader.style.display = 'none';
            });
        }

        // 🔎 Поиск через отдельный роут — всегда с нуля
        function searchFonts() {
            const data = {
                search: fontSearch.value.trim(),
                previewText: (previewText ? previewText.value.trim() : '') || 'Դրամ.ամ',
                fontSize: fontSizeSlider ? parseInt(fontSizeSlider.value) : 40
            };
            return postJSON('/ajax/fonts/search', data).then(data => {
                fontsContainer.innerHTML = data.html;
                fontItems = document.querySelectorAll('.font-item');
                hasMore = !!data.hasMore;
                loadMoreContainer.style.display = hasMore ? '' : 'none';
                showMore.disabled = !hasMore;

                updatePreviewText();
                updateFontSize();
                isLoading = false;
                loader.style.display = 'none';

                // offset = текущее число карточек после поиска
                currentOffset = document.querySelectorAll('.font-item').length;
            }).catch(err => {
                console.error(err);
                isLoading = false;
                showMore.disabled = false;
                loader.style.display = 'none';
            });
        }

        // Дебаунс поиска
        fontSearch.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const newSearch = fontSearch.value.trim();
                if (newSearch !== currentSearch) {
                    currentSearch = newSearch;
                    isLoading = true;
                    loader.style.display = 'inline-block';
                    showMore.disabled = true;
                    // запускаем новый поиск (глобально по БД)
                    searchFonts();
                }
            }, 300);
        });

        clearFontSearch.addEventListener('click', () => {
            fontSearch.value = '';
            fontSearch.dispatchEvent(new Event('input'));
            fontSearch.focus();
        });

        if (previewText) previewText.addEventListener('input', updatePreviewText);

        if (clearPreview) clearPreview.addEventListener('click', () => {
            previewText.value = '';
            updatePreviewText();
            previewText.focus();
        });

        if (fontSizeSlider) {
            fontSizeSlider.addEventListener('input', updateFontSize);
            updateFontSize();
        }

        showMore.addEventListener('click', () => {
            if (isLoading || !hasMore) return;
            isLoading = true;
            showMore.disabled = true;
            loader.style.display = 'inline-block';
            // offset берём из DOM — надёжнее при любом лимите и фильтрах
            const visible = document.querySelectorAll('.font-item').length;
            loadFonts(visible);
        });

        updatePreviewText();
    });
</script>
