<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM products WHERE id = ?", [$product_id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// حذف تصویر محصول از سرور
if (!empty($product['image']) && file_exists($product['image'])) {
    unlink($product['image']);
}

// حذف محصول از دیتابیس
$db->query("DELETE FROM products WHERE id = ?", [$product_id]);

echo json_encode(['success' => 'Product deleted successfully']);