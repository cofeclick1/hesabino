$(document).ready(function () {
    // متغیرهای عمومی
    let totalAmount = 0;
    let paidAmount = 0;
    let payments = [];
    let currentPaymentId = 1;
    
    // راه‌اندازی صفحه
    initializePage();

    // تابع فرمت‌بندی مبلغ
    function formatAmount(input) {
        const value = input.value.replace(/[^\d]/g, '');
        input.value = value ? new Intl.NumberFormat('fa-IR').format(value) : '';
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
        }).trigger('click');
    }
    
    // راه‌اندازی select2
    function initializeSelect2() {
        // برای سایر select‌ها (بجز جستجوی اشخاص)
        $('.select2:not(.person-search)').select2({
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
        $('#btnNewProject').on('click', showNewProjectModal);
        
        // رویدادهای توضیحات
        $('#btnAddDescription').on('click', showNewDescriptionModal);
        
        // رویداد دکمه‌های ذخیره در مودال‌ها
        $('#saveProject').on('click', saveProject);
        $('#saveDescription').on('click', saveDescription);
        
        // رویداد دکمه‌های انصراف مودال‌ها
        $('.modal .btn-secondary').on('click', function() {
            $(this).closest('.modal').modal('hide');
        });

        // رویداد تغییر مبلغ
        $(document).on('input', '.amount-input', function() {
            formatAmount(this);
            updateTotalAmount();
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

        // افزودن شخص جدید
        $(item).find('.btn-add-person').on('click', showNewPersonModal);
    }
    
    // تنظیم فرمت‌بندی مبلغ
    function setupAmountInput(input) {
        if (input) {
            new Cleave(input, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: '.',
                numeralPositiveOnly: true
            });
        }
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

    // نمایش مودال پروژه جدید
    function showNewProjectModal() {
        $('#projectForm')[0].reset();
        $('#projectForm').removeClass('was-validated');
        $('#projectModal').modal('show');
    }

    // ذخیره پروژه
    function saveProject() {
        const form = $('#projectForm')[0];
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }
        
        const formData = new FormData(form);
        
        // نمایش loading
        Swal.fire({
            title: 'در حال ذخیره...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: BASE_PATH + '/api/save-project.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    const project = response.project;
                    const option = new Option(project.name, project.id, true, true);
                    $(option).data('logo', project.logo_path);
                    $('select[name="project_id"]').append(option).trigger('change');
                    
                    $('#projectModal').modal('hide');
                    showSuccess(response.message);
                    
                    // پاکسازی فرم
                    form.reset();
                    $(form).removeClass('was-validated');
                }
            },
            error: function(xhr) {
                showError(xhr.responseJSON?.message || 'خطا در ارتباط با سرور');
            }
        });
    }

    // نمایش مودال توضیحات
    function showNewDescriptionModal() {
        $('#descriptionForm')[0].reset();
        $('#descriptionForm').removeClass('was-validated');
        $('#descriptionModal').modal('show');
    }

    // ذخیره توضیحات
    function saveDescription() {
        const form = $('#descriptionForm')[0];
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }
        
        // نمایش loading
        Swal.fire({
            title: 'در حال ذخیره...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const data = {
            text: $('#descriptionText').val(),
            type: 'payment'
        };
        
        $.ajax({
            url: BASE_PATH + '/api/save-description.php',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    const desc = response.description;
                    // افزودن به datalist
                    $('#commonDescriptions').append(
                        `<option value="${desc.text}"></option>`
                    );
                    
                    // افزودن به فیلد توضیحات فعلی
                    $('input[name="description"]').val(desc.text);
                    
                    $('#descriptionModal').modal('hide');
                    showSuccess(response.message);
                    
                    // پاکسازی فرم
                    form.reset();
                    $(form).removeClass('was-validated');
                }
            },
            error: function(xhr) {
                showError(xhr.responseJSON?.message || 'خطا در ارتباط با سرور');
            }
        });
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
            showError('لطفا حداقل یک آیتم پرداخت اضافه کنید');
            return;
        }
        
        if (totalAmount !== paidAmount) {
            showError('مبلغ پرداختی با جمع کل برابر نیست');
            return;
        }
        
        const paymentData = {
            documentNumber: $('input[name="document_number"]').val(),
            date: $('input[name="date"]').val(),
            projectId: $('select[name="project_id"]').val(),
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
                    showSuccess('پرداخت با موفقیت ثبت شد', () => {
                        window.location.href = BASE_PATH + '/people/payments.php';
                    });
                } else {
                    showError(response.message || 'خطا در ثبت پرداخت');
                }
            },
            error: function(xhr) {
                showError('خطا در ارتباط با سرور');
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
                personId: $(this).find('.person-id').val(),
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

    // راه‌اندازی صفحه
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

    function initializePersonSearch(wrapper) {
    const input = wrapper.querySelector('.person-search-input');
    const hiddenInput = wrapper.querySelector('.person-id');
    
    let contactPicker = new ContactPicker({
        width: '500px',
        selectedId: hiddenInput.value || null,
        onSelect: (person) => {
            // آپدیت مقادیر
            hiddenInput.value = person.id;
            
            // نمایش اطلاعات انتخاب شده
            input.replaceWith(`
                <div class="selected-person d-flex align-items-center p-2">
                    <img src="${person.avatar}" class="rounded-circle me-2" 
                         style="width: 24px; height: 24px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${person.name}</div>
                        ${person.mobile ? 
                            `<small class="text-muted">${person.mobile}</small>` : ''}
                    </div>
                    <button type="button" class="btn-close clear-person ms-2" 
                            aria-label="Clear"></button>
                </div>
            `);
            
            // آپدیت آواتار در کارت پرداخت
            const paymentItem = wrapper.closest('.payment-item');
            paymentItem.querySelector('.person-avatar').src = person.avatar;
            
            // مخفی کردن لیست
            contactPicker.hide();
        }
    });
    
    // نمایش لیست با کلیک روی input
    input.addEventListener('click', () => {
        // محاسبه موقعیت
        const rect = input.getBoundingClientRect();
        contactPicker.container.style.position = 'absolute';
        contactPicker.container.style.top = `${rect.bottom}px`;
        contactPicker.container.style.left = `${rect.left}px`;
        
        contactPicker.show();
    });
    
    // مخفی کردن لیست با کلیک خارج از آن
    document.addEventListener('click', (e) => {
        if (!contactPicker.container.contains(e.target) && 
            !input.contains(e.target)) {
            contactPicker.hide();
        }
    });
    
    // پاک کردن انتخاب
    wrapper.addEventListener('click', (e) => {
        if (e.target.matches('.clear-person')) {
            const selectedPerson = e.target.closest('.selected-person');
            selectedPerson.replaceWith(`
                <input type="text" class="form-control person-search-input" 
                       placeholder="نام، موبایل یا کد ملی را وارد کنید..."
                       autocomplete="off">
            `);
            
            hiddenInput.value = '';
            
            const paymentItem = wrapper.closest('.payment-item');
            paymentItem.querySelector('.person-avatar').src = BASE_PATH + '/assets/images/avatar.png';
            
            // بازسازی جستجو
            initializePersonSearch(wrapper);
        }
    });
}
});