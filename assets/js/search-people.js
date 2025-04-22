class ContactPicker {
    constructor(options = {}) {
        this.container = null;
        this.options = {
            onSelect: () => {},
            selectedId: null,
            zIndex: 1050,
            width: null,
            ...options
        };
        
        this.currentPage = 1;
        this.hasMore = true;
        this.isLoading = false;
        this.selectedItem = null;
        
        this.init();
    }
    
    init() {
        // ایجاد modal
        this.createModal();
        
        // اضافه کردن event listener ها
        this.setupEventListeners();
        
        // لود اولین صفحه
        this.loadContacts();
    }
    
    createModal() {
        const modal = document.createElement('div');
        modal.className = 'dx-overlay-wrapper dx-dropdowneditor-overlay dx-popup-wrapper dx-dropdownlist-popup-wrapper dx-selectbox-popup-wrapper';
        modal.style.zIndex = this.options.zIndex;
        
        let width = this.options.width || '300px';
        if (window.innerWidth <= 576) {
            width = '100%';
        }
        
        modal.innerHTML = `
            <div class="dx-overlay-content dx-rtl dx-popup-normal dx-resizable" 
                 style="max-height: 407.5px; width: ${width}; visibility: visible;">
                <div class="dx-popup-content">
                    <div class="dx-scrollable dx-scrollview dx-rtl dx-visibility-change-handler dx-scrollable-vertical dx-scrollable-simulated dx-list dx-widget dx-collection" role="listbox">
                        <div class="dx-scrollable-wrapper">
                            <div class="dx-scrollable-container">
                                <div class="dx-scrollable-content">
                                    <!-- Pull to refresh -->
                                    <div class="dx-scrollview-top-pocket">
                                        <div class="dx-scrollview-pull-down dx-state-invisible">
                                            <div class="dx-scrollview-pull-down-text">
                                                <div>برای بازیابی به پایین بکشید...</div>
                                                <div>برای بازیابی رها کنید...</div>
                                                <div>درحال بازیابی...</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Search box -->
                                    <div class="search-box p-2">
                                        <input type="text" class="form-control" placeholder="جستجو...">
                                    </div>
                                    
                                    <!-- Contact list -->
                                    <div class="contacts-list"></div>
                                    
                                    <!-- Loading -->
                                    <div class="loading-indicator text-center p-3 d-none">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">در حال بارگذاری...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.container = modal;
    }
    
    setupEventListeners() {
        // جستجو
        const searchInput = this.container.querySelector('.search-box input');
        searchInput.addEventListener('input', debounce(() => {
            this.currentPage = 1;
            this.hasMore = true;
            this.loadContacts(true);
        }, 300));
        
        // اسکرول
        const scrollContainer = this.container.querySelector('.dx-scrollable-container');
        scrollContainer.addEventListener('scroll', () => {
            if (this.hasMore && !this.isLoading) {
                const {scrollTop, scrollHeight, clientHeight} = scrollContainer;
                if (scrollTop + clientHeight >= scrollHeight - 50) {
                    this.currentPage++;
                    this.loadContacts();
                }
            }
        });
        
        // کلیک روی آیتم‌ها
        const contactsList = this.container.querySelector('.contacts-list');
        contactsList.addEventListener('click', (e) => {
            const item = e.target.closest('.contact-item');
            if (item) {
                const data = {
                    id: item.dataset.id,
                    name: item.dataset.name,
                    code: item.dataset.code,
                    avatar: item.querySelector('img').src,
                    mobile: item.dataset.mobile,
                    phone: item.dataset.phone,
                    email: item.dataset.email,
                    categories: item.dataset.categories
                };
                
                this.selectContact(data);
            }
        });
    }
    
    async loadContacts(clear = false) {
        if (this.isLoading || !this.hasMore) return;
        
        this.isLoading = true;
        this.toggleLoading(true);
        
        try {
            const searchValue = this.container.querySelector('.search-box input').value;
            const response = await fetch(`${BASE_PATH}/ajax/search_people.php?page=${this.currentPage}&search=${searchValue}`);
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.message);
            }
            
            this.hasMore = data.has_more;
            
            const contactsList = this.container.querySelector('.contacts-list');
            if (clear) {
                contactsList.innerHTML = '';
            }
            
            this.renderContacts(data.items);
            
        } catch (error) {
            console.error('Error loading contacts:', error);
            // نمایش خطا
        } finally {
            this.isLoading = false;
            this.toggleLoading(false);
        }
    }
    
    renderContacts(contacts) {
        const contactsList = this.container.querySelector('.contacts-list');
        
        contacts.forEach(contact => {
            const item = document.createElement('div');
            item.className = 'contact-item p-2 hover-bg';
            item.dataset.id = contact.id;
            item.dataset.name = contact.name;
            item.dataset.code = contact.code;
            item.dataset.mobile = contact.mobile;
            item.dataset.phone = contact.phone;
            item.dataset.email = contact.email;
            item.dataset.categories = contact.categories;
            
            item.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${contact.avatar}" class="rounded-circle me-2" 
                         style="width: 40px; height: 40px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <div>
                            <span class="text-muted">${contact.code}</span> - 
                            <b>${contact.name}</b>
                        </div>
                        ${contact.categories ? 
                            `<span class="cat-path">${contact.categories}</span>` : ''}
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            ${contact.mobile ? 
                                `<div><span class="icon icon-mobile cl-orange"></span> ${contact.mobile}</div>` : ''}
                            ${contact.phone ? 
                                `<div><span class="icon icon-phone cl-light-blue"></span> ${contact.phone}</div>` : ''}
                            ${contact.email ? 
                                `<div><span class="icon icon-email cl-green"></span> ${contact.email}</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            if (contact.id === this.options.selectedId) {
                item.classList.add('selected');
                this.selectedItem = item;
            }
            
            contactsList.appendChild(item);
        });
    }
    
    selectContact(data) {
        if (this.selectedItem) {
            this.selectedItem.classList.remove('selected');
        }
        
        const item = this.container.querySelector(`.contact-item[data-id="${data.id}"]`);
        if (item) {
            item.classList.add('selected');
            this.selectedItem = item;
        }
        
        this.options.onSelect(data);
    }
    
    toggleLoading(show) {
        const loader = this.container.querySelector('.loading-indicator');
        loader.classList.toggle('d-none', !show);
    }
    
    show() {
        this.container.style.display = 'block';
    }
    
    hide() {
        this.container.style.display = 'none';
    }
    
    destroy() {
        if (this.container) {
            this.container.remove();
        }
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

// اضافه کردن به window برای استفاده در کد‌های دیگر
window.ContactPicker = ContactPicker;