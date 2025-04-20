// تعریف متغیرهای سراسری
let globalCurrency;
let globalTaxRate;

// تابع راه‌اندازی اولیه
function initializeInvoice(currency, taxRate) {
    globalCurrency = currency;
    globalTaxRate = taxRate;
    
    $(document).ready(function() {
        let itemIndex = 0;
        let searchTimeout;

        // تابع فرمت‌کردن اعداد به فارسی
        function number_format(number) {
            return new Intl.NumberFormat('fa-IR').format(number);
        }

        // تعریف رویداد کلیک دکمه افزودن کالا
        $('#add-item').on('click', function() {
            addNewItem();
        });

        // افزودن آیتم جدید
        function addNewItem() {
            let newItem = `
    <div class="invoice-item">
        <div class="row g-3">
            <div class="col-md-5">
                <div class="position-relative">
                    <input type="text" 
                        name="items[${itemIndex}][product_search]" 
                        class="form-control product-search" 
                        placeholder="نام یا کد کالا را وارد کنید..."
                        autocomplete="off"
                        required>
                    <input type="hidden" 
                        name="items[${itemIndex}][product_id]" 
                        class="product-id">
                </div>
            </div>
            <div class="col-md-2">
                <input type="number" 
                    name="items[${itemIndex}][quantity]" 
                    class="form-control quantity-input" 
                    placeholder="تعداد"
                    min="1"
                    required>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="number" 
                        name="items[${itemIndex}][price]" 
                        class="form-control price-input" 
                        placeholder="قیمت واحد"
                        required>
                    <span class="input-group-text">تومان</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="amount-container">
                    <div class="amount-display text-success fw-bold">
                        0 ${currency}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-item w-100">
                    <i class="fas fa-trash-alt"></i> حذف کالا
                </button>
            </div>
        </div>
    </div>`;

$('#invoice-items').append(newItem);
itemIndex++;

            // نمایش نوتیفیکیشن کوچک
            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-center',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'success',
                title: 'آیتم جدید اضافه شد',
                customClass: {
                    popup: 'custom-toast',
                    title: 'custom-toast-title'
                }
            });
        }

        // افزودن اولین آیتم در لود صفحه تنها در صورتی که هیچ آیتمی موجود نباشد
        if ($('#invoice-items .invoice-item').length === 0) {
            addNewItem();
        }

        // حذف آیتم
        $(document).on('click', '.remove-item', function() {
            const $item = $(this).closest('.invoice-item');
            const productName = $item.find('.product-search').val();

            if (productName) {
                Swal.fire({
                    icon: 'warning',
                    title: 'حذف محصول',
                    text: `آیا از حذف محصول "${productName}" از سبد خرید اطمینان دارید؟`,
                    showCancelButton: true,
                    confirmButtonText: 'بله',
                    cancelButtonText: 'خیر'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $item.remove();
                        calculateTotal();

                        Swal.fire({
                            icon: 'success',
                            title: 'محصول حذف شد',
                            text: `محصول "${productName}" از سبد خرید حذف شد.`,
                            toast: true,
                            position: 'bottom-center',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'custom-toast custom-toast-success',
                                title: 'custom-toast-title'
                            }
                        });
                    }
                });
            } else {
                $item.remove();
                calculateTotal();

                Swal.fire({
                    icon: 'success',
                    title: 'کالا حذف شد',
                    text: 'کالا از فاکتور حذف شد.',
                    toast: true,
                    position: 'bottom-center',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'custom-toast custom-toast-success',
                        title: 'custom-toast-title'
                    }
                });
            }
        });

        // جستجوی محصول
        $(document).on('input', '.product-search', function() {
            let searchInput = $(this);
            let productRow = searchInput.closest('.invoice-item');
            let query = searchInput.val().trim();
            
            productRow.find('.product-search-results').remove();
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: 'ajax/search-products.php',
                        method: 'GET',
                        data: { query: query },
                        beforeSend: function() {
                            let loading = $(`
                                <div class="product-search-results">
                                    <div class="p-3 text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">در حال جستجو...</span>
                                        </div>
                                    </div>
                                </div>`);
                            searchInput.after(loading);
                        },
                        success: function(response) {
                            productRow.find('.product-search-results').remove();

                            if (response.status === 'success' && response.data.length > 0) {
                                let results = $('<div class="product-search-results"></div>');
                                
                                response.data.forEach(function(product) {
                                    let stockClass = product.stock > 0 ? 'text-success' : 'text-danger';
                                    let item = $(`
                                        <div class="search-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold">${product.name}</div>
                                                    <div class="small text-muted">${product.code || ''}</div>
                                                    <div class="small ${stockClass}">
                                                        ${product.stock_status}
                                                    </div>
                                                </div>
                                                <div class="text-primary fw-bold">
                                                    ${product.price_formatted}
                                                </div>
                                            </div>
                                        </div>`);

                                    item.on('click', function() {
                                        searchInput.val(product.name);
                                        productRow.find('.product-id').val(product.id);
                                        
                                        let priceInput = productRow.find('.price-input');
                                        priceInput.val(product.price);
                                        
                                        let quantityInput = productRow.find('.quantity-input');
                                        
                                        if (product.stock > 0) {
                                            // محصول موجود است
                                            quantityInput
                                                .attr('max', product.stock)
                                                .prop('disabled', false)
                                                .attr('placeholder', `حداکثر ${product.stock} عدد`);
                                            
                                            results.remove();
                                            calculateItemTotal(productRow);
                                            calculateTotal();

                                            // نمایش نوتیفیکیشن موفقیت فقط برای محصولات موجود
                                            const Toast = Swal.mixin({
                                                toast: true,
                                                position: 'bottom-center',
                                                showConfirmButton: false,
                                                timer: 3000,
                                                timerProgressBar: true,
                                                didOpen: (toast) => {
                                                    toast.addEventListener('mouseenter', Swal.stopTimer)
                                                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                                                }
                                            });

                                            Toast.fire({
                                                icon: 'success',
                                                title: `محصول ${product.name} به فاکتور اضافه شد`,
                                                customClass: {
                                                    popup: 'custom-toast',
                                                    title: 'custom-toast-title'
                                                }
                                            });

                                            // اگر موجودی کم است
                                            if (product.stock <= 5) {
                                                setTimeout(() => {
                                                    Toast.fire({
                                                        icon: 'warning',
                                                        title: `توجه: تنها ${product.stock} عدد از این کالا در انبار موجود است`,
                                                    });
                                                }, 3500);
                                            }
                                        } else {
                                            // محصول ناموجود است
                                            quantityInput
                                                .prop('disabled', true)
                                                .attr('placeholder', 'ناموجود')
                                                .val('');
                                            
                                            // پاک کردن قیمت برای محصول ناموجود
                                            priceInput.val('');
                                            
                                            results.remove();
                                            calculateItemTotal(productRow);
                                            calculateTotal();

                                            // نمایش نوتیفیکیشن خطا برای محصول ناموجود
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'خطا',
                                                text: `محصول ${product.name} در حال حاضر ناموجود است`,
                                                toast: true,
                                                position: 'bottom-center',
                                                showConfirmButton: false,
                                                timer: 3000,
                                                timerProgressBar: true,
                                                customClass: {
                                                    popup: 'custom-toast custom-toast-error',
                                                    title: 'custom-toast-title'
                                                }
                                            });
                                        }
                                    });

                                    results.append(item);
                                });

                                searchInput.after(results);
                            } else {
                                let noResults = $(`
                                    <div class="product-search-results">
                                        <div class="p-3 text-center text-muted">
                                            <i class="fas fa-search fa-2x mb-2"></i>
                                            <div>کالایی یافت نشد</div>
                                            <div class="small">لطفاً با کلمه کلیدی دیگری جستجو کنید</div>
                                        </div>
                                    </div>`);
                                searchInput.after(noResults);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('خطا در جستجوی کالا:', error);
                            showNotification('error', 'خطا در جستجوی کالا. لطفاً دوباره تلاش کنید.');
                        }
                    });
                }, 300);
            }
        });

        // محاسبه مجموع هر آیتم
        function calculateItemTotal(row) {
            let quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            let price = parseFloat(row.find('.price-input').val()) || 0;
            let total = quantity * price;
            row.find('.amount-display').text(number_format(total) + ` ${currency}`);
            return total;
        }

        // محاسبه مجموع کل فاکتور
        function calculateTotal() {
            let subtotal = 0;
            $('.invoice-item').each(function() {
                subtotal += calculateItemTotal($(this));
            });
            
            let taxRate = parseFloat($('#tax_rate').val()) || 0;
            let taxAmount = (subtotal * taxRate) / 100;
            let discount = parseFloat($('#discount_amount').val()) || 0;
            let finalAmount = subtotal + taxAmount - discount;
            
            $('#subtotal').text(number_format(subtotal) + ` ${currency}`);
            $('#tax_amount').text(number_format(taxAmount) + ` ${currency}`);
            $('#discount').text(number_format(discount) + ` ${currency}`);
            $('#final_amount').text(number_format(finalAmount) + ` ${currency}`);
        }

        // رویدادهای محاسبه مجموع
        $(document).on('input', '.quantity-input, .price-input', function() {
            calculateItemTotal($(this).closest('.invoice-item'));
            calculateTotal();
        });

        $(document).on('input', '#tax_rate, #discount_amount', calculateTotal);

        // بستن منوی جستجو در کلیک خارج از آن
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.invoice-item').length) {
                $('.product-search-results').remove();
            }
        });

        // نمایش اعلان
        function showNotification(type, message) {
            let alertClass = type === 'warning' ? 'alert-warning' : 
                            type === 'error' ? 'alert-danger' : 
                            'alert-info';
            
            let alert = $(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`);
            
            $('.container-fluid').prepend(alert);
            setTimeout(() => alert.alert('close'), 5000);
        }

        // اعتبارسنجی فرم
        $('#invoice-form').on('submit', function(e) {
            if ($('.invoice-item').length === 0) {
                e.preventDefault();
                showNotification('error', 'لطفاً حداقل یک کالا به فاکتور اضافه کنید');
                return false;
            }
            
            let isValid = true;
            $('.invoice-item').each(function() {
                let row = $(this);
                let productId = row.find('.product-id').val();
                let quantity = parseFloat(row.find('.quantity-input').val());
                let price = parseFloat(row.find('.price-input').val());
                
                if (!productId || !quantity || !price) {
                    isValid = false;
                    showNotification('error', 'لطفاً تمام فیلدهای کالاها را تکمیل کنید');
                    return false;
                }
            });
            
            if (!isValid) {
                e.preventPreventDefault();
                return false;
            }
        });

        // تعریف فرم مشتری جدید
        const customerForm = `
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" id="first_name" class="form-control" placeholder="نام *">
                </div>
                <div class="col-md-6">
                    <input type="text" id="last_name" class="form-control" placeholder="نام خانوادگی *">
                </div>
                <div class="col-md-6">
                    <input type="text" id="mobile" class="form-control" placeholder="شماره موبایل *" pattern="09[0-9]{9}">
                </div>
                <div class="col-md-6">
                    <input type="email" id="email" class="form-control" placeholder="ایمیل">
                </div>
                <div class="col-12">
                    <textarea id="address" class="form-control" placeholder="آدرس" rows="2"></textarea>
                </div>
            </div>`;

        // رویداد کلیک دکمه افزودن مشتری
        $('#addCustomerBtn').click(function() {
            $.ajax({
                url: 'ajax/get-customer-code.php',
                method: 'GET',
                success: function(response) {
                    console.log('Response:', response);
                    const customerCode = response.customer_code || 'خطا در دریافت کد';
                    Swal.fire({
                        title: 'افزودن مشتری جدید',
                        html: `
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" id="first_name" class="form-control" placeholder="نام *">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="last_name" class="form-control" placeholder="نام خانوادگی *">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="mobile" class="form-control" placeholder="شماره موبایل *" pattern="09[0-9]{9}">
                                </div>
                                <div class="col-md-6">
                                    <input type="email" id="email" class="form-control" placeholder="ایمیل">
                                </div>
                                <div class="col-12">
                                    <textarea id="address" class="form-control" placeholder="آدرس" rows="2"></textarea>
                                </div>
                            </div>`,
                        showCancelButton: true,
                        confirmButtonText: 'ثبت مشتری',
                        cancelButtonText: 'انصراف',
                        customClass: {
                            container: 'rtl-swal',
                            popup: 'border-radius-3',
                            title: 'fs-5 mb-4',
                            htmlContainer: 'py-3',
                            confirmButton: 'btn btn-primary ms-2',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        preConfirm: () => {
                            // اعتبارسنجی فرم
                            const firstName = Swal.getPopup().querySelector('#first_name').value;
                            const lastName = Swal.getPopup().querySelector('#last_name').value;
                            const mobile = Swal.getPopup().querySelector('#mobile').value;
                            const email = Swal.getPopup().querySelector('#email').value;
                            const address = Swal.getPopup().querySelector('#address').value;

                            if (!firstName || !lastName || !mobile) {
                                Swal.showValidationMessage('نام، نام خانوادگی و شماره موبایل الزامی هستند');
                                return false;
                            }

                            if (!mobile.match(/^09[0-9]{9}$/)) {
                                Swal.showValidationMessage('فرمت شماره موبایل صحیح نیست');
                                return false;
                            }

                            if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                                Swal.showValidationMessage('فرمت ایمیل صحیح نیست');
                                return false;
                            }

                            return { firstName, lastName, mobile, email, address };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const data = result.value;
                            
                            // ارسال اطلاعات به سرور
                            $.ajax({
                                url: 'ajax/add-customer.php',
                                method: 'POST',
                                data: {
                                    first_name: data.firstName,
                                    last_name: data.lastName,
                                    mobile: data.mobile,
                                    email: data.email,
                                    address: data.address
                                },
                                success: function(response) {
                                    // تبدیل رشته JSON به آبجکت
                                    if (typeof response === 'string') {
                                        response = JSON.parse(response);
                                    }
                                    
                                    if (response.status === 'success') {
                                        // اضافه کردن مشتری جدید به لیست
                                        const newOption = new Option(
                                            `${data.firstName} ${data.lastName}`,
                                            response.customer_id,
                                            true,
                                            true
                                        );
                                        $('#customer_id').append(newOption).trigger('change');
                                        
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'موفقیت',
                                            text: response.message,
                                            customClass: {
                                                confirmButton: 'btn btn-success'
                                            },
                                            confirmButtonText: 'تایید'
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'خطا',
                                            text: response.message,
                                                                                       customClass: {
                                                confirmButton: 'btn btn-danger'
                                            },
                                            confirmButtonText: 'تایید'
                                        });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'خطا',
                                        text: 'خطا در برقراری ارتباط با سرور',
                                        customClass: {
                                            confirmButton: 'btn btn-danger'
                                        },
                                        confirmButtonText: 'تایید'
                                    });
                                }
                            });
                        }
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: 'خطا در دریافت کد مشتری',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        confirmButtonText: 'تایید'
                    });
                }
            });
        });

        // استایل دهی به SweetAlert2
        $('<style>')
            .text(`
                .rtl-swal { direction: rtl; }
                .border-radius-3 { border-radius: 0.5rem; }
                .swal2-title { font-size: 1.1rem !important; }
                .swal2-html-container { margin: 1rem 0 !important; }
                .swal2-popup { padding: 2rem; }
                .swal2-styled { font-size: 0.875rem !important; padding: 0.5rem 1.5rem !important; }
            `)
            .appendTo('head');
    });
}