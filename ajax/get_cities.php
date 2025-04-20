<?php
require_once '../includes/init.php';

// بررسی دسترسی با AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('دسترسی مستقیم به این صفحه مجاز نیست');
}

// بررسی لاگین کاربر
if (!$auth->isLoggedIn()) {
    die('لطفا وارد حساب کاربری خود شوید');
}

// دریافت شناسه استان
$provinceId = isset($_POST['province_id']) ? (int)$_POST['province_id'] : 0;
if (!$provinceId) {
    die('<option value="">لطفا استان را انتخاب کنید</option>');
}

try {
    // دریافت لیست شهرها
    $cities = $db->query(
        "SELECT id, name FROM cities WHERE province_id = ? ORDER BY name",
        [$provinceId]
    )->fetchAll();

    if ($cities) {
        echo '<option value="">انتخاب کنید</option>';
        foreach ($cities as $city) {
            printf(
                '<option value="%d">%s</option>',
                $city['id'],
                htmlspecialchars($city['name'])
            );
        }
    } else {
        echo '<option value="">شهری یافت نشد</option>';
    }
} catch (Exception $e) {
    error_log("Error in get_cities.php: " . $e->getMessage());
    die('<option value="">خطا در دریافت اطلاعات</option>');
}