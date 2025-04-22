<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

try {
    // دریافت پارامترها
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perPage = 10;

    // ساخت کوئری
    $query = "SELECT 
                id,
                first_name,
                last_name,
                CONCAT(first_name, ' ', last_name) as full_name,
                mobile,
                COALESCE(company, '') as company,
                COALESCE(profile_image, 'assets/images/default-avatar.png') as avatar,
                type
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
    $items = $db->query($query, $params)->fetchAll();
    
    $results = [];
    foreach ($items as $item) {
        // اطمینان از مسیر کامل تصویر پروفایل
        $avatar = !empty($item['avatar']) ? BASE_PATH . '/' . $item['avatar'] : BASE_PATH . '/assets/images/default-avatar.png';
        
        // ساخت آیتم با فرمت مناسب برای Select2
        $result = [
            'id' => $item['id'],
            'text' => $item['full_name']
        ];
        
        // اضافه کردن شرکت به متن اگر وجود داشته باشد
        if (!empty($item['company'])) {
            $result['text'] .= ' (' . $item['company'] . ')';
        }
        
        // اضافه کردن اطلاعات اضافی
        if (!empty($item['mobile'])) {
            $result['mobile'] = $item['mobile'];
        }
        
        $result['avatar'] = $avatar;
        $result['type'] = $item['type'];
        
        $results[] = $result;
    }
    
    echo json_encode([
        'items' => $results,
        'total' => $total,
        'hasMore' => ($total > ($page * $perPage))
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'خطا در دریافت اطلاعات'
    ]);
}