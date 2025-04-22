$(document).ready(function() {
    // راه‌اندازی انتخاب‌گرها
    $('.select2').select2({
        theme: 'bootstrap-5',
        language: 'fa',
        dir: 'rtl'
    });

    // راه‌اندازی تاریخ‌پیکرها
    $('.date-picker').each(function() {
        $(this).pDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
            calendar: {
                persian: {
                    locale: 'fa'
                }
            },
            toolbox: {
                calendarSwitch: {
                    enabled: false
                }
            }
        });
    });

    // حذف پرداخت
    $('.delete-payment').on('click', function() {
        const paymentId = $(this).data('id');
        
        Swal.fire({
            title: 'حذف پرداخت',
            text: 'آیا از حذف این پرداخت اطمینان دارید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'خیر'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_PATH + '/api/delete-payment.php',
                    method: 'POST',
                    data: { id: paymentId },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'موفقیت',
                                text: response.message,
                                confirmButtonText: 'تایید'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا',
                                text: response.message,
                                confirmButtonText: 'تایید'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا',
                            text: 'خطا در ارتباط با سرور',
                            confirmButtonText: 'تایید'
                        });
                    }
                });
            }
        });
    });
});