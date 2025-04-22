<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

try {
    // دریافت پارامترها
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    
    // محاسبه offset
    $offset = ($page - 1) * $perPage;
    
    // ساخت query
    $params = [];
    $where = ['p.deleted_at IS NULL'];
    
    if (!empty($search)) {
        $where[] = "(
            p.first_name LIKE :search 
            OR p.last_name LIKE :search 
            OR CONCAT(p.first_name, ' ', p.last_name) LIKE :search
            OR p.code LIKE :search
            OR p.mobile LIKE :search
            OR p.national_code LIKE :search
            OR p.company LIKE :search
        )";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($category)) {
        $where[] = "pc.category_id = :category";
        $params[':category'] = $category;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // کوئری اصلی
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
            p.national_code,
            p.company,
            COALESCE(p.profile_image, '') as avatar,
            p.type,
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ' : ') as categories
        FROM people p
        LEFT JOIN person_categories pc ON p.id = pc.person_id
        LEFT JOIN categories c ON pc.category_id = c.id
        $whereClause
        GROUP BY p.id
        ORDER BY p.code ASC, p.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    // اضافه کردن پارامترهای limit و offset
    $params[':limit'] = $perPage;
    $params[':offset'] = $offset;
    
    // اجرای کوئری
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        if (in_array($key, [':limit', ':offset'])) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $people = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // شمارش کل رکوردها
    $countQuery = "
        SELECT COUNT(DISTINCT p.id) as total 
        FROM people p
        LEFT JOIN person_categories pc ON p.id = pc.person_id
        $whereClause
    ";
    
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        if (!in_array($key, [':limit', ':offset'])) {
            $countStmt->bindValue($key, $value);
        }
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // آماده‌سازی نتایج
    $items = [];
    foreach ($people as $person) {
        $item = [
            'id' => $person['id'],
            'code' => $person['code'],
            'name' => $person['full_name'],
            'company' => $person['company'],
            'mobile' => $person['mobile'],
            'phone' => $person['phone'],
            'email' => $person['email'],
            'categories' => $person['categories'],
            'type' => $person['type']
        ];

        // تنظیم آواتار
        if (!empty($person['avatar']) && file_exists(ROOT_PATH . '/' . $person['avatar'])) {
            $item['avatar'] = BASE_PATH . '/' . $person['avatar'];
        } else {
            $item['avatar'] = BASE_PATH . '/assets/images/avatar.png';
        }

        $items[] = $item;
    }
    
    // ارسال پاسخ
    echo json_encode([
        'items' => $items,
        'total' => (int)$totalCount,
        'page' => $page,
        'per_page' => $perPage,
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