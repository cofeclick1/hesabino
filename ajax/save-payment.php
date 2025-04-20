<?php
require_once '../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    die(json_encode([
        'status' => 'error',
        'message' => 'لطفا وارد حساب کاربری خود شوید'
    ]));
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('درخواست نامعتبر');
    }

    $invoiceId = $_POST['invoice_id'] ?? null;
    $amount = $_POST['amount'] ?? 0;
    $paymentMethod = $_POST['payment_method'] ?? null;
    $referenceNumber = $_POST['reference_number'] ?? null;
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $description = $_POST['description'] ?? null;

    if (!$invoiceId || !$amount || !$paymentMethod || !$paymentDate) {
        throw new Exception('لطفا تمام فیلدهای ضروری را تکمیل کنید');
    }

    $db->beginTransaction();

    // دریافت اطلاعات فاکتور
    $invoice = $db->get('invoices', '*', ['id' => $invoiceId]);
    if (!$invoice) {
        throw new Exception('فاکتور مورد نظر یافت نشد');
    }

    if ($invoice['status'] === 'cancelled') {
        throw new Exception('امکان ثبت پرداخت برای فاکتور لغو شده وجود ندارد');
    }

    // بررسی مبلغ پرداختی
    $totalPaid = $db->query(
        "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = ?",
        [$invoiceId]
    )->fetchColumn();

    $remainingAmount = $invoice['final_amount'] - $totalPaid;

    if ($amount > $remainingAmount) {
        throw new Exception('مبلغ پرداختی نمی‌تواند بیشتر از مبلغ باقی‌مانده باشد');
    }

    // ثبت پرداخت
    $db->insert('payments', [
        'invoice_id' => $invoiceId,
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'reference_number' => $referenceNumber,
        'payment_date' => $paymentDate,
        'description' => $description,
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // به‌روزرسانی وضعیت پرداخت فاکتور
    $newTotalPaid = $totalPaid + $amount;
    $paymentStatus = 'partial';
    
    if ($newTotalPaid >= $invoice['final_amount']) {
        $paymentStatus = 'paid';
    } elseif ($newTotalPaid <= 0) {
        $paymentStatus = 'unpaid';
    }

    $db->update('invoices',
        ['payment_status' => $paymentStatus],
        ['id' => $invoiceId]
    );

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'پرداخت با موفقیت ثبت شد'
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}