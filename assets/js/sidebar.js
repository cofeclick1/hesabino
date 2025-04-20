document.addEventListener('DOMContentLoaded', function() {
    // متغیرهای اصلی با بررسی وجود المان‌ها
    const sidebar = document.getElementById('mainSidebar');
    const menuItems = document.querySelectorAll('.menu-item.has-submenu');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const allMenuLinks = document.querySelectorAll('.menu-link');
    const allSubmenuLinks = document.querySelectorAll('.submenu a');

    if (!sidebar || !sidebarToggle) {
        console.error('المان‌های ضروری سایدبار یافت نشد.');
        return;
    }

    // مدیریت کلیک روی آیتم‌های منو
    menuItems.forEach(item => {
        const link = item.querySelector('.menu-link');
        const submenu = item.querySelector('.submenu');

        if (!link || !submenu) return;

        link.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            // بستن سایر منوهای باز
            menuItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                    const otherSubmenu = otherItem.querySelector('.submenu');
                    if (otherSubmenu) {
                        otherSubmenu.style.maxHeight = null;
                    }
                }
            });

            // باز/بسته کردن منوی فعلی
            const isActive = item.classList.contains('active');
            item.classList.toggle('active');
            
            if (!isActive) {
                submenu.style.maxHeight = submenu.scrollHeight + "px";
            } else {
                submenu.style.maxHeight = null;
            }
        });
    });

    // تشخیص منوی فعال بر اساس URL فعلی
    function setActiveMenu() {
        const currentPath = window.location.pathname;
        
        // حذف کلاس active از تمام لینک‌ها
        allMenuLinks.forEach(link => link.classList.remove('active'));
        allSubmenuLinks.forEach(link => link.classList.remove('active'));

        // بررسی و فعال‌سازی منوی مربوطه
        allSubmenuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href)) {
                link.classList.add('active');
                const parentItem = link.closest('.menu-item');
                if (parentItem && parentItem.classList.contains('has-submenu')) {
                    parentItem.classList.add('active');
                    const submenu = parentItem.querySelector('.submenu');
                    if (submenu) {
                        submenu.style.maxHeight = submenu.scrollHeight + "px";
                    }
                }
            }
        });
    }

    // مدیریت نمایش/مخفی‌سازی سایدبار در موبایل
    sidebarToggle.addEventListener('click', (e) => {
        e.preventDefault();
        sidebar.classList.toggle('mobile-visible');
    });

    // بستن سایدبار با کلیک خارج از آن در موبایل
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target) && 
            sidebar.classList.contains('mobile-visible')) {
            sidebar.classList.remove('mobile-visible');
        }
    });

    // مدیریت تغییر اندازه پنجره
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-visible');
            }
        }, 250);
    });

    // فراخوانی تابع تشخیص منوی فعال
    setActiveMenu();

    // مدیریت تغییر URL با History API
    window.addEventListener('popstate', setActiveMenu);
});