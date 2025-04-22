<?php
require_once '../../includes/init.php';

// بررسی درخواست Ajax
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode([
        'error' => true,
        'message' => 'دسترسی مستقیم مجاز نیست'
    ]));
}

// بررسی دسترسی کاربر
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode([
        'error' => true,
        'message' => 'لطفاً وارد حساب کاربری خود شوید'
    ]));
}

try {
    // دریافت شناسه دسته‌بندی
    $categoryId = (int)$_POST['category_id'];
    if ($categoryId <= 0) {
        throw new Exception('دسته‌بندی نامعتبر است');
    }

    $db = Database::getInstance();

    // بررسی وجود دسته‌بندی
    $category = $db->query("
        SELECT id, code 
        FROM categories 
        WHERE id = ? AND status = 'active' AND deleted_at IS NULL
    ", [$categoryId])->fetch();

    if (!$category) {
        throw new Exception('دسته‌بندی مورد نظر یافت نشد');
    }

    // دریافت آخرین شماره سریال برای این دسته‌بندی
    $lastSerial = $db->query("
        SELECT COALESCE(MAX(CAST(SUBSTRING(store_barcode, -6) AS UNSIGNED)), 0) as last_serial
        FROM products 
        WHERE category_id = ? 
        AND store_barcode IS NOT NULL 
        AND store_barcode != ''
    ", [$categoryId])->fetch()['last_serial'];

    // تولید بارکد جدید
    $nextSerial = str_pad($lastSerial + 1, 6, '0', STR_PAD_LEFT);
    $categoryCode = str_pad($category['code'], 3, '0', STR_PAD_LEFT);
    $storeBarcode = date('y') . $categoryCode . $nextSerial;

    // محاسبه Check Digit با الگوریتم EAN-13
    $sum = 0;
    for ($i = 0; $i < strlen($storeBarcode); $i++) {
        $digit = (int)$storeBarcode[$i];
        $sum += $i % 2 ? $digit * 3 : $digit;
    }
    $checkDigit = (10 - ($sum % 10)) % 10;
    
    // اضافه کردن Check Digit به بارکد
    $finalBarcode = $storeBarcode . $checkDigit;

    // ارسال پاسخ
    echo json_encode([
        'success' => true,
        'barcode' => $finalBarcode
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}