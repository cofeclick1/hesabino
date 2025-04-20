<?php
require_once '../includes/init.php';

// پاک کردن هرگونه خروجی قبلی
ob_clean();

// تنظیم هدرهای مناسب
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

try {
    // بررسی وجود سشن
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('لطفاً دوباره وارد سیستم شوید');
    }

    // دریافت فاکتورهای امروز به همراه نام مشتری و محصول
    $sql = "SELECT 
                i.id,
                i.invoice_number,
                i.total_amount as total_price,
                i.created_at,
                c.name as customer_name,
                p.name as product_name,
                ii.quantity
            FROM invoices i
            JOIN invoice_items ii ON i.id = ii.invoice_id
            JOIN customers c ON i.customer_id = c.id
            JOIN products p ON ii.product_id = p.id
            WHERE DATE(i.created_at) = CURDATE()
            ORDER BY i.id DESC
            LIMIT 50";

    $invoices = $db->query($sql)->fetchAll();

    echo json_encode([
        'success' => true,
        'invoices' => $invoices
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// اطمینان از اینکه هیچ خروجی دیگری بعد از JSON نداریم
exit();