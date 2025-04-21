$(document).ready(function() {
    // تنظیمات اولیه
    initializeDatePickers();
    setupFormValidation();
    setupEventListeners();
    
    // متغیرهای عمومی
    let totalAmount = 0;
    let paidAmount = 0;
    let payments = [];
    
    // راه‌اندازی تاریخ‌پیکرها
    function initializeDatePickers() {
        $('.date-picker').persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: true
        });
    }
    
    // راه‌اندازی اعتبارسنجی فرم
    function setupFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }
    
    // تنظیم رویدادها
    function setupEventListeners() {
        $('#btnAddItem').on('click', addPaymentItem);
        $('#btnSave').on('click', savePayment);
        $('#btnCalculate').on('click', showCalculationModal);
        $('#btnNew').on('click', resetForm);
        
        // رویدادهای مودال پرداخت
        $('select[name="paymentMethod"]').on('change', togglePaymentDetails);
        $('#savePayment').on('click', addPaymentDetail);
        
        // تغییر واحد پول
        $('#currencyCode').on('change', updateCurrencySymbols);
        
        // رویدادهای پروژه
        $('#projectId').on('change', handleProjectChange);
    }
    
    // افزودن آیتم پرداخت جدید
    function addPaymentItem() {
        const template = document.querySelector('#paymentItemTemplate');
        const clone = document.importNode(template.content, true);
        
        // تنظیم رویدادهای آیتم جدید
        setupPaymentItemEvents(clone);
        
        $('#paymentItems').append(clone);
        initializePersonSearch(clone.querySelector('.person-search'));
        updateTotalAmount();
    }
    
    // تنظیم رویدادهای آیتم پرداخت
    function setupPaymentItemEvents(item) {
        $(item).find('.delete-item').on('click', function() {
            $(this).closest('.payment-item').remove();
            updateTotalAmount();
        });
        
        $(item).find('.amount-input').on('input', function() {
            formatNumber(this);
            updateTotalAmount();
        });
    }
    
    // راه‌اندازی جستجوی افراد
    function initializePersonSearch(input) {
        $(input).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '../api/search-people.php',
                    data: { term: request.term },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                const item = $(this).closest('.payment-item');
                item.find('.person-avatar').attr('src', ui.item.avatar || '../assets/images/default-avatar.png');
                $(this).val(ui.item.label);
                return false;
            }
        });
    }
    
    // بروزرسانی نمادهای واحد پول
    function updateCurrencySymbols() {
        const symbol = $('#currencyCode option:selected').data('symbol');
        $('.currency-symbol').text(symbol);
    }
    
    // نمایش/مخفی کردن جزئیات پرداخت
    function togglePaymentDetails() {
        $('.payment-details').addClass('d-none');
        const method = $(this).val();
        if (method === 'card') {
            $('#cardDetails').removeClass('d-none');
        } else if (method === 'cheque') {
            $('#chequeDetails').removeClass('d-none');
        }
    }
    
    // افزودن جزئیات پرداخت
    function addPaymentDetail() {
        const form = $('#paymentDetailForm')[0];
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        const payment = {
            method: $('select[name="paymentMethod"]').val(),
            amount: parseCurrency($('input[name="amount"]').val()),
            description: $('textarea[name="description"]').val()
        };
        
        // اضافه کردن جزئیات خاص هر روش پرداخت
        if (payment.method === 'card') {
            payment.cardNumber = $('input[name="cardNumber"]').val();
            payment.trackingNumber = $('input[name="trackingNumber"]').val();
        } else if (payment.method === 'cheque') {
            payment.chequeNumber = $('input[name="chequeNumber"]').val();
            payment.dueDate = $('input[name="dueDate"]').val();
            payment.bankName = $('input[name="bankName"]').val();
        }
        
        payments.push(payment);
        paidAmount += payment.amount;
        updateRemainingAmount();
        
        $('#paymentModal').modal('hide');
        resetPaymentForm();
    }
    
    // بروزرسانی مجموع مبالغ
    function updateTotalAmount() {
        totalAmount = 0;
        $('.amount-input').each(function() {
            totalAmount += parseCurrency($(this).val());
        });
        $('#totalAmount').text(formatCurrency(totalAmount));
        updateRemainingAmount();
    }
    
    // بروزرسانی مبلغ باقیمانده
    function updateRemainingAmount() {
        const remaining = totalAmount - paidAmount;
        $('#remainingAmount').text(formatCurrency(remaining));
    }
    
    // نمایش مودال محاسبات
    function showCalculationModal() {
        // TODO: پیاده‌سازی محاسبه راس چک‌ها
    }
    
    // ذخیره پرداخت
    function savePayment() {
        const form = $('#paymentForm')[0];
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        if (totalAmount === 0) {
            showError('لطفا حداقل یک آیتم پرداخت اضافه کنید');
            return;
        }
        
        if (totalAmount !== paidAmount) {
            showError('مبلغ پرداختی با جمع کل برابر نیست');
            return;
        }
        
        const paymentData = {
            documentNumber: $('#documentNumber').val(),
            date: $('#date').val(),
            projectId: $('#projectId').val(),
            description: $('#description').val(),
            currencyCode: $('#currencyCode').val(),
            items: getPaymentItems(),
            payments: payments
        };
        
        $.ajax({
            url: '../api/save-payment.php',
            method: 'POST',
            data: JSON.stringify(paymentData),
            contentType: 'application/json',
            success: function(response) {
                showSuccess('پرداخت با موفقیت ثبت شد');
                setTimeout(() => {
                    window.location.href = 'payments.php';
                }, 1500);
            },
            error: function(xhr) {
                showError('خطا در ثبت پرداخت: ' + xhr.responseText);
            }
        });
    }
    
    // دریافت آیتم‌های پرداخت
    function getPaymentItems() {
        const items = [];
        $('.payment-item').each(function() {
            items.push({
                person: $(this).find('.person-search').val(),
                amount: parseCurrency($(this).find('.amount-input').val()),
                description: $(this).find('.item-description').val()
            });
        });
        return items;
    }
    
    // پاکسازی فرم
    function resetForm() {
        $('#paymentForm')[0].reset();
        $('#paymentItems').empty();
        payments = [];
        paidAmount = 0;
        totalAmount = 0;
        updateTotalAmount();
    }
    
    // پاکسازی فرم پرداخت
    function resetPaymentForm() {
        $('#paymentDetailForm')[0].reset();
        $('#paymentDetailForm').removeClass('was-validated');
        $('.payment-details').addClass('d-none');
    }
    
    // نمایش پیام موفقیت
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'موفقیت',
            text: message,
            confirmButtonText: 'تایید'
        });
    }
    
    // نمایش پیام خطا
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: message,
            confirmButtonText: 'تایید'
        });
    }
    
    // فرمت‌بندی اعداد
    function formatNumber(input) {
        let value = input.value.replace(/[^\d]/g, '');
        input.value = new Intl.NumberFormat().format(value);
    }
    
    // تبدیل متن به عدد
    function parseCurrency(value) {
        return parseInt(value.replace(/[^\d]/g, '') || '0');
    }
    
    // فرمت‌بندی مبلغ
    function formatCurrency(amount) {
        return new Intl.NumberFormat().format(amount);
    }
    
    // تغییر پروژه
    function handleProjectChange() {
        const logoPath = $(this).find('option:selected').data('logo');
        // TODO: نمایش لوگوی پروژه
    }
});