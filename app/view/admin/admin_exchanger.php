<?php /** @var array $exchanger */ ?>
<section class="content">
    <div class="container">
        <div><a href="/admin/"><img src="/img/clue-link.png" alt=""> Назад в админку</a></div>

        <h1>Редактирование банка</h1>
        <form action="" method="POST">
            <div class="card mb-4">
                <h5 class="card-header">Название обменника</h5>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3">
                        <?php foreach (['ru', 'en', 'am'] as $lang): ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $lang === 'ru' ? ' active' : '' ?>" data-bs-toggle="tab" href="#bank_name_<?= $lang ?>"><?= strtoupper($lang) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="tab-content">
                        <?php foreach (['ru', 'en', 'am'] as $lang): ?>
                            <div class="tab-pane fade<?= $lang === 'ru' ? ' show active' : '' ?>" id="bank_name_<?= $lang ?>">
                                <div class="form-group">
                                    <label>Название (<?= strtoupper($lang) ?>)</label>
                                    <input type="text" class="form-control" name="bank[name][<?= $lang ?>]" value="<?= htmlspecialchars($exchanger['name'][$lang] ?? '') ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Филиалы банка -->
            <div class="accordion" id="branchesAccordion">
                <?php $i = 0; foreach ($exchanger['baranches'] as $key => $branch): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $i ?>">
                            <button class="accordion-button<?= $i > 0 ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $i ?>">
                                <?= htmlspecialchars($branch['name']['ru'] ?? $key) ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $i ?>" class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>" aria-labelledby="heading<?= $i ?>" data-bs-parent="#branchesAccordion">
                            <div class="accordion-body">
                                <ul class="nav nav-tabs mb-3">
                                    <?php foreach (['ru', 'en', 'am'] as $lang): ?>
                                        <li class="nav-item">
                                            <a class="nav-link<?= $lang === 'ru' ? ' active' : '' ?>" data-bs-toggle="tab" href="#tab<?= $i ?>_<?= $lang ?>"><?= strtoupper($lang) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="tab-content">
                                    <?php foreach (['ru', 'en', 'am'] as $lang): ?>
                                        <div class="tab-pane fade<?= $lang === 'ru' ? ' show active' : '' ?>" id="tab<?= $i ?>_<?= $lang ?>">
                                            <div class="mb-2">
                                                <label>Название (<?= strtoupper($lang) ?>)</label>
                                                <input type="text" class="form-control" name="bank[baranches][<?= $key ?>][name][<?= $lang ?>]" value="<?= htmlspecialchars($branch['name'][$lang] ?? '') ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label>Адрес (<?= strtoupper($lang) ?>)</label>
                                                <input type="text" class="form-control" name="bank[baranches][<?= $key ?>][address][<?= $lang ?>]" value="<?= htmlspecialchars($branch['address'][$lang] ?? '') ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label>Часы работы (<?= strtoupper($lang) ?>)</label>
                                                <textarea class="form-control" name="bank[baranches][<?= $key ?>][hours][<?= $lang ?>]"><?= htmlspecialchars($branch['hours'][$lang] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mb-2">
                                    <label>Телефоны</label>
                                    <div class="row g-2">
                                        <?php foreach ($branch['phones'] ?? [] as $p => $phone): ?>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="bank[baranches][<?= $key ?>][phones][<?= $p ?>][text]" placeholder="Номер" value="<?= htmlspecialchars($phone['text']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="bank[baranches][<?= $key ?>][phones][<?= $p ?>][href]" placeholder="tel:" value="<?= htmlspecialchars($phone['href']) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label>Email</label>
                                    <div class="row g-2">
                                        <?php foreach ($branch['emails'] ?? [] as $e => $email): ?>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="bank[baranches][<?= $key ?>][emails][<?= $e ?>][text]" placeholder="Email" value="<?= htmlspecialchars($email['text']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="bank[baranches][<?= $key ?>][emails][<?= $e ?>][href]" placeholder="mailto:" value="<?= htmlspecialchars($email['href']) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label>Официальные сайты</label>
                                    <?php foreach ($branch['of_sites'] ?? [] as $s => $site): ?>
                                        <input type="text" class="form-control mb-2" name="bank[baranches][<?= $key ?>][of_sites][<?= $s ?>]" value="<?= htmlspecialchars($site) ?>">
                                    <?php endforeach; ?>
                                </div>
                                <div class="mb-2">
                                    <label>Социальные сети</label>
                                    <?php foreach ($branch['socials'] ?? [] as $j => $link): ?>
                                        <input type="text" class="form-control mb-2" name="bank[baranches][<?= $key ?>][socials][<?= $j ?>]" value="<?= htmlspecialchars($link) ?>">
                                    <?php endforeach; ?>
                                </div>
                                <div class="mb-2">
                                    <label>Координаты</label>
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="text" class="form-control" placeholder="Широта" name="bank[baranches][<?= $key ?>][latitude]" value="<?= htmlspecialchars($branch['latitude'] ?? '') ?>">
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" placeholder="Долгота" name="bank[baranches][<?= $key ?>][longitude]" value="<?= htmlspecialchars($branch['longitude'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label>Путь до изображения</label>
                                    <input type="text" class="form-control" name="bank[baranches][<?= $key ?>][img]" value="<?= htmlspecialchars($branch['img'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $i++; endforeach; ?>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</section>
