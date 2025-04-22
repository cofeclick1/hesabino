<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

try {
    // دریافت پارامترها
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // محاسبه offset
    $offset = ($page - 1) * $perPage;
    
    // ساخت query اصلی
    $query = "
        SELECT 
            p.id,
            p.code,
            p.first_name,
            p.last_name,
            CONCAT(p.first_name, ' ', p.last_name) as full_name,
            p.mobile,
            p.phone,
            p.email,
            COALESCE(p.company, '') as company,
            COALESCE(p.profile_image, '') as avatar,
            p.type,
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ' : ') as categories
        FROM people p
        LEFT JOIN person_categories pc ON p.id = pc.person_id  
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE p.deleted_at IS NULL
    ";

    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (
            p.code LIKE ? OR
            p.first_name LIKE ? OR 
            p.last_name LIKE ? OR
            CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR
            p.mobile LIKE ? OR
            p.phone LIKE ? OR
            p.company LIKE ?
        )";
        $searchTerm = "%$search%";
        $params = array_fill(0, 7, $searchTerm);
    }

    $query .= " GROUP BY p.id ORDER BY p.code ASC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    // اجرای query
    $stmt = $db->prepare($query);
    foreach ($params as $i => $param) {
        $stmt->bindValue($i + 1, $param);
    }
    $stmt->execute();
    $people = $stmt->fetchAll();

    // محاسبه تعداد کل نتایج برای صفحه‌بندی
    $countQuery = "SELECT COUNT(DISTINCT p.id) as total FROM people p WHERE p.deleted_at IS NULL";
    if (!empty($search)) {
        $countQuery .= " AND (
            p.code LIKE ? OR
            p.first_name LIKE ? OR 
            p.last_name LIKE ? OR
            CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR
            p.mobile LIKE ? OR
            p.phone LIKE ? OR
            p.company LIKE ?
        )";
        $countParams = array_fill(0, 7, $searchTerm);
        $stmt = $db->prepare($countQuery);
        foreach ($countParams as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
    } else {
        $stmt = $db->prepare($countQuery);
    }
    $stmt->execute();
    $totalCount = $stmt->fetch()['total'];

    // آماده‌سازی نتایج
    $items = [];
    foreach ($people as $person) {
        $item = [
            'id' => $person['id'],
            'code' => $person['code'],
            'name' => $person['company'] ? $person['company'] . ' - ' . $person['full_name'] : $person['full_name'],
            'avatar' => !empty($person['avatar']) ? BASE_PATH . '/' . $person['avatar'] : BASE_PATH . '/assets/images/avatar.png',
            'mobile' => $person['mobile'],
            'phone' => $person['phone'],
            'email' => $person['email'],
            'type' => $person['type'],
            'categories' => $person['categories']
        ];
        $items[] = $item;
    }

    // ارسال پاسخ
    echo json_encode([
        'items' => $items,
        'total' => $totalCount,
        'has_more' => ($offset + $perPage) < $totalCount
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'خطا در دریافت اطلاعات'
    ]);
}