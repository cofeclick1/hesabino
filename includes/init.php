<?php
session_start();
// لود کردن تنظیمات اصلی
require_once __DIR__ . '/../config/config.php';

// تنظیمات پایه مسیرها
define('BASE_PATH', '/hesabino');
define('ASSETS_URL', BASE_PATH . '/assets');
// بررسی و لود کردن فایل کانفیگ اصلی اگر لود نشده
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/hesabino');
}
// شروع session
session_start();

// تنظیم charset به UTF-8
header('Content-Type: text/html; charset=utf-8');

// لود کردن فایل‌های مورد نیاز
require_once __DIR__ . '/../config/config.php';
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
        '/hesabino/people/people_list.php' => 'people_view'
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

// چک کردن زمان آخرین پاکسازی لیست مشتریان
try {
    $cleanupTime = $db->query("SELECT last_cleanup FROM customer_cleanup ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($cleanupTime) {
        $cleanupTime = new DateTime($cleanupTime);
        $currentTime = new DateTime();
        $interval = $currentTime->diff($cleanupTime);

        // اگر بیشتر از یک روز گذشته باشد، لیست مشتریان را پاکسازی کنید
        if ($interval->days >= 1) {
            $db->query("DELETE FROM customers WHERE name = 'مشتری' AND DATE(created_at) < DATE_SUB(NOW(), INTERVAL 1 DAY)");
            $db->query("INSERT INTO customer_cleanup (last_cleanup) VALUES (NOW())");
        }
    }
} catch (Exception $e) {
    error_log("خطا در پاکسازی لیست مشتریان: " . $e->getMessage());
}