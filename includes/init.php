<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// لود کردن تنظیمات اصلی
require_once __DIR__ . '/../config/config.php';

// تنظیمات پایه مسیرها - فقط اگر تعریف نشده باشد
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/hesabino');
}
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_PATH . '/assets');
}

// تنظیم charset به UTF-8
header('Content-Type: text/html; charset=utf-8');

// لود کردن فایل‌های مورد نیاز
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/jdf.php';
require_once __DIR__ . '/auth.php';

// ایجاد نمونه از کلاس دیتابیس
$db = Database::getInstance();
$auth = new Auth();

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بررسی لاگین کاربر
$script_path = $_SERVER['SCRIPT_NAME'];
$public_pages = [
    '/hesabino/login.php',
    '/hesabino/register.php', 
    '/hesabino/forgot-password.php'
];

// اگر صفحه عمومی نیست، بررسی لاگین کنیم
if (!in_array($script_path, $public_pages)) {
    // اگر کاربر لاگین نکرده، به صفحه لاگین هدایت شود
    if (!$auth->isLoggedIn()) {
        header('Location: ' . BASE_PATH . '/login.php');
        exit;
    }

    // بررسی دسترسی‌ها برای صفحات مختلف
    $permission_map = [
        '/hesabino/people/new_person.php' => 'people_add',
        '/hesabino/people/edit_person.php' => 'people_edit',
        '/hesabino/people/delete_person.php' => 'people_delete',
        '/hesabino/people/people_list.php' => 'people_view',
        '/hesabino/people/receive.php' => 'receipts_add',     // اضافه کردن دسترسی صفحه دریافت
        '/hesabino/people/receive_list.php' => 'receipts_view' // اضافه کردن دسترسی لیست دریافت‌ها
    ];

    // اگر صفحه نیاز به دسترسی خاصی دارد
    if (isset($permission_map[$script_path])) {
        // اگر کاربر دسترسی ندارد
        if (!$auth->hasPermission($permission_map[$script_path]) && !$_SESSION['is_super_admin']) {
            $_SESSION['error'] = 'شما مجوز دسترسی به این بخش را ندارید';
            header('Location: ' . BASE_PATH . '/dashboard.php');
            exit;
        }
    }
}

// مقداردهی متغیرهای مورد نیاز
$user = $auth->getCurrentUser();
$lowStock = $db->query("SELECT COUNT(*) as total FROM products WHERE quantity <= min_quantity AND status = 'active'")->fetch()['total'];

// تنظیم منطقه زمانی
date_default_timezone_set('Asia/Tehran');