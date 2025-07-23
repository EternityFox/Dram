document.querySelectorAll('.quantity').forEach(function(input) {
    input.addEventListener('input', function() {
        var row = this.closest('.data-row');
        var quantities = parseInt(this.value) || 1;
        row.querySelectorAll('.price-item').forEach(function(item) {
            var basePrice = parseFloat(item.dataset.basePrice);
            var total = quantities * basePrice;
            item.textContent = total + ' AMD';
        });
    });
});