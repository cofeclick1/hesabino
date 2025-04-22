$(document).ready(function() {
    $('#btnCalculate').on('click', function() {
        // چک کردن وجود آیتم‌ها
        if ($('.payment-item').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'توجه',
                text: 'هیچ آیتمی برای محاسبه وجود ندارد',
                confirmButtonText: 'تایید'
            });
            return;
        }

        // جمع‌آوری مبالغ و تاریخ‌ها
        const items = [];
        $('.payment-item').each(function() {
            const amount = parseCurrency($(this).find('.amount-input').val());
            const date = $(this).find('.item-date').val();
            
            if (amount > 0) {
                items.push({
                    amount: amount,
                    date: date || $('input[name="date"]').val()
                });
            }
        });

        // محاسبه راس
        $.ajax({
            url: BASE_PATH + '/api/calculate-average.php',
            method: 'POST',
            data: JSON.stringify({ items }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'info',
                        title: 'نتیجه راس‌گیری',
                        html: `
                            <div class="text-start">
                                <p>تاریخ راس: ${response.average_date}</p>
                                <p>جمع کل: ${formatCurrency(response.total_amount)}</p>
                            </div>
                        `,
                        confirmButtonText: 'تایید'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در محاسبه راس',
                    confirmButtonText: 'تایید'
                });
            }
        });
    });
});