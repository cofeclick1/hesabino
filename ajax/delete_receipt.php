<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

// بررسی درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر']);
    exit;
}

$receiptId = intval($_POST['receipt_id'] ?? 0);

if ($receiptId <= 0) {
    echo json_encode(['success' => false, 'message' => 'شناسه دریافت نامعتبر است']);
    exit;
}

try {
    $db->beginTransaction();

    // بررسی وجود دریافت
    $receipt = $db->query(
        "SELECT id, status FROM receipts WHERE id = ? AND deleted_at IS NULL",
        [$receiptId]
    )->fetch();

    if (!$receipt) {
        echo json_encode(['success' => false, 'message' => 'دریافت مورد نظر یافت نشد']);
        exit;
    }

    // حذف نرم دریافت
    $db->update('receipts',
        ['deleted_at' => date('Y-m-d H:i:s')],
        ['id' => $receiptId]
    );

    $db->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollback();
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در حذف اطلاعات'
    ]);
}