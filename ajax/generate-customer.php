<?php
require_once '../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // دریافت تاریخ به فرمت مورد نظر
    $jYear = jdate('Y'); // سال شمسی
    $jMonth = jdate('m'); // ماه شمسی
    $jDay = jdate('d'); // روز شمسی
    
    // ساخت کد منحصر به فرد مشتری
    $dateCode = $jYear . $jMonth . $jDay;
    
    // پیدا کردن آخرین شماره برای امروز
    $sql = "SELECT MAX(SUBSTRING(name, -4)) as last_number 
            FROM customers 
            WHERE name LIKE ?";
    $lastNumber = $db->query($sql, ["فروش سریع " . $dateCode . '%'])->fetchColumn();
    
    // ساخت شماره جدید
    $newNumber = str_pad(($lastNumber ? $lastNumber + 1 : 1), 4, '0', STR_PAD_LEFT);
    $customerName = "فروش سریع " . $dateCode . $newNumber;
    
    // ایجاد مشتری جدید
    $db->insert('customers', [
        'name' => $customerName,
        'mobile' => '0000000000',
        'email' => null,
        'address' => null,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $customerId = $db->lastInsertId();
    
    // برگرداندن اطلاعات مشتری
    echo json_encode([
        'status' => 'success',
        'customer' => [
            'id' => $customerId,
            'name' => $customerName
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطا در ایجاد مشتری جدید: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}