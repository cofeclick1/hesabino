$(document).ready(function() {
    // متغیرهای سراسری با مقداردهی اولیه
    let cartItems = localStorage.getItem('cartItems') ? JSON.parse(localStorage.getItem('cartItems')) : [];
    let lastSearchQuery = '';
    let searchTimeout;
    let suspendedInvoices = localStorage.getItem('suspendedInvoices') ? JSON.parse(localStorage.getItem('suspendedInvoices')) : [];
    let isLoading = false;

    // بروزرسانی ساعت
    function updateClock() {
        const now = new Date();
        const timeString = new Intl.DateTimeFormat('fa-IR', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).format(now);
        $('#current-time').text(timeString);
    }
    setInterval(updateClock, 1000);

    // نمایش loading
    function showLoading() {
        if (!isLoading) {
            isLoading = true;
            $('body').append('<div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75" style="z-index: 9999;">\
    <div class="spinner-border text-primary" role="status">\
        <span class="visually-hidden">Loading...</span>\
    </div>\
</div>');
        }
    }

    function hideLoading() {
        isLoading = false;
        $('#loading-overlay').remove();
    }

    // تنظیمات Select2
    $('#customer').select2({
        theme: 'bootstrap-5',
        placeholder: 'انتخاب مشتری...',
        language: {
            noResults: function() {
                return 'مشتری یافت نشد';
            }
        }
    });

    // جستجوی محصول
    $('#product-search').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length < 2) {
            $('#search-results').addClass('d-none').empty();
            return;
        }

        if (query === lastSearchQuery) return;
        lastSearchQuery = query;

        searchTimeout = setTimeout(() => {
            showLoading();
            $.ajax({
                url: 'ajax/search-products.php',
                type: 'GET',
                data: { query: query },
                success: function(response) {
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        showSearchResults(response.data);
                    } else {
                        $('#search-results')
                            .html('<div class="p-3 text-center text-muted">موردی یافت نشد</div>')
                            .removeClass('d-none');
                    }
                },
                error: function() {
                    showError('خطا در جستجوی محصولات');
                },
                complete: hideLoading
            });
        }, 300);
    });

    // نمایش نتایج جستجو
    function showSearchResults(products) {
        const html = products.map(product => `
            <div class="search-item" data-product-id="${product.id}" data-product='${JSON.stringify(product)}'>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${product.name}</div>
                        <small class="text-muted">
                            ${product.code ? `کد: ${product.code}` : ''} 
                        </small>
                    </div>
                    <div class="text-end">
                        <div class="text-success">${number_format(product.sale_price)} تومان</div>
                        <small class="text-muted">${product.quantity} عدد</small>
                    </div>
                </div>
            </div>
        `).join('');
        
        $('#search-results').html(html).removeClass('d-none');
    }

    // انتخاب محصول
    $(document).on('click', '.search-item, .product-card', function() {
        const $this = $(this);
        const productId = $this.data('product-id');
        
        if ($this.hasClass('search-item')) {
            try {
                const product = $this.data('product');
                $('#search-results').addClass('d-none');
                $('#product-search').val('');
                addToCart(product);
            } catch (e) {
                console.error('Error parsing product data:', e);
                showError('خطا در افزودن محصول به سبد خرید');
            }
        } else {
            showLoading();
            $.ajax({
                url: 'ajax/get-product.php',
                type: 'POST',
                data: { id: productId },
                success: function(response) {
                    if (response.success && response.product) {
                        addToCart(response.product);
                    } else {
                        showError(response.message || 'خطا در دریافت اطلاعات محصول');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {xhr: xhr.responseText, status, error});
                    showError('خطا در ارتباط با سرور');
                },
                complete: hideLoading
            });
        }
    });

    // نمایش خطا
    function showError(message) {
        const alert = $(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('#cart-items').before(alert);
        setTimeout(() => alert.alert('close'), 5000);
    }

    // نمایش پیام موفقیت
    function showToast(message) {
        const toast = $(`
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div class="toast" role="alert">
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            </div>
        `);
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast.find('.toast')[0], {
            delay: 2000
        });
        bsToast.show();
        toast.on('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    // افزودن به سبد خرید
    function addToCart(product) {
        const existingItem = cartItems.find(item => item.id === product.id);
        if (existingItem) {
            if (existingItem.quantity < parseInt(product.quantity)) {
                existingItem.quantity++;
                showToast('تعداد محصول افزایش یافت');
                updateCart();
            } else {
                showError('موجودی کافی نیست');
            }
        } else {
            if (parseInt(product.quantity) > 0) {
                cartItems.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.sale_price),
                    quantity: 1,
                    maxQuantity: parseInt(product.quantity),
                    code: product.code
                });
                showToast('محصول به سبد خرید اضافه شد');
                updateCart();
            } else {
                showError('این محصول ناموجود است');
            }
        }
        window.cartItems = cartItems; // Update global cartItems
        saveCartToStorage();
    }

        // حذف از سبد خرید
        $(document).on('click', '.remove-item', function(e) {
            e.stopPropagation();
            const index = $(this).data('index');
            
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "این محصول از سبد خرید حذف خواهد شد",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    cartItems.splice(index, 1);
                    updateCart();
                    saveCartToStorage();
                    Swal.fire(
                        'حذف شد!',
                        'محصول با موفقیت از سبد خرید حذف شد',
                        'success'
                    );
                }
            });
        });

    // بروزرسانی سبد خرید
    function updateCart() {
        if (cartItems.length === 0) {
            $('#cart-items').html('<div class="text-center text-muted p-3">سبد خرید خالی است</div>');
        } else {
            const html = cartItems.map((item, index) => `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-bold">${item.name}</div>
                            <small class="text-muted">${item.code || ''}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="input-group" style="width: 120px">
                            <button class="btn btn-outline-secondary decrease-quantity" data-index="${index}" type="button">-</button>
                            <input type="number" class="form-control text-center item-quantity" value="${item.quantity}" 
                                   min="1" max="${item.maxQuantity}" data-index="${index}">
                            <button class="btn btn-outline-secondary increase-quantity" data-index="${index}" type="button">+</button>
                        </div>
                        <div class="text-end">
                            <div class="text-success">${number_format(item.price * item.quantity)} تومان</div>
                            <small class="text-muted">${number_format(item.price)} × ${item.quantity}</small>
                        </div>
                    </div>
                </div>
            `).join('');
            $('#cart-items').html(html);
        }
        updateTotals();
    }

    // بروزرسانی مبالغ
    function updateTotals() {
        const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = Math.round(subtotal * 0.09);
        const discountType = $('#discount-type').val();
        const discountValue = parseFloat($('#discount-amount').val()) || 0;
        
        let discount = 0;
        if (discountType === 'amount') {
            discount = discountValue;
        } else {
            discount = Math.round(subtotal * (discountValue / 100));
        }

        const total = subtotal + tax - discount;

        $('#subtotal').text(number_format(subtotal) + ' تومان');
        $('#tax').text(number_format(tax) + ' تومان');
        $('#total').text(number_format(total) + ' تومان');

        $('.payment-amount').first().val(total);
    }

    // فرمت‌بندی اعداد
    function number_format(number) {
        return new Intl.NumberFormat('fa-IR').format(number);
    }

    // ذخیره سبد خرید در localStorage
    function saveCartToStorage() {
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
    }

    // فیلتر محصولات بر اساس دسته‌بندی
    $('.category-tab').click(function() {
        const $this = $(this);
        const categoryId = $this.data('category');
        
        $('.category-tab').removeClass('active');
        $this.addClass('active');
        
        if (!categoryId) {
            $('.product-card').fadeIn(200);
        } else {
            $('.product-card').each(function() {
                const $card = $(this);
                const productCategory = $card.data('category');
                if (productCategory === categoryId) {
                    $card.fadeIn(200);
                } else {
                    $card.fadeOut(200);
                }
            });
        }

        setTimeout(() => {
            const visibleProducts = $('.product-card:visible').length;
            if (visibleProducts === 0) {
                if ($('.no-products-message').length === 0) {
                    $('.product-grid').append(`
                        <div class="no-products-message text-center text-muted p-3">
                            محصولی در این دسته‌بندی وجود ندارد
                        </div>
                    `);
                }
            } else {
                $('.no-products-message').remove();
            }
        }, 250);
    });

    // تغییر در تخفیف
    $('#discount-amount, #discount-type').on('change input', function() {
        updateTotals();
    });

    // بارگذاری اولیه
    updateCart();
});
// افزودن روش پرداخت جدید
$('#add-payment').click(function () {
    const currentPayments = $('#payment-methods').children().length;
    
    if (currentPayments >= 3) {
        alert('حداکثر سه روش پرداخت می‌توانید اضافه کنید');
        return;
    }

    const paymentId = Date.now();
    const paymentHtml = `
        <div class="payment-row mb-2" id="payment-${paymentId}">
            <select class="form-select mb-2 payment-method">
                <option value="cash">نقدی</option>
                <option value="card">کارت بانکی</option>
                <option value="cheque">چک</option>
                <option value="credit">اعتباری</option>
            </select>
            <div class="input-group">
                <input type="number" class="form-control payment-amount" 
                       placeholder="مبلغ" min="0">
                <button type="button" class="btn btn-outline-danger remove-payment">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    $('#payment-methods').append(paymentHtml);

    // اضافه کردن event listeners
    $(`#payment-${paymentId} .payment-method`).on('change', function() {
        if (typeof window.updateTotals === 'function') {
            window.updateTotals();
        }
    });

    $(`#payment-${paymentId} .payment-amount`).on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (typeof window.updateTotals === 'function') {
            window.updateTotals();
        }
    });

    $(`#payment-${paymentId} .remove-payment`).click(function() {
        if ($('#payment-methods').children().length > 1) {
            $(this).closest('.payment-row').remove();
            if (typeof window.updateTotals === 'function') {
                window.updateTotals();
            }
        }
    });

    if (typeof window.updateTotals === 'function') {
        window.updateTotals();
    }
});
// تعریف متغیرهای عمومی بدون تغییر
window.cartItems = [];
window.isProcessing = false;

$(document).ready(function() {
    // بازیابی cartItems از localStorage بدون تغییر
    window.cartItems = localStorage.getItem('cartItems') ? JSON.parse(localStorage.getItem('cartItems')) : [];

    // ثبت و چاپ فاکتور - فقط تغییر در آدرس درخواست
    $('#save-invoice').click(function () {
        if (window.isProcessing) {
            return;
        }

        if (!cartItems.length) { // Use local cartItems instead of window.cartItems
            alert('لطفاً حداقل یک محصول به سبد خرید اضافه کنید');
            return;
        }

        // کد بررسی پرداخت‌ها بدون تغییر
        const payments = [];
        let totalPayments = 0;

        $('.payment-method').each(function (index) {
            const amount = parseFloat($('.payment-amount').eq(index).val()) || 0;
            if (amount > 0) {
                payments.push({
                    method: $(this).val(),
                    amount: amount
                });
                totalPayments += amount;
            }
        });

        const invoiceTotal = parseFloat($('#total').text().replace(/[^\d.-]/g, ''));
        if (Math.abs(totalPayments - invoiceTotal) > 1) {
            alert('مجموع مبالغ پرداختی باید با مبلغ کل فاکتور برابر باشد');
            return;
        }

        window.isProcessing = true;
        $('#save-invoice').prop('disabled', true);

        const invoiceData = {
            customer_id: $('#customer').val() || '0',
            items: cartItems, // Use local cartItems instead of window.cartItems
            payments: payments,
            discount_type: $('#discount-type').val(),
            discount_value: parseFloat($('#discount-amount').val()) || 0
        };

        // Corrected AJAX Request - Stringify data and use absolute URL
        $.ajax({
            url: 'ajax/save-invoice.php', // Keep relative URL
            method: 'POST',
            data: JSON.stringify(invoiceData), // Stringify the data
            contentType: 'application/json', // Correct Content-Type
            dataType: 'json',
            success: function (response) {
                window.isProcessing = false;
                $('#save-invoice').prop('disabled', false);
                
                if (response && response.success) {
                    window.lastInvoiceId = response.invoice_id;
                    cartItems = []; // Use local cartItems instead of window.cartItems
                    localStorage.removeItem('cartItems');
                    updateCart();
                    $('#printSettingsModal').modal('show');
                } else {
                    alert(response?.message || 'خطا در ثبت فاکتور');
                }
            },
            error: function(xhr, status, error) {
                window.isProcessing = false;
                $('#save-invoice').prop('disabled', false);

                let errorMessage = 'خطا در ارتباط با سرور';
                
                if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        errorMessage = 'خطا در پردازش درخواست';
                        console.error('Server Error:', xhr.responseText);
                    }
                }

                alert(errorMessage + '. لطفا مجددا تلاش کنید.');
            }
        });
    });
});