<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

// دریافت پارامترها
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

try {
    // ساخت کوئری
    $query = "SELECT 
                id,
                CONCAT(first_name, ' ', last_name) as text,
                mobile,
                COALESCE(company, '') as company,
                COALESCE(profile_image, 'assets/images/default-avatar.png') as avatar
              FROM people 
              WHERE deleted_at IS NULL";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            mobile LIKE ? OR 
            company LIKE ? OR
            national_code LIKE ?
        )";
        $searchTerm = "%$search%";
        $params = array_fill(0, 5, $searchTerm);
    }
    
    // محاسبه تعداد کل نتایج
    $countQuery = "SELECT COUNT(*) as total FROM ($query) as t";
    $total = $db->query($countQuery, $params)->fetch()['total'];
    
    // اعمال صفحه‌بندی
    $offset = ($page - 1) * $perPage;
    $query .= " ORDER BY first_name, last_name LIMIT $perPage OFFSET $offset";
    
    // دریافت نتایج
    $results = $db->query($query, $params)->fetchAll();
    
    // آماده‌سازی نتایج برای Select2
    foreach ($results as &$result) {
        // اضافه کردن شرکت به متن اگر وجود داشته باشد
        if (!empty($result['company'])) {
            $result['text'] .= ' (' . $result['company'] . ')';
        }
        
        // اطمینان از مسیر کامل تصویر پروفایل
        if (!empty($result['avatar']) && !str_starts_with($result['avatar'], 'http')) {
            $result['avatar'] = BASE_PATH . '/' . $result['avatar'];
        }
    }
    
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => ($total > ($page * $perPage))
        ]
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'error' => 'خطا در دریافت اطلاعات',
        'results' => [],
        'pagination' => ['more' => false]
    ]);
}