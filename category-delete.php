<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = (int)$_POST['category_id'];
    
    // بررسی وجود دسته‌بندی
    $db = Database::getInstance();
    $category = $db->query("SELECT * FROM categories WHERE id = ?", [$category_id])->fetch();
    if (!$category) {
        exit(json_encode(['error' => 'دسته‌بندی یافت نشد.']));
    }
    
    // حذف دسته‌بندی
    if ($db->delete('categories', 'id = ?', [$category_id])) {
        exit(json_encode(['success' => 'دسته‌بندی با موفقیت حذف شد.']));
    } else {
        exit(json_encode(['error' => 'خطا در حذف دسته‌بندی. لطفاً دوباره تلاش کنید.']));
    }
}

exit(json_encode(['error' => 'Invalid request.']));