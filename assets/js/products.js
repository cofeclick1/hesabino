$(document).ready(function() {
    // Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // تولید کد محصول
    $('#generateCode').click(function() {
        const code = Math.floor(100000 + Math.random() * 900000);
        $('#code').val(code);
    });

    // آپلود تصویر
    $('.image-upload-wrapper').click(function() {
        $('#image').click();
    });

    $('#image').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5242880) { // 5MB
                alert('حجم فایل نباید بیشتر از 5 مگابایت باشد');
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').removeClass('d-none')
                    .find('img').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    $('#removeImage').click(function() {
        $('#image').val('');
        $('#imagePreview').addClass('d-none')
            .find('img').attr('src', '');
    });

    // فرمت‌بندی قیمت‌ها
    $('.price-input').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        $(this).val(numberFormat(value));
        calculateProfit();
    });

    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function calculateProfit() {
        const purchase = parseInt($('#purchase_price').val().replace(/,/g, '')) || 0;
        const sale = parseInt($('#sale_price').val().replace(/,/g, '')) || 0;
        
        if (purchase > 0 && sale > 0) {
            const profit = ((sale - purchase) / purchase * 100).toFixed(2);
            $('#profitMargin').text(profit);
        } else {
            $('#profitMargin').text('0');
        }
    }

    // اسکن بارکد
    $('#scanBarcode').click(function() {
        // اینجا می‌تونید کد مربوط به بارکدخوان رو اضافه کنید
        alert('لطفاً بارکد را اسکن کنید');
    });

    // تولید بارکد فروشگاه
    $('#generateStoreBarcode').click(function() {
        const category = $('#category').val();
        if (!category) {
            alert('لطفاً ابتدا دسته‌بندی را انتخاب کنید');
            return;
        }

        $.ajax({
            url: BASE_PATH + '/ajax/generate-barcode.php',
            method: 'POST',
            data: {
                category_id: category
            },
            success: function(response) {
                if (response.success) {
                    $('#store_barcode').val(response.barcode);
                } else {
                    alert('خطا در تولید بارکد: ' + response.message);
                }
            },
            error: function() {
                alert('خطا در ارتباط با سرور');
            }
        });
    });

    // Validation
    $('#productForm').on('submit', function(e) {
        const requiredFields = ['name', 'code', 'sale_price'];
        let isValid = true;

        requiredFields.forEach(field => {
            const value = $(`#${field}`).val();
            if (!value || value.trim() === '') {
                isValid = false;
                $(`#${field}`).addClass('is-invalid');
            } else {
                $(`#${field}`).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('لطفاً تمام فیلدهای ضروری را پر کنید');
        }

        // تبدیل قیمت‌ها به عدد
        $('.price-input').each(function() {
            $(this).val($(this).val().replace(/,/g, ''));
        });
    });
});