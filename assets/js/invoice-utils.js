// بروزرسانی مبالغ
export function updateTotals(cartItems) {
    const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = Math.round(subtotal * 0.09);
    const discountType = $('#discount-type').val();
    const discountValue = parseFloat($('#discount-amount').val()) || 0;
    
    let discount = 0;
    if (discountType === 'amount') {
        discount = discountValue;
    } else {
        discount = Math.round(subtotal * (discountValue / 100));
    }

    const total = subtotal + tax - discount;

    $('#subtotal').text(number_format(subtotal) + ' تومان');
    $('#tax').text(number_format(tax) + ' تومان');
    $('#total').text(number_format(total) + ' تومان');

    $('.payment-amount').first().val(total);
}

// فرمت‌بندی اعداد
export function number_format(number) {
    return new Intl.NumberFormat('fa-IR').format(number);
}

// گرفتن مقادیر subtotal، tax و total
export function getTotals() {
    const subtotal = parseFloat($('#subtotal').text().replace(/[^0-9.-]+/g,""));
    const tax = parseFloat($('#tax').text().replace(/[^0-9.-]+/g,""));
    const total = parseFloat($('#total').text().replace(/[^0-9.-]+/g,""));
    return { subtotal, tax, total };
}