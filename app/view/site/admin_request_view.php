<?php /** @var array $r */ ?>
<section class="content">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h1 class="mb-0">Заявка #<?= (int)$r['id'] ?></h1>
            <div class="d-flex gap-2">
                <form action="/admin/request/<?= (int)$r['id'] ?>/del" method="post"
                      onsubmit="return confirm('Удалить заявку #<?= (int)$r['id'] ?> безвозвратно?');">
                    <button class="btn btn-outline-danger">Удалить</button>
                </form>
                <a class="btn btn-secondary" href="/admin/requests">К списку</a>
            </div>
        </div>

        <?php if (!empty($saved)): ?>
            <div class="alert alert-success mt-3">Сохранено.</div>
        <?php endif; ?>

        <div class="row g-4 mt-2">
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-header fw-bold">Сообщение</div>
                    <div class="card-body">
                        <div class="mb-2 text-muted small">
                            Пользователь: <strong><?= htmlspecialchars($r['login'] ?? '—') ?></strong>
                            <?php if (!empty($r['email'])): ?>
                                · email: <a href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= htmlspecialchars($r['email']) ?></a>
                            <?php endif; ?>
                            · создана: <?= htmlspecialchars($r['created_at']) ?>
                        </div>
                        <h5 class="mb-2"><?= htmlspecialchars($r['subject']) ?></h5>
                        <div class="mb-3" style="white-space:pre-wrap;"><?= htmlspecialchars($r['message']) ?></div>

                        <?php if (!empty($r['file_path'])): ?>
                            <div class="mt-3">
                                Приложение:
                                <a class="link-primary" href="/<?= htmlspecialchars($r['file_path']) ?>" target="_blank" rel="noopener">
                                    скачать файл
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-header fw-bold">Ответ / статус</div>
                    <div class="card-body">
                        <form method="post" action="/admin/request/<?= (int)$r['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="new" <?= $r['status']==='new'?'selected':'' ?>>new</option>
                                    <option value="in_progress" <?= $r['status']==='in_progress'?'selected':'' ?>>in_progress</option>
                                    <option value="done" <?= $r['status']==='done'?'selected':'' ?>>done</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ответ пользователю</label>
                                <textarea name="answer" class="form-control" rows="8"
                                          placeholder="Введите текст ответа..."><?= htmlspecialchars($r['answer'] ?? '') ?></textarea>
                                <?php if (!empty($r['answered_at'])): ?>
                                    <div class="small text-muted mt-1">Отвечено: <?= htmlspecialchars($r['answered_at']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary">Сохранить</button>
                                <a class="btn btn-outline-secondary" href="/admin/requests">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
