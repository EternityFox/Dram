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
                        </div>
                    <?php endif; ?>

                    <div class="card card-custom mb-5">
                        <div class="card-header card-header-custom">
                            Просмотр шрифтов
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="input-group input-group-custom">
                                        <input type="text" id="fontSearch" class="form-control"
                                               placeholder="Поиск по шрифтам..." aria-label="Поиск шрифтов">
                                        <button class="btn btn-custom" type="button" id="clearSearch">Очистить</button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-custom">
                                        <input type="text" id="previewText" class="form-control"
                                               placeholder="Введите текст для предпросмотра"
                                               value="hello" aria-label="Текст для предпросмотра">
                                        <button class="btn btn-custom" type="button" id="resetText">Сброс</button>
                                    </div>
                                </div>
                            </div>
                            <div class="slider-container">
                                <label for="fontSizeSlider" class="form-label">Размер шрифта: <span
                                            id="fontSizeValue">40px</span></label>
                                <input type="range" class="form-range" id="fontSizeSlider" min="10" max="100"
                                       value="40">
                            </div>

                            <div class="row" id="fontsContainer">
                                <?php if (!empty($groupedFonts)): ?>
                                    <?php foreach ($groupedFonts as $folder => $group): ?>
                                        <?php $firstFont = reset($group); // Take the first font as a representative ?>
                                        <div class="col-12 col-md-6 col-lg-4 mb-4 font-item"
                                             data-name="<?php echo strtolower(htmlspecialchars($folder)); ?>">
                                            <a href="/font-family/<?php echo urlencode($folder); ?>" class="text-decoration-none">
                                                <div class="font-preview card h-100 shadow-sm border-0">
                                                    <div class="card-body">
                                                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($folder); ?> (<?php echo htmlspecialchars($firstFont['name']); ?>)</h5>
                                                        <style>
                                                            @font-face {
                                                                font-family: 'PreviewFont_<?php echo $firstFont['id']; ?>';
                                                                src: url('/fonts/<?php echo htmlspecialchars($firstFont['folder']) . '/' . htmlspecialchars($firstFont['display_filename']) . '?v=' . time(); ?>') format('<?php echo pathinfo($firstFont['display_filename'], PATHINFO_EXTENSION); ?>');
                                                            }
                                                            .preview-text-<?php echo $firstFont['id']; ?> {
                                                                font-family: 'PreviewFont_<?php echo $firstFont['id']; ?>', sans-serif !important;
                                                                font-size: 40px;
                                                                line-height: 1.6;
                                                                word-break: break-word;
                                                                transition: font-size 0.3s ease;
                                                            }
                                                        </style>
                                                        <div class="preview-text-<?php echo $firstFont['id']; ?>"
                                                             id="preview-<?php echo $firstFont['id']; ?>">hello
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">Шрифты отсутствуют.</p>
                                <?php endif; ?>
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
        const clearSearch = document.getElementById('clearSearch');
        const previewText = document.getElementById('previewText');
        const resetText = document.getElementById('resetText');
        const fontSizeSlider = document.getElementById('fontSizeSlider');
        const fontSizeValue = document.getElementById('fontSizeValue');
        const fontsContainer = document.getElementById('fontsContainer');
        const fontItems = document.querySelectorAll('.font-item');

        // Обновление текста предпросмотра с анимацией
        function updatePreviewText() {
            const text = previewText.value.trim() || 'hello';
            fontItems.forEach(item => {
                const preview = item.querySelector(`[id^="preview-"]`);
                if (preview) {
                    preview.style.opacity = '0';
                    setTimeout(() => {
                        preview.textContent = text;
                        preview.style.transition = 'opacity 0.3s ease';
                        preview.style.opacity = '1';
                    }, 100);
                }
            });
        }

        // Обновление размера шрифта
        function updateFontSize() {
            const size = fontSizeSlider.value + 'px';
            fontSizeValue.textContent = size;
            fontItems.forEach(item => {
                const preview = item.querySelector(`[id^="preview-"]`);
                if (preview) preview.style.fontSize = size;
            });
        }

        // Поиск шрифтов
        function filterFonts() {
            const searchTerm = fontSearch.value.toLowerCase().trim();
            fontItems.forEach(item => {
                const name = item.dataset.name;
                if (searchTerm === '' || name.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // События
        previewText.addEventListener('input', updatePreviewText);
        fontSizeSlider.addEventListener('input', updateFontSize);
        fontSearch.addEventListener('input', filterFonts);
        clearSearch.addEventListener('click', () => {
            fontSearch.value = '';
            filterFonts();
        });
        resetText.addEventListener('click', () => {
            previewText.value = 'hello';
            updatePreviewText();
        });

        // Инициализация
        updateFontSize();
        updatePreviewText();
    });
</script>