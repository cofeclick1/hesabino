/**
 * Hesabino Sidebar JavaScript
 * Version: 1.0.0
 * Author: Copilot
 * License: MIT
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // ---------- تعریف متغیرهای اصلی ----------
    const sidebar = document.getElementById('mainSidebar');
    const menuItems = document.querySelectorAll('.menu-item');
    const menuLinks = document.querySelectorAll('.menu-link, .submenu a');
    
    // ---------- مدیریت منوی فعال ----------
    function setActiveMenu() {
        const currentPath = window.location.pathname;
        
        menuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href)) {
                // افزودن کلاس active به لینک
                link.classList.add('active');
                
                // باز کردن منوی والد
                const parentMenuItem = link.closest('.menu-item');
                if (parentMenuItem && parentMenuItem.classList.contains('has-submenu')) {
                    parentMenuItem.classList.add('open', 'active');
                }
                
                // اضافه کردن کلاس active به والد برای زیرمنوها
                const submenuParent = link.closest('.submenu');
                if (submenuParent) {
                    submenuParent.parentElement.classList.add('active');
                }
            }
        });
    }

    // ---------- مدیریت منوهای کشویی ----------
    function initSubmenuToggle() {
        const menuItemsWithSubmenu = document.querySelectorAll('.has-submenu > .menu-link');
        
        menuItemsWithSubmenu.forEach(menuLink => {
            menuLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                const menuItem = this.parentElement;
                const isOpen = menuItem.classList.contains('open');
                
                // بستن سایر منوهای باز
                menuItems.forEach(item => {
                    if (item !== menuItem && item.classList.contains('has-submenu')) {
                        item.classList.remove('open');
                        const submenu = item.querySelector('.submenu');
                        if (submenu) {
                            submenu.style.maxHeight = null;
                        }
                    }
                });
                
                // باز/بسته کردن منوی کلیک شده
                menuItem.classList.toggle('open');
                
                // انیمیشن برای باز/بسته شدن
                const submenu = menuItem.querySelector('.submenu');
                if (submenu) {
                    if (!isOpen) {
                        submenu.style.display = 'block';
                        const height = submenu.scrollHeight;
                        submenu.style.maxHeight = '0px';
                        submenu.style.opacity = '0';
                        
                        setTimeout(() => {
                            submenu.style.maxHeight = height + 'px';
                            submenu.style.opacity = '1';
                        }, 10);
                    } else {
                        submenu.style.maxHeight = '0px';
                        submenu.style.opacity = '0';
                        
                        submenu.addEventListener('transitionend', function handler() {
                            if (submenu.style.maxHeight === '0px') {
                                submenu.style.display = 'none';
                                submenu.removeEventListener('transitionend', handler);
                            }
                        });
                    }
                }
            });
        });
    }

    // ---------- مدیریت حالت موبایل ----------
    function initMobileMenu() {
        // ایجاد دکمه تاگل
        if (!document.querySelector('.sidebar-toggle')) {
            const toggleButton = document.createElement('button');
            toggleButton.className = 'sidebar-toggle';
            toggleButton.setAttribute('aria-label', 'Toggle Sidebar');
            toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.appendChild(toggleButton);

            // ایجاد اورلی
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);

            // رویداد کلیک دکمه تاگل
            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });

            // رویداد کلیک اورلی
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
            });

            // بستن منو با کلیک روی لینک‌ها در حالت موبایل
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('open');
                    }
                });
            });
        }
    }

    // ---------- مدیریت اسکرول ----------
    function initSidebarScroll() {
        let lastScroll = 0;
        
        sidebar.addEventListener('scroll', function() {
            const currentScroll = this.scrollTop;
            
            // اضافه کردن سایه به هدر در هنگام اسکرول
            if (currentScroll > 10) {
                this.querySelector('.sidebar-header').style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
            } else {
                this.querySelector('.sidebar-header').style.boxShadow = 'none';
            }
            
            lastScroll = currentScroll;
        });
    }

    // ---------- راه‌اندازی اولیه ----------
    function init() {
        setActiveMenu();
        initSubmenuToggle();
        initMobileMenu();
        initSidebarScroll();
        
        // اضافه کردن کلاس loaded برای انیمیشن‌های اولیه
        setTimeout(() => {
            sidebar.classList.add('loaded');
        }, 100);
    }

    // شروع
    init();
});