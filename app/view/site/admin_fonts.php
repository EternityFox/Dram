<section class="content py-5 font-container">
    <div class="container">
        <h1 class="header-title mb-4 text-center">Управление шрифтами</h1>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p class="mb-0"><?php echo $error; ?></p>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card card-custom mb-5">
            <div class="card-header card-header-custom">
                Загрузка шрифтов
            </div>
            <div class="card-body">
                <form action="/admin/fonts-list" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="font_files" class="form-label">Выберите файлы шрифтов (TTF, OTF, WOFF, WOFF2):</label>
                        <input type="file" class="form-control" id="font_files" name="font_files[]" multiple required>
                        <div class="invalid-feedback">
                            Пожалуйста, выберите хотя бы один файл.
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="upload_fonts" class="btn btn-custom btn-lg">Загрузить</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header card-header-custom">
                Список шрифтов
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label for="previewText" class="form-label">Текст для предпросмотра (поддержка армянского, русского, английского):</label>
                    <input type="text" id="previewText" class="form-control" placeholder="հայерեն русский English" value="հայերեն русский English">
                </div>

                <?php if (!empty($groupedFonts)): ?>
                    <?php foreach ($groupedFonts as $folder => $group): ?>
                        <div class="accordion accordion-flush" id="accordion-<?php echo htmlspecialchars(str_replace(' ', '-', $folder)); ?>">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-<?php echo htmlspecialchars(str_replace(' ', '-', $folder)); ?>">
                                    <button class="font-family-header accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo htmlspecialchars(str_replace(' ', '-', $folder)); ?>" aria-expanded="false" aria-controls="collapse-<?php echo htmlspecialchars(str_replace(' ', '-', $folder)); ?>">
                                        <span><?php echo htmlspecialchars($folder); ?> (Семейство шрифтов)</span>
                                    </button>
                                </h2>
                                <div id="collapse-<?php echo htmlspecialchars(str_replace(' ', '-', $folder)); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo htmlspecialchars(str_replace(' ', '-', $folder)); ?>" data-bs-parent="#accordion-<?php echo htmlspecialchars(str_replace(' ', '-', $folder)); ?>">
                                    <div class="accordion-body">
                                        <table class="table table-striped table-hover mt-3">
                                            <thead>
                                            <tr>
                                                <th>Название</th>
                                                <th>Файл</th>
                                                <th>Размер</th>
                                                <th>Дата загрузки</th>
                                                <th>Предпросмотр</th>
                                                <th>Действия</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($group as $font): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($font['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($font['filename']); ?></td>
                                                    <td><?php echo number_format($font['size'] / 1024, 2); ?> KB</td>
                                                    <td><?php echo $font['uploaded_at']; ?></td>
                                                    <td>
                                                        <style>
                                                            @font-face {
                                                                font-family: 'PreviewFont_<?php echo $font['id']; ?>';
                                                                src: url('/fonts/<?php echo htmlspecialchars($font['folder']) . '/' . htmlspecialchars($font['display_filename']) . '?v=' . time(); ?>') format('<?php echo pathinfo($font['display_filename'], PATHINFO_EXTENSION); ?>');
                                                            }
                                                            .preview-text-<?php echo $font['id']; ?> {
                                                                font-family: 'PreviewFont_<?php echo $font['id']; ?>', sans-serif !important;
                                                                font-size: 18px;
                                                                line-height: 1.6;
                                                                padding: 10px;
                                                                background-color: #fff;
                                                                border: 1px solid #dee2e6;
                                                                border-radius: 8px;
                                                                min-height: 50px;
                                                                word-break: break-word;
                                                                transition: all 0.3s ease;
                                                            }
                                                            .preview-text-<?php echo $font['id']; ?>:hover {
                                                                background-color: #e0f7fa;
                                                                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                                            }
                                                        </style>
                                                        <div class="preview-text preview-text-<?php echo $font['id']; ?>">հայերեն русский English</div>
                                                    </td>
                                                    <td>
                                                        <form action="/admin/fonts-list" method="POST" style="display:inline;">
                                                            <input type="hidden" name="font_id" value="<?php echo $font['id']; ?>">
                                                            <button type="submit" name="delete_font" class="btn btn-danger btn-sm" onclick="return confirm('Удалить шрифт?')">Удалить</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Шрифты отсутствуют.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const previewInput = document.getElementById('previewText');
        const previewDivs = document.querySelectorAll('.preview-text');

        // Обновление текста предпросмотра
        function updatePreviews() {
            const text = previewInput.value.trim() || 'հայерեն русский English';
            previewDivs.forEach(div => {
                div.textContent = text;
            });
        }

        previewInput.addEventListener('input', updatePreviews);
        updatePreviews();
    });
</script>