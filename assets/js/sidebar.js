$(document).ready(function() {
    // تعریف متغیرهای کلی
    const $sidebar = $('.sidebar');
    const $mainContent = $('.main-content');
    let currentOpenSubmenu = null;

    // پاکسازی تمام رویدادهای قبلی
    $('.menu-item.has-submenu > .menu-link').off();
    $('.submenu a').off();
    $(document).off('click', '.sidebar-overlay');
    $('.sidebar-toggle').off();
    $('.mobile-menu-toggle').off();

    // بررسی وضعیت قبلی سایدبار
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $sidebar.addClass('collapsed');
        $mainContent.addClass('expanded');
    }

    // باز/بسته کردن سایدبار
    $('.sidebar-toggle').on('click', function(e) {
        e.preventDefault();
        $sidebar.toggleClass('collapsed');
        $mainContent.toggleClass('expanded');
        localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed'));
    });

    // مدیریت منو در موبایل
    $('.mobile-menu-toggle').on('click', function(e) {
        e.preventDefault();
        $sidebar.addClass('show');
        if (!$('.sidebar-overlay').length) {
            $('<div class="sidebar-overlay"></div>').insertAfter($sidebar);
        }
    });

    // بستن منو با کلیک روی overlay
    $(document).on('click', '.sidebar-overlay', function() {
        $sidebar.removeClass('show');
        $(this).remove();
    });

    // تابع بستن تمام زیرمنوها
    function closeAllSubmenus() {
        $('.menu-item.has-submenu.open').removeClass('open').find('.submenu').slideUp(300);
        currentOpenSubmenu = null;
    }

    // تابع باز کردن یک زیرمنو
    function openSubmenu($menuItem) {
        if (currentOpenSubmenu && !currentOpenSubmenu.is($menuItem)) {
            currentOpenSubmenu.removeClass('open').find('.submenu').slideUp(300);
        }
        
        $menuItem.addClass('open').find('.submenu').slideDown(300);
        currentOpenSubmenu = $menuItem;
    }

    // مدیریت کلیک روی منوها
    $('.menu-item.has-submenu > .menu-link').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $menuItem = $(this).parent();

        // اگر سایدبار جمع شده است، آن را باز کنیم
        if ($sidebar.hasClass('collapsed')) {
            $sidebar.removeClass('collapsed');
            $mainContent.removeClass('expanded');
            localStorage.setItem('sidebarCollapsed', false);
            
            // کمی صبر کنیم تا سایدبار باز شود
            setTimeout(() => {
                if ($menuItem.hasClass('open')) {
                    closeAllSubmenus();
                } else {
                    openSubmenu($menuItem);
                }
            }, 300);
            return;
        }

        // باز/بسته کردن زیرمنو
        if ($menuItem.hasClass('open')) {
            closeAllSubmenus();
        } else {
            openSubmenu($menuItem);
        }
    });

    // جلوگیری از تداخل رویدادها در زیرمنوها
    $('.submenu a').on('click', function(e) {
        e.stopPropagation();
    });

    // تنظیم منوی فعال و باز کردن والد آن
    function setActiveMenu() {
        const currentPath = window.location.pathname;

        // حذف کلاس active از همه لینک‌ها
        $('.menu-link, .submenu a').removeClass('active');

        // پیدا کردن و فعال کردن لینک جاری
        $('.menu-link, .submenu a').each(function() {
            const linkPath = $(this).attr('href');
            if (linkPath && (currentPath === linkPath || currentPath.startsWith(linkPath))) {
                $(this).addClass('active');

                // اگر لینک در زیرمنو است، منوی والد را باز کنیم
                const $parentItem = $(this).closest('.menu-item.has-submenu');
                if ($parentItem.length) {
                    openSubmenu($parentItem);
                }
                return false;
            }
        });

        // اگر هیچ منویی فعال نشد و در داشبورد هستیم
        if (currentPath === '/dashboard.php' && !$('.menu-link.active, .submenu a.active').length) {
            $('[href="/dashboard.php"]').addClass('active');
        }
    }

    // تنظیم ارتفاع اسکرول سایدبار
    function adjustSidebarHeight() {
        const windowHeight = window.innerHeight;
        const headerHeight = $('.sidebar-header').outerHeight() || 0;
        const profileHeight = $('.sidebar-profile').outerHeight() || 0;
        const footerHeight = $('.sidebar-footer').outerHeight() || 0;
        const alertsHeight = $('.sidebar-alerts').outerHeight() || 0;
        const statsHeight = $('.sidebar-stats').outerHeight() || 0;
        
        const menu = $('.menu');
        const availableHeight = windowHeight - headerHeight - profileHeight - footerHeight - alertsHeight - statsHeight;
        
        menu.css('height', `${Math.max(availableHeight, 200)}px`);
    }

    // اضافه کردن tooltip برای حالت جمع شده
    function initializeTooltips() {
        $('.menu-link').each(function() {
            const text = $(this).find('.menu-text').text();
            $(this).attr('data-title', text);
        });
    }

    // اجرای توابع اولیه
    setActiveMenu();
    adjustSidebarHeight();
    initializeTooltips();

    // تنظیم مجدد ارتفاع در تغییر سایز پنجره
    $(window).on('resize', adjustSidebarHeight);
});