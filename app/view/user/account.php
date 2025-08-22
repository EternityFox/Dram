<?php
$lang = $lang ?? fn($s) => $s;

$toast = null;
if (!empty($_GET['pwd_ok'])) $toast = ['type' => 'success', 'msg' => $lang('Пароль успешно изменён')];
if (!empty($_GET['pwd_error'])) $toast = ['type' => 'danger', 'msg' => $_GET['pwd_error']];
if (!empty($_GET['req_ok'])) $toast = ['type' => 'success', 'msg' => $lang('Заявка отправлена')];
if (!empty($_GET['req_error'])) $toast = ['type' => 'danger', 'msg' => $_GET['req_error']];
if (!empty($_GET['del_error'])) $toast = ['type' => 'danger', 'msg' => $_GET['del_error']];
?>
    <section class="content account-hero account-hero-background">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-8 d-flex align-items-center gap-3">
                    <div class="avatar-circle"><?= strtoupper(mb_substr($user['login'] ?? 'U', 0, 1)) ?></div>
                    <div>
                        <h1 class="display-6 fw-bold mb-1"><?= $lang("Личный кабинет") ?></h1>
                        <div class="text-muted">
                            <?= $lang("Добро пожаловать,") ?> <strong><?= htmlspecialchars($user['login']) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-end">
                    <a class="btn btn-light btn-outline-dark rounded-pill px-4" href="/logout"><?= $lang("Выйти") ?></a>
                </div>
            </div>
        </div>
    </section>

    <section class="content account-hero">
        <div class="container py-4">
            <h5 class="section-title"><?= $lang("Быстрый доступ к сервисам dram") ?></h5>
            <div class="row g-3 mb-4">
                <?php
                $tiles = [
                    [
                        '/direct/cash',
                        'nav-icon/exchange.png',
                        $lang("Курсы валют"),
                        $lang("Актуальные курсы наличных, безналичных и карт, собранные по банкам и обменным пунктам Армении для быстрого сравнения и выбора лучшего варианта")
                    ],
                    [
                        '/converter',
                        'nav-icon/calculator 5.png',
                        $lang("Конвертер валют"),
                        $lang("Удобный инструмент для мгновённых расчётов обмена валют по текущему курсу — просто введите сумму и получите точный результат")
                    ],
                    [
                        '/charts',
                        'nav-icon/Charts.png',
                        $lang("Графики котировок"),
                        $lang("Наглядные графики динамики курсов валют и драгоценных металлов, с возможностью анализа трендов и сравнения разных периодов")
                    ],
                    [
                        '/fuel',
                        'nav-icon/LPG.png',
                        $lang("Цены на топливо"),
                        $lang("Регулярные обновления стоимости бензина, дизеля и газа на АЗС — следите за изменениями цен в реальном времени")
                    ],
                    [
                        '/fonts-list',
                        'nav-icon/alphabet 2.png',
                        $lang("Каталог шрифтов"),
                        $lang("Большая коллекция шрифтов для работы и творчества: просматривайте, скачивайте и находите нужный стиль для вашего проекта")
                    ],
                    [
                        '/plate-number-search',
                        'nav-icon/plate 3.png',
                        $lang("Номерные знаки"),
                        $lang("Удобный поиск по базе автомобильных номеров — проверяйте доступность и резервируйте красивые номера для вашего авто")
                    ],
                ];
                foreach ($tiles as [$href, $icon, $title, $sub]): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a class="feature-card h-100" href="<?= htmlspecialchars($href) ?>">
                            <div class="fc-top">
                                <img src="/img/<?= htmlspecialchars($icon) ?>" alt="" class="">
                                <div class="fc-title ms-4"><?= htmlspecialchars($title) ?></div>
                            </div>
                            <div class="fc-body">
                                <div class="fc-sub"><?= htmlspecialchars($sub) ?></div>
                            </div>
                            <div class="fc-arrow">→</div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <ul class="nav nav-pills mb-3 account-switch" id="accSwitch" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="request-tab" data-bs-toggle="tab" data-bs-target="#request"
                            type="button" role="tab">
                        <?= $lang("Оставить заявку") ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tools-tab" data-bs-toggle="tab" data-bs-target="#tools"
                            type="button" role="tab">
                        <?= $lang("Инструменты аккаунта") ?>
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade" id="tools" role="tabpanel" aria-labelledby="tools-tab">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="tool-compact danger h-100">
                                <div class="tc-row">
                                    <svg class="tc-ico" width="20" height="20" aria-hidden="true">
                                        <use xlink:href="#i-delete"></use>
                                    </svg>
                                    <div>
                                        <div class="tc-title"><?= $lang("Удалить аккаунт") ?></div>
                                        <div class="tc-sub"><?= $lang("Безвозвратно удалить данные") ?></div>
                                    </div>
                                </div>
                                <button class="btn btn-danger btn-sm mt-2" data-bs-toggle="modal"
                                        data-bs-target="#delModal"><?= $lang("Удалить...") ?></button>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="tool-compact h-100">
                                <div class="tc-row">
                                    <svg class="tc-ico" width="20" height="20" aria-hidden="true">
                                        <use xlink:href="#i-logout"></use>
                                    </svg>
                                    <div>
                                        <div class="tc-title"><?= $lang("Выйти из аккаунта") ?></div>
                                        <div class="tc-sub"><?= $lang("Завершить текущую сессию") ?></div>
                                    </div>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm mt-2"
                                   href="/logout"><?= $lang("Выйти") ?></a>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <div class="glass-card h-100">
                                <div class="glass-head d-flex align-items-center gap-2">
                                    <svg width="18" height="18" aria-hidden="true">
                                        <use xlink:href="#i-lock"></use>
                                    </svg>
                                    <span><?= $lang("Смена пароля") ?></span>
                                </div>
                                <div class="glass-body">
                                    <form method="post" action="/user/account/change-password" id="pwdForm"
                                          class="vstack gap-3">
                                        <div>
                                            <label class="form-label"><?= $lang("Текущий пароль") ?></label>
                                            <input name="old_password" type="password" class="form-control"
                                                   autocomplete="current-password" required>
                                        </div>

                                        <?php
                                        $idPrefix = 'accNew';
                                        $nameNew = 'new_password';
                                        $nameConfirm = 'new_password_confirm';
                                        $labelNew = $lang("Новый пароль");
                                        $labelConfirm = $lang("Повторите пароль");
                                        $minLength = 6;
                                        $autofill = 'new-password';
                                        include __DIR__ . '/../site/partials/password_fields.php';
                                        ?>

                                        <div class="d-flex gap-2 mt-3">
                                            <button class="btn btn-primary"><?= $lang("Обновить пароль") ?></button>
                                            <button class="btn btn-outline-secondary"
                                                    type="reset"><?= $lang("Сбросить") ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="tab-pane fade show active" id="request" role="tabpanel" aria-labelledby="request-tab">
                    <div class="glass-card">
                        <div class="glass-head d-flex align-items-center gap-2">
                            <svg width="18" height="18" aria-hidden="true">
                                <use xlink:href="#i-mail"></use>
                            </svg>
                            <span><?= $lang("Оставить заявку") ?></span>
                        </div>
                        <div class="glass-body">
                            <form method="post" action="/user/account/request" enctype="multipart/form-data"
                                  class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label"><?= $lang("Тема") ?></label>
                                    <input name="subject" class="form-control" maxlength="200" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label"><?= $lang("Сообщение") ?></label>
                                    <textarea name="message" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label"><?= $lang("Файл (необязательно)") ?></label>
                                    <input type="file" name="file" class="form-control">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary"><?= $lang("Отправить заявку") ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="section-title mt-4"><?= $lang("Последние заявки и ответы") ?></h5>
            <div class="glass-card p-0">
                <?php if (!empty($lastRequests)): ?>
                    <div class="list-group list-group-flush request-list">
                        <?php foreach ($lastRequests as $r): ?>
                            <details class="req-item list-group-item">
                                <summary class="req-head d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3 me-2">
                                        <span class="req-id">#<?= (int)$r['id'] ?></span>
                                        <span class="req-title"><?= htmlspecialchars($r['subject']) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge
                                            <?= $r['status'] === 'done' ? 'bg-success' : ($r['status'] === 'in_progress' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                            <?= htmlspecialchars($r['status']) ?>
                                        </span>
                                        <span class="req-date text-muted"><?= htmlspecialchars($r['created_at']) ?></span>
                                    </div>
                                </summary>
                                <div class="req-body">
                                    <?php if (!empty($r['answer'])): ?>
                                        <div class="small"><?= nl2br(htmlspecialchars($r['answer'])) ?></div>
                                        <?php if (!empty($r['answered_at'])): ?>
                                            <div class="text-muted small mt-2"><?= $lang("Отвечено:") ?> <?= htmlspecialchars($r['answered_at']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted"><?= $lang("Ответ пока не получен") ?></span>
                                    <?php endif; ?>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted"><?= $lang("Заявок пока нет.") ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal fade" id="delModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="post" action="/user/account/delete">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger"><?= $lang("Подтвердите удаление аккаунта") ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="<?= $lang("Закрыть") ?>"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3"><?= $lang("Введите текущий пароль для подтверждения") ?>:</p>
                        <input type="password" name="confirm_password" class="form-control" required>
                        <p class="small text-muted mt-3"><?= $lang("Действие необратимо. Все связанные данные будут удалены.") ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal"><?= $lang("Отмена") ?></button>
                        <button class="btn btn-danger"><?= $lang("Удалить аккаунт") ?></button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($toast): ?>
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
                <div id="accToast"
                     class="toast align-items-center text-bg-<?= htmlspecialchars($toast['type']) ?> border-0"
                     role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body ms-auto"><?= htmlspecialchars($toast['msg']) ?></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
            <symbol id="i-converter" viewBox="0 0 24 24">
                <path d="M4 7h13m0 0-3-3m3 3-3 3M20 17H7m0 0 3-3m-3 3 3 3" stroke="currentColor" stroke-width="1.7"
                      fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-charts" viewBox="0 0 24 24">
                <path d="M4 19V5m0 14h16M8 16V9m4 7V5m4 11v-5" stroke="currentColor" stroke-width="1.7" fill="none"
                      stroke-linecap="round"/>
            </symbol>
            <symbol id="i-fuel" viewBox="0 0 24 24">
                <path d="M6 19V7a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v12M10 9h2m6 10v-8l2 2v3a2 2 0 0 1-2 2"
                      stroke="currentColor"
                      stroke-width="1.7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-fonts" viewBox="0 0 24 24">
                <path d="M4 19h6m0 0 5-14h3M10 19l5-14m-3 9h6" stroke="currentColor" stroke-width="1.7" fill="none"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-plate" viewBox="0 0 24 24">
                <rect x="3" y="7" width="18" height="10" rx="2" stroke="currentColor" stroke-width="1.7" fill="none"/>
                <path d="M7 12h5m4 0h1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </symbol>
            <symbol id="i-rates" viewBox="0 0 24 24">
                <path d="M4 12a6 6 0 1 1 12 0v3M16 7l4-2v14" stroke="currentColor" stroke-width="1.7" fill="none"
                      stroke-linecap="round"/>
                <circle cx="4" cy="15" r="2" fill="currentColor"/>
            </symbol>
            <symbol id="i-delete" viewBox="0 0 24 24">
                <path d="M4 7h16M9 7V5h6v2m-7 4v7m5-7v7M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="1.7"
                      fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-logout" viewBox="0 0 24 24">
                <path d="M15 17l5-5-5-5m5 5H9m0 8H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3" stroke="currentColor"
                      stroke-width="1.7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-lock" viewBox="0 0 24 24">
                <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.7" fill="none"/>
                <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.7" fill="none"/>
            </symbol>
            <symbol id="i-mail" viewBox="0 0 24 24">
                <path d="M4 6h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2zm0 2 8 5 8-5"
                      stroke="currentColor" stroke-width="1.7" fill="none" stroke-linecap="round"
                      stroke-linejoin="round"/>
            </symbol>
        </svg>
    </section>
<?php if ($toast): ?>
    <script>
        (function () {
            var el = document.getElementById('accToast');
            if (el && window.bootstrap) {
                new bootstrap.Toast(el, {delay: 3000}).show();
            }
        })();
    </script>
<?php endif; ?>