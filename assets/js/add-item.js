$(document).ready(function() {
    // افزودن آیتم جدید
    $('#btnAddItem').on('click', function() {
        const template = document.querySelector('#paymentItemTemplate');
        const clone = document.importNode(template.content, true);
        
        // تنظیم شماره آیتم
        const itemId = currentPaymentId++;
        $(clone).find('.payment-item').attr('data-id', itemId);
        
        // اضافه کردن به DOM
        $('#paymentItems').append(clone);

        // راه‌اندازی جستجوی شخص
        new PersonPicker(clone.querySelector('.search-wrapper'));

        // فرمت‌بندی مبلغ
        setupAmountInput(clone.querySelector('.amount-input'));

        // بروزرسانی نماد ارز
        updateCurrencySymbols();
    });

    // حذف آیتم 
    $(document).on('click', '.delete-item', function() {
        const paymentItem = $(this).closest('.payment-item');
        
        if ($('.payment-item').length > 1) {
            paymentItem.slideUp(200, function() {
                $(this).remove();
                updateTotalAmount();
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'توجه',
                text: 'حداقل یک آیتم پرداخت باید وجود داشته باشد',
                confirmButtonText: 'تایید'
            });
        }
    });
});