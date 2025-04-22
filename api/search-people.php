<?php
require_once '../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // بررسی پارامترهای ورودی
    $search = $_GET['q'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 10;

    if (empty($search)) {
        throw new Exception('پارامتر جستجو الزامی است');
    }

    // ساخت کوئری جستجو - اصلاح LIMIT و OFFSET
    $query = "
        SELECT 
            p.id,
            CONCAT(p.first_name, ' ', p.last_name) as text,
            p.mobile,
            p.company,
            p.national_code,
            p.email,
            p.profile_image,
            p.type
        FROM people p
        WHERE p.deleted_at IS NULL
        AND (
            p.first_name LIKE ? OR 
            p.last_name LIKE ? OR 
            p.mobile LIKE ? OR 
            p.company LIKE ? OR
            p.national_code LIKE ? OR
            CONCAT(p.first_name, ' ', p.last_name) LIKE ?
        )
        ORDER BY p.first_name ASC, p.last_name ASC
        LIMIT ? OFFSET ?
    ";

    $searchTerm = '%' . $search . '%';
    $offset = ($page - 1) * $perPage;
    
    // اصلاح پارامترها - تبدیل LIMIT و OFFSET به integer
    $params = [
        $searchTerm, // first_name
        $searchTerm, // last_name
        $searchTerm, // mobile
        $searchTerm, // company
        $searchTerm, // national_code
        $searchTerm, // full_name
        (int)$perPage,  // LIMIT
        (int)$offset    // OFFSET
    ];

    // اجرای کوئری و دریافت نتایج
    $results = $db->query($query, $params)->fetchAll();

    // شمارش کل نتایج برای صفحه‌بندی
    $countQuery = "
        SELECT COUNT(*) as total
        FROM people p
        WHERE p.deleted_at IS NULL
        AND (
            p.first_name LIKE ? OR 
            p.last_name LIKE ? OR 
            p.mobile LIKE ? OR 
            p.company LIKE ? OR
            p.national_code LIKE ? OR
            CONCAT(p.first_name, ' ', p.last_name) LIKE ?
        )
    ";
    
    // استفاده از 6 پارامتر اول برای کوئری شمارش
    $countParams = array_slice($params, 0, 6);
    $totalCount = $db->query($countQuery, $countParams)->fetch()['total'];

    // تبدیل نتایج به فرمت مناسب
    $items = array_map(function($person) {
        return [
            'id' => $person['id'],
            'text' => $person['text'],
            'mobile' => $person['mobile'],
            'national_code' => $person['national_code'],
            'company' => $person['company'],
            'email' => $person['email'],
            'type' => $person['type'],
            'avatar_path' => !empty($person['profile_image']) 
                ? BASE_PATH . '/' . $person['profile_image']
                : BASE_PATH . '/assets/images/avatar.png'
        ];
    }, $results);

    // ارسال پاسخ
    echo json_encode([
        'results' => $items,
        'pagination' => [
            'more' => ($totalCount > ($page * $perPage)),
            'total' => $totalCount,
            'page' => $page,
            'per_page' => $perPage
        ]
    ]);

} catch (Exception $e) {
    error_log('Search People Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'خطا در جستجوی اطلاعات: ' . $e->getMessage()
    ]);
}