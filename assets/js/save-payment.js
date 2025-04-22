$(document).ready(function() {
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();

        // اعتبارسنجی فرم
        if (!this.checkValidity()) {
            $(this).addClass('was-validated');
            return;
        }

        // جمع‌آوری داده‌ها
        const paymentData = {
            document_number: $('input[name="document_number"]').val(),
            date: $('input[name="date"]').val(),
            project_id: $('select[name="project_id"]').val(),
            description: $('input[name="description"]').val(),
            currency_code: $('select[name="currency_code"]').val(),
            items: []
        };

        // جمع‌آوری آیتم‌ها
        $('.payment-item').each(function() {
            paymentData.items.push({
                person_id: $(this).find('.person-id').val(),
                amount: parseCurrency($(this).find('.amount-input').val()),
                description: $(this).find('.item-description').val()
            });
        });

        // اعتبارسنجی داده‌ها
        let isValid = true;
        let errorMessage = '';

        if (paymentData.items.length === 0) {
            isValid = false;
            errorMessage = 'حداقل یک آیتم پرداخت باید وجود داشته باشد';
        }

        if (!paymentData.items.every(item => item.person_id)) {
            isValid = false;
            errorMessage = 'لطفاً برای همه آیتم‌ها شخص را انتخاب کنید';
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: errorMessage,
                confirmButtonText: 'تایید'
            });
            return;
        }

        // غیرفعال کردن دکمه‌ها
        const $submitBtn = $(this).find('button[type="submit"]');
        const $otherBtns = $('#btnNew, #btnCalculate');
        $submitBtn.prop('disabled', true);
        $otherBtns.prop('disabled', true);

        // نمایش loading
        Swal.fire({
            title: 'در حال ذخیره...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // ارسال درخواست
        $.ajax({
            url: BASE_PATH + '/api/save-payment.php',
            method: 'POST',
            data: JSON.stringify(paymentData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'موفقیت',
                        text: 'پرداخت با موفقیت ثبت شد',
                        confirmButtonText: 'تایید'
                    }).then(() => {
                        window.location.href = BASE_PATH + '/people/pay_list.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: response.message || 'خطا در ثبت پرداخت',
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
            },
            complete: function() {
                // فعال کردن دکمه‌ها
                $submitBtn.prop('disabled', false);
                $otherBtns.prop('disabled', false);
            }
        });
    });
});