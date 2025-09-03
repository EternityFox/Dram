<section class="content">
    <div class="container">
        <h1>Управление компаниями</h1>
        <form action="/admin/manage-companies" method="POST" class="mt-4">
            <h3>Создать компанию</h3>
            <div class="form-group mt-2">
                <label>Название:</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <button type="submit" name="create_company" class="btn btn-primary mt-2">Создать</button>
        </form>
        <form action="/admin/manage-companies" method="POST" class="mt-4" id="editCompanyForm" style="display:none;">
            <h3>Редактировать компанию</h3>
            <input type="hidden" name="company_id" id="editCompanyId">
            <div class="form-group">
                <label>Название:</label>
                <input type="text" class="form-control" name="name" id="editCompanyName" required>
            </div>
            <button type="submit" name="edit_company" class="btn btn-primary">Сохранить</button>
            <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('editCompanyForm').style.display='none';">Отмена
            </button>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const editButtons = document.querySelectorAll('.edit-btn');
                if (editButtons.length === 0) {
                    console.log('No edit buttons found');
                    return;
                }

                editButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        console.log('Edit button clicked');

                        const companyId = this.getAttribute('data-id');
                        const company = <?php echo json_encode($companies); ?>.
                        find(c => c.id == companyId);

                        if (!company) {
                            console.log('Company not found for ID:', companyId);
                            return;
                        }

                        console.log('Selected company:', company);

                        document.getElementById('editCompanyId').value = company.id;
                        document.getElementById('editCompanyName').value = company.name || '';
                        document.getElementById('editCompanyForm').style.display = 'block';
                    });
                });
            });
        </script>

        <h3 class="mt-4">Список компаний</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>
        <table class="table mt-2">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($companies as $company): ?>
                <tr>
                    <td><?= $company['id'] ?></td>
                    <td><?= htmlspecialchars($company['name']) ?></td>
                    <td>
                        <form action="/admin/manage-companies" method="POST" style="display:inline;">
                            <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                            <button type="button" name="edit_company" class="btn btn-warning btn-sm edit-btn"
                                    data-id="<?= $company['id'] ?>">Редактировать
                            </button>
                        </form>
                        <form action="/admin/manage-companies" method="POST" style="display:inline;"
                              onsubmit="return confirm('Удалить компанию?');">
                            <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                            <button type="submit" name="delete_company" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                        <a href="/user/company/<?= $company['id'] ?>" class="btn btn-success btn-sm">Просмотр точек</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>