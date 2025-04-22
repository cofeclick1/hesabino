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
    // دریافت پارامترهای جستجو
    $search = $_GET['q'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = 15;
    $offset = ($page - 1) * $limit;

    $db = Database::getInstance();

    // ساخت کوئری جستجو
    $query = "SELECT 
                c.id,
                c.name,
                c.description,
                c.parent_id,
                p.name as parent_name
              FROM categories c
              LEFT JOIN categories p ON c.parent_id = p.id
              WHERE c.status = 'active'
              AND c.deleted_at IS NULL";
    
    $params = [];
    
    // اضافه کردن شرط جستجو
    if (!empty($search)) {
        $query .= " AND (
            c.name LIKE ? OR 
            c.description LIKE ? OR
            c.code LIKE ?
        )";
        $searchTerm = "%{$search}%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }

    // دریافت تعداد کل نتایج
    $countQuery = str_replace("SELECT c.id, c.name, c.description, c.parent_id, p.name as parent_name", "SELECT COUNT(*) as total", $query);
    $totalCount = $db->query($countQuery, $params)->fetch()['total'];

    // اضافه کردن مرتب‌سازی و محدودیت
    $query .= " ORDER BY 
                CASE WHEN c.parent_id IS NULL THEN 0 ELSE 1 END, 
                c.sort_order ASC, 
                c.name ASC 
                LIMIT {$limit} OFFSET {$offset}";

    $categories = $db->query($query, $params)->fetchAll();

    // تبدیل به فرمت مورد نیاز Select2
    $items = [];
    foreach ($categories as $category) {
        // ساخت متن کامل دسته‌بندی
        $text = $category['name'];
        if (!empty($category['parent_name'])) {
            $text = $category['parent_name'] . ' > ' . $text;
        }

        $items[] = [
            'id' => $category['id'],
            'text' => $text,
            'description' => $category['description'],
            'parent_id' => $category['parent_id']
        ];
    }

    // ارسال پاسخ
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
        'message' => 'خطا در دریافت اطلاعات: ' . $e->getMessage()
    ]);
}