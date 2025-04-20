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
    $reason = $_POST['reason'] ?? null;

    if (!$invoiceId || !$reason) {
        throw new Exception('لطفا تمام فیلدهای ضروری را تکمیل کنید');
    }

    $db->beginTransaction();

    // دریافت اطلاعات فاکتور
    $invoice = $db->get('invoices', '*', ['id' => $invoiceId]);
    if (!$invoice) {
        throw new Exception('فاکتور مورد نظر یافت نشد');
    }

    if ($invoice['status'] === 'cancelled') {
        throw new Exception('این فاکتور قبلاً لغو شده است');
    }

    // دریافت آیتم‌های فاکتور
    $items = $db->query(
        "SELECT * FROM invoice_items WHERE invoice_id = ?",
        [$invoiceId]
    )->fetchAll();

    // برگرداندن موجودی محصولات
    foreach ($items as $item) {
        // به‌روزرسانی موجودی
        $db->query(
            "UPDATE products SET quantity = quantity + ? WHERE id = ?",
            [$item['quantity'], $item['product_id']]
        );

        // ثبت تراکنش انبار
        $db->insert('inventory_transactions', [
            'product_id' => $item['product_id'],
            'type' => 'in',
            'quantity' => $item['quantity'],
            'reference_type' => 'invoice_cancel',
            'reference_id' => $invoiceId,
            'description' => "برگشت به موجودی بابت لغو فاکتور شماره " . $invoice['invoice_number'],
            'created_by' => $_SESSION['user_id']
        ]);
    }

    // به‌روزرسانی وضعیت فاکتور
    $db->update('invoices',
        [
            'status' => 'cancelled',
            'description' => trim($invoice['description'] . "\n\nدلیل لغو: " . $reason)
        ],
        ['id' => $invoiceId]
    );

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'فاکتور با موفقیت لغو شد'
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