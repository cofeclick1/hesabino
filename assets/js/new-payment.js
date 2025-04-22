$(document).ready(function() {
    $('#btnNew').on('click', function() {
        Swal.fire({
            title: 'تایید پاکسازی',
            text: 'آیا مطمئن هستید؟ تمام اطلاعات وارد شده پاک خواهد شد.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، پاک شود',
            cancelButtonText: 'خیر'
        }).then((result) => {
            if (result.isConfirmed) {
                // پاک کردن فرم
                $('#paymentForm')[0].reset();
                $('#paymentItems').empty();
                
                // بازنشانی متغیرها
                totalAmount = 0;
                paidAmount = 0;
                currentPaymentId = 1;
                
                // افزودن یک آیتم جدید
                $('#btnAddItem').trigger('click');
                
                // بروزرسانی مبالغ
                updateTotalAmount();
                
                // تنظیم تاریخ امروز
                $('#btnToday').trigger('click');
                
                Swal.fire({
                    icon: 'success',
                    title: 'انجام شد',
                    text: 'فرم با موفقیت پاک شد',
                    confirmButtonText: 'تایید'
                });
            }
        });
    });
});