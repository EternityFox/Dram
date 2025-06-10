<section class="content">
    <div class="container">
        <h1>Редактировать страницу</h1>
        <form action="/admin/edit-page/<?= $page['id'] ?>" method="POST">
            <div class="form-group">
                <label>URL (slug)</label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($page['slug']) ?>" required>
            </div>
            <div class="form-group">
                <label>Содержимое</label>
                <div id="content-editor" style="height: 400px; width: 100%;"><?= htmlspecialchars($page['content']) ?></div>
                <input type="hidden" name="content" id="content-input" value="<?= htmlspecialchars($page['content']) ?>">
            </div>
            <div class="form-group">
                <label>SEO заголовок</label>
                <input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($page['seo_title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>SEO описание</label>
                <textarea name="seo_description" class="form-control" rows="3"><?= htmlspecialchars($page['seo_description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>SEO ключевые слова</label>
                <input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($page['seo_keywords'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
    <script>
        var editor = ace.edit("content-editor");
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/html");
        editor.setOptions({
            fontSize: "14px",
            showPrintMargin: false,
            enableBasicAutocompletion: true,
            enableSnippets: true,
            enableLiveAutocompletion: true
        });

        // Синхронизация содержимого редактора с скрытым input
        editor.session.on('change', function() {
            document.getElementById('content-input').value = editor.getValue();
        });
    </script>
</section>