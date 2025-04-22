/**
 * products.js
 * کنترل‌کننده رابط کاربری صفحه محصولات
 * نسخه: 1.0.0
 */

$(document).ready(function() {
    'use strict';

    // ==================== تنظیمات اولیه ====================
    
    // تنظیمات Select2
    $('.select2-category').select2({
        theme: 'bootstrap-5',
        language: 'fa',
        dir: 'rtl',
        placeholder: 'نام دسته‌بندی را وارد کنید...',
        allowClear: true,
        ajax: {
            url: BASE_PATH + '/ajax/search-categories.php',
            dataType: 'json',
            delay: 250,
            cache: true,
            data: function(params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        minimumInputLength: 2,
        templateResult: formatCategoryResult,
        templateSelection: formatCategorySelection,
        escapeMarkup: function(markup) {
            return markup;
        }
    });

    // ==================== مدیریت تصویر ====================
    
    const imageUpload = $('.image-upload-wrapper');
    const imageInput = $('#image');
    const imagePreview = $('#imagePreview');
    const defaultImage = BASE_PATH + '/assets/images/product-placeholder.png';
    let isDragging = false;

    // کلیک روی ناحیه آپلود
    imageUpload.on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('upload-content')) {
            imageInput.click();
        }
    });

    // Drag & Drop
    imageUpload.on('dragenter dragover', function(e) {
        e.preventDefault();
        if (!isDragging) {
            isDragging = true;
            $(this).addClass('drag-over');
        }
    });

    imageUpload.on('dragleave drop', function(e) {
        e.preventDefault();
        isDragging = false;
        $(this).removeClass('drag-over');
        
        if (e.type === 'drop') {
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleImageUpload(files[0]);
            }
        }
    });

    // تغییر فایل
    imageInput.on('change', function(e) {
        if (this.files.length > 0) {
            handleImageUpload(this.files[0]);
        }
    });

    // پردازش آپلود تصویر
    function handleImageUpload(file) {
        // بررسی نوع فایل
        if (!file.type.match('image.*')) {
            showError('لطفاً فقط تصویر آپلود کنید');
            return;
        }

        // بررسی سایز فایل (5MB)
        if (file.size > 5242880) {
            showError('حجم فایل نباید بیشتر از 5 مگابایت باشد');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.html(`
                <div class="position-relative">
                    <img src="${e.target.result}" alt="پیش‌نمایش تصویر" class="img-fluid rounded fade-in">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" id="removeImage">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).removeClass('d-none');
        };
        reader.readAsDataURL(file);
    }

    // حذف تصویر
    $(document).on('click', '#removeImage', function() {
        imageInput.val('');
        imagePreview.addClass('d-none').empty();
    });

    // ==================== مدیریت قیمت‌ها ====================
    
    // فرمت‌بندی قیمت‌ها
    $('.price-input').each(function() {
        new Cleave(this, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: ',',
            numeralDecimalScale: 0,
            numeralPositiveOnly: true
        });
    });

    // محاسبه حاشیه سود
    function calculateProfit() {
        const purchase = parseCurrency($('#purchase_price').val());
        const sale = parseCurrency($('#sale_price').val());
        
        if (purchase > 0 && sale > 0) {
            const profit = ((sale - purchase) / purchase * 100).toFixed(1);
            const profitAmount = sale - purchase;
            
            $('#profitMargin').html(`
                <div class="profit-display fade-in">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="profit-title">حاشیه سود</div>
                            <div class="profit-amount">
                                ${profit}٪
                                <small class="text-muted">
                                    (${formatCurrency(profitAmount)} ریال)
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="profit-chart">
                                <div class="chart-bar" style="width: ${Math.min(profit, 100)}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        } else {
            $('#profitMargin').empty();
        }
    }

    $('.price-input').on('input', debounce(calculateProfit, 300));

    // ==================== کد و بارکد ====================
    
    // تولید کد محصول
    $('#generateCode').click(function() {
        const code = Math.floor(100000 + Math.random() * 900000);
        $('#code').val(code).addClass('highlight');
        setTimeout(() => $('#code').removeClass('highlight'), 500);
    });

    // اسکن بارکد
    $('#scanBarcode').click(function() {
        if (typeof Html5QrcodeScanner !== 'undefined') {
            const modal = new bootstrap.Modal('#barcodeModal');
            modal.show();

            const html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: 250,
                    rememberLastUsedCamera: true,
                    aspectRatio: 1
                }
            );
            
            html5QrcodeScanner.render((decodedText) => {
                $('#barcode').val(decodedText);
                modal.hide();
                html5QrcodeScanner.clear();
                showSuccess('بارکد با موفقیت خوانده شد');
            });

            $('#barcodeModal').on('hidden.bs.modal', function() {
                html5QrcodeScanner.clear();
            });
        } else {
            showError('لطفاً ابتدا بارکدخوان را به سیستم متصل کنید');
        }
    });

    // تولید بارکد فروشگاه
    $('#generateStoreBarcode').click(function() {
        const category = $('#category_id').val();
        if (!category) {
            showError('لطفاً ابتدا دسته‌بندی را انتخاب کنید');
            return;
        }

        $.ajax({
            url: BASE_PATH + '/ajax/generate-store-barcode.php',
            method: 'POST',
            data: { category_id: category },
            success: function(response) {
                if (response.success) {
                    $('#store_barcode').val(response.barcode);
                    showSuccess('بارکد فروشگاه با موفقیت تولید شد');
                } else {
                    showError(response.message || 'خطا در تولید بارکد فروشگاه');
                }
            },
            error: function() {
                showError('خطا در ارتباط با سرور');
            }
        });
    });

    // ==================== اعتبارسنجی و ارسال فرم ====================
    
    // اعتبارسنجی فرم
    const form = document.getElementById('productForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    }

    // ارسال فرم با Ajax
    $('#productForm').on('submit', function(e) {
        e.preventDefault();

        // بررسی اعتبارسنجی
        if (!this.checkValidity()) {
            $(this).addClass('was-validated');
            showError('لطفاً تمام فیلدهای ضروری را پر کنید');
            return;
        }

        // نمایش لودینگ
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-2"></i> در حال ذخیره...');

        // تبدیل قیمت‌ها به عدد
        $('.price-input').each(function() {
            $(this).val(parseCurrency($(this).val()));
        });

        // ارسال فرم
        const formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccess('محصول با موفقیت ذخیره شد', function() {
                        if (formData.get('redirect_to_list')) {
                            window.location.href = 'list.php';
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    showError(response.message || 'خطا در ذخیره محصول');
                }
            },
            error: function() {
                showError('خطا در ارتباط با سرور');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
                // برگرداندن فرمت قیمت‌ها
                $('.price-input').each(function() {
                    $(this).val(formatCurrency($(this).val()));
                });
            }
        });
    });

    // ==================== توابع کمکی ====================
    
    // فرمت نتایج جستجوی دسته‌بندی
    function formatCategoryResult(category) {
        if (category.loading) {
            return $('<div class="select2-result-loading">در حال جستجو...</div>');
        }

        const $container = $(
            '<div class="select2-result-category">' +
                '<div class="select2-result-category__title"></div>' +
                '<div class="select2-result-category__description text-muted small"></div>' +
            '</div>'
        );

        $container.find('.select2-result-category__title').text(category.text);
        
        if (category.description) {
            $container.find('.select2-result-category__description').text(category.description);
        } else {
            $container.find('.select2-result-category__description').remove();
        }

        return $container;
    }

    // فرمت نمایش دسته‌بندی انتخاب شده
    function formatCategorySelection(category) {
        return category.text || 'انتخاب دسته‌بندی';
    }

    // تبدیل قیمت به عدد
    function parseCurrency(value) {
        return parseInt(value.replace(/[^\d]/g, '')) || 0;
    }

    // فرمت‌بندی قیمت
    function formatCurrency(value) {
        return new Intl.NumberFormat('fa-IR').format(value);
    }

    // تاخیر در اجرای تابع
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

    // نمایش پیام موفقیت
    function showSuccess(message, callback) {
        Swal.fire({
            icon: 'success',
            title: 'موفقیت',
            text: message,
            confirmButtonText: 'تایید',
            customClass: {
                confirmButton: 'btn btn-success'
            }
        }).then(() => {
            if (typeof callback === 'function') {
                callback();
            }
        });
    }

    // نمایش پیام خطا
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: message,
            confirmButtonText: 'تایید',
            customClass: {
                confirmButton: 'btn btn-danger'
            }
        });
    }

    // ==================== مدیریت تب‌ها ====================
    
    // ذخیره و بازیابی تب فعال
    const activeTab = localStorage.getItem('activeProductTab');
    if (activeTab) {
        $(`.nav-tabs .nav-link[href="${activeTab}"]`).tab('show');
    }

    $('.nav-tabs .nav-link').on('shown.bs.tab', function(e) {
        localStorage.setItem('activeProductTab', $(e.target).attr('href'));
    });

    // ==================== راه‌اندازی ابزارها ====================
    
    // فعال‌سازی تولتیپ‌ها
    $('[data-bs-toggle="tooltip"]').tooltip();

    // فعال‌سازی پاپ‌اورها
    $('[data-bs-toggle="popover"]').popover();
});