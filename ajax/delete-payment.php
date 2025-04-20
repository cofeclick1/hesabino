<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'درخواست نامعتبر']));
}

try {
    $db->beginTransaction();
    
    $paymentId = (int)$_POST['payment_id'];
    
    // دریافت اطلاعات پرداخت برای به‌روزرسانی مبلغ فاکتور
    $sql = "SELECT invoice_id, amount, payment_type FROM payments WHERE id = ?";
    $payment = $db->query($sql, [$paymentId])->fetch();
    
    if (!$payment) {
        throw new Exception('پرداخت مورد نظر یافت نشد');
    }
    
    // حذف اطلاعات تکمیلی بر اساس نوع پرداخت
    switch ($payment['payment_type']) {
        case 'card':
            $sql = "DELETE FROM payment_card_details WHERE payment_id = ?";
            $db->query($sql, [$paymentId]);
            break;
            
        case 'cheque':
            $sql = "DELETE FROM payment_cheque_details WHERE payment_id = ?";
            $db->query($sql, [$paymentId]);
            break;
            
        case 'installment':
            $sql = "DELETE FROM payment_installments WHERE payment_id = ?";
            $db->query($sql, [$paymentId]);
            break;
    }
    
    // حذف پرداخت
    $sql = "DELETE FROM payments WHERE id = ?";
    $db->query($sql, [$paymentId]);
    
    // به‌روزرسانی وضعیت پرداخت فاکتور
    $sql = "SELECT SUM(amount) as total_paid FROM payments WHERE invoice_id = ?";
    $totalPaid = $db->query($sql, [$payment['invoice_id']])->fetchColumn() ?: 0;
    
    $sql = "SELECT final_amount FROM invoices WHERE id = ?";
    $finalAmount = $db->query($sql, [$payment['invoice_id']])->fetchColumn();
    
    $newStatus = $totalPaid >= $finalAmount ? 'paid' : 
                ($totalPaid > 0 ? 'partial' : 'unpaid');
    
    $sql = "UPDATE invoices SET 
            payment_status = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $db->query($sql, [$newStatus, $payment['invoice_id']]);
    
    $db->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}