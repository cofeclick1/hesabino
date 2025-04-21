<?php
require_once '../includes/init.php';

// بررسی درخواست Ajax
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    exit('Direct access not permitted');
}

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
            p.full_name as text,
            p.mobile,
            p.avatar_path,
            p.national_code,
            COALESCE(
                (SELECT SUM(amount) FROM transactions WHERE person_id = p.id AND type = 'debit'),
                0
            ) as total_debit,
            COALESCE(
                (SELECT SUM(amount) FROM transactions WHERE person_id = p.id AND type = 'credit'),
                0
            ) as total_credit
        FROM people p
        WHERE p.deleted_at IS NULL
        AND (
            p.full_name LIKE ? OR 
            p.mobile LIKE ? OR 
            p.national_code LIKE ?
        )
        ORDER BY p.full_name
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
        // محاسبه مانده حساب
        $person['balance'] = $person['total_credit'] - $person['total_debit'];
        
        // اضافه کردن مسیر آواتار
        if (!empty($person['avatar_path'])) {
            $person['avatar_path'] = BASE_PATH . '/uploads/avatars/' . $person['avatar_path'];
        } else {
            $person['avatar_path'] = BASE_PATH . '/assets/images/default-avatar.png';
        }
        
        $people[] = $person;
    }
    
    // بررسی وجود نتایج بیشتر
    $totalQuery = "
        SELECT COUNT(*) as total
        FROM people
        WHERE deleted_at IS NULL
        AND (
            full_name LIKE ? OR 
            mobile LIKE ? OR 
            national_code LIKE ?
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
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'خطا در جستجوی اطلاعات: ' . $e->getMessage()
    ]);
}