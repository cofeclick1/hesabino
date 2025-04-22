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
 * دریافت مقدار قبلی فیلد فرم
 */
function old($key, $default = '') {
    if (isset($_POST[$key])) {
        return htmlspecialchars($_POST[$key]);
    }
    if (isset($_GET[$key])) {
        return htmlspecialchars($_GET[$key]);
    }
    return $default;
}
/**
 * انتقال به صفحه دیگر
 */
function redirect($url) {
    // اگر url با / شروع نشده باشد، BASE_PATH را اضافه می‌کنیم
    if (strpos($url, '/') !== 0 && strpos($url, 'http') !== 0) {
        $url = BASE_PATH . '/' . $url;
    }
    header('Location: ' . $url);
    exit;
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

/**
 * فرمت کردن مبلغ
 */
function formatMoney($amount) {
    return number_format($amount, 0, '', ',');
}

/**
 * حذف فرمت از مبلغ
 */
function unformatMoney($amount) {
    return (int)str_replace(',', '', $amount);
}

/**
 * فرمت کردن تاریخ جلالی
 */
function formatJalaliDate($date) {
    if (empty($date)) return '';
    return toJalali($date);
}

/**
 * تبدیل عدد به حروف فارسی
 */
function numberToWords($number) {
    $ones = [
        0 => '',
        1 => 'یک',
        2 => 'دو',
        3 => 'سه',
        4 => 'چهار',
        5 => 'پنج',
        6 => 'شش',
        7 => 'هفت',
        8 => 'هشت',
        9 => 'نه'
    ];
    
    $tens = [
        0 => '',
        1 => 'ده',
        2 => 'بیست',
        3 => 'سی',
        4 => 'چهل',
        5 => 'پنجاه',
        6 => 'شصت',
        7 => 'هفتاد',
        8 => 'هشتاد',
        9 => 'نود'
    ];
    
    $teens = [
        11 => 'یازده',
        12 => 'دوازده',
        13 => 'سیزده',
        14 => 'چهارده',
        15 => 'پانزده',
        16 => 'شانزده',
        17 => 'هفده',
        18 => 'هجده',
        19 => 'نوزده'
    ];
    
    $hundreds = [
        0 => '',
        1 => 'صد',
        2 => 'دویست',
        3 => 'سیصد',
        4 => 'چهارصد',
        5 => 'پانصد',
        6 => 'ششصد',
        7 => 'هفتصد',
        8 => 'هشتصد',
        9 => 'نهصد'
    ];
    
    $thousands = [
        0 => '',
        1 => 'هزار',
        2 => 'میلیون',
        3 => 'میلیارد',
        4 => 'تریلیون'
    ];
    
    if ($number === 0) {
        return 'صفر';
    }
    
    $formattedNumber = number_format($number, 0, '', ',');
    $groups = explode(',', $formattedNumber);
    $groupCount = count($groups);
    $output = '';
    
    foreach ($groups as $index => $group) {
        $group = (int)$group;
        if ($group === 0) {
            continue;
        }
        
        $groupOutput = '';
        $h = floor($group / 100);
        $t = floor(($group % 100) / 10);
        $o = $group % 10;
        
        if ($h > 0) {
            $groupOutput .= $hundreds[$h] . ' ';
        }
        
        if ($t === 1 && $o > 0) {
            $groupOutput .= $teens[10 + $o] . ' ';
        } else {
            if ($t > 0) {
                $groupOutput .= $tens[$t] . ' ';
            }
            if ($o > 0) {
                $groupOutput .= $ones[$o] . ' ';
            }
        }
        
        $remaining = $groupCount - $index - 1;
        if ($remaining > 0) {
            $groupOutput .= $thousands[$remaining] . ' ';
        }
        
        $output .= $groupOutput;
    }
    
    return trim($output);
}

/**
 * تبدیل مبلغ به حروف با پسوند ریال/تومان
 */
function moneyToWords($amount, $unit = 'ریال') {
    if ($amount == 0) {
        return 'صفر ' . $unit;
    }
    
    return numberToWords($amount) . ' ' . $unit;
}

/**
 * ایجاد شماره سند یکتا
 */
function generateDocumentNumber($prefix = '') {
    $date = date('Ymd');
    $random = mt_rand(1000, 9999);
    return $prefix . $date . $random;
}