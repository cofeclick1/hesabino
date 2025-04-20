<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست مشتریان
$customers = $db->query("
    SELECT *, 
           CONCAT(first_name, ' ', last_name) as full_name 
    FROM customers 
    WHERE deleted_at IS NULL 
    ORDER BY full_name ASC")->fetchAll();

// دریافت تنظیمات پیش‌فرض
$defaultTaxRate = $db->query("SELECT value FROM settings WHERE `key` = 'tax_rate'")->fetchColumn();
$currency = $db->query("SELECT value FROM settings WHERE `key` = 'currency'")->fetchColumn();
$invoicePrefix = $db->query("SELECT value FROM settings WHERE `key` = 'invoice_prefix'")->fetchColumn();

// اگر درخواست ایجاد فاکتور باشد
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        $customerId = $_POST['customer_id'];
        $invoiceItems = $_POST['items'];
        $totalAmount = 0;

        // تولید شماره فاکتور
        $lastInvoiceNumber = $db->query("SELECT MAX(CAST(SUBSTRING(invoice_number, LENGTH('$invoicePrefix') + 1) AS SIGNED)) FROM invoices")->fetchColumn();
        $nextInvoiceNumber = $invoicePrefix . str_pad(($lastInvoiceNumber + 1), 5, '0', STR_PAD_LEFT);

        foreach ($invoiceItems as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        // محاسبه مالیات و تخفیف
        $taxRate = $_POST['tax_rate'];
        $discountAmount = $_POST['discount_amount'];
        $taxAmount = ($totalAmount * $taxRate / 100);
        $finalAmount = $totalAmount + $taxAmount - $discountAmount;

        // ایجاد فاکتور
        $db->insert('invoices', [
            'invoice_number' => $nextInvoiceNumber,
            'customer_id' => $customerId,
            'total_amount' => $totalAmount,
            'tax_rate' => $taxRate,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'payment_status' => 'unpaid',
            'status' => 'confirmed'
        ]);

        $invoiceId = $db->lastInsertId();

        // افزودن آیتم‌های فاکتور
        foreach ($invoiceItems as $item) {
            // بررسی موجودی
            $currentStock = $db->query("SELECT quantity FROM products WHERE id = ?", [$item['product_id']])->fetchColumn();
            
            if ($currentStock < $item['quantity']) {
                throw new Exception("موجودی کالا کافی نیست");
            }

            // ثبت آیتم فاکتور
            $db->insert('invoice_items', [
                'invoice_id' => $invoiceId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => 0,
                'total_amount' => $item['quantity'] * $item['price']
            ]);

            // به‌روزرسانی موجودی
            $db->update('products', 
                ['quantity' => $currentStock - $item['quantity']],
                ['id' => $item['product_id']]
            );

            // ثبت تراکنش انبار
            $db->insert('inventory_transactions', [
                'product_id' => $item['product_id'],
                'type' => 'out',
                'quantity' => $item['quantity'],
                'reference_type' => 'invoice',
                'reference_id' => $invoiceId,
                'description' => "کسر از موجودی بابت فاکتور شماره " . $nextInvoiceNumber,
                'created_by' => $_SESSION['user_id']
            ]);
        }

        $db->commit();
        flashMessage('فاکتور با موفقیت ایجاد شد', 'success');
        header('Location: invoices.php');
        exit;

    } catch (Exception $e) {
        $db->rollback();
        flashMessage('خطا در ثبت فاکتور: ' . $e->getMessage(), 'error');
    }
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ایجاد فاکتور - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/invoices.css">
</head>
<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content w-100">
            <!-- Top Navbar -->
            <?php include 'includes/navbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4">
                <?php echo showFlashMessage(); ?>
                
                <div class="row g-4 my-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-gradient-primary text-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-file-invoice me-2"></i>
                                        ایجاد فاکتور جدید
                                    </h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="post" action="" id="invoice-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="customer_id">
                                                    <i class="fas fa-user me-1"></i>
                                                    انتخاب مشتری
                                                </label>
                                                <div class="d-flex align-items-center">
                                                    <select name="customer_id" id="customer_id" class="form-select custom-select2" required>
                                                        <option value="">انتخاب کنید...</option>
                                                        <?php foreach ($customers as $customer): ?>
                                                            <option value="<?php echo $customer['id']; ?>">
                                                                <?php echo htmlspecialchars($customer['full_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="button" id="addCustomerBtn" class="btn btn-primary btn-add-customer">
                                                        <i class="fas fa-plus me-1"></i>
                                                        مشتری جدید
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="tax_rate">
                                                    <i class="fas fa-percent me-1"></i>
                                                    نرخ مالیات (%)
                                                </label>
                                                <input type="number" name="tax_rate" id="tax_rate" 
                                                    class="form-control" value="<?php echo $defaultTaxRate; ?>" 
                                                    min="0" max="100" step="0.1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="discount_amount">
                                                    <i class="fas fa-tag me-1"></i>
                                                    مبلغ تخفیف
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" name="discount_amount" id="discount_amount" 
                                                        class="form-control" value="0" min="0" required>
                                                    <span class="input-group-text"><?php echo $currency; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="mb-0">
                                            <i class="fas fa-shopping-cart me-2"></i>
                                            اقلام فاکتور
                                        </h5>
                                        <button type="button" id="add-item" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            افزودن کالا
                                        </button>
                                    </div>

                                    <div id="invoice-items">
                                        <!-- آیتم‌های فاکتور اینجا اضافه می‌شوند -->
                                    </div>

                                    <!-- خلاصه فاکتور -->
                                    <div class="summary-section">
                                        <div class="row justify-content-end">
                                            <div class="col-md-5">
                                                <div class="card summary-card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <span>جمع کل:</span>
                                                            <span id="subtotal" class="amount">0 <?php echo $currency; ?></span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <span>مالیات:</span>
                                                            <span id="tax_amount" class="amount">0 <?php echo $currency; ?></span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <span>تخفیف:</span>
                                                            <span id="discount" class="amount">0 <?php echo $currency; ?></span>
                                                        </div>
                                                        <hr class="my-3 opacity-25">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="h5 mb-0">مبلغ نهایی:</span>
                                                            <span id="final_amount" class="h4 mb-0">0 <?php echo $currency; ?></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-end mt-4">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                                        <i class="fas fa-times me-1"></i>
                                                        انصراف
                                                    </button>
                                                    <button type="submit" class="btn btn-success ms-2">
                                                        <i class="fas fa-save me-1"></i>
                                                        ثبت فاکتور
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- لیست فاکتورها -->
                <div class="card mt-5">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            لیست فاکتورهای اخیر
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // دریافت لیست فاکتورها
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $perPage = 10;
                        $offset = ($page - 1) * $perPage;

                        $sql = "SELECT i.*, 
                               CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                               c.mobile as customer_mobile,
                               p.payment_type as last_payment_type
                         FROM invoices i
                         LEFT JOIN customers c ON i.customer_id = c.id
                         LEFT JOIN (
                             SELECT invoice_id, payment_type
                             FROM payments 
                             WHERE id IN (
                                 SELECT MAX(id) 
                                 FROM payments 
                                 GROUP BY invoice_id
                             )
                         ) p ON p.invoice_id = i.id
                         ORDER BY i.created_at DESC
                         LIMIT :offset, :limit";

                        $invoices = $db->query($sql, [':offset' => $offset, ':limit' => $perPage])->fetchAll();
                        
                        // محاسبه تعداد کل صفحات
                        $total = $db->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
                        $totalPages = ceil($total / $perPage);
                        ?>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>شماره فاکتور</th>
                                        <th>مشتری</th>
                                        <th>تاریخ صدور</th>
                                        <th>مبلغ کل</th>
                                        <th>تخفیف</th>
                                        <th>مالیات</th>
                                        <th>مبلغ نهایی</th>
                                        <th>وضعیت پرداخت</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($invoices)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>هیچ فاکتوری یافت نشد</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo $invoice['invoice_number']; ?></td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($invoice['customer_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($invoice['customer_mobile']); ?></small>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $date = new DateTime($invoice['created_at']);
                                                    echo jdate('Y/m/d', strtotime($invoice['created_at'])); 
                                                    ?>
                                                    <div class="small text-muted">
                                                        <?php echo $date->format('H:i'); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($invoice['total_amount']) . ' ' . $currency; ?></td>
                                                <td class="text-danger">
                                                    <?php echo $invoice['discount_amount'] > 0 ? '-' . number_format($invoice['discount_amount']) . ' ' . $currency : '0'; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $taxAmount = ($invoice['total_amount'] * $invoice['tax_rate'] / 100);
                                                    echo number_format($taxAmount) . ' ' . $currency; 
                                                    ?>
                                                </td>
                                                <td class="fw-bold text-success">
                                                    <?php echo number_format($invoice['final_amount']) . ' ' . $currency; ?>
                                                </td>
                                                <td>
                                                <td>
                                                    <?php
                                                    $statusClasses = [
                                                        'paid' => 'success',
                                                        'unpaid' => 'danger',
                                                        'partial' => 'warning'
                                                    ];
                                                    $statusTexts = [
                                                        'cash' => 'نقدی پرداخت شده',
                                                        'card' => 'کارت به کارت شده',
                                                        'cheque' => 'چک پرداخت شده',
                                                        'installment' => 'پرداخت اقساطی',
                                                        'unpaid' => 'پرداخت نشده',
                                                        'partial' => 'پرداخت ناقص'
                                                    ];
                                                    $statusClass = $statusClasses[$invoice['payment_status']] ?? 'secondary';
                                                    $statusText = $invoice['payment_status'] == 'paid' ? 
                                                        ($statusTexts[$invoice['last_payment_type']] ?? 'پرداخت شده') : 
                                                        ($statusTexts[$invoice['payment_status']] ?? 'نامعلوم');
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?> bg-opacity-10 text-<?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="invoice-print.php?id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-light" 
                                                           title="چاپ فاکتور"
                                                           target="_blank">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        <a href="invoice-payment.php?id=<?php echo $invoice['id']; ?>" 
                                                           class="btn <?php echo $invoice['payment_status'] == 'paid' ? 'btn-success' : 'btn-warning'; ?>" 
                                                           title="<?php echo $invoice['payment_status'] == 'paid' ? 'مشاهده جزئیات پرداخت' : 'ثبت پرداخت'; ?>">
                                                            <i class="fas <?php echo $invoice['payment_status'] == 'paid' ? 'fa-info-circle' : 'fa-money-bill'; ?>"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer border-0 py-3">
                                <nav aria-label="صفحه‌بندی">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                                <i class="fas fa-chevron-right me-1"></i>
                                                قبلی
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                                <?php if ($i > 1 && $i != $page - 2 && $i != $page + 2 && $i != 2 && $i != $totalPages - 1): ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">...</span>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                                بعدی
                                                <i class="fas fa-chevron-left me-1"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script src="assets/js/invoice-functions.js"></script>
<script>
    initializeInvoice(
        <?php echo json_encode($currency); ?>,
        <?php echo json_encode($defaultTaxRate); ?>
    );
</script>
</body>
</html>