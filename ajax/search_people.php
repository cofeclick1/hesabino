<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

try {
    // دریافت پارامترها
    $search = isset($_GET['q']) ? trim($_GET['q']) : ''; // تغییر از search به q
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perPage = 10;

    // ساخت پارامترهای جستجو
    $params = [];
    $whereClause = "WHERE deleted_at IS NULL";
    
    if (!empty($search)) {
        $whereClause .= " AND (
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            mobile LIKE ? OR 
            company LIKE ? OR
            national_code LIKE ? OR
            CONCAT(first_name, ' ', last_name) LIKE ?
        )";
        $searchTerm = "%{$search}%";
        $params = array_fill(0, 6, $searchTerm);
    }

    // شمارش کل نتایج - بدون subquery
    $countQuery = "SELECT COUNT(*) as total FROM people {$whereClause}";
    $total = $db->query($countQuery, $params)->fetch(PDO::FETCH_ASSOC)['total'];

    // محاسبه offset برای صفحه‌بندی
    $offset = ($page - 1) * $perPage;

    // کوئری اصلی با LIMIT و OFFSET
    $query = "
        SELECT 
            id,
            first_name,
            last_name,
            CONCAT(first_name, ' ', last_name) as full_name,
            mobile,
            national_code,
            COALESCE(company, '') as company,
            type,
            profile_image
        FROM people 
        {$whereClause}
        ORDER BY first_name, last_name 
        LIMIT {$perPage} OFFSET {$offset}
    ";

    // دریافت نتایج
    $items = $db->query($query, $params)->fetchAll(PDO::FETCH_ASSOC);

    // تبدیل نتایج به فرمت مناسب
    $results = [];
    foreach ($items as $item) {
        $avatar = !empty($item['profile_image']) 
            ? rtrim(BASE_PATH, '/') . '/' . ltrim($item['profile_image'], '/') 
            : BASE_PATH . '/assets/images/default-avatar.png';

        $result = [
            'id' => $item['id'],
            'text' => $item['full_name'],
            'first_name' => $item['first_name'],
            'last_name' => $item['last_name'],
            'mobile' => $item['mobile'],
            'national_code' => $item['national_code'],
            'company' => $item['company'],
            'type' => $item['type'],
            'avatar_path' => $avatar
        ];

        if (!empty($item['company'])) {
            $result['text'] .= ' (' . $item['company'] . ')';
        }

        $results[] = $result;
    }

    // ارسال پاسخ
    echo json_encode([
        'success' => true,
        'results' => $results,
        'pagination' => [
            'more' => ($total > ($page * $perPage)),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ]
    ]);

} catch (Exception $e) {
    error_log('Search People Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => 'خطا در جستجوی اطلاعات: ' . $e->getMessage()
    ]);
}