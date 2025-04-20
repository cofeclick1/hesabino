// حذف از سبد خرید
$(document).on('click', '.remove-item', function(e) {
    e.stopPropagation(); // جلوگیری از انتشار رویداد به المان‌های والد
    const index = $(this).data('index');
    
    if (confirm('آیا از حذف این محصول اطمینان دارید؟')) {
        cartItems.splice(index, 1);
        updateCart();
        showToast('محصول با موفقیت حذف شد');
    }
});