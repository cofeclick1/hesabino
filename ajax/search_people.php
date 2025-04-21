<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

// دریافت پارامترها
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// ساخت کوئری
$query = "SELECT id, CONCAT(first_name, ' ', last_name) as text, mobile,
                 COALESCE(profile_image, 'assets/images/default-avatar.png') as avatar
          FROM people
          WHERE deleted_at IS NULL";

$params = [];

if (!empty($search)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR mobile LIKE ? OR 
                     national_code LIKE ? OR company LIKE ? OR phone LIKE ?)";
    $searchParam = "%$search%";
    $params = array_fill(0, 6, $searchParam);
}

// محاسبه تعداد کل رکوردها
$countQuery = "SELECT COUNT(*) as total FROM (" . $query . ") as t";
$total = $db->query($countQuery, $params)->fetch()['total'];

// اعمال صفحه‌بندی
$offset = ($page - 1) * $perPage;
$query .= " LIMIT $perPage OFFSET $offset";

// دریافت نتایج
$results = $db->query($query, $params)->fetchAll();

// آماده‌سازی پاسخ
$response = [
    'items' => $results,
    'pagination' => [
        'more' => ($page * $perPage) < $total
    ]
];

echo json_encode($response);