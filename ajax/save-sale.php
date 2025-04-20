<?php
require_once '../includes/init.php';

// تنظیم هدر
header('Content-Type: application/json; charset=utf-8');

try {
    // بررسی وجود پارامتر محصول
    if (!isset($_POST['product_id'])) {
        throw new Exception('شناسه محصول وجود ندارد');
    }

    $productId = intval($_POST['product_id']);

    if ($productId <= 0) {
        throw new Exception('شناسه محصول نامعتبر است');
    }

    // دریافت اطلاعات محصول
    $product = $db->get('products', '*', ['id' => $productId, 'status' => 'active']);
    if (!$product) {
        throw new Exception('محصول یافت نشد یا غیرفعال است');
    }

    // بررسی موجودی
    if ($product['quantity'] <= 0) {
        throw new Exception('موجودی کافی نیست');
    }

    // کاهش موجودی محصول
    $updateResult = $db->update('products', 
        ['quantity' => $product['quantity'] - 1],
        ['id' => $productId]
    );

    if (!$updateResult) {
        throw new Exception('خطا در به‌روزرسانی موجودی');
    }

    // ثبت فروش (ایجاد فاکتور)
    $invoiceResult = $db->insert('invoices', [
        'customer_id' => 0, // مشتری متفرقه
        'total_amount' => $product['sale_price'],
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);

    if (!$invoiceResult) {
        throw new Exception('خطا در ثبت فروش');
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'فروش با موفقیت ثبت شد'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}