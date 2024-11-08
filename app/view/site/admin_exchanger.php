<section class="content">
    <div class="container">
        <div>
            <a href="/admin/"><img src="img/clue-link.png" alt="">&nbsp; Назад в админку</a>
        </div>

        <h1>Редактирование обменника</h1>

        <form action="" method="POST" class="mt-4">

            <div class="card">
                <h5 class="card-header">
                    <img src="img/exchanger/<?= $bank['raid'] ?>.png" alt=""
                         style="width: 32px; height: 16px;">
                    <?= $bank['name'] ?>
                </h5>

                <div class="card-body">
                    <div class="form-group">
                        <ul class="nav nav-tabs" style="border-bottom: none;">
                            <li class="nav-item head">
                                <div class="nav-link tabs active" data-target="#bank_info_ru">Ру</div>
                            </li>
                            <li class="nav-item head">
                                <div class="nav-link tabs" data-target="#bank_info_en">EN</div>
                            </li>
                            <li class="nav-item head">
                                <div class="nav-link tabs" data-target="#bank_info_am">Հա</div>
                            </li>
                        </ul>

                        <table id="bank_info_ru" class="bank-info mb-3 collapse show">
                            <tr>
                                <th class="bank-info-th"><?= $lang("Центральный офис") ?></th>
                                <td><input type="text" name="bank[head_office][ru]" value='<?= $bank['head_office']['ru'] ?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Телефон(ы)") ?></th>
                                <td><input type="text" name="bank[phone][ru]" value='<?= $bank['phone']['ru'] ?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Факс") ?></th>
                                <td><input type="text" name="bank[fax][ru]" value='<?= $bank['fax']['ru']?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Сайт") ?></th>
                                <td><input type="text" name="bank[url][ru]" value='<?= $bank['url']['ru'] ?>'></td>
                            </tr>
                        </table>

                        <table id="bank_info_en" class="bank-info mb-3 collapse">
                            <tr>
                                <th class="bank-info-th"><?= $lang("Центральный офис") ?></th>
                                <td><input type="text" name="bank[head_office][en]" value='<?= $bank['head_office']['en'] ?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Телефон(ы)") ?></th>
                                <td><input type="text" name="bank[phone][en]" value='<?= $bank['phone']['en'] ?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Факс") ?></th>
                                <td><input type="text" name="bank[fax][en]" value='<?= $bank['fax']['en']?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Сайт") ?></th>
                                <td><input type="text" name="bank[url][en]" value='<?= $bank['url']['en'] ?>'></td>
                            </tr>
                        </table>

                        <table id="bank_info_am" class="bank-info mb-3 collapse">
                            <tr>
                                <th class="bank-info-th"><?= $lang("Центральный офис") ?></th>
                                <td><input type="text" name="bank[head_office][am]" value='<?= $bank['head_office']['am'] ?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Телефон(ы)") ?></th>
                                <td><input type="text" name="bank[phone][am]" value='<?= $bank['phone']['am'] ?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Факс") ?></th>
                                <td><input type="text" name="bank[fax][am]" value='<?= $bank['fax']['am']?>'></td>
                            </tr>
                            <tr>
                                <th class="bank-info-th"><?= $lang("Сайт") ?></th>
                                <td><input type="text" name="bank[url][am]" value='<?= $bank['url']['am'] ?>'></td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary mt-2">Сохранить</button>
                    </div>

                    <div class="form-group">
                        <ul class="nav nav-tabs" style="border-bottom: none;">
                            <li class="nav-item head">
                                <div class="nav-link tabs active" data-target="#branches_info_ru">Ру</div>
                            </li>
                            <li class="nav-item head">
                                <div class="nav-link tabs" data-target="#branches_info_en">EN</div>
                            </li>
                            <li class="nav-item head">
                                <div class="nav-link tabs" data-target="#branches_info_am">Հա</div>
                            </li>
                        </ul>

                        <table id="branches_info_ru" class="branches branches-admin collapse show">
                            <tr class="branches-th">
                                <th></th>
                                <th><?= $lang("Отделение") ?></th>
                                <th><?= $lang("Адрес") ?></th>
                                <th><?= $lang("Телефон(ы)") ?></th>
                            </tr>
                            <?php foreach ($bank['baranches'] as $key => $branch): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td>
                                        <textarea name="bank[baranches][name][ru][]"><?= $branch['name']['ru'] ?></textarea>
                                    </td>
                                    <td>
                                        <textarea name="bank[baranches][address][ru][]"><?= $branch['address']['ru'] ?></textarea>
                                    </td>
                                    <td>
                                        <textarea name="bank[baranches][phone][ru][]"><?= $branch['phone']['ru'] ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </table>

                        <table id="branches_info_en" class="branches branches-admin collapse">
                            <tr class="branches-th">
                                <th></th>
                                <th><?= $lang("Отделение") ?></th>
                                <th><?= $lang("Адрес") ?></th>
                                <th><?= $lang("Телефон(ы)") ?></th>
                            </tr>
                            <?php foreach ($bank['baranches'] as $key => $branch): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td>
                                        <textarea name="bank[baranches][name][en][]"><?= $branch['name']['en'] ?></textarea>
                                    </td>
                                    <td>
                                        <textarea name="bank[baranches][address][en][]"><?= $branch['address']['en'] ?></textarea>
                                    </td>
                                    <td>
                                        <textarea name="bank[baranches][phone][en][]"><?= $branch['phone']['en'] ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </table>

                        <table id="branches_info_am" class="branches branches-admin collapse">
                            <tr class="branches-th">
                                <th></th>
                                <th><?= $lang("Отделение") ?></th>
                                <th><?= $lang("Адрес") ?></th>
                                <th><?= $lang("Телефон(ы)") ?></th>
                            </tr>
                            <?php foreach ($bank['baranches'] as $key => $branch): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td>
                                        <textarea name="bank[baranches][name][am][]"><?= $branch['name']['am'] ?></textarea>
                                    </td>
                                    <td>
                                        <textarea name="bank[baranches][address][am][]"><?= $branch['address']['am'] ?></textarea>
                                    </td>
                                    <td>
                                        <textarea name="bank[baranches][phone][am][]"><?= $branch['phone']['am'] ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary mt-4">Сохранить</button>
                    </div>

                    <div>
                        <a href="/admin/"><img src="img/clue-link.png" alt="">&nbsp; Назад в админку</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>