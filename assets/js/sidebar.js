$(document).ready(function() {
    // تعریف متغیرهای کلی
    const $sidebar = $('.sidebar');
    const $mainContent = $('.main-content');
    let currentOpenSubmenu = null;

    // پاکسازی تمام رویدادهای قبلی
    $(document).off('click.sidebar');
    $('.sidebar-toggle, .mobile-menu-toggle').off('click.sidebar');
    $('.menu-item.has-submenu > .menu-link').off('click.sidebar');
    $('.submenu a').off('click.sidebar');

    // بررسی وضعیت قبلی سایدبار
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $sidebar.addClass('collapsed');
        $mainContent.addClass('expanded');
    }

    // باز/بسته کردن سایدبار
    $('.sidebar-toggle').on('click.sidebar', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $sidebar.toggleClass('collapsed');
        $mainContent.toggleClass('expanded');
        localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed'));

        if ($sidebar.hasClass('collapsed')) {
            closeAllSubmenus();
        }
    });

    // مدیریت منو در موبایل
    $('.mobile-menu-toggle').on('click.sidebar', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $sidebar.addClass('show');
        if (!$('.sidebar-overlay').length) {
            $('<div class="sidebar-overlay"></div>').insertAfter($sidebar);
        }
    });

    // بستن منو با کلیک روی overlay
    $(document).on('click.sidebar', '.sidebar-overlay', function() {
        $sidebar.removeClass('show');
        $(this).remove();
    });

    // تابع بستن تمام زیرمنوها
    function closeAllSubmenus() {
        if (currentOpenSubmenu) {
            currentOpenSubmenu.removeClass('open');
            currentOpenSubmenu.find('.submenu').stop(true, true).slideUp(200);
            currentOpenSubmenu = null;
        }
    }

    // تابع باز کردن یک زیرمنو
    function openSubmenu($menuItem) {
        // اگر منو قبلاً باز است
        if ($menuItem.hasClass('open')) {
            closeAllSubmenus();
            return;
        }

        // بستن منوی قبلی
        closeAllSubmenus();

        // باز کردن منوی جدید
        $menuItem.addClass('open');
        $menuItem.find('.submenu').stop(true, true).slideDown(200);
        currentOpenSubmenu = $menuItem;
    }

    // مدیریت کلیک روی منوها
    $('.menu-item.has-submenu > .menu-link').on('click.sidebar', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $menuItem = $(this).parent();

        // اگر سایدبار جمع شده است
        if ($sidebar.hasClass('collapsed')) {
            $sidebar.removeClass('collapsed');
            $mainContent.removeClass('expanded');
            localStorage.setItem('sidebarCollapsed', 'false');
            
            setTimeout(() => {
                openSubmenu($menuItem);
            }, 150);
            return;
        }

        openSubmenu($menuItem);
    });

    // جلوگیری از تداخل رویدادها در زیرمنوها
    $('.submenu a').on('click.sidebar', function(e) {
        e.stopPropagation();
    });

    // تنظیم منوی فعال
    function setActiveMenu() {
        const currentPath = window.location.pathname;

        $('.menu-link, .submenu a').removeClass('active');

        $('.menu-link, .submenu a').each(function() {
            const linkPath = $(this).attr('href');
            if (linkPath && (currentPath === linkPath || currentPath.startsWith(linkPath))) {
                $(this).addClass('active');

                const $parentItem = $(this).closest('.menu-item.has-submenu');
                if ($parentItem.length && !$sidebar.hasClass('collapsed')) {
                    $parentItem.addClass('open');
                    $parentItem.find('.submenu').show();
                    currentOpenSubmenu = $parentItem;
                }
                return false;
            }
        });

        if (currentPath === '/dashboard.php' && !$('.menu-link.active, .submenu a.active').length) {
            $('[href="/dashboard.php"]').addClass('active');
        }
    }

    // تنظیم ارتفاع اسکرول سایدبار
    function adjustSidebarHeight() {
        const windowHeight = window.innerHeight;
        const $header = $('.sidebar-header');
        const $profile = $('.sidebar-profile');
        const $footer = $('.sidebar-footer');
        const $alerts = $('.sidebar-alerts');
        const $stats = $('.sidebar-stats');
        const $menu = $('.menu');
        
        if ($menu.length && $header.length && $profile.length && $footer.length) {
            const headerHeight = $header.outerHeight() || 0;
            const profileHeight = $profile.outerHeight() || 0;
            const footerHeight = $footer.outerHeight() || 0;
            const alertsHeight = $alerts.length ? $alerts.outerHeight() : 0;
            const statsHeight = $stats.length ? $stats.outerHeight() : 0;
            
            const availableHeight = windowHeight - headerHeight - profileHeight - footerHeight - alertsHeight - statsHeight - 20;
            $menu.css('height', `${Math.max(availableHeight, 200)}px`);
        }
    }

    // تنظیم tooltips
    function initializeTooltips() {
        $('.menu-link').each(function() {
            const $text = $(this).find('.menu-text');
            if ($text.length) {
                $(this).attr('data-title', $text.text());
            }
        });
    }

    // کلیک خارج از منو
    $(document).on('click.sidebar', function(e) {
        if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('.mobile-menu-toggle').length) {
            if (window.innerWidth <= 768) {
                $sidebar.removeClass('show');
                $('.sidebar-overlay').remove();
            }
        }
    });

    // اجرای توابع اولیه
    setActiveMenu();
    adjustSidebarHeight();
    initializeTooltips();

    // تنظیم مجدد ارتفاع در تغییر سایز پنجره
    let resizeTimer;
    $(window).on('resize.sidebar', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(adjustSidebarHeight, 250);
    });
});