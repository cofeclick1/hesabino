<?php
/**
 * File: functions.php
 * این فایل شامل توابع عمومی مورد نیاز برنامه است
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * بررسی دسترسی کاربر به یک قابلیت خاص
 * @param string $permission نام دسترسی مورد نظر
 * @return bool true در صورت داشتن دسترسی و false در غیر این صورت
 */
function isUserHaveAccess($permission) {
    if (!isLoggedIn()) {
        return false;
    }

    // دریافت دسترسی‌های کاربر از سشن
    $userPermissions = $_SESSION['user_permissions'] ?? [];
    
    // اگر کاربر ادمین است، همه دسترسی‌ها را دارد
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return true;
    }
    
    // بررسی دسترسی خاص
    return in_array($permission, $userPermissions);
}

/**
 * بررسی لاگین بودن کاربر
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * ریدایرکت به یک صفحه با پیام
 * @param string $url آدرس مقصد
 * @param string $message پیام
 * @param string $type نوع پیام (success/error/warning/info)
 */
function redirectTo($url, $message = '', $type = 'error') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type
        ];
    }
    
    header("Location: $url");
    exit();
}

/**
 * نمایش پیام فلش
 * @return string
 */
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];
        
        unset($_SESSION['flash_message']);
        
        return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                    {$message}
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return '';
}

/**
 * تبدیل تاریخ میلادی به شمسی
 * @param string $date تاریخ میلادی (Y-m-d)
 * @return string تاریخ شمسی (Y/m/d)
 */
function toJalali($date) {
    if (empty($date)) return '';
    
    $date = explode('-', $date);
    if (count($date) !== 3) return '';
    
    $jdf = new jDateTime(true, true, 'Asia/Tehran');
    return $jdf->gregorian_to_jalali($date[0], $date[1], $date[2], '/');
}

/**
 * تبدیل تاریخ شمسی به میلادی
 * @param string $date تاریخ شمسی (Y/m/d)
 * @return string تاریخ میلادی (Y-m-d)
 */
function toGregorian($date) {
    if (empty($date)) return '';
    
    $date = str_replace('/', '-', $date);
    $date = explode('-', $date);
    if (count($date) !== 3) return '';
    
    $jdf = new jDateTime(true, true, 'Asia/Tehran');
    return implode('-', $jdf->jalali_to_gregorian($date[0], $date[1], $date[2]));
}

/**
 * فرمت کردن مبلغ
 * @param float $amount مبلغ
 * @return string مبلغ فرمت شده با جداکننده هزارگان
 */
function formatAmount($amount) {
    return number_format($amount, 0, '.', ',');
}

/**
 * تبدیل عدد فارسی به انگلیسی
 * @param string $string
 * @return string
 */
function toEnglishNumber($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $num = range(0, 9);
    $string = str_replace($persian, $num, $string);
    $string = str_replace($arabic, $num, $string);
    return $string;
}

/**
 * تبدیل عدد انگلیسی به فارسی
 * @param string $string
 * @return string
 */
function toPersianNumber($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $num = range(0, 9);
    return str_replace($num, $persian, $string);
}

/**
 * پاکسازی داده ورودی
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * تولید توکن CSRF
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * بررسی توکن CSRF
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}

/**
 * ایجاد breadcrumb
 * @param array $items آرایه‌ای از آیتم‌ها با کلیدهای title و url
 * @return string
 */
function generateBreadcrumb($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($items as $key => $item) {
        if ($key === array_key_last($items)) {
            $html .= "<li class='breadcrumb-item active' aria-current='page'>{$item['title']}</li>";
        } else {
            $html .= "<li class='breadcrumb-item'><a href='{$item['url']}'>{$item['title']}</a></li>";
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

/**
 * بررسی دسترسی به API
 * @return bool
 */
function checkAPIAccess() {
    // بررسی هدر Authorization
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        return false;
    }
    
    // جدا کردن توکن از هدر
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    
    // بررسی اعتبار توکن
    // TODO: پیاده‌سازی سیستم بررسی توکن
    return true;
}

/**
 * ارسال پاسخ API
 * @param mixed $data داده‌های پاسخ
 * @param int $status کد وضعیت HTTP
 */
function sendAPIResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * ارسال خطای API
 * @param string $message پیام خطا
 * @param int $status کد وضعیت HTTP
 */
function sendAPIError($message, $status = 400) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'error' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}