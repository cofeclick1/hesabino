class PersonPicker {
    constructor(wrapper) {
        this.wrapper = wrapper;
        this.searchInput = wrapper.querySelector('.person-search-input');
        this.searchResults = wrapper.querySelector('.search-results');
        this.hiddenInput = wrapper.querySelector('.person-id');
        this.avatarImg = wrapper.closest('.payment-item').querySelector('.person-avatar');
        this.page = 1;
        this.loading = false;
        this.hasMore = true;
        this.lastQuery = '';

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
            this.page = 1;
            this.hasMore = true;
            this.lastQuery = query;
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

        // اسکرول بی‌نهایت
        this.searchResults.addEventListener('scroll', () => {
            if (this.loading || !this.hasMore) return;

            const {scrollTop, scrollHeight, clientHeight} = this.searchResults;
            if (scrollTop + clientHeight >= scrollHeight - 50) {
                this.page++;
                this.searchPeople(this.lastQuery, true);
            }
        });
    }

    async searchPeople(query, append = false) {
        if (this.loading) return;
        this.loading = true;

        if (!append) {
            this.showLoading();
        }

        try {
            const response = await fetch(`${BASE_PATH}/ajax/search_people.php?search=${encodeURIComponent(query)}&page=${this.page}`);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.message);
            }

            this.hasMore = data.has_more;
            this.renderResults(data.items, append);
            this.showResults();

        } catch (error) {
            console.error('Error searching people:', error);
            if (!append) {
                this.renderError('خطا در جستجو');
            }
        } finally {
            this.loading = false;
        }
    }

    renderResults(items, append = false) {
        if (!items || items.length === 0) {
            if (!append) {
                this.searchResults.innerHTML = `
                    <div class="p-2 text-center text-muted">
                        <i class="fas fa-search me-1"></i>
                        نتیجه‌ای یافت نشد
                    </div>`;
            }
            return;
        }

        const html = items.map(person => `
            <div class="search-result-item p-2 hover-bg" 
                 data-id="${person.id}" 
                 data-name="${person.name}"
                 data-mobile="${person.mobile || ''}"
                 data-type="${person.type || 'real'}">
                <div class="d-flex align-items-center">
                    <img src="${person.avatar}" class="rounded-circle me-2" 
                         style="width: 40px; height: 40px; object-fit: cover;"
                         onerror="this.src='${BASE_PATH}/assets/images/avatar.png'">
                    <div class="flex-grow-1">
                        <div class="fw-bold">
                            ${person.code ? `<span class="text-muted">${person.code}</span> - ` : ''}
                            ${person.name}
                        </div>
                        ${person.categories ? 
                            `<div class="small text-muted">${person.categories}</div>` : ''}
                        <div class="d-flex gap-2 mt-1">
                            ${person.mobile ? 
                                `<div class="small">
                                    <i class="fas fa-mobile-alt text-primary"></i>
                                    ${person.mobile}
                                </div>` : ''}
                            ${person.phone ? 
                                `<div class="small">
                                    <i class="fas fa-phone text-success"></i>
                                    ${person.phone}
                                </div>` : ''}
                            ${person.email ? 
                                `<div class="small">
                                    <i class="fas fa-envelope text-warning"></i>
                                    ${person.email}
                                </div>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        if (append) {
            this.searchResults.insertAdjacentHTML('beforeend', html);
        } else {
            this.searchResults.innerHTML = html;
        }

        // اضافه کردن event listener برای آیتم‌های جدید
        const newItems = Array.from(this.searchResults.querySelectorAll('.search-result-item:not([data-initialized])'));
        newItems.forEach(item => {
            item.dataset.initialized = 'true';
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
    }

    selectPerson(person) {
        // ذخیره id در hidden input
        this.hiddenInput.value = person.id;

        // تغییر input به حالت انتخاب شده
        const selectedHtml = `
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
        `;

        const selectedElement = new DOMParser().parseFromString(selectedHtml, 'text/html').body.firstChild;
        selectedElement.querySelector('.clear-person').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.clearSelection();
        });

        this.searchInput.replaceWith(selectedElement);

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
        
        this.wrapper.querySelector('.selected-person').replaceWith(newInput);
        this.searchInput = newInput;
        
        // پاک کردن مقادیر
        this.hiddenInput.value = '';
        this.avatarImg.src = BASE_PATH + '/assets/images/avatar.png';
        
        // اضافه کردن مجدد event listener
        this.setupEventListeners();
    }

    showLoading() {
        this.searchResults.innerHTML = `
            <div class="p-3 text-center">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">در حال جستجو...</span>
                </div>
            </div>
        `;
        this.showResults();
    }

    renderError(message) {
        this.searchResults.innerHTML = `
            <div class="p-2 text-center text-danger">
                <i class="fas fa-exclamation-circle me-1"></i>
                ${message}
            </div>
        `;
    }

    showResults() {
        this.searchResults.style.display = 'block';
    }

    hideResults() {
        this.searchResults.style.display = 'none';
    }
}

// Helper function
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