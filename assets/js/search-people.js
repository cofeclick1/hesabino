// جستجوی افراد با استفاده از API
class PersonSearch {
    constructor(wrapper) {
        this.wrapper = wrapper;
        this.searchInput = wrapper.querySelector('.search-input');
        this.resultsContainer = wrapper.querySelector('.search-results');
        this.hiddenInput = wrapper.querySelector('input[type="hidden"]');
        this.avatarContainer = wrapper.closest('.payment-item').querySelector('.avatar-wrapper img');
        this.searchTimeout = null;

        this.setupEventListeners();
    }

    setupEventListeners() {
        // رویداد تایپ در فیلد جستجو
        this.searchInput.addEventListener('input', () => this.handleSearch());

        // رویداد فوکوس
        this.searchInput.addEventListener('focus', () => {
            const query = this.searchInput.value.trim();
            if (query.length >= 2) {
                this.showResults();
            }
        });

        // رویداد کلیک خارج از باکس جستجو
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.hideResults();
            }
        });

        // رویداد انتخاب نتیجه
        this.resultsContainer.addEventListener('click', (e) => {
            const item = e.target.closest('.search-result-item');
            if (item) {
                this.selectPerson(item);
            }
        });
    }

    handleSearch() {
        const query = this.searchInput.value.trim();
        
        clearTimeout(this.searchTimeout);
        
        if (query.length < 2) {
            this.hideResults();
            return;
        }
        
        // تاخیر برای جلوگیری از درخواست‌های مکرر
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    }

    async performSearch(query) {
        try {
            this.showLoading();
            
            const response = await fetch(`${BASE_PATH}/api/search-people.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.renderResults(data);
        } catch (error) {
            console.error('Error searching people:', error);
            this.showError('خطا در جستجو، لطفا مجدداً تلاش کنید');
        } finally {
            this.hideLoading();
        }
    }

    renderResults(data) {
        if (!Array.isArray(data.items) || data.items.length === 0) {
            this.showNoResults();
            return;
        }

        const html = data.items.map(person => this.createResultItem(person)).join('');
        this.resultsContainer.innerHTML = html;
        this.showResults();
    }

    createResultItem(person) {
        const avatar = person.avatar_path || `${BASE_PATH}/assets/images/avatar.png`;
        const mobile = person.mobile ? `<i class="fas fa-phone"></i> ${person.mobile}` : '';
        const nationalCode = person.national_code ? 
            `<span class="mx-2">|</span><i class="fas fa-id-card"></i> ${person.national_code}` : '';
        
        return `
            <div class="search-result-item" 
                 data-id="${person.id}" 
                 data-name="${person.text}"
                 data-mobile="${person.mobile || ''}"
                 data-avatar="${avatar}">
                <div class="d-flex align-items-center">
                    <img src="${avatar}" alt="" class="result-avatar">
                    <div class="result-info">
                        <div class="result-name">${person.text}</div>
                        <div class="result-details">
                            ${mobile}
                            ${nationalCode}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    selectPerson(item) {
        const { id, name, mobile, avatar } = item.dataset;
        
        // ذخیره مقادیر
        this.hiddenInput.value = id;
        
        // نمایش اطلاعات انتخاب شده
        this.wrapper.innerHTML = `
            <div class="selected-person">
                <img src="${avatar}" alt="">
                <div class="selected-person-info">
                    <div class="selected-person-name">${name}</div>
                    ${mobile ? `<div class="selected-person-details">${mobile}</div>` : ''}
                </div>
                <button type="button" class="btn-close clear-selection" aria-label="Clear"></button>
            </div>
        `;

        // به‌روزرسانی آواتار در کارت پرداخت
        if (this.avatarContainer) {
            this.avatarContainer.src = avatar;
        }

        // اضافه کردن رویداد حذف
        const clearBtn = this.wrapper.querySelector('.clear-selection');
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.resetSelection();
            });
        }

        this.hideResults();
    }

    resetSelection() {
        // بازگرداندن به حالت اولیه
        this.wrapper.innerHTML = `
            <input type="text" class="search-input form-control" placeholder="نام، موبایل یا کد ملی را وارد کنید...">
            <div class="search-results"></div>
        `;
        
        // پاک کردن مقدار ذخیره شده
        this.hiddenInput.value = '';
        
        // بازنشانی آواتار
        if (this.avatarContainer) {
            this.avatarContainer.src = `${BASE_PATH}/assets/images/avatar.png`;
        }

        // بازنشانی متغیرهای کلاس
        this.searchInput = this.wrapper.querySelector('.search-input');
        this.resultsContainer = this.wrapper.querySelector('.search-results');
        
        // راه‌اندازی مجدد رویدادها
        this.setupEventListeners();
    }

    showResults() {
        this.resultsContainer.style.display = 'block';
    }

    hideResults() {
        this.resultsContainer.style.display = 'none';
    }

    showLoading() {
        this.resultsContainer.innerHTML = `
            <div class="search-loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">در حال جستجو...</span>
                </div>
            </div>
        `;
        this.showResults();
    }

    hideLoading() {
        const loading = this.resultsContainer.querySelector('.search-loading');
        if (loading) {
            loading.remove();
        }
    }

    showError(message) {
        this.resultsContainer.innerHTML = `
            <div class="search-error">
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
            </div>
        `;
        this.showResults();
    }

    showNoResults() {
        this.resultsContainer.innerHTML = `
            <div class="no-results">
                <i class="fas fa-search me-2"></i>
                موردی یافت نشد
            </div>
        `;
        this.showResults();
    }
}

// راه‌اندازی جستجو برای همه المان‌های موجود
document.addEventListener('DOMContentLoaded', () => {
    // راه‌اندازی برای المان‌های موجود
    document.querySelectorAll('.search-wrapper').forEach(wrapper => {
        new PersonSearch(wrapper);
    });

    // راه‌اندازی برای المان‌های جدید (مثلاً در آیتم‌های پرداخت جدید)
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === 1) { // المان DOM
                    const searchWrappers = node.querySelectorAll('.search-wrapper');
                    searchWrappers.forEach(wrapper => new PersonSearch(wrapper));
                }
            });
        });
    });

    observer.observe(document.getElementById('paymentItems'), {
        childList: true,
        subtree: true
    });
});