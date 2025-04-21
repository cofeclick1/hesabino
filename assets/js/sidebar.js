$(document).ready(function() {
    // تعریف متغیرهای کلی
    const $sidebar = $('.sidebar');
    const $mainContent = $('.main-content');
    let currentOpenSubmenu = null;

    // بررسی وضعیت قبلی سایدبار
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $sidebar.addClass('collapsed');
        $mainContent.addClass('expanded');
    }

    // باز/بسته کردن سایدبار
    $('.sidebar-toggle').click(function(e) {
        e.preventDefault();
        $sidebar.toggleClass('collapsed');
        $mainContent.toggleClass('expanded');
        localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed'));
    });

    // مدیریت زیرمنوها
    $('.nav-item.has-submenu > .nav-link').click(function(e) {
        e.preventDefault();
        const $navItem = $(this).parent();
        
        // اگر سایدبار جمع شده است، آن را باز کنیم
        if ($sidebar.hasClass('collapsed')) {
            $sidebar.removeClass('collapsed');
            $mainContent.removeClass('expanded');
            localStorage.setItem('sidebarCollapsed', false);
        }

        // بستن زیرمنوی قبلی اگر باز است
        if (currentOpenSubmenu && !currentOpenSubmenu.is($navItem)) {
            currentOpenSubmenu.removeClass('open');
            currentOpenSubmenu.find('.submenu').slideUp(300);
        }

        // باز/بسته کردن زیرمنوی کلیک شده
        $navItem.toggleClass('open');
        $navItem.find('.submenu').slideToggle(300);

        // بروزرسانی زیرمنوی فعلی
        currentOpenSubmenu = $navItem.hasClass('open') ? $navItem : null;
    });
// تشخیص URL جاری و باز کردن زیرمنوی مربوطه
const currentPath = window.location.pathname;
$('.nav-item.has-submenu').each(function() {
    const $submenuLinks = $(this).find('.submenu a');
    $submenuLinks.each(function() {
        if (currentPath.includes($(this).attr('href'))) {
            const $navItem = $(this).closest('.nav-item.has-submenu');
            $navItem.addClass('open');
            $navItem.find('.submenu').show();
            currentOpenSubmenu = $navItem;
            return false;
        }
    });
});
    // مدیریت منو در موبایل
    $('.mobile-menu-toggle').click(function(e) {
        e.preventDefault();
        $sidebar.toggleClass('show');
        $('<div class="sidebar-overlay"></div>').insertAfter($sidebar);
    });

    // بستن منو با کلیک بیرون از آن در موبایل
    $(document).on('click', '.sidebar-overlay', function() {
        $sidebar.removeClass('show');
        $('.sidebar-overlay').remove();
    });

    // تنظیم کلاس active برای منوی جاری
    function setActiveMenu() {
        const currentPath = window.location.pathname;
        let activeFound = false;

        $('.nav-link').each(function() {
            const linkPath = $(this).attr('href');
            if (currentPath === linkPath || currentPath.startsWith(linkPath)) {
                $(this).addClass('active');
                
                // اگر لینک در زیرمنو است، منوی والد را باز کنیم
                const $parentItem = $(this).closest('.nav-item.has-submenu');
                if ($parentItem.length) {
                    $parentItem.addClass('open');
                    $parentItem.find('.submenu').show();
                    currentOpenSubmenu = $parentItem;
                }
                
                activeFound = true;
            } else {
                $(this).removeClass('active');
            }
        });

        // اگر هیچ منویی فعال نشد، منوی داشبورد را فعال کنیم
        if (!activeFound && currentPath === '/dashboard.php') {
            $('[href="/dashboard.php"]').addClass('active');
        }
    }
    handleSubmenuState();
    // اجرای تابع تنظیم منوی فعال
    setActiveMenu();

    // تنظیم ارتفاع اسکرول سایدبار
    function adjustSidebarHeight() {
        const windowHeight = window.innerHeight;
        const sidebarHeaderHeight = $('.sidebar-header').outerHeight();
        const sidebarProfileHeight = $('.sidebar-profile').outerHeight();
        const sidebarNavHeight = windowHeight - sidebarHeaderHeight - sidebarProfileHeight;
        $('.sidebar-nav').css('height', `${sidebarNavHeight}px`);
    }

    // اجرای تنظیم ارتفاع در لود و تغییر سایز صفحه
    adjustSidebarHeight();
    $(window).resize(adjustSidebarHeight);

    // اضافه کردن tooltip برای حالت جمع شده
    $('.nav-link').each(function() {
        const $link = $(this);
        const title = $link.find('.nav-text').text();
        $link.attr('data-title', title);
    });

    // انیمیشن نرم برای اسکرول به بخش فعال
    function scrollToActiveItem() {
        const $activeLink = $('.nav-link.active');
        if ($activeLink.length) {
            const $sidebarNav = $('.sidebar-nav');
            const activeTop = $activeLink.offset().top;
            const sidebarTop = $sidebarNav.offset().top;
            const scrollTop = activeTop - sidebarTop - ($sidebarNav.height() / 2);
            
            $sidebarNav.animate({
                scrollTop: scrollTop
            }, 500);
        }
    }

    // اجرای اسکرول به آیتم فعال
    setTimeout(scrollToActiveItem, 100);

    function handleSubmenuState() {
    $('.nav-item.has-submenu').each(function() {
        const $submenu = $(this).find('.submenu');
        const $toggle = $(this).children('.nav-link');
        
        if ($(this).hasClass('open')) {
            $submenu.slideDown(300);
        } else {
            $submenu.slideUp(300);
        }
        
        $toggle.off('click').on('click', function(e) {
            e.preventDefault();
            const $navItem = $(this).parent();
            
            // Close other open submenus
            if (!$navItem.hasClass('open')) {
                $('.nav-item.has-submenu.open').removeClass('open').find('.submenu').slideUp(300);
            }
            
            $navItem.toggleClass('open');
            $submenu.slideToggle(300);
        });
    });
}
});