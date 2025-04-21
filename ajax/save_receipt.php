<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

// بررسی درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر']);
    exit;
}

// دریافت داده‌های اصلی دریافت
$receiptNumber = sanitize($_POST['receipt_number'] ?? '');
$date = sanitize($_POST['date'] ?? '');
$projectId = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
$currencyId = intval($_POST['currency_id'] ?? 1);
$description = sanitize($_POST['description'] ?? '');

// اعتبارسنجی داده‌ها
if (empty($receiptNumber) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'شماره و تاریخ دریافت الزامی است']);
    exit;
}

// تبدیل تاریخ شمسی به میلادی
$gregorianDate = convertJalaliToGregorian($date);
if (!$gregorianDate) {
    echo json_encode(['success' => false, 'message' => 'تاریخ نامعتبر است']);
    exit;
}

try {
    $db->beginTransaction();

    // بررسی تکراری نبودن شماره دریافت
    $existing = $db->query(
        "SELECT id FROM receipts WHERE receipt_number = ? AND deleted_at IS NULL",
        [$receiptNumber]
    )->fetch();

    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'این شماره دریافت قبلاً ثبت شده است']);
        exit;
    }

    // درج دریافت
    $receiptId = $db->insert('receipts', [
        'receipt_number' => $receiptNumber,
        'date' => $gregorianDate,
        'project_id' => $projectId,
        'currency_id' => $currencyId,
        'description' => $description,
        'status' => 'draft',
        'created_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // درج اقلام دریافت (اشخاص)
    if (isset($_POST['persons']) && is_array($_POST['persons'])) {
        foreach ($_POST['persons'] as $person) {
            if (!empty($person['person_id']) && !empty($person['amount'])) {
                $db->insert('receipt_items', [
                    'receipt_id' => $receiptId,
                    'person_id' => intval($person['person_id']),
                    'amount' => str_replace(',', '', $person['amount']),
                    'description' => sanitize($person['description'] ?? ''),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    // درج تراکنش‌ها
    if (isset($_POST['transactions']) && is_array($_POST['transactions'])) {
        foreach ($_POST['transactions'] as $transaction) {
            if (!empty($transaction['type']) && !empty($transaction['amount'])) {
                // درج تراکنش
                $transactionId = $db->insert('receipt_transactions', [
                    'receipt_id' => $receiptId,
                    'type' => $transaction['type'],
                    'amount' => str_replace(',', '', $transaction['amount']),
                    'reference' => sanitize($transaction['reference'] ?? ''),
                    'fee' => !empty($transaction['fee']) ? str_replace(',', '', $transaction['fee']) : 0,
                    'bank_id' => !empty($transaction['bank_id']) ? intval($transaction['bank_id']) : null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // درج اطلاعات چک در صورت وجود
                if ($transaction['type'] === 'cheque' && !empty($transaction['cheque_number'])) {
                    $db->insert('receipt_cheques', [
                        'transaction_id' => $transactionId,
                        'cheque_number' => sanitize($transaction['cheque_number']),
                        'cheque_date' => convertJalaliToGregorian($transaction['cheque_date']),
                        'issuing_bank_id' => !empty($transaction['issuing_bank_id']) ? intval($transaction['issuing_bank_id']) : null,
                        'bank_branch' => sanitize($transaction['bank_branch'] ?? ''),
                        'account_owner' => sanitize($transaction['account_owner'] ?? ''),
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    // محاسبه و بروزرسانی جمع کل دریافت
    $totalAmount = $db->query(
        "SELECT COALESCE(SUM(amount), 0) as total FROM receipt_items WHERE receipt_id = ?",
        [$receiptId]
    )->fetch()['total'];

    $db->update('receipts', 
        ['total_amount' => $totalAmount, 'status' => 'confirmed'],
        ['id' => $receiptId]
    );

    $db->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollback();
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در ثبت اطلاعات'
    ]);
}