<section class="content">
    <div class="container">
        <h1>Управление пользователями</h1>
        <form action="/admin/manage-users" method="POST" class="mt-4">
            <h3>Создать пользователя</h3>
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" class="form-control" name="login" required>
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" class="form-control" name="email">
            </div>
            <div class="form-group">
                <label>Компания:</label>
                <select class="form-control" name="company_id">
                    <option value="">Нет компании</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['id'] ?>"><?= $company['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Роль:</label>
                <select class="form-control" name="role" required>
                    <option value="user">Пользователь</option>
                    <option value="company">Компания</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
            <button type="submit" name="create_user" class="btn btn-primary">Создать</button>
        </form>

        <form action="/admin/manage-users" method="POST" class="mt-4" id="editUserForm" style="display:none;">
            <h3>Редактировать пользователя</h3>
            <input type="hidden" name="user_id" id="editUserId">
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" class="form-control" name="login" id="editUserLogin" required>
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" class="form-control" name="password" id="editUserPassword">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" class="form-control" name="email" id="editUserEmail">
            </div>
            <div class="form-group">
                <label>Компания:</label>
                <select class="form-control" name="company_id" id="editUserCompanyId">
                    <option value="">Нет компании</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['id'] ?>"><?= $company['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Роль:</label>
                <select class="form-control" name="role" id="editUserRole" required>
                    <option value="user">Пользователь</option>
                    <option value="company">Компания</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
            <button type="submit" name="edit_user" class="btn btn-primary">Сохранить</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editUserForm').style.display='none';">Отмена</button>
        </form>

        <h3 class="mt-4">Список пользователей</h3>
        <table class="table mt-2">
            <thead>
            <tr>
                <th>ID</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Компания</th>
                <th>Роль</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['login']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['company_name'] ?? 'Нет') ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <form action="/admin/manage-users" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="button" name="edit_user" class="btn btn-warning btn-sm edit-btn" data-id="<?= $user['id'] ?>" data-login="<?= htmlspecialchars($user['login']) ?>" data-email="<?= htmlspecialchars($user['email']) ?>" data-company="<?= $user['company_id'] ?? '' ?>" data-role="<?= htmlspecialchars($user['role']) ?>">Редактировать</button>
                        </form>
                        <form action="/admin/manage-users" method="POST" style="display:inline;" onsubmit="return confirm('Удалить пользователя?');">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const login = this.getAttribute('data-login');
                const email = this.getAttribute('data-email');
                const companyId = this.getAttribute('data-company');
                const role = this.getAttribute('data-role');
                document.getElementById('editUserId').value = id;
                document.getElementById('editUserLogin').value = login;
                document.getElementById('editUserEmail').value = email;
                document.getElementById('editUserCompanyId').value = companyId;
                document.getElementById('editUserRole').value = role;
                document.getElementById('editUserForm').style.display = 'block';
            });
        });
    </script>
</section>