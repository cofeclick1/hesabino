<?php
require_once '../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Get product ID from POST
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($product_id <= 0) {
        throw new Exception('شناسه محصول نامعتبر است');
    }

    // Get product details
    $product = $db->query("
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.status = 'active'
    ", [$product_id])->fetch();

    if (!$product) {
        throw new Exception('محصول یافت نشد');
    }

    // Format product data
    $formatted_product = [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'code' => $product['code'],
        'sale_price' => (float)$product['sale_price'],
        'quantity' => (int)$product['quantity'],
        'min_quantity' => (int)$product['min_quantity'],
        'category_id' => (int)$product['category_id'],
        'category_name' => $product['category_name'],
        'stock_status' => ($product['quantity'] > 0) ? $product['quantity'] . ' عدد' : 'ناموجود'
    ];

    echo json_encode([
        'success' => true,
        'product' => $formatted_product
    ]);

} catch (Exception $e) {
    error_log("Product fetch error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}