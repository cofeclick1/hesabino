<?php
require_once '../includes/init.php';

// حذف خروجی‌های قبلی
ob_clean();

// تنظیم header
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
        throw new Exception('شناسه محصول ارسال نشده است');
    }

    $productId = (int)$_GET['product_id'];
    
    if ($productId <= 0) {
        throw new Exception('شناسه محصول نامعتبر است');
    }

    // استفاده از متد get کلاس Database برای دریافت قیمت محصول
    // از ستون sale_price به جای price استفاده می‌کنیم
    $product = $db->get('products', 'sale_price', ['id' => $productId]);
    
    if (!$product || !isset($product['sale_price'])) {
        throw new Exception('محصول یافت نشد');
    }

    echo json_encode([
        'status' => 'success',
        'price' => (float)$product['sale_price']
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطا در بازیابی اطلاعات: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}