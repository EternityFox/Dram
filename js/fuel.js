$(document).ready(function () {
    $(document).on('click', '.add-data-close', function () {
        $('#addDataBanner').hide();
    });
    $(document).on('change', '#city-select', function () {
        var url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('city', this.value);
        } else {
            url.searchParams.delete('city');
        }
        window.location.href = url.toString();
    });

    function recomputeAllColumns() {
        $('.fuel-table-entry-item-input input').each(function () {
            var col = $(this).closest('th').index();
            var qty = parseInt($(this).val(), 10) || 1;
            updatePricesForColumn(col, qty);
        });
    }

    function toggleBest() {
        var $rows = $('tr.company-row.best');
        var isOpen = $rows.filter(':visible').length > 0;
        if (isOpen) {
            $rows.addClass('hidden');
            $('.best-row .caret').removeClass('open');
        } else {
            $rows.removeClass('hidden');
            $('.best-row .caret').addClass('open');
        }
        recomputeAllColumns();
    }

    $(document).on('click', '.best-row .best-toggle', function (e) {
        e.preventDefault();
        toggleBest();
    });

    function toggleRegion(regionId) {
        var $cityRows = $('tr.city-row[data-region-id="' + regionId + '"]');
        var isOpen = $cityRows.filter(':visible').length > 0;
        if (isOpen) {
            $cityRows.addClass('hidden');
            $('tr.company-row[data-region-id="' + regionId + '"]').addClass('hidden');
            $('tr.region-row[data-region-id="' + regionId + '"] .caret').removeClass('open');
        } else {
            $cityRows.removeClass('hidden');
            $('tr.region-row[data-region-id="' + regionId + '"] .caret').addClass('open');
        }
        recomputeAllColumns();
    }

    function toggleCity(cityId) {
        var $companies = $('tr.company-row[data-city-id="' + cityId + '"]');
        var isOpen = $companies.filter(':visible').length > 0;
        if (isOpen) {
            $companies.addClass('hidden');
            $('tr.city-row[data-city-id="' + cityId + '"] .caret').removeClass('open');
        } else {
            $companies.removeClass('hidden');
            $('tr.city-row[data-city-id="' + cityId + '"] .caret').addClass('open');
        }
        recomputeAllColumns();
    }

    $(document).on('click', '.region-row .region-toggle', function (e) {
        e.preventDefault();
        var rid = $(this).closest('tr').data('region-id');
        toggleRegion(rid);
    });
    $(document).on('click', '.city-row .city-toggle', function (e) {
        e.preventDefault();
        var cid = $(this).closest('tr').data('city-id');
        toggleCity(cid);
    });

    (function autoExpandSelected() {
        var selected = $('.fuel-table').data('selected-city');
        if (!selected) return;
        var $city = $('.city-row[data-city-slug="' + selected + '"]');
        if ($city.length) {
            var rid = $city.data('region-id');
            toggleRegion(rid);
            toggleCity($city.data('city-id'));
            setTimeout(function () {
                $city[0].scrollIntoView({behavior: 'smooth', block: 'center'});
            }, 100);
        }
    })();

    $('.fuel-table-entry-item-input').each(function () {
        var input = $(this).find('input');
        input.val(1);
        var col = $(this).closest('th').index();
        updatePricesForColumn(col, 1);
    });

    $(document).on('input', '.fuel-table-entry-item-input input', function () {
        var col = $(this).closest('th').index();
        var qty = parseInt($(this).val(), 10) || 1;
        updatePricesForColumn(col, qty);
    });

    $('.fuel-table-entry-item-input').click(function () {
        $(this).find('input').val('');
    });

    $(".fuel-table-entry-item-input input").focusout(function () {
        if ($(this).val() == '') {
            $(this).val(1);
        }
    });

    function updatePricesForColumn(colIndex, qty) {
        // Пересчёт текста для всех видимых уровней
        $('tbody tr').each(function () {
            var $priceBox = $(this).find('td').eq(colIndex).find('.price-item-fuel');
            if (!$priceBox.length) return;
            var base = $priceBox.data('base-price');
            if (base === '-' || base === undefined) {
                $priceBox.text('-');
                return;
            }
            var total = (parseFloat(base) * qty) + ' ֏';
            $priceBox.text(total);
            adjustFontSize($priceBox);
        });

        // Подсветка «лучшая цена» среди видимых компаний (включая «Лучшие»)
        var best = Infinity;
        $('tbody tr.company-row:visible').each(function () {
            var $p = $(this).find('td').eq(colIndex).find('.price-item-fuel');
            var b = parseFloat($p.data('base-price'));
            if (!isNaN(b) && b < best) best = b;
        });
        $('tbody tr.company-row').each(function () {
            var $p = $(this).find('td').eq(colIndex).find('.price-item-fuel');
            $p.removeClass('bold');
            var b = parseFloat($p.data('base-price'));
            if (!isNaN(b) && b === best) {
                $p.addClass('bold');
                adjustFontSize($p);
            }
        });
    }

    function adjustFontSize(elem) {
        var textLength = elem.text().length;
        var sizes = {
            normal: [[6, 14], [10, 11], [12, 10], [14, 8]],
            bold: [[6, 14], [10, 12], [12, 11], [14, 9]]
        };
        var isBold = elem.hasClass('bold');
        var sizeTable = isBold ? sizes.bold : sizes.normal;
        for (var i = 0; i < sizeTable.length; i++) {
            if (textLength <= sizeTable[i][0]) {
                elem.css('fontSize', sizeTable[i][1] + 'px');
                break;
            }
        }
    }

    (function(){
        const wrap   = document.getElementById('city-combobox');
        if(!wrap) return;

        const select = document.getElementById('city-select');
        const btn    = document.getElementById('city-trigger');
        const menu   = document.getElementById('city-menu');
        const items  = Array.from(menu.querySelectorAll('li'));

        const applyFromSelect = () => {
            const opt = select.options[select.selectedIndex];
            btn.textContent = (opt ? opt.textContent.trim() : '');
            const val = (opt ? opt.value : '');
            items.forEach(li => {
                const on = (li.dataset.value === val);
                li.classList.toggle('is-selected', on);
                li.setAttribute('aria-selected', on ? 'true' : 'false');
            });
        };
        applyFromSelect();
        const open  = () => { wrap.setAttribute('aria-expanded','true'); menu.focus(); };
        const close = () => { wrap.setAttribute('aria-expanded','false'); };

        btn.addEventListener('click', (e)=>{
            const isOpen = wrap.getAttribute('aria-expanded') === 'true';
            isOpen ? close() : open();
        });

        menu.addEventListener('click', (e)=>{
            const li = e.target.closest('li');
            if(!li) return;
            select.value = li.dataset.value || '';
            select.dispatchEvent(new Event('change', {bubbles:true}));
            applyFromSelect();
            close();
            btn.focus();
        });

        document.addEventListener('click', (e)=>{
            if(!wrap.contains(e.target)) close();
        });

        let activeIndex = Math.max(0, items.findIndex(li => li.classList.contains('is-selected')));
        const setActive = (i)=>{
            activeIndex = Math.max(0, Math.min(items.length-1, i));
            items.forEach((li,idx)=>li.setAttribute('aria-current', idx===activeIndex ? 'true' : 'false'));
            items[activeIndex].scrollIntoView({block:'nearest'});
        };
        setActive(activeIndex);

        wrap.addEventListener('keydown', (e)=>{
            const isOpen = wrap.getAttribute('aria-expanded') === 'true';
            switch(e.key){
                case 'ArrowDown':
                    e.preventDefault();
                    if(!isOpen){ open(); } else { setActive(activeIndex+1); }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if(!isOpen){ open(); } else { setActive(activeIndex-1); }
                    break;
                case 'Enter':
                    e.preventDefault();
                    if(isOpen){ items[activeIndex].click(); } else { open(); }
                    break;
                case 'Escape':
                    if(isOpen){ e.preventDefault(); close(); btn.focus(); }
                    break;
            }
        });

        select.addEventListener('change', applyFromSelect);
    })();

});
