<?php
require_once '../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$category_id = (int)$_POST['category_id'];
if ($category_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid category ID']);
    exit;
}

$db = Database::getInstance();
$category = $db->query("SELECT * FROM categories WHERE id = ?", [$category_id])->fetch();

if (!$category) {
    http_response_code(404);
    echo json_encode(['error' => 'Category not found']);
    exit;
}

// حذف تصویر دسته‌بندی از سرور
if (!empty($category['image']) && file_exists($category['image'])) {
    unlink($category['image']);
}

// حذف دسته‌بندی و زیر دسته‌های آن از دیتابیس
$db->query("DELETE FROM categories WHERE id = ? OR parent_id = ?", [$category_id, $category_id]);

echo json_encode(['success' => 'Category deleted successfully']);