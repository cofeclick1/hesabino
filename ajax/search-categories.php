<?php
require_once '../../includes/init.php';

// بررسی درخواست Ajax
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('دسترسی مستقیم امکان پذیر نیست');
}

// دریافت پارامترها
$search = $_GET['q'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $db = Database::getInstance();
    
    // شمارش کل نتایج برای صفحه‌بندی
    $countQuery = "SELECT COUNT(*) as total FROM categories WHERE status = 'active'";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (name LIKE ? OR description LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    $totalCount = $db->query($countQuery, $params)->fetch()['total'];

    // دریافت دسته‌بندی‌ها
    $query = "SELECT id, name, description, parent_id 
              FROM categories 
              WHERE status = 'active'";
    
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR description LIKE ?)";
    }
    
    $query .= " ORDER BY name LIMIT {$limit} OFFSET {$offset}";
    
    $categories = $db->query($query, $params)->fetchAll();

    // تبدیل به فرمت مورد نیاز Select2
    $items = [];
    foreach ($categories as $category) {
        $items[] = [
            'id' => $category['id'],
            'text' => $category['name'],
            'description' => $category['description'],
            'parent_id' => $category['parent_id']
        ];
    }

    // ارسال پاسخ
    header('Content-Type: application/json');
    echo json_encode([
        'items' => $items,
        'total_count' => $totalCount,
        'pagination' => [
            'more' => ($offset + $limit) < $totalCount
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'خطا در دریافت اطلاعات دسته‌بندی‌ها'
    ]);
}