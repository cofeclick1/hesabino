<?php
require_once '../includes/init.php';

// تابع تولید کد مشتری
function generateAndSaveCustomerCode()
{
    global $db;
    
    // دریافت تاریخ شمسی بدون تبدیل اعداد به فارسی
    $jalaliDate = jdate('Y-m-d', time(), '', 'Asia/Tehran', 'en');
    list($year, $month, $day) = explode('-', $jalaliDate);
    
    // ساخت کد با فرمت درخواستی
    $dateCode = $year . sprintf('%02d', $month) . sprintf('%02d', $day);
    
    try {
        $db->beginTransaction();
        
        // دریافت آخرین کد مشتری از جدول customers
        $lastCustomerCode = $db->query("SELECT MAX(customer_code) FROM customers WHERE customer_code LIKE '$dateCode%'")->fetchColumn();
        $lastNumber = $lastCustomerCode ? (int)substr($lastCustomerCode, -4) : 0;
        $newNumber = $lastNumber + 1;
        
        // ترکیب تاریخ و شماره سریال
        $newCustomerCode = $dateCode . sprintf('%04d', $newNumber);
        
        $db->commit();
        
        return $newCustomerCode;
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollback();
        }
        error_log("خطا در تولید کد مشتری: " . $e->getMessage());
        throw new Exception("خطا در تولید کد مشتری");
    }
}

try {
    // تولید کد مشتری
    $customerCode = generateAndSaveCustomerCode();

    echo json_encode([
        'status' => 'success',
        'customer_code' => $customerCode
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}