$(document).ready(function () {
    try {
        $('.toggle .card-header').each(function () {
            $(this).click(function () {
                $(this).next().toggle();
            })
        });
        $('#pc-version').click(function () {
            $('#viewport').remove()
            let meta = document.createElement('meta');
            meta.name = "viewport";
            meta.content = "width=1200";
            meta.id = 'viewport'
            document.getElementsByTagName('head')[0].appendChild(meta);
            Cookies.set('show-pc-version', 1, {expires: 365});
        });

        if ('/web' === window.location.pathname || '1' == Cookies.get('show-pc-version')) {
            $('#pc-version').click();
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }


        if ($('.table-entry-item-input-placeholder').length) {
            var typed = new Typed('.table-entry-item-input-placeholder', {
                strings: ['120 USD', '250 USD', '52.5 USD'],
                typeSpeed: 120,
                backSpeed: 60,
                loop: true,
                showCursor: false,
            });
        }

        $(this).find('#ursuminput').css({"color": "transparent"});

        $('.table-entry-item-input').click(function () {
            $(this).find('.table-entry-item-input-placeholder').fadeOut(0);
            $(this).find('#ursuminput').css({"color": "#111"});
        });


        $(".table-entry-item-input input").focusout(function () {
            if ($(this).val() == '') {
                $(this).find('#ursuminput').css({"color": "transparent"});
                $(this).closest('.table-entry-item-input').find('.table-entry-item-input-placeholder').fadeIn(0);
            }
        });


        // value.split("").forEach(function(elem, index) {
        //     setTimeout(function() {
        //         $("#ursuminput").val($("#ursuminput").val() + elem);
        //     }, index * 500);
        //     $("#ursuminput").focus();
        // });

        $('#mobile-version').click(function () {
            $('#viewport').remove()
            let meta = document.createElement('meta');
            meta.name = "viewport";
            if (screen.width > 1024) meta.content = "width=" + 600;
            else meta.content = "width=" + screen.width;
            meta.id = 'viewport'
            document.getElementsByTagName('head')[0].appendChild(meta);
            Cookies.set('show-pc-version', 0, {expires: 365});
        });

        if ('/mob' === window.location.pathname || '0' == Cookies.get('show-pc-version')) {
            $('#mobile-version').click();
        }

        if ($(window).width() > 1200) {

            $('.header-search-arrow').click(function () {
                $('.header-menu').toggleClass('mob');
            });

        }
        if ($(window).width() > 1600) {
            $('.header-search-arrow').click(function () {
                $('.header-menu-block').slideToggle(300);
            });
        }
        if ($(window).width() <= 1200) {

            $('.header-search-arrow').click(function () {
                $('.header-menu-block').slideToggle(300);
            });

        }

        $('.tab-btn-item').not($('.idle')).click(function () {
            var tabIndex = $(this).attr('data-tab');
            $('.tab-btn-item').removeClass('active');
            $(this).addClass('active');
            $('.tab-content-item').fadeOut(50);
            $('.tab-content-item').eq(tabIndex).fadeIn(200);

        });

        $('.clue-close').click(function () {
            $(this).closest('.clue').addClass('hide');
        });
        $('.header-notif-block-inner-btn').click(function () {
            $('.header-notif-block-inner-btn').removeClass('active');
            $(this).addClass('active');
        });

        if ($(window).width() < 640) {
            // $('.tab-btn').slick({
            //     slidesToShow: 5,
            //     swipeToSlide: true,
            //     speed: 100,
            //     infinite: false,
            //     arrows: false,
            //     variableWidth: true,
            //     responsive: [{
            //         breakpoint: 500,
            //         settings: {
            //             slidesToShow: 4,
            //         }
            //     },
            //         {
            //             breakpoint: 400,
            //             settings: {
            //                 slidesToShow: 3,
            //             }
            //         },
            //     ]
            // });

        }

        function checkWinWidth(hq) {
            if (hq.matches) {

            } else {
                $('.tab-btn').slick({
                    slidesToShow: 5,
                    swipeToSlide: true,
                    speed: 100,
                    infinite: false,
                    arrows: false,
                    variableWidth: true,
                    responsive: [{
                        breakpoint: 500,
                        settings: {
                            slidesToShow: 4,
                        }
                    },
                        {
                            breakpoint: 400,
                            settings: {
                                slidesToShow: 3,
                            }
                        },
                    ]
                });

            }
        }

        var hq = window.matchMedia("(max-width: 640px)");
        hq.addListener(checkWinWidth);
        checkWinWidth(hq)


        $(document).click(function (event) {
            if ($(event.target).closest(".accordion-content, .accordion-header").length) return; //при клике на эти блоки не скрывать .display_settings_content
            $(".accordion-content").hide(); //скрываем .display_settings_content при клике вне .display_settings_content
            event.stopPropagation();
        });

        // $('.header-list').slick({
        //     slidesToShow: 3,
        //     swipeToSlide: true,
        //     speed: 10,
        //     useCSS: "false",
        //     useTransform: 'false',
        //     arrows: false,
        //     infinite: false,
        // });
        // $('.widget-slider').slick({
        //     slidesToShow: 1,
        //     swipeToSlide: true,
        //     speed: 10,
        //     useCSS: "false",
        //     useTransform: 'false',
        //     arrows: false,
        //     infinite: false,
        //
        // });
        // $('.table-list').slick({
        //     slidesToShow: 4,
        //     swipeToSlide: true,
        //     speed: 3,
        //     useCSS: "false",
        //     useTransform: 'false',
        //     arrows: false,
        //     infinite: false,
        // });

        $('.header-list-item').click(function () {
            $('.header-list-item').removeClass('active');
            $(this).addClass('active');
        });
        $('.table-entry-item-input-clear').click(function () {
            $(this).closest('.table-entry-item-input').find('input').val('');
            $(this).closest('.table-entry-item-input').find('input').focus();
        });

        $('.tab-inner').click(function () {
            let elem = $(this);
            $.ajax({
                url: 'ajax/chart/' + elem.data('graph')
            }).done(function (response) {
                let canvas = document.getElementById('graph_' + elem.data('graph-num'))
                    .getContext('2d');
                document.chart[elem.data('graph-num')].destroy();
                document.chart[elem.data('graph-num')] = new Chart(
                    canvas, {
                        type: 'line',
                        data: {
                            labels: response.labels,
                            datasets: [{
                                label: response.symbol,
                                backgroundColor: '#2D84EC',
                                borderColor: '#2D84EC',
                                data: response.data,
                            }]
                        },
                        options: {responsive: true, maintainAspectRatio: false}
                    });

                elem.closest('.tab-content-item').find('.tab-inner').removeClass('active');
                elem.addClass('active');
            });
        });

        handleElemContent($('html'));
    } catch (e) {
        console.log(e)
    }

    if ($(window).width() > 1200) {
        $('.header-menu').addClass('mob');
        $('.header-menu').hover(
            function () {
                $(this).removeClass('mob');
            },
            function () {
                $(this).addClass('mob');
            }
        );
    } else {
        $('.removeCLass').addClass('mob');
    }

    function enableDragScroll(selector) {
        const navIcons = $(selector);
        let isDown = false;
        let startX;
        let scrollLeft;

        // Перетаскивание мышью
        navIcons.on("mousedown", function (e) {
            isDown = true;
            navIcons.addClass("active");
            startX = e.pageX - navIcons.offset().left;
            scrollLeft = navIcons.scrollLeft();
        });

        $(document).on("mouseup", function () {
            isDown = false;
            navIcons.removeClass("active");
        });

        navIcons.on("mouseleave", function () {
            isDown = false;
            navIcons.removeClass("active");
        });

        navIcons.on("mousemove", function (e) {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - navIcons.offset().left;
            const walk = (x - startX) * 2; // Ускоряем прокрутку
            navIcons.scrollLeft(scrollLeft - walk);
        });

        // Прокрутка колесиком мыши (горизонтальная при вертикальном скролле)
        navIcons.on("wheel", function (e) {
            e.preventDefault();
            const delta = e.originalEvent.deltaY; // Получаем направление колеса
            navIcons.scrollLeft(navIcons.scrollLeft() + delta); // Горизонтальная прокрутка
        });
    }

    enableDragScroll(".nav-icons");
    if (document.querySelector(".car_number_button_block")) {
        $("#personal_code").on("input", function () {
            this.value = this.value.replace(/[^A-Za-z ]/g, "").toUpperCase();
        });
        $(".car_numbers_block input").on('keyup', function (e) {
            var maxlength = $(this).attr('maxlength') * 1;
            if ($(this).val().length >= maxlength) {
                $(this).val($(this).val().substr(0, maxlength));
                if ($(this).parents('.block').next().find('input').length) {
                    $(this).blur();
                    $(this).parents('.block').next().find('input').focus();
                }
            }
            if (e.keyCode == 8 && $(this).val().length == 0) {
                if ($(this).parents('.block').prev().find('input').length > 0) {
                    $(this).blur();
                    $(this).parents('.block').prev().find('input').focus();
                }
            }
        });

        $("#personal_pre").keydown(function (e) {
            if (e.keyCode == 9 && $("#personal_pre").val().length < 2) {
                e.preventDefault();
                return;
            }
        });

        $("#personal_code").keydown(function (e) {
            if (e.keyCode == 9 && $(this).val().length == 0) {
                e.preventDefault();
                $("#personal_post").focus();
                return;
            }
            if ($(this).val().length == 1 && (e.keyCode == 9 || e.keyCode == 39)) {
                e.preventDefault();
                return;
            }
            if (e.keyCode == 39 && $(this).val().length == 0) {
                e.preventDefault();
                $("#personal_post").focus();
                return;
            }
        });

        $("#personal_post").keyup(function (e) {
            el = document.getElementById('personal_post');
            val = el.value;
            pointer = val.slice(0, el.selectionStart).length;
            if (e.keyCode == 37 && pointer == 0) {
                $("#personal_code").focus();
                return;
            }
        });

        function updateTabState(dataId) {
            if (dataId === "#tab-id-2") {
                setInputState("3", "111", "2", "11");
            } else {
                setInputState("2", "11", "3", "111");
            }
        }

        function setInputState(preMaxLength, prePlaceholder, postMaxLength, postPlaceholder) {
            const pre = document.getElementById("personal_pre");
            const code = document.getElementById("personal_code");
            const post = document.getElementById("personal_post");
            const preParent = pre.closest('.block');
            const postParent = post.closest('.block');
            const preParentSpan = preParent.querySelector('span');
            const postParentSpan = postParent.querySelector('span');
            code.value = '';
            pre.value = '';
            post.value = '';
            pre.setAttribute("maxlength", preMaxLength);
            pre.setAttribute("placeholder", prePlaceholder);
            post.setAttribute("maxlength", postMaxLength);
            post.setAttribute("placeholder", postPlaceholder);
            preParent.style.width = preMaxLength === "3" ? "40%" : "30%";
            postParent.style.width = postMaxLength === "3" ? "30%" : "40%";
            preParentSpan.innerHTML = preParentSpan.innerHTML.replace(/\d/, preMaxLength);
            postParentSpan.innerHTML = postParentSpan.innerHTML.replace(/\d/, postMaxLength);
        }

        $('.tabs_holder_ul').children('li').click(function () {
            const dataId = $(this).data('id');
            $(this).parents('.tabs_holder_ul').find('.active_tabe').removeClass('active_tabe');
            $(this).addClass('active_tabe');
            updateTabState(dataId);
        });

        const urlParams = new URLSearchParams(window.location.search);
        const numberParam = urlParams.get("number");
        if (numberParam) {
            const formattedNumber = numberParam.toUpperCase();
            if (/^\d{3}\s(?:[A-Z]{2}|\s{2}|[A-Z]\s|\s[A-Z])\s\d{2}$/.test(formattedNumber)) {
                $('.tabs_holder_ul li[data-id="#tab-id-2"]')[0].click();
                document.getElementById("personal_pre").value = formattedNumber.slice(0, 3);
                document.getElementById("personal_code").value = formattedNumber.slice(4, 6);
                document.getElementById("personal_post").value = formattedNumber.slice(7);
                searchPlateNumber();
            }
            if (/^\d{2}\s(?:[A-Z]{2}|\s{2}|[A-Z]\s|\s[A-Z])\s\d{3}$/.test(formattedNumber)) {
                $('.tabs_holder_ul li [data-id="#tab-id-1"]').click();
                document.getElementById("personal_pre").value = formattedNumber.slice(0, 2);
                document.getElementById("personal_code").value = formattedNumber.slice(3, 5);
                document.getElementById("personal_post").value = formattedNumber.slice(6);
                searchPlateNumber();
            }
        }

        $("#search-btn-number-car").on("click", function (e) {
            e.preventDefault();
            searchPlateNumber();
        });

        function searchPlateNumber() {
            let pre = document.getElementById("personal_pre").value;
            let code = document.getElementById("personal_code").value;
            let post = document.getElementById("personal_post").value;
            let resultTable = document.querySelector(".table-result-append");
            let errorMessage = document.querySelector(".error-message");
            let currentLang = document.getElementById("lang-data").dataset.lang;
            const searchNumberAuto = {
                'fields_required': {
                    'ru': 'Ошибка: Поля "2 цифры" и "3 цифры" обязательны!',
                    'am': 'Սխալ. ‘2 թվանշան’ և ‘3 թվանշան’ դաշտերը պարտադիր են!',
                    'en': 'Error: The fields ‘2 digits’ and ‘3 digits’ are required!',
                },
                'number_busy': {
                    'ru': 'Запрошенный номер(а) занят(ы).',
                    'am': 'The requested number(s) is/are taken.',
                    'en': 'Պահանջված համար(ները) զբաղված է(են)։',
                },
                'number_000': {
                    'ru': 'В поиск не попадают номера с особым требованием о регистрации транспортного средства, то есть номера с нулями',
                    'am': 'Numbers with special vehicle registration requirements, such as those with zeros, are not included in the search.',
                    'en': 'Որոնման մեջ չեն ներառվում համարանիշերը, որոնք ունեն տրանսպորտային միջոցի գրանցման հատուկ պահանջներ, օրինակ՝ զրոներով համարանիշերը։',
                },
                'request_error': {
                    'ru': 'Ошибка при обработке запроса',
                    'am': 'Error processing request',
                    'en': 'Սխալ հարցման մշակման ընթացքում',
                },
                'server_error': {
                    'ru': 'Ошибка соединения с сервером',
                    'am': 'Server connection error',
                    'en': 'Սխալ՝ սերվերի միացման ընթացքում',
                }
            };
            errorMessage.innerHTML = '';
            if (!pre || !post) {
                errorMessage.innerHTML = `<p>${searchNumberAuto['fields_required'][currentLang]}</p>`;
                return;
            }

            let formattedPlate = `${pre} ${code.padEnd(2, " ").includes(' ') ? '  ' : code.padEnd(2, " ")} ${post}`;
            document.querySelector(".loader").style.display = "block";

            function formatPrice(price) {
                return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Запрос к серверу
            $.post("/ajax/plate-search", {plate_number: formattedPlate}, function (data) {
                document.querySelector(".loader").style.display = "none";
                resultTable.innerHTML = "";
                const newUrl = `/plate-number-search?number=${encodeURIComponent(formattedPlate)}`;
                window.history.pushState({path: newUrl}, "", newUrl);
                if (data.status === "OK") {
                    let results = Array.isArray(data.data) ? data.data : [data.data];
                    if (results.length > 0) {
                        results.forEach((item, index) => {
                            let row = document.createElement("tr");
                            row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.plate}</td>
                        <td>${formatPrice(item.price)} <sub> ֏</sub></td>
                    `;
                            resultTable.appendChild(row);
                        });
                    } else {
                        resultTable.innerHTML = `<tr><td colspan="3">Нет доступных номеров</td></tr>`;
                    }
                } else if (data.message && data.message === "Сервер вернул HTML-код") {
                    errorMessage.innerHTML = `<p>${searchNumberAuto['number_busy'][currentLang]}</p>`;
                    resultTable.innerHTML = `<tr><td colspan="3">${searchNumberAuto['number_busy'][currentLang]}</td></tr>`;
                } else if (data.message && data.message === "В поиск не попадают номера с нулями") {
                    errorMessage.innerHTML = `<p>${searchNumberAuto['number_000'][currentLang]}</p>`;
                    resultTable.innerHTML = `<tr><td colspan="3">${searchNumberAuto['number_000'][currentLang]}</td></tr>`;
                } else {
                    errorMessage.innerHTML = `<p class="error-message">${data.message || searchNumberAuto['request_error'][currentLang]}</p>`;
                    resultTable.innerHTML = `<tr><td colspan="3">${searchNumberAuto['request_error'][currentLang]}</td></tr>`;
                }
            }, "json").fail(function () {
                document.querySelector(".loader").style.display = "none";
                errorMessage.innerHTML = `<p class="error-message">${searchNumberAuto['server_error'][currentLang]}</p>`;
                resultTable.innerHTML = `<tr><td colspan="3">${searchNumberAuto['server_error'][currentLang]}</td></tr>`;
            });
        }

    }


    $('.header-bars').click(function () {
        $('.header-menu-block').slideToggle(300);
    });

    $('.header-menu-more').click(function () {
        $(this).toggleClass('active');
        $('.menu-hide').slideToggle(300);
    });

    $('.menu-link').click(function () {
        $('.menu-link').not($(this)).removeClass('active');
        $('.menu-two').not($(this).closest('.menu-item').find('.menu-two')).fadeOut(0);
        $(this).toggleClass('active');
        $(this).closest('.menu-item').find('.menu-two').slideToggle(300);
    });

    $('.accordion-header').click(function () {
        $('.accordion-content').not($(this).closest('.accordion').find('.accordion-content')).fadeOut(50);
        $(this).closest('.accordion').find('.accordion-content').slideToggle(300);
    });
    $('.accordion-close').click(function () {
        $(this).closest('.accordion-content').fadeOut(200);
    });

    $('.tabs').click(function () {
        let el = $(this);

        if (el.hasClass('active')) {
            return false;
        }

        el.closest('.nav.nav-tabs').find('.tabs.active').removeClass('active');
        el.addClass('active');

        el.closest('.form-group').find('.collapse.show').removeClass('show');
        $(el.data('target')).addClass('show');
    });

    $('#top-menu a').each(function () {
        let el = $(this);
        let lang = ['/ru', '/en', '/am'];
        let href = el.attr('href');

        if (href === window.location.pathname
            || (lang.includes(window.location.pathname) && '/' === href)
        ) {
            el.addClass('active');
        }
    });

    $('.table-entry-item-input input').hover(function () {
        let el = $(this);

        if (!el.hasClass('hovered-input')) {
            $('.hovered-input').removeClass('hovered-input');
        }
    });
});

function handleElemContent(elem) {
    elem.find('[data-maxlen]').each(function () {
        let text = $(this).text().trim(),
            max = $(this).data('maxlen');

        if (text.length > max)
            $(this).text(text.substring(0, max) + '...');
    });

    elem.find('[data-maxchild]').each(function () {
        let childs = $(this).children(),
            max = $(this).data('maxchild');

        if (childs.length > (max + 1)) {
            childs.each(function (i, el) {
                el = $(this);
                if (i >= max && i !== (childs.length - 1)) {
                    el.addClass('d-none');
                    el.attr('data-spoiler', 'true');
                }
            });

            $(this).find('[data-spoiler="open"]').removeClass('d-none');
            $(this).find('[data-spoiler="close"]').addClass('d-none');
        } else {
            childs.last().addClass('d-none');
        }
    });
    elem.find('[data-spoiler="open"]').click(function (e) {
        let container = $(this).closest('[data-maxchild]');

        container.find('[data-spoiler="true"]').toggleClass('d-none');
        $(this).toggleClass('d-none');
        container.find('[data-spoiler="close"]').toggleClass('d-none');
        e.preventDefault();
    });
    elem.find('[data-spoiler="close"]').click(function (e) {
        let container = $(this).closest('[data-maxchild]');

        container.find('[data-spoiler="true"]').toggleClass('d-none');
        $(this).toggleClass('d-none');
        container.find('[data-spoiler="open"]').toggleClass('d-none');
        e.preventDefault();
    });
}

function bindEventList(list) {
    let selector, event, elems;

    for (selector in list) {
        if ('init' === selector) {
            list[selector]();
        } else {
            elems = $(selector);
            for (event in list[selector]) {
                elems.on(event, list[selector][event]);
            }
        }
    }
}


$(document).click(function (event) {
    if ($(event.target).closest(".exchange-block-list-header, .exchange-block-list-footer").length) return; //при клике на эти блоки не скрывать .display_settings_content

    $(this).closest('.exchange-block-list').find(".exchange-block-list-footer").hide(); //скрываем .display_settings_content при клике вне .display_settings_content

    $(this).closest('.exchange-block-list').find('.exchange-block-list-header').removeClass('active');
    event.stopPropagation();

    if ($(event.target).closest(".exchange-list-header.first, .exchange-list-footer.first").length) return; //при клике на эти блоки не скрывать .display_settings_content

    $(".exchange-list-footer.first").hide(); //скрываем .display_settings_content при клике вне .display_settings_content

    $('.exchange-list-header.first').removeClass('active');

    event.stopPropagation();

    if ($(event.target).closest(".exchange-list-header.second, .exchange-list-footer.second").length) return; //при клике на эти блоки не скрывать .display_settings_content

    $(".exchange-list-footer.second").hide(); //скрываем .display_settings_content при клике вне .display_settings_content

    $('.exchange-list-header.second').removeClass('active');

    event.stopPropagation();

    if ($(event.target).closest(".accordion-content, .accordion-header").length) return; //при клике на эти блоки не скрывать .display_settings_content

    $(".accordion-content").hide(); //скрываем .display_settings_content при клике вне .display_settings_content
    event.stopPropagation();

    if ($(event.target).closest(".table-point-item, .table-point-item-list").length) return; //при клике на эти блоки не скрывать .display_settings_content

    $(".table-point-item-list").hide(); //скрываем .display_settings_content при клике вне .display_settings_content

    $('.table-point-container').removeClass('active');

    event.stopPropagation();

    if ($(event.target).closest(".table-entry-item-inner, .table-entry-item-inner").length) return; //при клике на эти блоки не скрывать .display_settings_content

    $(".table-entry-list").hide(); //скрываем .display_settings_content при клике вне .display_settings_content

    $('.table-entry-item-inner').removeClass('active');

    event.stopPropagation();

    if ($(event.target).closest(".exchange-block-list-header, .exchange-block-list-footer").length) return; //при клике на эти блоки не скрывать .display_settings_content

    $(".exchange-block-list-footer").hide(); //скрываем .display_settings_content при клике вне .display_settings_content

    $('.exchange-block-list-header').removeClass('active');

    event.stopPropagation();
});

$('.drop-header').click(function () {
    $('.drop').find('.drop-footer').not($(this).closest('.drop').find('.drop-footer')).fadeOut(200);
    $('.drop').find('.drop-header').not($(this)).removeClass('active');
    $(this).closest('.drop').find('.drop-footer').slideToggle(200);
    $(this).toggleClass('active');
});

let currencyPrices = $('input[name="currencyPrices"]').data('currency-prices');
let currencyPricesCrossSell = $('input[name="currencyPricesCrossSell"]').data('currency-prices-cross-sell');

// $('.exchange-course .exchange-course-inner-item').each(function () {
//     let $this = $(this);
//     currencyPrices[$this.data('currency')] = Number($this.data('price'));
// });
// currencyPrices = $('input[name="currencyPrices"]').data('currency-prices');
// console.log('currencyPrices');
// console.log(currencyPrices);
// console.log('currencyPricesCrossSell');
// console.log(currencyPricesCrossSell);

// $('.i-have, .i-get').focus(function() {
$('.i-have, .i-get').on('focus, input', function () {
    let $this = $(this);
    let exchangeType = $this.data('exchange-type');
    // console.log('focus, input', exchangeType, Date.now());
    $this.attr('data-currency', $('.exchange-currency-' + exchangeType + ' .exchange-list-header-item-val').data('currency-val'));

    let other = ('i-get' == exchangeType ? 'i-have' : 'i-get');

    $('.' + other).attr('data-currency', $('.exchange-currency-' + other + ' .exchange-list-header-item-val').data('currency-val'));
});

$('.i-have, .i-get').on('input', function () {
    let $this = $(this);
    let val = $this.val();
    let exchangeType = $this.data('exchange-type');
    // console.log('input', exchangeType, Date.now());
    let other = ('i-get' == exchangeType ? 'i-have' : 'i-get');
    let $other = $('.' + other);
    let otherVal = $other.val();
    let fromCurrency = $this.attr('data-currency');
    let toCurrency = $other.attr('data-currency');
    let digits = ('AMD' != fromCurrency && 'AMD' != toCurrency) ? 4 : 2;
    // let tableVal;

    // console.log(fromCurrency);
    // console.log(toCurrency);

    let needReplace = false;

    if (val.includes(',')) {
        val = val.replace(',', '.');
        needReplace = true;
    }

    if (otherVal.includes(',')) {
        otherVal = otherVal.replace(',', '.');
        needReplace = true;
    }

    let res;
    // console.log(currencyPrices);
    // console.log(exchangeType);
    // console.log(Number(val));

    if ('AMD' == fromCurrency) {
        res = Number(
            (
                Number(val) / currencyPrices[toCurrency]
            ).toFixed(digits)
        );

        // if ('i-have' == exchangeType) {
        //     // tableVal = Number(val);
        //     console.log('fill the table');
        //
        //     $('.exchange-table-item-text').each(function () {
        //         console.log(tableVal);
        //         console.log($(this).data('price'));
        //         console.log(Number(val) / $(this).data('price'));
        //
        //         $(this).html(
        //             Number(
        //                 (
        //                     $(this).data('price') * Number(val)
        //                 ).toFixed(digits)
        //             )
        //         );
        //     });
        // } else {
        //     tableVal = val;
        // }
        // } else if ('AMD' == toCurrency) {
    } else {
        // console.log('from NOT AMD');
        if ('AMD' == toCurrency) {
            res = Number(
                (
                    Number(val) * currencyPrices[fromCurrency]
                ).toFixed(digits)
            );
        } else {
            let currencyData;

            if ('i-have' == exchangeType) {
                currencyData = currencyPrices;

                $('.exchange-table-container').not('.cross').removeClass('d-none');
                $('.exchange-table-container.cross').addClass('d-none');
            } else {
                currencyData = currencyPricesCrossSell;

                $('.exchange-table-container').not('.cross').addClass('d-none');
                $('.exchange-table-container.cross').removeClass('d-none');
            }

            res = Number(
                (
                    Number(val) * currencyData[fromCurrency]
                ).toFixed(digits)
            );
        }
    }/* else {
        res = Number(
            (
                Number(val) * currencyPrices[fromCurrency] / currencyPrices[toCurrency]
            ).toFixed(2)
        );
    }*/
    // console.log(exchangeType);
    // console.log('fromCurrency', fromCurrency);
    // console.log('toCurrency', toCurrency);
    // console.log('val', Number(val));
    // console.log('otherVal', Number(otherVal));

    if ('i-have' == exchangeType && 'AMD' == toCurrency) {
        $('.exchange-table-item-text').each(function () {
            $(this).html(
                Number(
                    (
                        $(this).data('price') * Number(val)
                    ).toFixed(digits)
                )
            );
        });
    } else if ('i-get' == exchangeType && 'AMD' == toCurrency) {
        $('.exchange-table-item-text').each(function () {
            $(this).html(
                Number(
                    (
                        res / $(this).data('price')
                    ).toFixed(digits)
                )
            );
        });
    } else if ('i-get' == exchangeType && 'AMD' == fromCurrency) {
        $('.exchange-table-item-text').each(function () {
            $(this).html(
                Number(
                    (
                        res * $(this).data('price')
                    ).toFixed(digits)
                )
            );
        });
    } else if ('i-have' == exchangeType && 'AMD' == fromCurrency) {
        $('.exchange-table-item-text').each(function () {
            $(this).html(
                Number(
                    (
                        Number(val) / $(this).data('price')
                    ).toFixed(digits)
                )
            );
        });
    }

    if ('AMD' != fromCurrency && 'AMD' != toCurrency) {
        // console.log('cross change');
        if ('i-have' == exchangeType) {
            $('.exchange-table-item-text').each(function () {
                $(this).html(
                    Number(
                        (
                            Number(val) * $(this).data('price')
                        ).toFixed(digits)
                    )
                );
            });
        } else if ('i-get' == exchangeType) {
            $('.exchange-table-item-text').each(function () {
                $(this).html(
                    Number(
                        (
                            res / $(this).data('price')
                        ).toFixed(digits)
                    )
                );
            });

        }
    }

    // console.log(res);

    if (isNaN(res)) {
        $other.val('');
        return;
    }

    if (needReplace) {
        res = res.toString().replace('.', ',');
    }

    $other.val(res);
});

let currencyVal;

$('.drop-footer .drop-item').on('click', function () {
    var $this = $(this);

    currencyVal = $this.find('.exchange-list-header-item-val').data('currency-val');

    var $first = $(this).closest('.drop').find('.drop-header .drop-item');
    var firstHTML = $first.html();
    $first.html($this.html());
    $this.html(firstHTML);
    $(this).closest('.drop-footer').slideUp(200);
    $(this).closest('.drop').find('.drop-header').toggleClass('active');
});

$('.exchange-block-list-footer .drop-item').on('click', function () {
    refreshConverter();
});

// скрываем в соседнем выпадающем списке валют для обмена выбранную валюту,
// чтобы нельзя было выбрать обмен валюты на саму себя;
// так же отображаем ранее скрытую валюту
$('.exchange-list-footer.drop-footer .drop-item').on('click', function () {
    let $this = $(this);
    let targetClass;
    let $inputForTrigger;

    if ($this.closest('.exchange-list-footer.drop-footer').hasClass('first')) {
        targetClass = 'second';
        $inputForTrigger = $('.i-have');
    } else {
        targetClass = 'first';
        $inputForTrigger = $('.i-get');
    }

    $('.exchange-list-footer.' + targetClass + ' .exchange-list-header-item.d-none').removeClass('d-none');

    $('.exchange-list-footer.' + targetClass + ' .exchange-list-header-item').find('.exchange-list-header-item-val[data-currency-val="' + currencyVal + '"]').closest('.exchange-list-header-item').addClass('d-none');

    // let $ihave = $('.i-have');
    // let iHaveVal = $ihave.val();
    // let $iGet = $('.i-get');
    // let iGetVal = $iGet.val();

    // триггерим инпуты, чтобы отработал повешенный на них функционал
    // $('.i-have, .i-get').trigger("focus");
    // $inputForTrigger.trigger("input");
    // console.log($this.closest('.exchange-list').find('.exchange-list-header .exchange-list-header-item-val').data('currency-val'));
    $inputForTrigger.attr('data-currency', $this.closest('.exchange-list').find('.exchange-list-header .exchange-list-header-item-val').data('currency-val'));
    // return;

    // $ihave.val(iHaveVal);
    // $iGet.val(iGetVal);

    refreshConverter();
});

function refreshConverter() {
    // console.log('i-have', $('.i-have').attr('data-currency'));
    // console.log('i-get', $('.i-get').attr('data-currency'));
    $.ajax({
        url: 'ajax/converter/' + $('.exchange-block-list-header').find('input[name="type"]').val() + '/' + $('.i-have').attr('data-currency') + '/' + $('.i-get').attr('data-currency')
    }).done(function (response) {
        $('.exchange-table').html(response.data);
        // console.log($('input[name="currencyPrices"]').data('currency-prices'));
        currencyPrices = $('input[name="currencyPrices"]').data('currency-prices');
        currencyPricesCrossSell = $('input[name="currencyPricesCrossSell"]').data('currency-prices-cross-sell');
        // console.log('currencyPrices');
        // console.log(currencyPrices);
        // console.log('currencyPricesCrossSell');
        // console.log(currencyPricesCrossSell);

        if ('AMD' == $('.i-have').attr('data-currency')) {
            $('.i-get').trigger("input");
        } else {
            $('.i-have').trigger("input");
        }
    });
}

// $('.i-have, .i-get').trigger("focus");
$('.i-have, .i-get').trigger("input");
$('.i-have').val(1).trigger("input")
// $('.i-have, .i-get').blur();

let angle = 0;
$('.exchange-inner-object').click(function () {
    let el = $(this);
    let img = el.find('img');

    angle = angle - 180;

    img.css({transform: 'rotate(' + angle + 'deg)'});

    let iHave = $('.i-have');
    let iHaveVal = iHave.val();
    let iGet = $('.i-get');
    let iGetVal = iGet.val();

    // меняем местами значения в input
    // if ('AMD' == iHave.attr('data-currency') || 'AMD' == iGet.attr('data-currency')) {
    iHave.val(iGetVal);
    iGet.val(iHaveVal);
    // }

    let currencyIHave = $('.exchange-currency-i-have');
    let currencyIHaveVal = currencyIHave.find('.exchange-list-header-item-val[data-currency-val]').data('currency-val');
    let currencyIHaveHtml = currencyIHave.html();

    let currencyIGet = $('.exchange-currency-i-get');
    let currencyIGetVal = currencyIGet.find('.exchange-list-header-item-val[data-currency-val]').data('currency-val');
    let currencyIGetHtml = currencyIGet.html();

    // меняем местами названия валют в шапке выпадающих списков
    currencyIHave.html(currencyIGetHtml);
    currencyIGet.html(currencyIHaveHtml);

    let currencyIHaveInList = currencyIHave.closest('.exchange-list').find('.exchange-list-footer .exchange-list-header-item .exchange-list-header-item-val[data-currency-val="' + currencyIGetVal + '"]');
    let currencyIGetInList = currencyIGet.closest('.exchange-list').find('.exchange-list-footer .exchange-list-header-item .exchange-list-header-item-val[data-currency-val="' + currencyIHaveVal + '"]');

    // меняем местами значения валют из шапки выпадающих списков внутри выпдающих списков
    currencyIHaveInList.closest('.exchange-list-header-item').html(currencyIHaveHtml);
    currencyIGetInList.closest('.exchange-list-header-item').html(currencyIGetHtml);

    // if ('AMD' == iHave.attr('data-currency') || 'AMD' == iGet.attr('data-currency')) {
    $('.i-have, .i-get').trigger("input");
    $('.i-have, .i-get').blur();
    // }
    refreshConverter();
});

$('.exchange-course-btn').click(function () {
    let el = $(this);
    el.siblings('.exchange-course').find('.last-item').toggleClass('last-active');
    el.toggleClass('active');

    let showLbl = el.find('.exchange-course-btn-title').first();
    let hideLbl = el.find('.exchange-course-btn-title').last();

    showLbl.toggleClass('d-none');
    hideLbl.toggleClass('d-none');
});

// $('.exchange-table-item.head').click(function() {
$('.exchange-table').on('click', '.exchange-table-item.head', function () {
    $(this).closest('.exchange-table-container').toggleClass('active');
    $('.exchange-table-container').not($(this).closest('.exchange-table-container')).removeClass('active');
});

$('.exchange-course .tab-course').click(function () {
    let currency = $(this).data('currency');

    $('.exchange-currency-i-have-list').find('.drop-footer').find('.exchange-list-header-item-val[data-currency-val="' + currency + '"]').closest('.drop-item').click();
});

