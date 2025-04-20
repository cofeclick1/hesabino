<?php
require_once '../includes/init.php';

if (!$auth->isLoggedIn()) {
    die(json_encode([
        'status' => 'error',
        'message' => 'دسترسی غیرمجاز'
    ]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode([
        'status' => 'error',
        'message' => 'درخواست نامعتبر'
    ]));
}

$invoiceId = $_POST['invoice_id'] ?? 0;
$changes = $_POST['changes'] ?? [];

try {
    $db->beginTransaction();

    // بروزرسانی آیتم‌های فاکتور
    foreach ($changes as $field => $value) {
        if (strpos($field, 'item_') === 0) {
            list($type, $field, $itemId) = explode('_', $field);
            
            switch ($field) {
                case 'quantity':
                case 'price':
                    $value = filter_var($value, FILTER_VALIDATE_FLOAT);
                    // محاسبه مجدد قیمت کل آیتم
                    $item = $db->get('invoice_items', '*', ['id' => $itemId]);
                    if ($field == 'quantity') {
                        $totalAmount = $value * $item['price'];
                    } else {
                        $totalAmount = $item['quantity'] * $value;
                    }
                    
                    $db->update('invoice_items', [
                        $field => $value,
                        'total_amount' => $totalAmount
                    ], ['id' => $itemId]);
                    break;
                    
                case 'discount':
                    $value = filter_var($value, FILTER_VALIDATE_FLOAT);
                    $db->update('invoice_items', [
                        'discount' => $value
                    ], ['id' => $itemId]);
                    break;
                
                case 'name':
                    $db->update('invoice_items', [
                        'description' => $value
                    ], ['id' => $itemId]);
                    break;
            }
        }
    }
        // بروزرسانی سایر فیلدهای فاکتور
        foreach ($changes as $field => $value) {
            // فقط اگر فیلد با item_ شروع نشود، بررسی کنیم
            if (strpos($field, 'item_') !== 0) {
                switch ($field) {
                    case 'description':
                        // اگر فیلد توضیحات بود، آپدیت کنیم
                        $db->update('invoices', [
                            'description' => $value
                        ], ['id' => $invoiceId]);
                        break;
                        
                    // اینجا می‌توانیم سایر فیلدها را هم اضافه کنیم
                }
            }
        }

    // محاسبه مجدد مبالغ فاکتور
    $items = $db->query("SELECT SUM(total_amount) as total, SUM(discount) as discount 
                        FROM invoice_items WHERE invoice_id = ?", 
                        [$invoiceId])->fetch();

    $totalAmount = $items['total'];
    $discountAmount = $items['discount'];
    
    // دریافت نرخ مالیات از فاکتور
    $invoice = $db->get('invoices', ['tax_rate'], ['id' => $invoiceId]);
    $taxAmount = $totalAmount * ($invoice['tax_rate'] / 100);
    
    // محاسبه مبلغ نهایی
    $finalAmount = $totalAmount + $taxAmount - $discountAmount;
    // برداشتن فیلد description از changes اگر وجود داشت
    if (isset($changes['description'])) {
        unset($changes['description']);
    }

    // بروزرسانی فاکتور
    $db->update('invoices', [
        'total_amount' => $totalAmount,
        'tax_amount' => $taxAmount,
        'discount_amount' => $discountAmount,
        'final_amount' => $finalAmount,
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['user_id']
    ], ['id' => $invoiceId]);

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'تغییرات با موفقیت ذخیره شد'
    ]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'خطا در به‌روزرسانی اطلاعات: ' . $e->getMessage()
    ]);
}