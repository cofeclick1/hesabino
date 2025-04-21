<?php
/**
 * تبدیل تاریخ میلادی به شمسی
 */
function toJalali($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    list($year, $month, $day) = explode('-', date('Y-m-d', $timestamp));
    $result = gregorian_to_jalali($year, $month, $day);
    return sprintf('%04d/%02d/%02d', $result[0], $result[1], $result[2]);
}

/**
 * تبدیل تاریخ شمسی به میلادی
 */
function toGregorian($date) {
    if (empty($date)) return null;
    $parts = explode('/', $date);
    if (count($parts) !== 3) return null;
    
    $result = jalali_to_gregorian($parts[0], $parts[1], $parts[2]);
    return sprintf('%04d-%02d-%02d', $result[0], $result[1], $result[2]);
}

/**
 * پاکسازی داده‌های ورودی
 */
function sanitize($input) {
    if (is_null($input)) return '';
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
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
    
    $divideRemaining = $sum % 11;
    $lastDigit = intval(substr($code, 9, 1));
    
    if ($divideRemaining < 2) {
        return ($divideRemaining == $lastDigit);
    }
    return ((11 - $divideRemaining) == $lastDigit);
}

/**
 * اعتبارسنجی شماره کارت
 */
function validateCardNumber($card) {
    if (!preg_match('/^[0-9]{16}$/', $card)) {
        return false;
    }
    
    $sum = 0;
    for ($i = 0; $i < 16; $i++) {
        $digit = intval(substr($card, $i, 1));
        if ($i % 2 == 0) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    
    return ($sum % 10 == 0);
}

/**
 * اعتبارسنجی شماره شبا
 */
function validateIBAN($iban) {
    if (!preg_match('/^IR[0-9]{24}$/', $iban)) {
        return false;
    }
    
    $iban = substr($iban, 2) . '1827';
    $remainder = '';
    
    for ($i = 0; $i < strlen($iban); $i++) {
        $remainder = ($remainder . substr($iban, $i, 1)) % 97;
    }
    
    return ($remainder == 1);
}

/**
 * تبدیل تاریخ شمسی به میلادی برای ذخیره در دیتابیس
 */
function convertJalaliToGregorian($jalaliDate) {
    if (empty($jalaliDate)) return null;
    
    $parts = explode('/', $jalaliDate);
    if (count($parts) !== 3) return null;
    
    $result = jalali_to_gregorian($parts[0], $parts[1], $parts[2]);
    return sprintf('%04d-%02d-%02d', $result[0], $result[1], $result[2]);
}