<section class="content py-5">
    <div class="container">
        <?php
        $searchNumberAuto = [
            "search-title-number-auto" => [
                'ru' => 'Поиск доступности номерного знака',
                'am' => 'Պետհամարանիշի հասանելիության որոնում',
                'en' => 'License plate availability search'
            ],
            "wrong-text-number-auto" => [
                'ru' => 'В поиск не попадают номера с особым требованием к регистрации транспортного средства, то есть номера с нулями. Для полного списка вместо букв пробел.',
                'am' => 'Որոնման մեջ չեն ներառվում համարանիշերը, որոնք ունեն տրանսպորտային միջոցի գրանցման հատուկ պահանջներ, օրինակ՝ զրոներով համարանիշերը։ Լիարժեք ցուցակի համար տառերի փոխարեն օգտագործեք բացատ։',
                'en' => 'Numbers with special vehicle registration requirements, such as those with zeros, are not included in the search. For a full list, use a space instead of letters.'
            ],
            "two-number" => [
                'ru' => '2 цифры',
                'am' => '2 թվանշան',
                'en' => '2 digits',
            ],
            "three-number" => [
                'ru' => '3 цифры',
                'am' => '3 թվանշան',
                'en' => '3 digits',
            ],
            "two-word" => [
                'ru' => '2 буквы',
                'am' => '2 տառ',
                'en' => '2 letters',
            ],
            "check-availability" => [
                'ru' => 'Проверить наличие',
                'am' => 'Ստուգել հասանելիությունը',
                'en' => 'Check availability',
            ],
            "list-result-search" => [
                'ru' => 'Список по результатам поиска',
                'am' => 'Որոնման արդյունքների ցանկ',
                'en' => 'Search results list',
            ],
            "license-plate" => [
                'ru' => 'Номерной знак',
                'am' => 'Պետհամարանիշ',
                'en' => 'License plate',
            ],
            "fixed-price" => [
                'ru' => 'Фиксированная цена',
                'am' => 'Ֆիքսված գին',
                'en' => 'Fixed price',
            ],
            "empty" => [
                'ru' => 'Пусто',
                'am' => 'Դատարկ',
                'en' => 'Empty',
            ],
            "individual" => [
                'ru' => 'Физическое лицо',
                'am' => 'Ֆիզիկական անձ',
                'en' => 'Individual',
            ],
            "legal" => [
                'ru' => 'Юридическое лицо',
                'am' => 'Իրավաբանական անձ',
                'en' => 'Legal entity',
            ]
        ];
        ?>
        <div class="text-center">
            <h1 class="mb-3 mb-4 search-number-text"><?= $searchNumberAuto['search-title-number-auto'][$lang->getLang()] ?></h1>
            <p class="lead alert-box">
                <strong>⚠ </strong><?= $searchNumberAuto['wrong-text-number-auto'][$lang->getLang()] ?>
            </p>
        </div>
        <ul class="tabs_holder_ul mt-5 pb-5">
            <li data-id="#tab-id-1" class="active_tabe"><?= $searchNumberAuto['individual'][$lang->getLang()] ?></li>
            <li data-id="#tab-id-2" class=""><?= $searchNumberAuto['legal'][$lang->getLang()] ?></li>
        </ul>
        <div class="car_number_button_block">
            <div class="car_numbers_block">
                <div class="flag_car_number"><img src="img/flag_plate_number.png" alt="Флаг на номер" width="102"
                                                  height="106"></div>
                <div class="car_numbers">
                    <div class="two_numbers block">
                        <div class="symbol_count symbol_count_two">
                            <span><?= $searchNumberAuto['two-number'][$lang->getLang()] ?></span>
                        </div>
                        <input type="number" name="pre" id="personal_pre" autocomplete="off" maxlength="2"
                               value="" placeholder="11">
                    </div>
                    <div class="letters block">
                        <div class="symbol_count symbol_count_letters">
                            <span><?= $searchNumberAuto['two-word'][$lang->getLang()] ?></span>
                        </div>
                        <input type="text" name="code" id="personal_code" autocomplete="off"
                               maxlength="2" value="" placeholder="xx">
                    </div>
                    <div class="three_numbers block">
                        <div class="symbol_count symbol_count_three">
                            <span><?= $searchNumberAuto['three-number'][$lang->getLang()] ?></span>
                        </div>
                        <input type="number" name="post" id="personal_post" autocomplete="off"
                               maxlength="3" value="" placeholder="111">
                    </div>
                    <input type="hidden" id="lang-data" data-lang="<?= $lang->getLang() ?>">
                </div>
            </div>
            <div>
                <button type="submit" id="search-btn-number-car"
                        class="btn"><?= $searchNumberAuto['check-availability'][$lang->getLang()] ?></button>
            </div>
        </div>
        <div class="error-message"></div>
        <div class="loader"></div>
        <div id="result" class="result-section">
            <h2 class="inner-content__title"><?= $searchNumberAuto['list-result-search'][$lang->getLang()] ?></h2>
            <div class="table-box">
                <table>
                    <thead>
                    <tr>
                        <th>№</th>
                        <th><?= $searchNumberAuto['license-plate'][$lang->getLang()] ?></th>
                        <th><?= $searchNumberAuto['fixed-price'][$lang->getLang()] ?></th>
                    </tr>
                    </thead>
                    <tbody class="table-result-append">
                    <tr>
                        <td colspan="3" class="empty-row"><?= $searchNumberAuto['empty'][$lang->getLang()] ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>