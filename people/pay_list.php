<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!$auth->hasPermission('payments_view') && !$_SESSION['is_super_admin']) {
    $_SESSION['error'] = 'شما دسترسی لازم برای مشاهده این صفحه را ندارید';
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

// پارامترهای فیلتر
$filters = [
    'search' => $_GET['search'] ?? '',
    'from_date' => $_GET['from_date'] ?? '',
    'to_date' => $_GET['to_date'] ?? '',
    'project_id' => $_GET['project_id'] ?? '',
    'currency_code' => $_GET['currency_code'] ?? '',
    'status' => $_GET['status'] ?? '',
    'sort' => $_GET['sort'] ?? 'date',
    'order' => $_GET['order'] ?? 'desc',
    'page' => max(1, intval($_GET['page'] ?? 1)),
    'per_page' => 20
];

// پارامترهای کوئری
$params = [];
$whereConditions = ['1=1']; // شرط پیش‌فرض که همیشه درسته

// اعمال فیلترها به شرط‌ها
if (!empty($filters['search'])) {
    $whereConditions[] = "(
        p.document_number LIKE ? OR
        p.description LIKE ?
    )";
    $searchTerm = '%' . $filters['search'] . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm]);
}

if (!empty($filters['from_date'])) {
    $whereConditions[] = "p.date >= ?";
    $params[] = $filters['from_date'];
}

if (!empty($filters['to_date'])) {
    $whereConditions[] = "p.date <= ?";
    $params[] = $filters['to_date'];
}

if (!empty($filters['project_id'])) {
    $whereConditions[] = "p.project_id = ?";
    $params[] = $filters['project_id'];
}

if (!empty($filters['currency_code'])) {
    $whereConditions[] = "p.currency_code = ?";
    $params[] = $filters['currency_code'];
}

if (!empty($filters['status'])) {
    $whereConditions[] = "p.status = ?";
    $params[] = $filters['status'];
}

// ساخت WHERE clause
$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// مرتب‌سازی
$validSortColumns = ['date', 'document_number', 'total_amount', 'status'];
$sort = in_array($filters['sort'], $validSortColumns) ? $filters['sort'] : 'date';
$order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
$orderClause = "ORDER BY p.{$sort} {$order}";

try {
    // کوئری شمارش کل رکوردها
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM payments p
        {$whereClause}
    ";

    $totalRows = $db->query($countQuery, $params)->fetch()['total'];
    $totalPages = ceil($totalRows / $filters['per_page']);

    // صفحه‌بندی
    $offset = ($filters['page'] - 1) * $filters['per_page'];
    $limitClause = "LIMIT {$filters['per_page']} OFFSET {$offset}";

    // کوئری اصلی
    $query = "
        SELECT 
            p.*,
            c.symbol as currency_symbol,
            c.name as currency_name,
            pr.name as project_name,
            pr.logo_path as project_logo
        FROM payments p
        LEFT JOIN currencies c ON p.currency_code = c.code
        LEFT JOIN projects pr ON p.project_id = pr.id
        {$whereClause}
        {$orderClause}
        {$limitClause}
    ";

    // دریافت لیست پرداخت‌ها
    $payments = $db->query($query, $params)->fetchAll();

    // دریافت لیست پروژه‌ها برای فیلتر
    $projects = $db->query("
        SELECT id, name, logo_path 
        FROM projects 
        WHERE status = 'active' 
        ORDER BY name
    ")->fetchAll();

    // دریافت لیست ارزها برای فیلتر
    $currencies = $db->query("
        SELECT code, symbol, name 
        FROM currencies 
        WHERE is_active = 1 
        ORDER BY is_default DESC, name
    ")->fetchAll();

} catch (Exception $e) {
    // در صورت خطا در کوئری‌ها
    $_SESSION['error'] = 'خطا در دریافت اطلاعات: ' . $e->getMessage();