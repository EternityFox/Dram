
let Table = {

    fontSizes: {
        normal: [[4, 11], [6, 10], [8, 9], [10, 8]],
        bold: [[5, 13], [6, 12], [7, 11], [8, 9]]
    },
    symbolColumns: [['four', 'five'], ['six', 'seven'], ['eight', 'nine'], ['ten', 'eleven']],
    inverseSorting: ['four', 'six', 'eight', 'ten'],
    sortedBy: 'zerofalse',
    disabled: true,

    menu: {
        get courseType() {
            return $('#courseType').attr('data-active');
        },
        get exchangeType() {
            return $('#exchangeType').attr('data-active');
        },
        get wholeSale() {
            return $('#wholeSale').is(':checked');
        },

        openTypeList: function(e) {
            let container = $(this).closest('.table-point-container');

            if ($(e.target).closest('.custom-control').length)
                return;
            if (container.closest('.table-item').hasClass('clue-mob'))
                return;
            container.toggleClass('active');
            container.find('.table-point-item-list').slideToggle(300);
            e.preventDefault();
        },
        changeType: function(e) {
            let container = $(this).closest('.table-point-container');
            if (Table.disabled)
                return;

            container.attr('data-active', $(this).data('type'));
            container.find('span').text($(this).text());
            container.removeClass('active');
            container.find('.table-point-item-list').slideToggle(300);

            Table.load();
            e.preventDefault();
            e.stopPropagation();
        },
        changeWholeSale: function() {
            setTimeout(function(){
                Table.findBestCourses();
                Table.calculate();
            }, 400);

            $('#table .table-all').find('[data-reverse]').each(function(){
                let elem = $(this), price1, price2;
                if (0 < elem.attr('data-reverse') && elem.attr('data-reverse') != elem.text()) {
                    elem.fadeOut(100, function(){
                        let price1 = elem.text(),
                            price2 = elem.attr('data-price');
                        elem.text(elem.attr('data-reverse'));
                        elem.attr('data-price', elem.attr('data-reverse-price'));
                        elem.attr('data-reverse', price1);
                        elem.attr('data-reverse-price', price2);
                        elem.fadeIn(300);
                    });
                }
            });
        },

        getEventList: function() {
            return {
                '.table-point-container': {click: this.openTypeList},
                '.table-point-item-list-item[data-type]': {click: this.changeType},
                '#wholeSale': {click: this.changeWholeSale}
            };
        }
    },

    symbolPanel: {
        container: $('#tableSymbolList'),

        openSymbolList: function() {
            let container = $(this);

            container.toggleClass('active');
            container.find('.table-entry-list').slideToggle(300);
        },
        changeSymbol: function(e) {
            if (Table.disabled)
                return;

            Table.load(
                '/ajax/' + $(this).data('symbol-change')
                + '/' + Table.menu.courseType + '/' + Table.menu.exchangeType
            );
            e.preventDefault();
        },
        changeSymbolMobile: function() {
            if (Table.disabled)
                return;

            $(this).closest('.table-flex').find('.active[data-symbol-change-mobile]')
                .removeClass('active');
            $(this).addClass('active');
            Table.load(
                '/ajax/0_' + $(this).data('symbol-change-mobile')
                + '/' + Table.menu.courseType + '/' + Table.menu.exchangeType
            );
        },
        amount: {
            focus: function() {
                let elem = $(this);

                if ('1' === elem.val())
                    elem.val('');
            },
            focusout: function() {
                let elem = $(this);

                if (elem.val() === "" || 0 > elem.val()) {
                    elem.val('1');
                    Table.calculate(elem.data('symbol-num'), elem.val());
                }
            },
            keydown: function(e) {
                let val = $(this).val();

                if (e.key.length === 1 && e.key.match(/[^0-9'".]/))
                    return false;
                else if (!val && !e.key.match(/[^'".]/))
                    return false;
            },
            input: function() {
                let elem = $(this);

                if(elem.val().length > 8)
                    elem.val(elem.val().substring(0, 8));

                Table.calculate(
                    elem.data('symbol-num'),
                    (!elem.val() ? 1 : elem.val())
                );
                Table.sort(Table.inverseSorting[elem.data('symbol-num')]);
            }
        },

        getEventList: function() {
            return {
                '.table-entry-item-inner': {click: this.openSymbolList},
                '[data-symbol-change]': {click: this.changeSymbol},
                '[data-symbol-change-mobile]': {click: this.changeSymbolMobile},
                '[data-symbol-num]': this.amount
            };
        }
    },

    scroll: function(elem = undefined) {
        if (!elem)
            elem = $('#table');

        $('html,body').animate({
            scrollTop: elem.offset().top
        }, 700);
    },

    load: function(url = undefined) {
        if (!url) {
            url = '/ajax/' + this.menu.courseType + '/' + this.menu.exchangeType;
            history.pushState(
                null, null, '/' + this.menu.courseType + '/' + this.menu.exchangeType
            );
        }

        Table.disabled = true;

        $.ajax({
            url: url
        }).done(function(response) {
            let container, nulled;

            Table.scroll();
            if (undefined !== response.symbolPanel) {
                container = $('#symbolPanel');
                nulled = container.prev();
                container.remove();
                nulled.after(response.symbolPanel);
                bindEventList(Table.symbolPanel.getEventList());
            }

            container = $('#table .table-all');
            container.before(
                '<div id="removeMe"'
                + ' style="display:none;height:' + container.css('height') + '">'
                + '</div>'
            );
            nulled = $('#removeMe');
            container.fadeOut(700, function() {
                container.remove();
                nulled.after(response.table);
                container = $('#table .table-all');
                container.fadeOut(100, function(){
                    container.toggle(700);
                });
                handleElemContent(container);
                Table.init();
            });
            nulled.show(700, function(){
                nulled.toggle(600, function(){
                    nulled.remove();
                });
            });
        });
    },

    findBestCourses: function() {
        let columns = [],
            tbls = $('#table .table-all-item'),
            tbl, price, best, inverse, one, two;

        this.symbolColumns.forEach(clmns => columns.push(clmns[0], clmns[1]));

        tbls.each(function(){
            tbl = $(this);

            $.each(columns, function(i, column){
                inverse = (-1 !== Table.inverseSorting.indexOf(column));

                tbl.find('.' + column).each(function(){
                    if (!(price = $(this).find('p').attr('data-price'))
                        || !(price = parseFloat(price))
                    )
                        return;
                    else if (!best
                        || (inverse && price > best)
                        || (!inverse && price < best)
                    )
                        best = price;
                });

                if (0 === best)
                    return;

                tbl.find('.' + column + ' p[data-price]').removeClass('bold');
                tbl.find('.' + column + ' [data-price="' + best + '"]')
                    .addClass('bold');
                best = undefined;
            });
        });

        if (3 === tbls.length) {
            $.each(columns, function (i, column) {
                inverse = (-1 !== Table.inverseSorting.indexOf(column));
                one = $(tbls[0]).find('.' + column + ' .bold').data('price');
                two = $(tbls[1]).find('.' + column + ' .bold').last().text();
                if (one != two && ((inverse && two > one) || (!inverse && two < one))) {
                    $(tbls[0]).find('.' + column + ' .bold').removeClass('bold');
                    $(tbls[1]).find('.' + column + ' p').first().css('color', '#000');
                }
            });
        }
    },

    addBestCourses: function() {
        $('#table .table-all-item').each(function(i){
            let tbl = $(this), columns = [];
            if (0 === i)
                return;

            Table.symbolColumns.forEach(clmns => columns.push(clmns[0], clmns[1]));
            $.each(columns, function(i, column){
                tbl.find('.' + column + ' .bold').first().text(
                    tbl.find('.' + column + ' .bold').last().text()
                );
            });
        });
    },

    calculate: function(num = undefined, amt = undefined) {
        let fs = ('cross' === this.menu.courseType ? 4 : 2),
            sizes, elem, val, size;

        if (!num && 0 !== num) {
            this.calculate(0, $('[data-symbol-num="0"]').val());
            this.calculate(1, $('[data-symbol-num="1"]').val());
            this.calculate(2, $('[data-symbol-num="2"]').val());
            this.calculate(3, $('[data-symbol-num="3"]').val());

            return;
        }

        $.each(this.symbolColumns[num], function(i, column){
            $('.' + column).each(function(){
                if ($(this).hasClass('blue'))
                    return;

                elem = $(this).find('p');
                if ((val = parseFloat(elem.attr('data-price'))) > 0)
                    elem.text(parseFloat((amt * val).toFixed(fs)));
                if ((val = parseFloat(elem.attr('data-reverse-price'))) > 0)
                    elem.attr('data-reverse', parseFloat((amt * val).toFixed(fs)));

                size = elem.text().length;
                sizes = elem.hasClass('bold')
                    ? Table.fontSizes.bold : Table.fontSizes.normal;
                $.each(sizes, function (i, val){
                    if (size <= val[0]) {
                        elem.css('fontSize', val[1] + 'px');
                        return false;
                    }
                })
            });
        });

        Table.addBestCourses();
    },

    sort: function(column, inverse = undefined) {
        let tables = $('#table .table-all-item'), num, container;

        if (undefined === inverse)
            inverse = (-1 !== Table.inverseSorting.indexOf(column));
        if (Table.sortedBy === column + inverse)
            return;

        tables.each(function(i, tbl){
            $(tbl).find('.' + column).filter(function(i){
                return (0 !== i);
            }).sortElements(function(a, b){
                if (!parseFloat($.text([a])))
                    return 1;
                else if (!parseFloat($.text([b])))
                    return inverse ? -1 : -1;
                return parseFloat($.text([a])) > parseFloat($.text([b]))
                    ? inverse ? -1 : 1
                    : inverse ? 1 : -1;
            }, function(){
                return this.parentNode;
            });

            num = 1;
            $(tbl).find('.one .table-item-number').each(function(){
                $(this).text(num);
                ++num;
            });
        });

        container = $('#table [data-sort=' + column + ']');
        $('#table [data-sort]').not(container).find('.table-item-arrow-sort')
            .each(function() {
                let th = $(this).closest('[data-sort]'),
                    p = th.find('p');

                p.html(p.text());
                p.css('textDecoration', 'none');
                th.removeClass('active');
            });

        Table.sortedBy = column + inverse;
        if ('zero' === column)
            return;

        if (!container.find('.table-item-arrow-sort').length) {
            let p = container.find('p');
            p.css('textDecoration', 'underline');
            p.append(
                '<br><div class="table-item-arrow-sort">'
                + '<img src="img/table-arrow.png" alt=""></div>'
            );
        }

        if (inverse)
            container.removeClass('active');
        else
            container.addClass('active');
    },

    sortClick: function() {
        let container = $(this),
            column = container.data('sort'),
            inverse = (-1 !== Table.inverseSorting.indexOf(column));

        if (!container.find('.table-item-arrow-sort').length)
            Table.sort(column, inverse);
        else if ((!inverse && !container.hasClass('active'))
            || (inverse && container.hasClass('active'))
        )
            Table.sort('zero');
        else
            Table.sort(column, !inverse);
    },

    toggleSubTable: function() {
        let row = $(this);

        row.toggleClass('active');
        row.closest('.table-all-item').find('.table-row')
            .not(row.closest('.table-row')).toggleClass('active');
    },

    init: function() {
        Table.sortedBy = 'zerofalse';

        setTimeout(function() {
            $('.table-all-item').each(function () {
                $(this).find(':first').addClass('table-bg');
            });
        }, 1000);

        $('[data-symbol-num]').val('1');

        if (Table.menu.wholeSale) {
            Table.menu.changeWholeSale();
        } else {
            Table.findBestCourses();
            Table.calculate();
            Table.findBestCourses();
            Table.calculate();
        }

        $('.table-item').hover(
            function() {
                let tableRow = $('.table-row').length,
                    tableIndex = $(this).index();
                for (let i = 0; i < tableRow; i++) {
                    $('.table-row').eq(i).find('.table-item').eq(tableIndex)
                        .not($('.table-item.one, .table-item.two, .table-item.three'))
                        .addClass('table-item-hover-row');
                }
                $(this).closest('.table-all-item').find('.table-item').eq(tableIndex)
                    .not($('.table-item.one, .table-item.two, .table-item.three'))
                    .addClass('table-item-hover-row');
                $(this).closest('.table-row').find('.table-item')
                    .addClass('table-item-hover-row');
                $(this).not($('.table-item.one, .table-item.two, .table-item.three'))
                    .addClass('table-item-hover-object');

            },
            function() {
                let tableRow = $('.table-row').length,
                    tableIndex = $(this).index();
                for (let i = 0; i < tableRow; i++) {
                    $('.table-row').eq(i).find('.table-item').eq(tableIndex)
                        .removeClass('table-item-hover-row');
                }
                $(this).closest('.table-all-item').find('.table-item').eq(tableIndex)
                    .removeClass('table-item-hover-row');
                $(this).closest('.table-row').find('.table-item')
                    .removeClass('table-item-hover-row');
                $(this).removeClass('table-item-hover-object');
            }
        );

        bindEventList({
            '#table .head': {click: Table.toggleSubTable},
            '#table [data-sort]': {click: Table.sortClick}
        });

        setTimeout(function() {
            Table.disabled = false;
        }, 700);
    },

    getEventList: function() {
        return Object.assign(
            this.menu.getEventList(),
            this.symbolPanel.getEventList(),
            {init: this.init}
        );
    }

};

$(document).ready(function() {
    bindEventList(Table.getEventList());

    $(document).click(function(e) {
        let container;

        if (!$(e.target).closest('.custom-control').length) {
            container = $('.table-point-container').not(
                $(e.target).closest('.table-point-container')
            );
            container.find('.table-point-item-list').slideUp();
            container.removeClass('active');

            container = $('.table-entry-item-inner').not(
                $(e.target).closest('.table-entry-item-inner')
            );
            container.find('.table-entry-list').slideUp();
            container.removeClass('active');
        }
    });

    if ($('.table-all').length) {
        $(window).scroll(function () {
            let elem = $('.clue-container'),
                to = $('.table-all'),
                scroll = $(window).scrollTop(),
                offset = to.offset().top + to.height() - 85;

            if (scroll > offset) {
                elem.fadeOut(200);
            } else if (scroll < offset) {
                elem.fadeIn(200);
            }
        });
    }

    $('.clue-mob-close').click(function(){
        $(this).closest('.clue-mob').fadeOut(200, function(){
            $('.clue-mob').next('div').removeClass('d-none');
        });
    });
    setTimeout(function() {
        $('.clue-mob-close').click();
    }, 3000);


    // let maxDate = 0;
    // let currDate;
    // let labelDate;
    // $('.table-all-item .table-row:not(.table-bg) .table-item.three .table-item-text').each(function () {
    //     let el = $(this);
    //
    //     currDate = parseInt(el.data('raw-date'));
    //
    //     if (currDate > maxDate) {
    //         maxDate = currDate;
    //         labelDate = el.text();
    //     }
    // });
    //
    // $('.refresh-time').text(labelDate);

    // $('.refresh-time').text($('#refresh-time').text());
});
