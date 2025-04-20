<?php
/**
 * توابع کمکی برای پروژه حسابینو
 */

/**
 * بررسی لاگین بودن کاربر
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * پاکسازی داده‌های ورودی
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * آپلود تصویر
 */
function uploadImage($file, $targetDir = 'assets/images/uploads/') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }

    // ایجاد دایرکتوری اگر وجود نداشت
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . $fileName;

    // بررسی نوع فایل
    $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return false;
    }

    // بررسی سایز فایل (حداکثر 5 مگابایت)
    if ($file['size'] > 5000000) {
        return false;
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    }

    return false;
}

/**
 * ریدایرکت به صفحه دیگر
 */
function redirect($path) {
    header("Location: " . BASE_PATH . $path);
    exit;
}

/**
 * نمایش پیام فلش
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * دریافت پیام فلش
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * تبدیل تاریخ میلادی به شمسی
 */
function toJalali($date) {
    if (empty($date)) return '';
    
    $date = date('Y-m-d', strtotime($date));
    list($year, $month, $day) = explode('-', $date);
    
    require_once 'jdf.php';
    return gregorian_to_jalali($year, $month, $day, '/');
}

/**
 * فرمت قیمت
 */
function formatPrice($price) {
    return number_format($price) . ' ریال';
}

/**
 * تولید کد تصادفی
 */
function generateRandomCode($length = 8) {
    return substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

/**
 * اعتبارسنجی کد ملی
 */
function validateNationalCode($code) {
    if (!preg_match('/^[0-9]{10}$/', $code)) {
        return false;
    }
    
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += ((10 - $i) * intval(substr($code, $i, 1)));
    }
    
    $ret = $sum % 11;
    $parity = intval(substr($code, 9, 1));
    
    if ($ret < 2) {
        return ($ret == $parity);
    }
    return ((11 - $ret) == $parity);
}

/**
 * تبدیل اعداد انگلیسی به فارسی
 */
function toFarsiNumber($number) {
    $farsi_array = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english_array = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    
    return str_replace($english_array, $farsi_array, $number);
}

/**
 * بررسی https بودن درخواست
 */
function isSecure() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

/**
 * تولید URL امن
 */
function secureUrl($path) {
    return (isSecure() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . BASE_PATH . $path;
}