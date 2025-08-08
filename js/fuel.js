$(document).ready(function () {
    // Обработчик ввода количества для обновления цен
    $('.fuel-table-entry-item-input input').on('input', function () {
        var quantity = parseInt($(this).val()) || 1; // Убеждаемся, что значение не меньше 1
        updatePrices($(this)); // Обновляем цены в колонке
    });

    // Инициализация: выделение лучших цен при загрузке для каждой колонки
    $('.fuel-table-entry-item-input').each(function () {
        var input = $(this).find('input');
        input.val(1); // Устанавливаем начальное значение
        var columnIndex = $(this).closest('th').index(); // Индекс текущей колонки
        updatePricesForColumn(input, columnIndex); // Применяем для каждой колонки отдельно
    });

    // Функция для обновления цен и размера шрифта в конкретной колонке
    function updatePrices(input) {
        var quantity = parseInt(input.val()) || 1;
        var columnIndex = input.closest('th').index(); // Индекс колонки (0 - пустая, 1-5 - типы топлива)
        updatePricesForColumn(input, columnIndex);
    }

    // Функция для обработки одной колонки
    function updatePricesForColumn(input, columnIndex) {
        var quantity = parseInt(input.val()) || 1;

        // Сбрасываем все текущие выделения в этой колонке
        $('.fuel-comparison tbody tr').each(function () {
            $(this).find('td').eq(columnIndex).find('.price-item-fuel').removeClass('bold');
        });

        // Находим лучшую цену (максимальную) на основе базовых цен в текущей колонке
        var bestBasePrice = +Infinity; // Инициализируем как отрицательную бесконечность для поиска максимума
        $('.fuel-comparison tbody tr').each(function () {
            var priceItem = $(this).find('td').eq(columnIndex).find('.price-item-fuel');
            var basePrice = parseFloat(priceItem.data('base-price')) || '-';
            if (basePrice < bestBasePrice) {
                bestBasePrice = basePrice;
            }
        });

        // Обновляем цены и выделяем лучшую в текущей колонке
        $('.fuel-comparison tbody tr').each(function () {
            var priceItem = $(this).find('td').eq(columnIndex).find('.price-item-fuel');
            var basePrice = parseFloat(priceItem.data('base-price')) || '-';
            var totalPrice = basePrice === '-' ? '-' : (basePrice * quantity) + ' ֏';
            priceItem.text(totalPrice);
            adjustFontSize(priceItem); // Корректируем размер шрифта

            // Выделяем элементы с лучшей базовой ценой (максимальной)
            var currentBasePrice = parseFloat(priceItem.data('base-price'));
            if (currentBasePrice === bestBasePrice) {
                priceItem.addClass('bold');
                adjustFontSize(priceItem); // Пересчитываем размер шрифта для жирного текста
            }
        });
    }

    // Функция для корректировки размера шрифта
    function adjustFontSize(elem) {
        var textLength = elem.text().length;
        var sizes = {
            normal: [[6, 14], [10, 11], [12, 10], [14, 8]],
            bold: [[6, 14], [10, 12], [12, 11], [14, 9]]
        };
        var isBold = elem.hasClass('bold'); // Проверяем, есть ли класс bold
        var sizeTable = isBold ? sizes.bold : sizes.normal;

        $.each(sizeTable, function (i, val) {
            if (textLength <= val[0]) {
                elem.css('fontSize', val[1] + 'px');
                return false;
            }
        });
    }
});