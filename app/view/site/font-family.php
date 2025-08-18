<section class="content font-family-container-block">
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
                        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                            <span><?= $lang('Шрифт') ?>: <?php echo htmlspecialchars($family); ?></span>
                            <a href="/download-font/<?php echo urlencode($family); ?>" class="btn btn-custom btn-sm">
                                <?= $lang('Скачать все') ?>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-center mb-3 p-3">
                                <div class="col-12">
                                    <div class="font-search-wrap">
                                            <span class="fsw-icon" aria-hidden="true">
                                                <img src="img/write.svg" alt="Icon write">
                                            </span>
                                        <input type="text" id="previewText" class="fsw-input"
                                               placeholder="<?= $lang('Напишите тут') ?>" value="Դրամ.ամ"
                                               aria-label="<?= $lang('Напишите тут') ?>">
                                        <img src="img/close.svg" alt="Icon close" id="clearSearch" class="fsw-clear"
                                             aria-label="Close">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="fontSizeSlider" class="form-label"><?= $lang('Размер шрифта') ?>:
                                        <span id="fontSizeValue">40px</span>
                                    </label>
                                    <input type="range" class="form-range" id="fontSizeSlider" min="10" max="100"
                                           value="40">
                                </div>
                            </div>

                            <div class="row" id="fontsContainer">
                                <?php if (!empty($fonts)): ?>
                                    <?php foreach ($fonts as $font): ?>
                                        <div class="col-12 col-md-6 col-lg-4 mb-4 font-item">
                                            <div class="font-preview card shadow-sm border-0">
                                                <div class="card-body">
                                                    <h5 class="card-title mb-3">
                                                        <?php
                                                        $variant = $font['variant'];
                                                        echo htmlspecialchars($weightMap[$variant] ?? $lang('Неизвестный вариант'));
                                                        ?>
                                                    </h5>
                                                    <style>
                                                        @font-face {
                                                            font-family: 'PreviewFont_<?php echo $font['id']; ?>';
                                                            src: url('/fonts/<?php echo htmlspecialchars($font['folder']) . '/' . htmlspecialchars($font['display_filename']) . '?v=' . time(); ?>') format('<?php echo pathinfo($font['display_filename'], PATHINFO_EXTENSION); ?>');
                                                        }

                                                        .preview-text-<?php echo $font['id']; ?> {
                                                            font-family: 'PreviewFont_<?php echo $font['id']; ?>', sans-serif !important;
                                                            font-size: 40px;
                                                            line-height: 1.6;
                                                            word-break: break-word;
                                                            transition: font-size 0.3s ease;
                                                        }
                                                    </style>
                                                    <div class="preview-text-<?php echo $font['id']; ?>"
                                                         id="preview-<?php echo $font['id']; ?>">Դրամ.ամ
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted"><?= $lang('Варианты шрифта отсутствуют') ?>.</p>
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
        const previewText = document.getElementById('previewText');
        const resetText = document.getElementById('resetText');
        const fontSizeSlider = document.getElementById('fontSizeSlider');
        const fontSizeValue = document.getElementById('fontSizeValue');
        const fontItems = document.querySelectorAll('.font-item');

        // Обновление текста предпросмотра с анимацией
        function updatePreviewText() {
            const text = previewText.value.trim() || 'Դրամ.ամ';
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

        // События
        previewText.addEventListener('input', updatePreviewText);
        fontSizeSlider.addEventListener('input', updateFontSize);
        resetText.addEventListener('click', () => {
            previewText.value = 'Դրամ.ամ';
            updatePreviewText();
        });

        // Инициализация
        updateFontSize();
        updatePreviewText();
    });
</script>