<section class="content">
    <div class="container">
        <h1>Заявки пользователей</h1>

        <?php if (!empty($_GET['deleted'])): ?>
            <div class="alert alert-success mt-3">Заявка удалена.</div>
        <?php endif; ?>

        <form class="row g-2 mt-3 mb-3" method="get" action="/admin/requests">
            <div class="col-12 col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Поиск по теме/сообщению/логину"
                       value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="new" <?= $status==='new'?'selected':'' ?>>new</option>
                    <option value="in_progress" <?= $status==='in_progress'?'selected':'' ?>>in_progress</option>
                    <option value="done" <?= $status==='done'?'selected':'' ?>>done</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-primary w-100">Фильтровать</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Пользователь</th>
                    <th>Тема</th>
                    <th>Статус</th>
                    <th>Создана</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if ($rows): foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td><?= htmlspecialchars($r['login'] ?? '—') ?></td>
                        <td class="text-truncate" style="max-width:380px;"><?= htmlspecialchars($r['subject']) ?></td>
                        <td>
              <span class="badge <?= $r['status']==='done'?'bg-success':($r['status']==='in_progress'?'bg-warning text-dark':'bg-secondary') ?>">
                <?= htmlspecialchars($r['status']) ?>
              </span>
                        </td>
                        <td><?= htmlspecialchars($r['created_at']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="/admin/request/<?= (int)$r['id'] ?>">Открыть</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-muted">Заявок не найдено.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
