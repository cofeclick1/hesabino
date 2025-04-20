<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'درخواست نامعتبر']));
}

try {
    $db->beginTransaction();
    
    $paymentId = (int)$_POST['payment_id'];
    
    // دریافت اطلاعات پرداخت
    $sql = "SELECT payment_type FROM payments WHERE id = ?";
    $paymentType = $db->query($sql, [$paymentId])->fetchColumn();
    
    if ($paymentType === 'cheque') {
        $sql = "UPDATE payment_cheque_details SET status = ? WHERE payment_id = ?";
        $db->query($sql, [$_POST['cheque_status'], $paymentId]);
        
    } elseif ($paymentType === 'installment' && isset($_POST['installment_status'])) {
        foreach ($_POST['installment_status'] as $installmentId => $status) {
            $sql = "UPDATE payment_installments SET status = ? WHERE id = ? AND payment_id = ?";
            $db->query($sql, [$status, $installmentId, $paymentId]);
        }
    }
    
    // به‌روزرسانی توضیحات پرداخت
    if (isset($_POST['description'])) {
        $sql = "UPDATE payments SET description = ? WHERE id = ?";
        $db->query($sql, [$_POST['description'], $paymentId]);
    }
    
    $db->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}