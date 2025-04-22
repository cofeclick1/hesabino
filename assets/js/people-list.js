class PeopleList {
    constructor(options) {
        this.container = options.container;
        this.loadingElement = options.loadingElement;
        this.searchInput = options.searchInput;
        this.categorySelect = options.categorySelect;
        
        this.page = 1;
        this.perPage = 20;
        this.loading = false;
        this.hasMore = true;
        this.searchTimeout = null;
        
        this.init();
    }
    
    init() {
        // اضافه کردن اسکرول بی‌نهایت
        this.container.closest('.scrollable-container').addEventListener('scroll', () => {
            this.handleScroll();
        });
        
        // جستجو
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.resetAndLoad();
                }, 300);
            });
        }
        
        // فیلتر دسته‌بندی
        if (this.categorySelect) {
            this.categorySelect.addEventListener('change', () => {
                this.resetAndLoad();
            });
        }
        
        // لود اولیه
        this.loadPeople();
    }
    
    handleScroll() {
        const container = this.container.closest('.scrollable-container');
        const scrollPosition = container.scrollTop + container.clientHeight;
        const scrollHeight = container.scrollHeight;
        
        // اگر به انتهای اسکرول رسیدیم و در حال لود نیستیم و آیتم بیشتری هست
        if (scrollPosition > scrollHeight - 100 && !this.loading && this.hasMore) {
            this.loadPeople();
        }
    }
    
    resetAndLoad() {
        this.page = 1;
        this.hasMore = true;
        this.container.innerHTML = '';
        this.loadPeople();
    }
    
    async loadPeople() {
        if (this.loading || !this.hasMore) return;
        
        this.loading = true;
        this.loadingElement.classList.remove('d-none');
        
        try {
            const params = new URLSearchParams({
                page: this.page,
                per_page: this.perPage,
                search: this.searchInput ? this.searchInput.value : '',
                category: this.categorySelect ? this.categorySelect.value : ''
            });
            
            const response = await fetch(`${BASE_PATH}/ajax/get_people.php?${params}`);
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.message);
            }
            
            this.hasMore = data.has_more;
            this.page++;
            
            // افزودن آیتم‌های جدید
            data.items.forEach(person => {
                this.container.appendChild(this.createPersonElement(person));
            });
            
        } catch (error) {
            console.error('Error loading people:', error);
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: 'خطا در دریافت اطلاعات',
                confirmButtonText: 'تایید'
            });
        } finally {
            this.loading = false;
            this.loadingElement.classList.add('d-none');
        }
    }
    
    createPersonElement(person) {
        const element = document.createElement('div');
        element.className = 'person-item';
        element.innerHTML = `
            <div class="d-flex align-items-center p-3 border-bottom hover-bg">
                <div class="flex-shrink-0">
                    <img src="${person.avatar}" class="rounded-circle" 
                         style="width: 48px; height: 48px; object-fit: cover;"
                         alt="${person.name}"
                         onerror="this.src='${BASE_PATH}/assets/images/avatar.png'">
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted">${person.code}</span> - 
                            <strong>${person.name}</strong>
                            ${person.company ? `<br><small>(${person.company})</small>` : ''}
                        </div>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectPerson(${person.id})">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-1">
                        ${person.categories ? `<small class="text-muted">${person.categories}</small>` : ''}
                    </div>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        ${person.mobile ? `
                            <div>
                                <i class="fas fa-mobile-alt text-primary"></i>
                                <small>${person.mobile}</small>
                            </div>
                        ` : ''}
                        ${person.phone ? `
                            <div>
                                <i class="fas fa-phone text-success"></i>
                                <small>${person.phone}</small>
                            </div>
                        ` : ''}
                        ${person.email ? `
                            <div>
                                <i class="fas fa-envelope text-warning"></i>
                                <small>${person.email}</small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        return element;
    }
}

// تابع انتخاب شخص
function selectPerson(personId) {
    // اینجا کد مربوط به انتخاب شخص رو بنویسید
    console.log('Selected person:', personId);
}

// راه‌اندازی در زمان لود صفحه
document.addEventListener('DOMContentLoaded', function() {
    const peopleList = new PeopleList({
        container: document.querySelector('#peopleList'),
        loadingElement: document.querySelector('#loadingIndicator'),
        searchInput: document.querySelector('#searchInput'),
        categorySelect: document.querySelector('#categorySelect')
    });
});