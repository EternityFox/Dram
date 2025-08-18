<?php if (!empty($groupedFonts)): ?>
    <?php foreach ($groupedFonts as $folder => $group): ?>
        <?php $firstFont = reset($group); ?>
        <div class="col-12 col-md-6 col-lg-4 mb-4 font-item"
             data-name="<?php echo mb_strtolower(htmlspecialchars($folder), 'UTF-8'); ?>">
            <a href="/font-family/<?php echo urlencode($folder); ?>"
               class="text-decoration-none">
                <div class="font-preview card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($folder); ?>
                            (<?php echo htmlspecialchars($firstFont['name']); ?>)</h5>
                        <style>
                            @font-face {
                                font-family: 'PreviewFont_<?php echo $firstFont['id']; ?>';
                                src: url('/fonts/<?php echo htmlspecialchars($firstFont['folder']) . '/' . htmlspecialchars($firstFont['display_filename']) . '?v=' . time(); ?>') format('<?php echo pathinfo($firstFont['display_filename'], PATHINFO_EXTENSION); ?>');
                            }

                            .preview-text-<?php echo $firstFont['id']; ?> {
                                font-family: 'PreviewFont_<?php echo $firstFont['id']; ?>', sans-serif !important;
                                font-size: <?php echo $fontSize; ?>px;
                                line-height: 1.6;
                                word-break: break-word;
                                transition: font-size 0.3s ease;
                            }
                        </style>
                        <div class="preview-text-<?php echo $firstFont['id']; ?>"
                             id="preview-<?php echo $firstFont['id']; ?>"><?php echo htmlspecialchars($previewText); ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-center text-muted"><?= $lang('Шрифты отсутствуют') ?>.</p>
<?php endif; ?>