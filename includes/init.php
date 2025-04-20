<?php
// لود کردن تنظیمات اصلی
require_once __DIR__ . '/../config/config.php';

// تنظیمات پایه مسیرها
define('BASE_PATH', '/hesabino');
define('ASSETS_URL', BASE_PATH . '/assets');

// شروع session
session_start();

// تنظیم charset به UTF-8
header('Content-Type: text/html; charset=utf-8');

// لود کردن فایل‌های اصلی
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/jdf.php';

// ایجاد نمونه از کلاس دیتابیس
$db = Database::getInstance();

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ایجاد نمونه از کلاس Auth
$auth = new Auth();

// بررسی لاگین کاربر در صفحات غیر از login و register
$current_page = basename($_SERVER['PHP_SELF']);
if (!in_array($current_page, ['login.php', 'register.php']) && !$auth->isLoggedIn()) {
    header('Location: ' . BASE_PATH . '/login.php');
    exit;
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