$(document).ready(function() {
    // تنظیمات Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        language: 'fa',
        dir: 'rtl'
    });

    // آپلود تصویر با drag & drop
    const imageUpload = $('.image-upload-wrapper');
    const imageInput = $('#image');
    const imagePreview = $('#imagePreview');
    const defaultImage = BASE_PATH + '/assets/images/product-placeholder.png';

    imageUpload.on('click', function(e) {
        if (e.target === this) {
            imageInput.click();
        }
    });

    // Drag & Drop
    imageUpload.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });

    imageUpload.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });

    imageUpload.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleImageUpload(files[0]);
        }
    });

    imageInput.on('change', function(e) {
        if (e.target.files.length > 0) {
            handleImageUpload(e.target.files[0]);
        }
    });

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
                <img src="${e.target.result}" alt="preview" class="fade-in">
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImage">
                    <i class="fas fa-trash"></i>
                    حذف تصویر
                </button>
            `).removeClass('d-none');
        };
        reader.readAsDataURL(file);
    }

    // حذف تصویر
    $(document).on('click', '#removeImage', function() {
        imageInput.val('');
        imagePreview.addClass('d-none').empty();
    });

    // فرمت‌بندی قیمت‌ها
    $('.price-input').each(function() {
        new Cleave(this, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: ',',
            numeralDecimalScale: 0
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
                    <div class="profit-title">حاشیه سود</div>
                    <div class="profit-amount">${profit}%</div>
                    <small class="text-muted">${formatCurrency(profitAmount)} ریال</small>
                </div>
            `);
        } else {
            $('#profitMargin').empty();
        }
    }

    $('.price-input').on('input', function() {
        calculateProfit();
    });

    // تولید کد محصول
    $('#generateCode').click(function() {
        const code = Math.floor(100000 + Math.random() * 900000);
        $('#code').val(code);

        // انیمیشن برای نمایش تغییر
        $('#code').addClass('highlight');
        setTimeout(() => {
            $('#code').removeClass('highlight');
        }, 500);
    });

    // بارکد خوان
    $('#scanBarcode').click(function() {
        // در صورت وجود کتابخانه بارکدخوان
        if (typeof Html5QrcodeScanner !== 'undefined') {
            $('#barcodeModal').modal('show');
            const html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", { fps: 10, qrbox: 250 });
            
            html5QrcodeScanner.render((decodedText) => {
                $('#barcode').val(decodedText);
                $('#barcodeModal').modal('hide');
                html5QrcodeScanner.clear();
            });
        } else {
            showError('لطفاً ابتدا بارکدخوان را به سیستم متصل کنید');
        }
    });

    // ذخیره فرم
    $('#productForm').on('submit', function(e) {
        e.preventDefault();

        // بررسی فیلدهای اجباری
        const requiredFields = ['name', 'code', 'sale_price'];
        let isValid = true;

        requiredFields.forEach(field => {
            const value = $(`#${field}`).val();
            if (!value || value.trim() === '') {
                showError(`لطفاً ${$(`label[for="${field}"]`).text()} را وارد کنید`);
                $(`#${field}`).addClass('is-invalid');
                isValid = false;
            } else {
                $(`#${field}`).removeClass('is-invalid');
            }
        });

        if (!isValid) return;

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
                        window.location.href = 'list.php';
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

    // توابع کمکی
    function parseCurrency(value) {
        return parseInt(value.replace(/[^\d]/g, '')) || 0;
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('fa-IR').format(value);
    }

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

    // مدیریت تب‌ها
    $('.nav-tabs .nav-link').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // ذخیره و بازیابی تب فعال
    const activeTab = localStorage.getItem('activeProductTab');
    if (activeTab) {
        $(`.nav-tabs .nav-link[href="${activeTab}"]`).tab('show');
    }

    $('.nav-tabs .nav-link').on('shown.bs.tab', function(e) {
        localStorage.setItem('activeProductTab', $(e.target).attr('href'));
    });
});