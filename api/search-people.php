<?php
require_once '../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // دریافت پارامترهای جستجو
    $search = $_GET['search'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // ساخت کوئری جستجو
    $query = "
        SELECT 
            p.id,
            CONCAT(p.first_name, ' ', p.last_name) as text,
            p.mobile,
            p.profile_image as avatar_path,
            p.national_code
        FROM people p
        WHERE p.deleted_at IS NULL
        AND (
            CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR 
            p.mobile LIKE ? OR 
            p.national_code LIKE ?
        )
        ORDER BY p.first_name, p.last_name
        LIMIT ? OFFSET ?
    ";
    
    // اجرای کوئری با پارامترها
    $searchTerm = "%{$search}%";
    $stmt = $db->query($query, [
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $limit,
        $offset
    ]);
    
    // دریافت نتایج
    $people = [];
    while ($person = $stmt->fetch()) {
        // اضافه کردن مسیر آواتار
        if (!empty($person['avatar_path'])) {
            $person['avatar_path'] = BASE_PATH . '/uploads/profiles/' . $person['avatar_path'];
        } else {
            $person['avatar_path'] = BASE_PATH . '/assets/images/avatar.png';
        }
        
        $people[] = $person;
    }
    
    // بررسی وجود نتایج بیشتر
    $totalQuery = "
        SELECT COUNT(*) as total
        FROM people p
        WHERE p.deleted_at IS NULL
        AND (
            CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR 
            p.mobile LIKE ? OR 
            p.national_code LIKE ?
        )
    ";
    
    $totalStmt = $db->query($totalQuery, [
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);
    $total = $totalStmt->fetch()['total'];
    
    // ساخت پاسخ
    $response = [
        'items' => $people,
        'total' => $total,
        'hasMore' => ($offset + $limit) < $total
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Error in search-people.php: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'خطا در جستجوی اطلاعات'
    ]);
}