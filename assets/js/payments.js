// اضافه کردن بخش زیر به ابتدای فایل payments.js

class PersonPicker {
    constructor(wrapper) {
        this.wrapper = wrapper;
        this.searchInput = wrapper.querySelector('.person-search-input');
        this.searchResults = wrapper.querySelector('.search-results');
        this.hiddenInput = wrapper.querySelector('.person-id');
        this.avatarImg = wrapper.closest('.payment-item').querySelector('.person-avatar');
        
        this.setupEventListeners();
    }

    setupEventListeners() {
        // جستجو با تایپ
        this.searchInput.addEventListener('input', debounce(() => {
            const query = this.searchInput.value.trim();
            if (query.length < 2) {
                this.hideResults();
                return;
            }
            this.searchPeople(query);
        }, 300));

        // مخفی کردن نتایج با کلیک بیرون
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.hideResults();
            }
        });

        // نمایش نتایج با کلیک روی input 
        this.searchInput.addEventListener('click', () => {
            if (this.searchInput.value.trim().length >= 2) {
                this.searchPeople(this.searchInput.value.trim());
            }
        });
    }

    async searchPeople(query) {
        this.showLoading();

        try {
            const response = await fetch(`${BASE_PATH}/ajax/search_people.php?search=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.message);
            }

            this.renderResults(data.items);

        } catch (error) {
            console.error('Error searching people:', error);
            this.renderError('خطا در جستجو');
        }
    }

    renderResults(items) {
        if (!items || items.length === 0) {
            this.searchResults.innerHTML = `
                <div class="p-2 text-center text-muted">
                    <i class="fas fa-search me-1"></i>
                    نتیجه‌ای یافت نشد
                </div>`;
            this.showResults();
            return;
        }

        this.searchResults.innerHTML = items.map(person => `
            <div class="search-result-item" 
                 data-id="${person.id}" 
                 data-name="${person.name}"
                 data-mobile="${person.mobile || ''}"
                 data-type="${person.type || 'real'}">
                <div class="d-flex align-items-center">
                    <img src="${person.avatar}" class="me-2" 
                         onerror="this.src='${BASE_PATH}/assets/images/avatar.png'">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${person.name}</div>
                        <div class="person-details">
                            ${person.mobile ? 
                                `<span><i class="fas fa-mobile-alt text-primary me-1"></i>${person.mobile}</span>` : ''}
                            ${person.phone ? 
                                `<span><i class="fas fa-phone text-success me-1"></i>${person.phone}</span>` : ''}
                            ${person.email ? 
                                `<span><i class="fas fa-envelope text-warning me-1"></i>${person.email}</span>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        // اضافه کردن event listeners
        this.searchResults.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectPerson({
                    id: item.dataset.id,
                    name: item.dataset.name,
                    mobile: item.dataset.mobile,
                    type: item.dataset.type,
                    avatar: item.querySelector('img').src
                });
            });
        });

        this.showResults();
    }

    selectPerson(person) {
        // ذخیره id در hidden input
        this.hiddenInput.value = person.id;

        // تغییر input به حالت انتخاب شده
        const selectedHtml = `
            <div class="selected-person">
                <img src="${person.avatar}">
                <div class="flex-grow-1">
                    <div class="fw-bold">${person.name}</div>
                    ${person.mobile ? 
                        `<small class="text-muted">${person.mobile}</small>` : ''}
                </div>
                <button type="button" class="btn-close clear-person" 
                        aria-label="Clear"></button>
            </div>
        `;

        const selectedElement = new DOMParser().parseFromString(selectedHtml, 'text/html').body.firstChild;
        selectedElement.querySelector('.clear-person').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.clearSelection();
        });

        this.searchInput.replaceWith(selectedElement);
        this.searchInput = selectedElement;

        // آپدیت آواتار اصلی
        this.avatarImg.src = person.avatar;
        
        // مخفی کردن نتایج
        this.hideResults();
    }

    clearSelection() {
        // بازگشت به حالت جستجو
        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.className = 'form-control person-search-input';
        newInput.placeholder = 'نام، موبایل یا کد ملی را وارد کنید...';
        
        this.searchInput.replaceWith(newInput);
        this.searchInput = newInput;
        
        // پاک کردن مقادیر
        this.hiddenInput.value = '';
        this.avatarImg.src = BASE_PATH + '/assets/images/avatar.png';
        
        // اضافه کردن مجدد event listener
        this.setupEventListeners();
    }

    showLoading() {
        this.searchResults.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner-border spinner-border-sm"></div>
                در حال جستجو...
            </div>
        `;
        this.showResults();
    }

    renderError(message) {
        this.searchResults.innerHTML = `
            <div class="search-error">
                <i class="fas fa-exclamation-circle me-1"></i>
                ${message}
            </div>
        `;
        this.showResults();
    }

    showResults() {
        this.searchResults.classList.add('show');
    }

    hideResults() {
        this.searchResults.classList.remove('show');
    }
}





// اضافه کردن helper function
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
// متغیرهای عمومی
let totalAmount = 0;
let paidAmount = 0;
let currentPaymentId = 1;

// Document Ready
$(document).ready(function() {

    // راه‌اندازی صفحه
    function initializePage() {
        initializeDatePickers();
        setupValidation();
        setupEventListeners();
        addPaymentItem();
        updateCurrencySymbols();
    }

    // افزودن آیتم جدید
    function addPaymentItem() {
        const template = document.querySelector('#paymentItemTemplate');
        const clone = document.importNode(template.content, true);
        
        // تنظیم شماره آیتم
        const itemId = currentPaymentId++;
        $(clone).find('.payment-item').attr('data-id', itemId);
        
        // اضافه کردن به DOM
        $('#paymentItems').append(clone);

        // راه‌اندازی جستجوی شخص
        new PersonPicker(clone.querySelector('.search-wrapper'));
        // فرمت‌بندی مبلغ
        setupAmountInput(clone.querySelector('.amount-input'));

        // بروزرسانی نماد ارز
        updateCurrencySymbols();
    }

    // [بقیه کدهای فایل بدون تغییر]
    
    // شروع صفحه
    initializePage();
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

