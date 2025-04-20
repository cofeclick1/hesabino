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
?>

<div class="sidebar" id="mainSidebar">
    <div class="sidebar-header">
        <img src="<?php echo BASE_PATH; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="logo">
        <h3><?php echo SITE_NAME; ?></h3>
    </div>

    <ul class="menu">
        <!-- داشبورد -->
        <li class="menu-item <?php echo isActiveMenu('/dashboard.php'); ?>">
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="menu-link">
                <i class="fas fa-tachometer-alt"></i>
                <span>داشبورد</span>
            </a>
        </li>

        <!-- اشخاص -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/people/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-users"></i>
                <span>اشخاص</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/people/new_person.php">شخص جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/people_list.php">لیست اشخاص</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/receive.php">دریافت</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/receive_list.php">لیست دریافت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/pay.php">پرداخت</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/pay_list.php">لیست پرداخت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/shareholders.php">سهامداران</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/people/sellers.php">فروشندگان</a></li>
            </ul>
        </li>

        <!-- کالاها و خدمات -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/products/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-box"></i>
                <span>کالاها و خدمات</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/products/new_product.php">افزودن محصول</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/new_service.php">خدمات جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/products_services.php">کالاها و خدمات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/update_price_list.php">به‌روزرسانی لیست قیمت</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/print_barcode.php">چاپ بارکد</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/print_bulk_barcode.php">چاپ بارکد تعدادی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/products/price_list_page.php">صفحه لیست قیمت کالا</a></li>
            </ul>
        </li>

        <!-- بانکداری -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/banking/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-university"></i>
                <span>بانکداری</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/banking/banks.php">بانک‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/funds.php">صندوق‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/petty_cash.php">تنخواه‌گردان‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/transfer.php">انتقال</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/transfer_list.php">لیست انتقال‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/received_checks.php">لیست چک‌های دریافتی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/banking/paid_checks.php">لیست چک‌های پرداختی</a></li>
            </ul>
        </li>

        <!-- فروش و درآمد -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/sales/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-shopping-cart"></i>
                <span>فروش و درآمد</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/sales/new_sale.php">فروش جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/quick_invoice.php">فاکتور سریع</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/return_from_sale.php">برگشت از فروش</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/sale_invoices.php">فاکتورهای فروش</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/return_invoices.php">فاکتورهای برگشت از فروش</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/income.php">درآمد</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/income_list.php">لیست درآمدها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/installment_contract.php">قرارداد فروش اقساطی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/installment_list.php">لیست فروش اقساطی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/sales/discounted_items.php">اقلام تخفیف‌دار</a></li>
            </ul>
        </li>

        <!-- خرید و هزینه -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/purchases/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-shopping-basket"></i>
                <span>خرید و هزینه</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/purchases/new_purchase.php">خرید جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/return_from_purchase.php">برگشت از خرید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/purchase_invoices.php">فاکتورهای خرید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/return_purchase_invoices.php">فاکتورهای برگشت از خرید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/expense.php">هزینه</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/expense_list.php">لیست هزینه‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/waste.php">ضایعات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/purchases/waste_list.php">لیست ضایعات</a></li>
            </ul>
        </li>

        <!-- انبارداری -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/inventory/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-warehouse"></i>
                <span>انبارداری</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/inventory/warehouses.php">انبارها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/new_transfer.php">حواله جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/warehouse_transfers.php">رسید و حواله‌های انبار</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/stock.php">موجودی کالا</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/all_warehouse_stock.php">موجودی تمامی انبارها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/inventory/inventory_audit.php">انبارگردانی</a></li>
            </ul>
        </li>

        <!-- حسابداری -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/accounting/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-calculator"></i>
                <span>حسابداری</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/accounting/new_document.php">سند جدید</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/document_list.php">لیست اسناد</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/opening_balance.php">تراز افتتاحیه</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/close_fiscal_year.php">بستن سال مالی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/accounts_table.php">جدول حساب‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/accounting/consolidate_documents.php">تجمیع اسناد</a></li>
            </ul>
        </li>

        <!-- سایر -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/others/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-ellipsis-h"></i>
                <span>سایر</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/others/archive.php">آرشیو</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/sms_panel.php">پنل پیامک</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/inquiry.php">استعلام</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/other_receive.php">دریافت سایر</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/receive_list.php">لیست دریافت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/other_pay.php">پرداخت سایر</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/pay_list.php">لیست پرداخت‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/currency_adjustment.php">سند تسعیر ارز</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/people_balance.php">سند توازن اشخاص</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/product_balance.php">سند توازن کالاها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/others/salary_document.php">سند حقوق</a></li>
            </ul>
        </li>

        <!-- گزارش‌ها -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/reports/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-chart-bar"></i>
                <span>گزارش‌ها</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/reports/all_reports.php">تمام گزارش‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/balance_sheet.php">ترازنامه</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/debtors_creditors.php">بدهکاران و بستانکاران</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/person_account_card.php">کارت حساب اشخاص</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/product_account_card.php">کارت حساب کالا</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/sales_by_product.php">فروش به تفکیک کالا</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/reports/project_card.php">کارت پروژه</a></li>
            </ul>
        </li>
        
        <!-- تنظیمات -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/settings/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-cog"></i>
                <span>تنظیمات</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/settings/projects.php">پروژه‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/business_info.php">اطلاعات کسب‌وکار</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/financial_settings.php">تنظیمات مالی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/currency_conversion.php">جدول تبدیل نرخ ارز</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/user_management.php">مدیریت کاربران</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/print_settings.php">تنظیمات چاپ</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/form_builder.php">فرم‌ساز</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/notifications.php">اعلانات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/backup.php">پشتیبان‌گیری</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/restore.php">بازیابی اطلاعات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/permissions.php">مدیریت دسترسی‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/settings/activity_log.php">گزارش فعالیت‌ها</a></li>
            </ul>
        </li>

        <!-- پروفایل -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/profile/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-user"></i>
                <span>پروفایل</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/profile/view.php">مشاهده پروفایل</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/edit.php">ویرایش اطلاعات</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/change_password.php">تغییر رمز عبور</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/notifications.php">تنظیمات اعلان‌ها</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/profile/activity.php">تاریخچه فعالیت‌ها</a></li>
            </ul>
        </li>
        
        <!-- راهنما -->
        <li class="menu-item has-submenu <?php echo isActiveMenu('/help/'); ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-question-circle"></i>
                <span>راهنما</span>
                <i class="fas fa-chevron-left submenu-arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="<?php echo BASE_PATH; ?>/help/guide.php">راهنمای کاربری</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/help/faq.php">سؤالات متداول</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/help/support.php">پشتیبانی</a></li>
                <li><a href="<?php echo BASE_PATH; ?>/help/about.php">درباره ما</a></li>
            </ul>
        </li>

        <!-- خروج -->
        <li class="menu-item">
            <a href="<?php echo BASE_PATH; ?>/auth/logout.php" class="menu-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>خروج</span>
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
</div>