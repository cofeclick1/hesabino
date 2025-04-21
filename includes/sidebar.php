<?php
// بررسی دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// تابع کمکی برای نمایش کلاس active در منوی جاری
function isActiveMenu($path) {
    $current_path = $_SERVER['REQUEST_URI'];
    return strpos($current_path, $path) !== false ? 'active' : '';
}

// تابع کمکی برای نمایش باز بودن زیرمنو
function isSubmenuOpen($path) {
    $current_path = $_SERVER['REQUEST_URI'];
    return strpos($current_path, $path) !== false ? 'open' : '';
}
?>

<div class="sidebar" id="mainSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="sidebar-brand">
                <img src="<?php echo BASE_PATH; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="logo">
                <h3 class="brand-text"><?php echo SITE_NAME; ?></h3>
            </a>
            <button class="sidebar-toggle d-none d-md-block">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- User Profile -->
    <div class="sidebar-profile">
        <div class="profile-info">
            <img src="<?php echo !empty($user['profile_image']) ? BASE_PATH . '/' . $user['profile_image'] : BASE_PATH . '/assets/images/default-avatar.png'; ?>" 
                 alt="<?php echo htmlspecialchars($user['username']); ?>" 
                 class="profile-image">
            <div class="profile-details">
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
                <div class="profile-role"><?php echo htmlspecialchars($user['role'] ?? 'کاربر'); ?></div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="menu">
        <!-- داشبورد -->
        <li class="menu-item <?php echo isActiveMenu('/dashboard.php'); ?>">
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="menu-link" data-title="داشبورد">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">داشبورد</span>
            </a>
        </li>

        <!-- اشخاص -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/people/'); ?>">
            <a href="#" class="menu-link" data-title="اشخاص">
                <i class="fas fa-users"></i>
                <span class="menu-text">اشخاص</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/people/new_person.php" class="<?php echo isActiveMenu('/people/new_person.php'); ?>">شخص جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/people_list.php" class="<?php echo isActiveMenu('/people/people_list.php'); ?>">لیست اشخاص</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/receive.php" class="<?php echo isActiveMenu('/people/receive.php'); ?>">دریافت</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/receive_list.php" class="<?php echo isActiveMenu('/people/receive_list.php'); ?>">لیست دریافت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/pay.php" class="<?php echo isActiveMenu('/people/pay.php'); ?>">پرداخت</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/pay_list.php" class="<?php echo isActiveMenu('/people/pay_list.php'); ?>">لیست پرداخت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/shareholders.php" class="<?php echo isActiveMenu('/people/shareholders.php'); ?>">سهامداران</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/sellers.php" class="<?php echo isActiveMenu('/people/sellers.php'); ?>">فروشندگان</a></li>
            </ul>
        </li>

        <!-- کالاها و خدمات -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/products/'); ?>">
            <a href="#" class="menu-link" data-title="کالاها و خدمات">
                <i class="fas fa-box"></i>
                <span class="menu-text">کالاها و خدمات</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/products/new_product.php" class="<?php echo isActiveMenu('/products/new_product.php'); ?>">افزودن محصول</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/new_service.php" class="<?php echo isActiveMenu('/products/new_service.php'); ?>">خدمات جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/products_services.php" class="<?php echo isActiveMenu('/products/products_services.php'); ?>">کالاها و خدمات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/update_price_list.php" class="<?php echo isActiveMenu('/products/update_price_list.php'); ?>">به‌روزرسانی لیست قیمت</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/print_barcode.php" class="<?php echo isActiveMenu('/products/print_barcode.php'); ?>">چاپ بارکد</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/print_bulk_barcode.php" class="<?php echo isActiveMenu('/products/print_bulk_barcode.php'); ?>">چاپ بارکد تعدادی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/price_list_page.php" class="<?php echo isActiveMenu('/products/price_list_page.php'); ?>">صفحه لیست قیمت کالا</a></li>
            </ul>
        </li>

        <!-- بانکداری -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/banking/'); ?>">
            <a href="#" class="menu-link" data-title="بانکداری">
                <i class="fas fa-university"></i>
                <span class="menu-text">بانکداری</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/banking/banks.php" class="<?php echo isActiveMenu('/banking/banks.php'); ?>">بانک‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/funds.php" class="<?php echo isActiveMenu('/banking/funds.php'); ?>">صندوق‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/petty_cash.php" class="<?php echo isActiveMenu('/banking/petty_cash.php'); ?>">تنخواه‌گردان‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/transfer.php" class="<?php echo isActiveMenu('/banking/transfer.php'); ?>">انتقال</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/transfer_list.php" class="<?php echo isActiveMenu('/banking/transfer_list.php'); ?>">لیست انتقال‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/received_checks.php" class="<?php echo isActiveMenu('/banking/received_checks.php'); ?>">لیست چک‌های دریافتی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/paid_checks.php" class="<?php echo isActiveMenu('/banking/paid_checks.php'); ?>">لیست چک‌های پرداختی</a></li>
            </ul>
        </li>

        <!-- فروش و درآمد -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/sales/'); ?>">
            <a href="#" class="menu-link" data-title="فروش و درآمد">
                <i class="fas fa-shopping-cart"></i>
                <span class="menu-text">فروش و درآمد</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/sales/new_sale.php" class="<?php echo isActiveMenu('/sales/new_sale.php'); ?>">فروش جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/quick_invoice.php" class="<?php echo isActiveMenu('/sales/quick_invoice.php'); ?>">فاکتور سریع</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/return_from_sale.php" class="<?php echo isActiveMenu('/sales/return_from_sale.php'); ?>">برگشت از فروش</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/sale_invoices.php" class="<?php echo isActiveMenu('/sales/sale_invoices.php'); ?>">فاکتورهای فروش</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/return_invoices.php" class="<?php echo isActiveMenu('/sales/return_invoices.php'); ?>">فاکتورهای برگشت از فروش</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/income.php" class="<?php echo isActiveMenu('/sales/income.php'); ?>">درآمد</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/income_list.php" class="<?php echo isActiveMenu('/sales/income_list.php'); ?>">لیست درآمدها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/installment_contract.php" class="<?php echo isActiveMenu('/sales/installment_contract.php'); ?>">قرارداد فروش اقساطی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/installment_list.php" class="<?php echo isActiveMenu('/sales/installment_list.php'); ?>">لیست فروش اقساطی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/discounted_items.php" class="<?php echo isActiveMenu('/sales/discounted_items.php'); ?>">اقلام تخفیف‌دار</a></li>
            </ul>
        </li>

        <!-- خرید و هزینه -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/purchases/'); ?>">
            <a href="#" class="menu-link" data-title="خرید و هزینه">
                <i class="fas fa-shopping-basket"></i>
                <span class="menu-text">خرید و هزینه</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/purchases/new_purchase.php" class="<?php echo isActiveMenu('/purchases/new_purchase.php'); ?>">خرید جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/return_from_purchase.php" class="<?php echo isActiveMenu('/purchases/return_from_purchase.php'); ?>">برگشت از خرید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/purchase_invoices.php" class="<?php echo isActiveMenu('/purchases/purchase_invoices.php'); ?>">فاکتورهای خرید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/return_purchase_invoices.php" class="<?php echo isActiveMenu('/purchases/return_purchase_invoices.php'); ?>">فاکتورهای برگشت از خرید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/expense.php" class="<?php echo isActiveMenu('/purchases/expense.php'); ?>">هزینه</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/expense_list.php" class="<?php echo isActiveMenu('/purchases/expense_list.php'); ?>">لیست هزینه‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/waste.php" class="<?php echo isActiveMenu('/purchases/waste.php'); ?>">ضایعات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/waste_list.php" class="<?php echo isActiveMenu('/purchases/waste_list.php'); ?>">لیست ضایعات</a></li>
            </ul>
        </li>

        <!-- انبارداری -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/inventory/'); ?>">
            <a href="#" class="menu-link" data-title="انبارداری">
                <i class="fas fa-warehouse"></i>
                <span class="menu-text">انبارداری</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/inventory/warehouses.php" class="<?php echo isActiveMenu('/inventory/warehouses.php'); ?>">انبارها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/new_transfer.php" class="<?php echo isActiveMenu('/inventory/new_transfer.php'); ?>">حواله جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/warehouse_transfers.php" class="<?php echo isActiveMenu('/inventory/warehouse_transfers.php'); ?>">رسید و حواله‌های انبار</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/stock.php" class="<?php echo isActiveMenu('/inventory/stock.php'); ?>">موجودی کالا</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/all_warehouse_stock.php" class="<?php echo isActiveMenu('/inventory/all_warehouse_stock.php'); ?>">موجودی تمامی انبارها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/inventory_audit.php" class="<?php echo isActiveMenu('/inventory/inventory_audit.php'); ?>">انبارگردانی</a></li>
            </ul>
        </li>

        <!-- حسابداری -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/accounting/'); ?>">
            <a href="#" class="menu-link" data-title="حسابداری">
                <i class="fas fa-calculator"></i>
                <span class="menu-text">حسابداری</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/accounting/new_document.php" class="<?php echo isActiveMenu('/accounting/new_document.php'); ?>">سند جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/document_list.php" class="<?php echo isActiveMenu('/accounting/document_list.php'); ?>">لیست اسناد</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/opening_balance.php" class="<?php echo isActiveMenu('/accounting/opening_balance.php'); ?>">تراز افتتاحیه</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/close_fiscal_year.php" class="<?php echo isActiveMenu('/accounting/close_fiscal_year.php'); ?>">بستن سال مالی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/accounts_table.php" class="<?php echo isActiveMenu('/accounting/accounts_table.php'); ?>">جدول حساب‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/consolidate_documents.php" class="<?php echo isActiveMenu('/accounting/consolidate_documents.php'); ?>">تجمیع اسناد</a></li>
            </ul>
        </li>

        <!-- سایر -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/others/'); ?>">
            <a href="#" class="menu-link" data-title="سایر">
                <i class="fas fa-ellipsis-h"></i>
                <span class="menu-text">سایر</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/others/archive.php" class="<?php echo isActiveMenu('/others/archive.php'); ?>">آرشیو</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/sms_panel.php" class="<?php echo isActiveMenu('/others/sms_panel.php'); ?>">پنل پیامک</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/inquiry.php" class="<?php echo isActiveMenu('/others/inquiry.php'); ?>">استعلام</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/other_receive.php" class="<?php echo isActiveMenu('/others/other_receive.php'); ?>">دریافت سایر</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/receive_list.php" class="<?php echo isActiveMenu('/others/receive_list.php'); ?>">لیست دریافت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/other_pay.php" class="<?php echo isActiveMenu('/others/other_pay.php'); ?>">پرداخت سایر</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/pay_list.php" class="<?php echo isActiveMenu('/others/pay_list.php'); ?>">لیست پرداخت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/currency_adjustment.php" class="<?php echo isActiveMenu('/others/currency_adjustment.php'); ?>">سند تسعیر ارز</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/people_balance.php" class="<?php echo isActiveMenu('/others/people_balance.php'); ?>">سند توازن اشخاص</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/product_balance.php" class="<?php echo isActiveMenu('/others/product_balance.php'); ?>">سند توازن کالاها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/salary_document.php" class="<?php echo isActiveMenu('/others/salary_document.php'); ?>">سند حقوق</a></li>
            </ul>
        </li>

        <!-- گزارش‌ها -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/reports/'); ?>">
            <a href="#" class="menu-link" data-title="گزارش‌ها">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">گزارش‌ها</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/reports/all_reports.php" class="<?php echo isActiveMenu('/reports/all_reports.php'); ?>">تمام گزارش‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/balance_sheet.php" class="<?php echo isActiveMenu('/reports/balance_sheet.php'); ?>">ترازنامه</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/debtors_creditors.php" class="<?php echo isActiveMenu('/reports/debtors_creditors.php'); ?>">بدهکاران و بستانکاران</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/person_account_card.php" class="<?php echo isActiveMenu('/reports/person_account_card.php'); ?>">کارت حساب اشخاص</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/product_account_card.php" class="<?php echo isActiveMenu('/reports/product_account_card.php'); ?>">کارت حساب کالا</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/sales_by_product.php" class="<?php echo isActiveMenu('/reports/sales_by_product.php'); ?>">فروش به تفکیک کالا</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/project_card.php" class="<?php echo isActiveMenu('/reports/project_card.php'); ?>">کارت پروژه</a></li>
            </ul>
        </li>

        <!-- تنظیمات -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/settings/'); ?>">
            <a href="#" class="menu-link" data-title="تنظیمات">
                <i class="fas fa-cog"></i>
                <span class="menu-text">تنظیمات</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/settings/projects.php" class="<?php echo isActiveMenu('/settings/projects.php'); ?>">پروژه‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/business_info.php" class="<?php echo isActiveMenu('/settings/business_info.php'); ?>">اطلاعات کسب‌وکار</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/financial_settings.php" class="<?php echo isActiveMenu('/settings/financial_settings.php'); ?>">تنظیمات مالی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/currency_conversion.php" class="<?php echo isActiveMenu('/settings/currency_conversion.php'); ?>">جدول تبدیل نرخ ارز</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/user_management.php" class="<?php echo isActiveMenu('/settings/user_management.php'); ?>">مدیریت کاربران</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/print_settings.php" class="<?php echo isActiveMenu('/settings/print_settings.php'); ?>">تنظیمات چاپ</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/form_builder.php" class="<?php echo isActiveMenu('/settings/form_builder.php'); ?>">فرم‌ساز</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/notifications.php" class="<?php echo isActiveMenu('/settings/notifications.php'); ?>">اعلانات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/backup.php" class="<?php echo isActiveMenu('/settings/backup.php'); ?>">پشتیبان‌گیری</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/restore.php" class="<?php echo isActiveMenu('/settings/restore.php'); ?>">بازیابی اطلاعات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/permissions.php" class="<?php echo isActiveMenu('/settings/permissions.php'); ?>">مدیریت دسترسی‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/activity_log.php" class="<?php echo isActiveMenu('/settings/activity_log.php'); ?>">گزارش فعالیت‌ها</a></li>
            </ul>
        </li>

        <!-- پروفایل -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/profile/'); ?>">
            <a href="#" class="menu-link" data-title="پروفایل">
                <i class="fas fa-user"></i>
                <span class="menu-text">پروفایل</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/profile/view.php" class="<?php echo isActiveMenu('/profile/view.php'); ?>">مشاهده پروفایل</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/edit.php" class="<?php echo isActiveMenu('/profile/edit.php'); ?>">ویرایش اطلاعات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/change_password.php" class="<?php echo isActiveMenu('/profile/change_password.php'); ?>">تغییر رمز عبور</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/notifications.php" class="<?php echo isActiveMenu('/profile/notifications.php'); ?>">تنظیمات اعلان‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/activity.php" class="<?php echo isActiveMenu('/profile/activity.php'); ?>">تاریخچه فعالیت‌ها</a></li>
            </ul>
        </li>

        <!-- راهنما -->
        <li class="menu-item has-submenu <?php echo isSubmenuOpen('/help/'); ?>">
            <a href="#" class="menu-link" data-title="راهنما">
                <i class="fas fa-question-circle"></i>
                <span class="menu-text">راهنما</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/help/guide.php" class="<?php echo isActiveMenu('/help/guide.php'); ?>">راهنمای کاربری</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/help/faq.php" class="<?php echo isActiveMenu('/help/faq.php'); ?>">سؤالات متداول</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/help/support.php" class="<?php echo isActiveMenu('/help/support.php'); ?>">پشتیبانی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/help/about.php" class="<?php echo isActiveMenu('/help/about.php'); ?>">درباره ما</a></li>
            </ul>
        </li>

        <!-- خروج -->
        <li class="menu-item">
            <a href="<?php echo BASE_PATH; ?>/auth/logout.php" class="menu-link" data-title="خروج">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">خروج</span>
            </a>
        </li>
    </ul>

    <!-- نمایش اطلاعات کاربر -->
    <div class="sidebar-footer">
        <div class="user-info">
            <img src="<?php echo BASE_PATH; ?>/assets/images/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png'); ?>" alt="تصویر کاربر" class="user-avatar">
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_fullname'] ?? $_SESSION['username']); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'کاربر'); ?></span>
            </div>
        </div>
        <div class="version-info">
                        <small>نسخه <?php echo APP_VERSION; ?></small>
        </div>
    </div>

    <?php if (isset($lowStock) && $lowStock > 0): ?>
    <!-- نمایش هشدارها -->
    <div class="sidebar-alerts">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo $lowStock; ?> کالا در حد نصاب</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- نوار پیشرفت -->
    <?php if (isset($_SESSION['storage_usage'])): ?>
    <div class="sidebar-stats">
        <div class="storage-usage">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?php echo $_SESSION['storage_usage']; ?>%">
                    <span class="sr-only">فضای استفاده شده: <?php echo $_SESSION['storage_usage']; ?>%</span>
                </div>
            </div>
            <small>فضای استفاده شده: <?php echo $_SESSION['storage_usage']; ?>%</small>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Mobile Menu Toggle -->
<div class="d-block d-md-none">
    <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>

<!-- اسکریپت‌های مربوط به سایدبار -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // بررسی وضعیت قبلی سایدبار
    const sidebar = document.getElementById('mainSidebar');
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
    }

    // دکمه تغییر وضعیت سایدبار
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // مدیریت کلیک روی دکمه موبایل
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.add('show');
            document.querySelector('.sidebar-overlay').style.display = 'block';
        });
    }

    // بستن منو با کلیک روی overlay
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            this.style.display = 'none';
        });
    }

    // مدیریت زیرمنوها
    const submenuToggles = document.querySelectorAll('.has-submenu > .menu-link');
    let currentOpenSubmenu = null;

    submenuToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const menuItem = this.parentElement;
            const submenu = menuItem.querySelector('.submenu');

            // اگر سایدبار جمع شده است، آن را باز کنیم
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                document.body.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', false);
            }

            // بستن زیرمنوی قبلی
            if (currentOpenSubmenu && currentOpenSubmenu !== menuItem) {
                currentOpenSubmenu.classList.remove('open');
                const previousSubmenu = currentOpenSubmenu.querySelector('.submenu');
                if (previousSubmenu) {
                    previousSubmenu.style.maxHeight = null;
                }
            }

            // باز/بسته کردن زیرمنوی فعلی
            menuItem.classList.toggle('open');
            if (submenu) {
                if (menuItem.classList.contains('open')) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    currentOpenSubmenu = menuItem;
                } else {
                    submenu.style.maxHeight = null;
                    currentOpenSubmenu = null;
                }
            }
        });
    });

    // باز کردن زیرمنوی فعال در لود صفحه
    const activeSubmenuItem = document.querySelector('.submenu .menu-link.active');
    if (activeSubmenuItem) {
        const parentMenu = activeSubmenuItem.closest('.has-submenu');
        if (parentMenu) {
            parentMenu.classList.add('open');
            const submenu = parentMenu.querySelector('.submenu');
            if (submenu) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                currentOpenSubmenu = parentMenu;
            }
        }
    }

    // اضافه کردن tooltip برای حالت جمع شده
    const menuLinks = document.querySelectorAll('.menu-link');
    menuLinks.forEach(function(link) {
        const text = link.querySelector('.menu-text');
        if (text) {
            link.setAttribute('data-title', text.textContent);
        }
    });

    // تنظیم ارتفاع اسکرول سایدبار
    function adjustSidebarHeight() {
        const header = document.querySelector('.sidebar-header');
        const profile = document.querySelector('.sidebar-profile');
        const footer = document.querySelector('.sidebar-footer');
        const alerts = document.querySelector('.sidebar-alerts');
        const stats = document.querySelector('.sidebar-stats');
        
        const menu = document.querySelector('.menu');
        
        if (menu && header && profile && footer) {
            const headerHeight = header.offsetHeight;
            const profileHeight = profile.offsetHeight;
            const footerHeight = footer.offsetHeight;
            const alertsHeight = alerts ? alerts.offsetHeight : 0;
            const statsHeight = stats ? stats.offsetHeight : 0;
            
            const availableHeight = window.innerHeight - headerHeight - profileHeight - footerHeight - alertsHeight - statsHeight;
            menu.style.height = `${availableHeight}px`;
        }
    }

    // اجرای تنظیم ارتفاع در لود و تغییر سایز صفحه
    adjustSidebarHeight();
    window.addEventListener('resize', adjustSidebarHeight);
});
</script>