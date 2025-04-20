<?php
require_once '../includes/init.php';
require_once '../includes/jdf.php'; // اضافه کردن کتابخانه تاریخ شمسی

header('Content-Type: application/json');

$period = $_GET['period'] ?? 'week';
$db = Database::getInstance();

switch($period) {
    case 'week':
        $query = "SELECT DATE(created_at) as date, SUM(final_amount) as total
                 FROM invoices 
                 WHERE status = 'confirmed'
                 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date";
        break;
    case 'month':
        $query = "SELECT DATE(created_at) as date, SUM(final_amount) as total
                 FROM invoices 
                 WHERE status = 'confirmed'
                 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date";
        break;
    case 'year':
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as date, SUM(final_amount) as total
                 FROM invoices 
                 WHERE status = 'confirmed'
                 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY date";
        break;
}

$result = $db->query($query)->fetchAll();

$data = [];
$labels = [];

foreach ($result as $row) {
    // تبدیل تاریخ میلادی به شمسی
    $timestamp = strtotime($row['date']);
    if ($period === 'year') {
        list($year, $month) = explode('-', $row['date']);
        $shamsi_date = gregorian_to_jalali($year, $month, 1);
        $labels[] = $shamsi_date[0] . '/' . str_pad($shamsi_date[1], 2, '0', STR_PAD_LEFT);
    } else {
        $shamsi_date = jdate("Y/m/d", $timestamp);
        $labels[] = $shamsi_date;
    }
    $data[] = (float)$row['total'];
}

echo json_encode([
    'success' => true,
    'data' => $data,
    'labels' => $labels
]);