$(document).ready(function() {
    // متغیرهای عمومی
    let totalAmount = 0;
    let paidAmount = 0;
    let payments = [];
    let currentPaymentId = 1;
    
    // تنظیمات اولیه
    initializePage();
    
    function initializePage() {
        initializeDatePickers();
        initializeSelect2();
        setupValidation();
        setupEventListeners();
        
        // اضافه کردن اولین آیتم
        addPaymentItem();

        // تنظیم مقدار اولیه واحد پول
        updateCurrencySymbols();
    }
    
    // راه‌اندازی تاریخ‌پیکرها
    function initializeDatePickers() {
        $('.date-picker').each(function() {
            $(this).pDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: true,
                initialValueType: 'persian',
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                },
                onSelect: function(unix) {
                    // اگر رویداد change دستی نیاز باشد
                    $(this.model.inputElement).trigger('change');
                },
                toolbox: {
                    calendarSwitch: {
                        enabled: false
                    }
                }
            });
        });

    // تنظیم تاریخ امروز
    $('#btnToday').click(function() {
        const today = new persianDate();
        $('input[name="date"]').val(today.format('YYYY/MM/DD'));
    });
    
    // تنظیم تاریخ امروز به صورت پیش‌فرض
    $('#btnToday').trigger('click');
}
    
    // راه‌اندازی select2
    function initializeSelect2() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            language: 'fa',
            dir: 'rtl'
        });
    }
    
    // تنظیم اعتبارسنجی فرم
    function setupValidation() {
        // اعتبارسنجی فرم اصلی
        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                savePayment();
            }
            $(this).addClass('was-validated');
        });

        // اعتبارسنجی فرم پرداخت
        $('#paymentDetailForm').on('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                addPaymentDetail();
            }
            $(this).addClass('was-validated');
        });
    }
    
    // تنظیم رویدادها
    function setupEventListeners() {
        // دکمه‌های اصلی
        $('#btnAddItem').on('click', addPaymentItem);
        $('#btnNew').on('click', resetForm);
        $('#btnSave').on('click', () => $('#paymentForm').submit());
        $('#btnCalculate').on('click', showCalculationModal);
        
        // شماره سند خودکار/دستی
        $('#autoNumber').on('change', function() {
            const documentNumberInput = $('input[name="document_number"]');
            documentNumberInput.prop('readonly', this.checked);
            if (this.checked) {
                documentNumberInput.val($('#documentNumber').data('auto-number'));
            }
        });

        // مودال پرداخت
        $('#btnAddPayment').on('click', () => {
            resetPaymentForm();
            $('#paymentModal').modal('show');
        });

        // تغییر روش پرداخت
        $('select[name="paymentMethod"]').on('change', togglePaymentDetails);

        // تغییر واحد پول
        $('select[name="currency_code"]').on('change', updateCurrencySymbols);

        // رویدادهای پروژه
        $('#projectId').on('change', handleProjectChange);
        $('#btnNewProject').on('click', showNewProjectModal);
        
        // رویداد دکمه انصراف مودال‌ها
        $('.modal .btn-secondary').on('click', function() {
            $(this).closest('.modal').modal('hide');
        });
    }
    
    // افزودن آیتم پرداخت جدید
    function addPaymentItem() {
        const template = document.querySelector('#paymentItemTemplate');
        const clone = document.importNode(template.content, true);
        
        // تنظیم شماره آیتم
        const itemId = currentPaymentId++;
        $(clone).find('.payment-item').attr('data-id', itemId);
        
        // تنظیم رویدادهای آیتم
        setupPaymentItemEvents(clone);
        
        $('#paymentItems').append(clone);

        // راه‌اندازی جستجوی شخص
        initializePersonSearch(clone.querySelector('.person-search'));

        // فرمت‌بندی مبلغ
        setupAmountInput(clone.querySelector('.amount-input'));

        // بروزرسانی نماد ارز
        updateCurrencySymbols();

        return itemId;
    }
    
    // تنظیم رویدادهای آیتم پرداخت
    function setupPaymentItemEvents(item) {
        // حذف آیتم
        $(item).find('.delete-item').on('click', function() {
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
        
        // تغییر مبلغ
        $(item).find('.amount-input').on('input', function() {
            formatAmount(this);
            updateTotalAmount();
        });

        // افزودن شخص جدید
        $(item).find('.btn-add-person').on('click', () => {
            showNewPersonModal();
        });
    }

    // راه‌اندازی جستجوی افراد با Select2
    function initializePersonSearch(input) {
        $(input).select2({
            theme: 'bootstrap-5',
            language: 'fa',
            dir: 'rtl',
            ajax: {
                url: BASE_PATH + '/api/search-people.php',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    return {
                        results: data.items,
                        pagination: {
                            more: data.hasMore
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: formatPerson,
            templateSelection: formatPerson
        });
    }

    // فرمت شخص در Select2
    function formatPerson(person) {
        if (!person.id) return person.text;
        
        return $(`
            <div class="d-flex align-items-center">
                <img src="${person.avatar || BASE_PATH + '/assets/images/default-avatar.png'}" 
                     class="rounded-circle me-2" 
                     style="width: 24px; height: 24px;">
                <div>
                    <div class="fw-bold">${person.text}</div>
                    ${person.mobile ? `<small class="text-muted">${person.mobile}</small>` : ''}
                </div>
            </div>
        `);
    }
    
    // تنظیم فرمت‌بندی مبلغ
    function setupAmountInput(input) {
        new Cleave(input, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: '.',
            numeralPositiveOnly: true
        });
    }
    
    // بروزرسانی نمادهای واحد پول
    function updateCurrencySymbols() {
        const symbol = $('select[name="currency_code"] option:selected').data('symbol') || 'ریال';
        $('.currency-symbol').text(symbol);
    }
    
    // نمایش/مخفی کردن جزئیات پرداخت
    function togglePaymentDetails() {
        $('.payment-details').addClass('d-none');
        const method = $(this).val();
        if (method === 'card') {
            $('#cardDetails').removeClass('d-none')
                .find('input, select').prop('required', true);
        } else if (method === 'cheque') {
            $('#chequeDetails').removeClass('d-none')
                .find('input, select').prop('required', true);
        } else {
            $('.payment-details')
                .find('input, select').prop('required', false);
        }
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
        $('#remainingAmount')
            .text(formatCurrency(remaining))
            .toggleClass('text-danger', remaining > 0)
            .toggleClass('text-success', remaining <= 0);
    }
    
    // فرمت کردن مبلغ
    function formatCurrency(amount) {
        return new Intl.NumberFormat('fa-IR').format(amount);
    }
    
    // تبدیل رشته مبلغ به عدد
    function parseCurrency(value) {
        return parseInt(value.replace(/[^0-9-]/g, '')) || 0;
    }
    
    // نمایش مودال محاسبات
    function showCalculationModal() {
        Swal.fire({
            title: 'راس‌گیری',
            text: 'این قابلیت در نسخه بعدی اضافه خواهد شد',
            icon: 'info',
            confirmButtonText: 'تایید'
        });
    }

    // نمایش مودال شخص جدید
    function showNewPersonModal() {
        $('#personModal').modal('show');
    }
    
    // تغییر پروژه
    function handleProjectChange() {
        const selectedOption = $(this).find(':selected');
        const logoPath = selectedOption.data('logo');
        if (logoPath) {
            selectedOption.closest('.card').find('.project-logo').attr('src', logoPath);
        }
    }

    // نمایش مودال پروژه جدید
        function showNewProjectModal() {
            // پاکسازی فرم قبل از نمایش
            $('#projectForm')[0].reset();
            $('#projectForm').removeClass('was-validated');
            
            // نمایش مودال
            $('#projectModal').modal('show');
            
            // اضافه کردن رویداد ذخیره برای دکمه
            $('#saveProject').off('click').on('click', function() {
                const form = $('#projectForm')[0];
                if (form.checkValidity()) {
                    saveProject();
                }
                $(form).addClass('was-validated');
            });
        }
    // ذخیره پروژه جدید
function saveProject() {
    const formData = new FormData($('#projectForm')[0]);
    
    $.ajax({
        url: BASE_PATH + '/api/save-project.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // اضافه کردن پروژه جدید به لیست
                const option = new Option(response.project.name, response.project.id, true, true);
                $(option).data('logo', response.project.logo_path);
                $('select[name="project_id"]').append(option).trigger('change');
                
                // بستن مودال
                $('#projectModal').modal('hide');
                
                // نمایش پیام موفقیت
                showSuccess('پروژه جدید با موفقیت اضافه شد');
            } else {
                showError(response.message || 'خطا در ثبت پروژه');
            }
        },
        error: function() {
            showError('خطا در ارتباط با سرور');
        }
    });
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
        
        // اضافه کردن اطلاعات خاص هر روش
        if (payment.method === 'card') {
            payment.cardNumber = $('input[name="cardNumber"]').val();
            payment.trackingNumber = $('input[name="trackingNumber"]').val();
            payment.bankName = $('select[name="bankName"]').val();
        } else if (payment.method === 'cheque') {
            payment.chequeNumber = $('input[name="chequeNumber"]').val();
            payment.dueDate = $('input[name="dueDate"]').val();
            payment.bankName = $('select[name="bankName"]').val();
        }
        
        payments.push(payment);
        paidAmount += payment.amount;
        updateRemainingAmount();
        
        $('#paymentModal').modal('hide');
        resetPaymentForm();
        
        // نمایش پیام موفقیت
        Swal.fire({
            icon: 'success',
            title: 'موفقیت',
            text: 'پرداخت با موفقیت اضافه شد',
            confirmButtonText: 'تایید'
        });
    }
    
    // پاکسازی فرم
    function resetForm() {
        Swal.fire({
            title: 'پاکسازی فرم',
            text: 'آیا از پاک کردن فرم اطمینان دارید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#paymentForm')[0].reset();
                $('#paymentItems').empty();
                payments = [];
                paidAmount = 0;
                totalAmount = 0;
                currentPaymentId = 1;
                
                addPaymentItem();
                updateTotalAmount();
                
                // برگرداندن شماره سند به حالت خودکار
                $('#autoNumber').prop('checked', true).trigger('change');
                
                // تنظیم تاریخ امروز
                $('#btnToday').trigger('click');
            }
        });
    }
    
    // پاکسازی فرم پرداخت
    function resetPaymentForm() {
        const form = $('#paymentDetailForm')[0];
        form.reset();
        form.classList.remove('was-validated');
        $('.payment-details').addClass('d-none')
            .find('input, select').prop('required', false);
    }
    
    // ذخیره پرداخت
    function savePayment() {
        if (totalAmount === 0) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: 'لطفا حداقل یک آیتم پرداخت اضافه کنید',
                confirmButtonText: 'تایید'
            });
            return;
        }
        
        if (totalAmount !== paidAmount) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: 'مبلغ پرداختی با جمع کل برابر نیست',
                confirmButtonText: 'تایید'
            });
            return;
        }
        
        const paymentData = {
            documentNumber: $('input[name="document_number"]').val(),
            date: $('input[name="date"]').val(),
            projectId: $('#projectId').val(),
            description: $('input[name="description"]').val(),
            currencyCode: $('select[name="currency_code"]').val(),
            items: getPaymentItems(),
            payments: payments
        };
        
        // غیرفعال کردن دکمه‌ها
        $('#btnSave, #btnNew, #btnCalculate').prop('disabled', true);
        
        // نمایش لودینگ
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
                        window.location.href = BASE_PATH + '/people/payments.php';
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
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در ارتباط با سرور',
                    confirmButtonText: 'تایید'
                });
            },
            complete: function() {
                // فعال کردن دکمه‌ها
                $('#btnSave, #btnNew, #btnCalculate').prop('disabled', false);
            }
        });
    }
    
    // دریافت آیتم‌های پرداخت
    function getPaymentItems() {
        const items = [];
        $('.payment-item').each(function() {
            items.push({
                personId: $(this).find('.person-search').val(),
                amount: parseCurrency($(this).find('.amount-input').val()),
                description: $(this).find('.item-description').val()
            });
        });
        return items;
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
    
    // نمایش پیام موفقیت
    function showSuccess(message, callback) {
        Swal.fire({
            icon: 'success',
            title: 'موفقیت',
            text: message,
            confirmButtonText: 'تایید'
        }).then(() => {
            if (typeof callback === 'function') {
                callback();
            }
        });
    }
});