<section class="content">
    <div class="container">
        <h1>Список страниц</h1>
        <a href="/admin/create-page" class="btn btn-success mb-3">Создать новую страницу</a>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>URL</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pages as $page): ?>
                <tr>
                    <td><?= $page['id'] ?></td>
                    <td><?= htmlspecialchars($page['seo_title']) ?></td>
                    <td><?= htmlspecialchars($page['slug']) ?></td>
                    <td>
                        <a href="/admin/edit-page/<?= $page['id'] ?>" class="btn btn-primary">Редактировать</a>
                        <a href="/admin/delete-page/<?= $page['id'] ?>" class="btn btn-danger" onclick="return confirm('Вы уверены?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>