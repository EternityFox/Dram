<section class="content">
    <div class="container">
        <h1>Создать новую страницу</h1>
        <form action="/admin/create-page" method="POST">
            <div class="form-group">
                <label>URL (slug)</label>
                <input type="text" name="slug" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Содержимое</label>
                <div id="content-editor" style="height: 400px; width: 100%;"></div>
                <input type="hidden" name="content" id="content-input">
            </div>
            <div class="form-group">
                <label>SEO заголовок</label>
                <input type="text" name="seo_title" class="form-control">
            </div>
            <div class="form-group">
                <label>SEO описание</label>
                <textarea name="seo_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>SEO ключевые слова</label>
                <input type="text" name="seo_keywords" class="form-control">
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