<?php
require_once '../includes/init.php';
define('DEBUG', true);
// پاک کردن هرگونه خروجی قبلی
if (ob_get_length()) ob_clean();

// تنظیم هدرهای مناسب
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // بررسی وجود سشن
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('نشست کاربری شما منقضی شده است. لطفاً دوباره وارد شوید.');
    }

    // بررسی متد درخواست
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('درخواست نامعتبر است');
    }

    // دریافت و تجزیه داده‌های ورودی
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('خطا در پردازش داده‌های ورودی');
    }

    // بررسی اطلاعات ورودی
    $requiredFields = ['product_id', 'customer_id', 'quantity', 'payment_method'];
    $missingFields = array_filter($requiredFields, function($field) use ($input) {
        return !isset($input[$field]) || trim($input[$field]) === '';
    });

    if (!empty($missingFields)) {
        throw new Exception('لطفاً همه فیلدها را پر کنید: ' . implode(', ', $missingFields));
    }

    // تبدیل و اعتبارسنجی داده‌های ورودی
    $productId = filter_var($input['product_id'], FILTER_VALIDATE_INT);
    $customerId = filter_var($input['customer_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($input['quantity'], FILTER_VALIDATE_INT);
    // به دلیل منسوخ شدن FILTER_SANITIZE_STRING از FILTER_SANITIZE_FULL_SPECIAL_CHARS استفاده می‌کنیم
    $paymentMethod = filter_var($input['payment_method'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // بررسی معتبر بودن داده‌ها
    if (!$productId || $productId <= 0) {
        throw new Exception('شناسه محصول نامعتبر است');
    }
    if (!$customerId || $customerId <= 0) {
        throw new Exception('شناسه مشتری نامعتبر است');
    }
    if (!$quantity || $quantity <= 0) {
        throw new Exception('مقدار تعداد نامعتبر است');
    }
    if (!in_array($paymentMethod, ['cash', 'card', 'cheque'])) {
        throw new Exception('روش پرداخت نامعتبر است');
    }

    $db->beginTransaction();

    // بررسی و دریافت اطلاعات محصول
    $product = $db->get('products', '*', ['id' => $productId, 'status' => 'active']);
    if (!$product) {
        throw new Exception('محصول مورد نظر یافت نشد یا غیرفعال است');
    }
    
    // بررسی موجودی
    if ($product['quantity'] < $quantity) {
        throw new Exception("موجودی کافی نیست. موجودی فعلی: {$product['quantity']} عدد");
    }

    // بررسی مشتری
    $customer = $db->get('customers', '*', ['id' => $customerId]);
    if (!$customer) {
        throw new Exception('مشتری مورد نظر یافت نشد');
    }

    // محاسبات مالی
    $unitPrice = floatval($product['sale_price']);
    $totalAmount = $quantity * $unitPrice;

    // دریافت تنظیمات
    $settings = $db->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('invoice_prefix', 'tax_rate')")->fetchAll(); // Corrected the query
    $invoicePrefix = 'INV-';
    $taxRate = 0;
    
    foreach ($settings as $setting) {
        if ($setting['key'] === 'invoice_prefix') {
            $invoicePrefix = $setting['value'] ?: 'INV-';
        } else if ($setting['key'] === 'tax_rate') {
            $taxRate = floatval($setting['value']);
        }
    }

    // ایجاد شماره فاکتور
    $stmt = $db->query(
        "SELECT MAX(CAST(SUBSTRING(invoice_number, ? + 1) AS SIGNED)) as last_number FROM invoices", 
        [strlen($invoicePrefix)]
    );
    $lastNumber = $stmt->fetch()['last_number'] ?: 0;
    $nextInvoiceNumber = $invoicePrefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

    // محاسبه مالیات
    $taxAmount = ($totalAmount * $taxRate) / 100;
    $finalAmount = $totalAmount + $taxAmount;

    // تاریخ و زمان فعلی
    $currentDateTime = date('Y-m-d H:i:s');

    // ثبت فاکتور
    $invoiceResult = $db->insert('invoices', [
        'invoice_number' => $nextInvoiceNumber,
        'customer_id' => $customerId,
        'total_amount' => $totalAmount,
        'tax_rate' => $taxRate,
        'tax_amount' => $taxAmount,
        'discount_amount' => 0,
        'final_amount' => $finalAmount,
        'payment_status' => 'paid',
        'status' => 'confirmed',
        'created_by' => $_SESSION['user_id'],
        'created_at' => $currentDateTime
    ]);

    if (!$invoiceResult) {
        throw new Exception('خطا در ثبت فاکتور: ' . $db->error());
    }

    $invoiceId = $db->lastInsertId();

    // ثبت آیتم فاکتور
    $itemResult = $db->insert('invoice_items', [
        'invoice_id' => $invoiceId,
        'product_id' => $productId,
        'quantity' => $quantity,
        'price' => $unitPrice,
        'total_amount' => $totalAmount,
        'created_at' => $currentDateTime
    ]);

    if (!$itemResult) {
        throw new Exception('خطا در ثبت اقلام فاکتور: ' . $db->error());
    }

    // به‌روزرسانی موجودی محصول
    $updateResult = $db->update('products', 
        ['quantity' => $product['quantity'] - $quantity],
        ['id' => $productId]
    );

    if (!$updateResult) {
        throw new Exception('خطا در به‌روزرسانی موجودی: ' . $db->error());
    }

    // ثبت تراکنش انبار
    $transactionResult = $db->insert('inventory_transactions', [
        'product_id' => $productId,
        'type' => 'out',
        'quantity' => $quantity,
        'reference_type' => 'invoice',
        'reference_id' => $invoiceId,
        'description' => "کسر از موجودی بابت فاکتور شماره " . $nextInvoiceNumber,
        'created_by' => $_SESSION['user_id'],
        'created_at' => $currentDateTime
    ]);

    if (!$transactionResult) {
        throw new Exception('خطا در ثبت تراکنش انبار: ' . $db->error());
    }

    // بررسی پرداخت‌ها
    foreach ($input['payments'] as $payment) {
        if (!in_array($payment['method'], ['cash', 'card', 'cheque', 'credit'])) {
            throw new Exception('روش پرداخت نامعتبر است');
        }

        $paymentResult = $db->insert('payments', [
            'invoice_id' => $invoiceId,
            'payment_type' => $payment['method'],
            'amount' => $payment['amount'],
            'payment_date' => date('Y-m-d'),
            'created_by' => $_SESSION['user_id'],
            'created_at' => $currentDateTime
        ]);

        if (!$paymentResult) {
            throw new Exception('خطا در ثبت پرداخت: ' . $db->error());
        }
    }

    $db->commit();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'فاکتور با موفقیت ثبت شد',
        'data' => [
            'invoice_id' => $invoiceId,
            'invoice_number' => $nextInvoiceNumber,
            'total_amount' => $finalAmount
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollback();
    }
    
    error_log('Error in save-invoice.php: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => DEBUG ? [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ] : null
    ], JSON_UNESCAPED_UNICODE);

    
}

exit();
?>