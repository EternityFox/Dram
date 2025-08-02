<section class="content">
    <div class="container">
        <h1>Управление типами топлива</h1>

        <!-- Форма для создания нового типа топлива -->
        <form action="/admin/manage-fuel-types" method="POST" class="mt-4">
            <h3>Создать тип топлива</h3>
            <div class="form-group">
                <label>Название:</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="form-group">
                <label>Описание:</label>
                <textarea class="form-control" name="description"></textarea>
            </div>
            <button type="submit" name="create_fuel_type" class="btn btn-primary">Создать</button>
        </form>

        <!-- Форма для редактирования типа топлива -->
        <form action="/admin/manage-fuel-types" method="POST" class="mt-4" id="editFuelTypeForm" style="display:none;">
            <h3>Редактировать тип топлива</h3>
            <input type="hidden" name="fuel_type_id" id="editFuelTypeId">
            <div class="form-group">
                <label>Название:</label>
                <input type="text" class="form-control" name="name" id="editFuelTypeName" required>
            </div>
            <div class="form-group">
                <label>Описание:</label>
                <textarea class="form-control" name="description" id="editFuelTypeDescription"></textarea>
            </div>
            <button type="submit" name="edit_fuel_type" class="btn btn-primary">Сохранить</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editFuelTypeForm').style.display='none';">Отмена</button>
        </form>

        <!-- Список типов топлива -->
        <h3 class="mt-4">Список типов топлива</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <table class="table mt-2">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fuelTypes as $fuelType): ?>
                <tr>
                    <td><?= $fuelType['id'] ?></td>
                    <td><?= htmlspecialchars($fuelType['name']) ?></td>
                    <td><?= htmlspecialchars($fuelType['description'] ?? 'Нет') ?></td>
                    <td>
                        <form action="/admin/manage-fuel-types" method="POST" style="display:inline;">
                            <input type="hidden" name="fuel_type_id" value="<?= $fuelType['id'] ?>">
                            <button type="button" name="edit_fuel_type" class="btn btn-warning btn-sm edit-btn" data-id="<?= $fuelType['id'] ?>" data-name="<?= htmlspecialchars($fuelType['name']) ?>" data-description="<?= htmlspecialchars($fuelType['description'] ?? '') ?>">Редактировать</button>
                        </form>
                        <form action="/admin/manage-fuel-types" method="POST" style="display:inline;" onsubmit="return confirm('Удалить тип топлива?');">
                            <input type="hidden" name="fuel_type_id" value="<?= $fuelType['id'] ?>">
                            <button type="submit" name="delete_fuel_type" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript для обработки редактирования -->
    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                document.getElementById('editFuelTypeId').value = id;
                document.getElementById('editFuelTypeName').value = name;
                document.getElementById('editFuelTypeDescription').value = description;
                document.getElementById('editFuelTypeForm').style.display = 'block';
            });
        });
    </script>
</section>