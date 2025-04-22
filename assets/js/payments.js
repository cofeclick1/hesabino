// در ابتدای فایل قبلی اضافه کنید
function formatPersonItem(person) {
    if (!person.id) return person.text;
    
    return $(
        '<div class="person-result">' +
            '<img src="' + person.avatar_path + '" alt="" onerror="this.src=\'' + BASE_PATH + '/assets/images/avatar.png\'">' +
            '<div class="person-info">' +
                '<div class="person-name">' + person.text + '</div>' +
                '<div class="person-details">' +
                    (person.mobile ? '<i class="fas fa-phone me-1"></i>' + person.mobile : '') +
                    (person.national_code ? '<span class="mx-2">|</span><i class="fas fa-id-card me-1"></i>' + person.national_code : '') +
                '</div>' +
            '</div>' +
        '</div>'
    );
}
$(document).ready(function () {
    // متغیرهای عمومی
    let totalAmount = 0;
    let paidAmount = 0;
    let payments = [];
    let currentPaymentId = 1;
    
    // راه‌اندازی صفحه
    initializePage();
    // اضافه کردن تابع formatAmount (بعد از initializePage)
function formatAmount(input) {
    const value = input.value.replace(/[^\d]/g, '');
    input.value = value ? new Intl.NumberFormat('fa-IR').format(value) : '';
}
    function initializePage() {
        initializeDatePickers();
        initializeSelect2();
        setupValidation();
        setupEventListeners();
        addPaymentItem();
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
        // برای سایر select‌ها
        $('.select2').not('.person-search').select2({
            theme: 'bootstrap-5',
            language: 'fa',
            dir: 'rtl'
        });

        // برای جستجوی اشخاص
        $('.person-search').select2({
            theme: 'bootstrap-5',
            language: 'fa',
            dir: 'rtl',
            placeholder: 'نام، موبایل یا کد ملی را وارد کنید...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: BASE_PATH + '/api/search-people.php',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results || [],
                        pagination: {
                            more: data.pagination?.more || false
                        }
                    };
                },
                cache: true,
                error: function(xhr, status, error) {
                    console.error('Search Error:', error);
                    let errorMessage = 'خطا در جستجوی اطلاعات';
                    if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا در جستجو',
                        text: errorMessage,
                        confirmButtonText: 'تایید'
                    });
                }
            },
            minimumInputLength: 2,
            templateResult: formatPersonResult,
            templateSelection: formatPersonSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        }).on('select2:select', function(e) {
            updatePersonDetails($(this), e.params.data);
        }).on('select2:clear', function() {
            clearPersonDetails($(this));
        });
    }
    

    
    // راه‌اندازی جستجوی افراد با Select2
function initializePersonSearch() {
    $('.person-search').select2({
        theme: 'bootstrap-5',
        language: 'fa',
        dir: 'rtl',
        placeholder: 'نام، موبایل یا کد ملی را وارد کنید...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: BASE_PATH + '/api/search-people.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results || [],
                    pagination: {
                        more: data.pagination ? data.pagination.more : false
                    }
                };
            },
            cache: true,
            error: function(xhr, status, error) {
                console.error('Search Error:', error);
                // نمایش خطا به کاربر
                Swal.fire({
                    icon: 'error',
                    title: 'خطا در جستجو',
                    text: 'متأسفانه خطایی در جستجوی اطلاعات رخ داد. لطفاً مجدداً تلاش کنید.',
                    confirmButtonText: 'تایید'
                });
            }
        },
        minimumInputLength: 2,
        templateResult: formatPersonResult,
        templateSelection: formatPersonSelection,
        escapeMarkup: function (markup) {
            return markup;
        }
    }).on('select2:select', function(e) {
        // وقتی یک شخص انتخاب می‌شود
        const data = e.params.data;
        updatePersonDetails($(this), data);
    }).on('select2:clear', function() {
        // وقتی انتخاب پاک می‌شود
        clearPersonDetails($(this));
    });
}
    // فرمت نمایش نتیجه جستجوی شخص
    function formatPersonResult(person) {
        if (!person.id) return person.text;

        return $(`
            <div class="d-flex align-items-center py-1">
                <div class="flex-shrink-0">
                    <img src="${person.avatar_path}" class="rounded-circle" 
                         style="width: 40px; height: 40px; object-fit: cover;"
                         onerror="this.src='${BASE_PATH}/assets/images/avatar.png'">
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="font-weight-bold">${person.text}</div>
                    <div class="small text-muted">
                        ${person.mobile ? `<i class="fas fa-phone me-1"></i>${person.mobile}` : ''}
                        ${person.national_code ? `<span class="mx-2">|</span><i class="fas fa-id-card me-1"></i>${person.national_code}` : ''}
                        ${person.type === 'legal' ? '<span class="badge bg-warning ms-2">حقوقی</span>' : ''}
                    </div>
                </div>
            </div>
        `);
    }

    // فرمت نمایش شخص انتخاب شده
    function formatPersonSelection(person) {
        if (!person.id) return person.text;

        return $(`
            <div class="d-flex align-items-center">
                <img src="${person.avatar_path}" class="rounded-circle me-2" 
                     style="width: 24px; height: 24px; object-fit: cover;"
                     onerror="this.src='${BASE_PATH}/assets/images/avatar.png'">
                <div>${person.text}</div>
                ${person.mobile ? `<small class="text-muted ms-2">(${person.mobile})</small>` : ''}
            </div>
        `);
    }

    function updatePersonDetails($select, person) {
        const $paymentItem = $select.closest('.payment-item');
        $paymentItem.find('.person-avatar').attr('src', person.avatar_path);
        $select.data('person', person);
    }

    function clearPersonDetails($select) {
        const $paymentItem = $select.closest('.payment-item');
        $paymentItem.find('.person-avatar').attr('src', BASE_PATH + '/assets/images/avatar.png');
        $select.removeData('person');
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
        $(item).find('.btn-add-person').on('click', showNewPersonModal);
    }
    
    // تنظیم فرمت‌بندی مبلغ
function setupAmountInput(input) {
    if (input) { // اضافه کردن چک کردن وجود input
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
                Swal.fire({
                    icon: 'success',
                    title: 'موفقیت',
                    text: response.message,
                    confirmButtonText: 'تایید'
                });
                
                // پاکسازی فرم
                form.reset();
                $(form).removeClass('was-validated');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: xhr.responseJSON?.message || 'خطا در ارتباط با سرور',
                confirmButtonText: 'تایید'
            });
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
                // اصلاح خط زیر
                $('#commonDescriptions').append(
                    '<option value="' + desc.text + '"></option>'
                );
                
                // افزودن به فیلد توضیحات فعلی
                $('input[name="description"]').val(desc.text);
                
                $('#descriptionModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'موفقیت',
                    text: response.message,
                    confirmButtonText: 'تایید'
                });
                
                // پاکسازی فرم
                form.reset();
                $(form).removeClass('was-validated');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: xhr.responseJSON?.message || 'خطا در ارتباط با سرور',
                confirmButtonText: 'تایید'
            });
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
// تنظیم جستجوی شخص
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

    // راه‌اندازی جستجوی شخص - روش جدید
    function setupPersonSearch() {
        // تایپ در فیلد جستجو
        $(document).on('input', '.person-search-input', debounce(function() {
            const searchInput = $(this);
            const searchWrapper = searchInput.closest('.search-wrapper');
            const resultsContainer = searchWrapper.find('.search-results');
            const query = searchInput.val().trim();
            
            if (query.length < 2) {
                resultsContainer.hide();
                return;
            }
            
            // نمایش loading
            resultsContainer.html(`
                <div class="p-3 text-center">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">در حال جستجو...</span>
                    </div>
                </div>
            `).show();
            
            // ارسال درخواست AJAX
            $.ajax({
                url: BASE_PATH + '/ajax/search_people.php',
                data: { search: query },
                method: 'GET',
                success: function(response) {
                    if (response.items && response.items.length > 0) {
                        const resultsHtml = response.items.map(person => `
                            <div class="search-result-item p-2 hover-bg" 
                                 data-id="${person.id}" 
                                 data-name="${person.text}"
                                 data-mobile="${person.mobile || ''}"
                                 data-type="${person.type || 'real'}">
                                <div class="d-flex align-items-center">
                                    <img src="${person.avatar}" class="rounded-circle me-2" 
                                         style="width: 40px; height: 40px; object-fit: cover;"
                                         onerror="this.src='${BASE_PATH}/assets/images/avatar.png'">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">${person.text}</div>
                                        <div class="small text-muted">
                                            ${person.mobile ? `<i class="fas fa-phone me-1"></i>${person.mobile}` : ''}
                                            ${person.type === 'legal' ? 
                                                '<span class="badge bg-warning ms-2">حقوقی</span>' : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                        
                        resultsContainer.html(resultsHtml);
                    } else {
                        resultsContainer.html(`
                            <div class="p-2 text-center text-muted">
                                <i class="fas fa-search me-1"></i>
                                نتیجه‌ای یافت نشد
                            </div>
                        `);
                    }
                },
                error: function() {
                    resultsContainer.html(`
                        <div class="p-2 text-center text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            خطا در جستجو
                        </div>
                    `);
                }
            });
        }, 300));

        // انتخاب شخص از نتایج
        $(document).on('click', '.search-result-item', function() {
            const item = $(this);
            const searchWrapper = item.closest('.search-wrapper');
            const paymentItem = searchWrapper.closest('.payment-item');
            
            // ذخیره اطلاعات شخص
            searchWrapper.find('.person-id').val(item.data('id'));
            
            // نمایش اطلاعات انتخاب شده
            searchWrapper.find('.person-search-input').replaceWith(`
                <div class="selected-person d-flex align-items-center p-2">
                    <img src="${item.find('img').attr('src')}" class="rounded-circle me-2" 
                         style="width: 24px; height: 24px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${item.data('name')}</div>
                        ${item.data('mobile') ? 
                            `<small class="text-muted">${item.data('mobile')}</small>` : ''}
                    </div>
                    <button type="button" class="btn-close clear-person ms-2" 
                            aria-label="Clear"></button>
                </div>
            `);

            // آپدیت آواتار در کارت پرداخت
            paymentItem.find('.person-avatar').attr('src', item.find('img').attr('src'));
            
            // مخفی کردن نتایج
            searchWrapper.find('.search-results').hide();
        });

        // پاک کردن شخص انتخاب شده
        $(document).on('click', '.clear-person', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const searchWrapper = $(this).closest('.search-wrapper');
            const paymentItem = searchWrapper.closest('.payment-item');
            
            // بازگشت به حالت جستجو
            $(this).closest('.selected-person').replaceWith(`
                <input type="text" class="form-control person-search-input" 
                       placeholder="نام، موبایل یا کد ملی را وارد کنید...">
            `);
            
            // پاک کردن مقادیر
            searchWrapper.find('.person-id').val('');
            paymentItem.find('.person-avatar')
                .attr('src', BASE_PATH + '/assets/images/avatar.png');
        });

        // مخفی کردن نتایج با کلیک خارج از باکس
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-wrapper').length) {
                $('.search-results').hide();
            }
        });
}
    
    // تابع تاخیر در جستجو
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
// فراخوانی تابع در initializePage
function initializePage() {
    initializeDatePickers();
    initializeSelect2();
    setupValidation();
    setupEventListeners();
    setupPersonSearch(); // اضافه کردن این خط
    
    // اضافه کردن اولین آیتم
    addPaymentItem();

    // تنظیم مقدار اولیه واحد پول
    updateCurrencySymbols();
}

