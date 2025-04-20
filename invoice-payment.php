<?php
require_once 'includes/init.php';

// بررسی و دریافت شناسه فاکتور
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('شناسه فاکتور نامعتبر است');
}

$invoiceId = (int)$_GET['id'];

// دریافت اطلاعات فاکتور
$sql = "SELECT i.*, 
               c.first_name, 
               c.last_name,
               c.company
        FROM invoices i
        LEFT JOIN customers c ON i.customer_id = c.id
        WHERE i.id = ?";

$invoice = $db->query($sql, [$invoiceId])->fetch();

if (!$invoice) {
    die('فاکتور مورد نظر یافت نشد');
}

// دریافت پرداخت‌های قبلی
$sql = "SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC";
$previousPayments = $db->query($sql, [$invoiceId])->fetchAll();

$totalPaid = 0;
foreach ($previousPayments as $payment) {
    $totalPaid += $payment['amount'];
}

$remainingAmount = $invoice['final_amount'] - $totalPaid;

// پردازش فرم در صورت ارسال
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        $paymentType = $_POST['payment_type'];
        $amount = str_replace(',', '', $_POST['amount']);
        $paymentDate = $_POST['payment_date'];
        $description = $_POST['description'];
        
        // بررسی مقدار پرداختی
        if ($amount <= 0 || $amount > $remainingAmount) {
            throw new Exception('مبلغ پرداختی نامعتبر است');
        }
        
        // درج اطلاعات پایه پرداخت
        $sql = "INSERT INTO payments (
                    invoice_id, 
                    payment_type,
                    amount,
                    payment_date,
                    description,
                    created_by,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                
        $db->query($sql, [
            $invoiceId,
            $paymentType,
            $amount,
            $paymentDate,
            $description,
            $_SESSION['user_id']
        ]);
        
        $paymentId = $db->lastInsertId();
        
        // درج اطلاعات تکمیلی بر اساس نوع پرداخت
        switch ($paymentType) {
            case 'card':
                $sql = "INSERT INTO payment_card_details (
                            payment_id,
                            card_number,
                            tracking_number,
                            bank_name
                        ) VALUES (?, ?, ?, ?)";
                        
                $db->query($sql, [
                    $paymentId,
                    $_POST['card_number'],
                    $_POST['tracking_number'],
                    $_POST['bank_name']
                ]);
                break;
                
            case 'cheque':
                $sql = "INSERT INTO payment_cheque_details (
                            payment_id,
                            cheque_number,
                            due_date,
                            bank_name,
                            branch_name,
                            account_number
                        ) VALUES (?, ?, ?, ?, ?, ?)";
                        
                $db->query($sql, [
                    $paymentId,
                    $_POST['cheque_number'],
                    $_POST['due_date'],
                    $_POST['bank_name'],
                    $_POST['branch_name'],
                    $_POST['account_number']
                ]);
                break;
                
            case 'installment':
                $installmentCount = (int)$_POST['installment_count'];
                $installmentAmount = $amount / $installmentCount;
                $startDate = $_POST['start_date'];
                $interval = $_POST['interval']; // monthly, weekly
                
                for ($i = 0; $i < $installmentCount; $i++) {
                    $dueDate = date('Y-m-d', strtotime($startDate . " +$i " . ($interval == 'weekly' ? 'week' : 'month')));
                    
                    $sql = "INSERT INTO payment_installments (
                                payment_id,
                                installment_number,
                                amount,
                                due_date,
                                status
                            ) VALUES (?, ?, ?, ?, 'pending')";
                            
                    $db->query($sql, [
                        $paymentId,
                        $i + 1,
                        $installmentAmount,
                        $dueDate
                    ]);
                }
                break;
        }
        
        // بروزرسانی وضعیت پرداخت فاکتور
$newTotalPaid = $totalPaid + $amount;
$newStatus = $newTotalPaid >= $invoice['final_amount'] ? 'paid' : 
            ($newTotalPaid > 0 ? 'partial' : 'unpaid');

// اول ستون last_payment_type رو چک و در صورت نیاز اضافه می‌کنیم
$checkColumn = "SHOW COLUMNS FROM invoices LIKE 'last_payment_type'";
$columnExists = $db->query($checkColumn)->fetch();

if (!$columnExists) {
    $addColumn = "ALTER TABLE invoices ADD COLUMN last_payment_type VARCHAR(20) AFTER payment_status";
    $db->query($addColumn);
}

// بروزرسانی وضعیت فاکتور با نوع پرداخت
$sql = "UPDATE invoices SET 
        payment_status = ?,
        last_payment_type = ?,
        updated_at = NOW()
        WHERE id = ?";
                
$db->query($sql, [
    $newStatus,
    $paymentType, // نوع پرداخت: cash, card, cheque, installment
    $invoiceId
]);
                    
                        
        $db->commit();
        $_SESSION['success'] = 'پرداخت با موفقیت ثبت شد';
        header("Location: invoice-payment.php?id=$invoiceId");
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}

// دریافت لیست بانک‌ها
$banks = [
    'mellat' => 'بانک ملت',
    'melli' => 'بانک ملی',
    'saderat' => 'بانک صادرات',
    'parsian' => 'بانک پارسیان',
    'pasargad' => 'بانک پاسارگاد',
    'saman' => 'بانک سامان',
    'tejarat' => 'بانک تجارت',
    'sepah' => 'بانک سپه'
];

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت پرداخت فاکتور <?php echo $invoice['invoice_number']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @font-face {
            font-family: 'iran-sans';
            src: url('assets/fonts/IRANSansWeb.woff2') format('woff2');
        }
        body {
            font-family: 'iran-sans', tahoma, arial;
            background: #f8f9fa;
            font-size: 14px;
        }
        .payment-container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .invoice-info {
            background: #f8f9ff;
            border: 1px solid #0d6efd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .payment-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .payment-type-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-type-btn:hover {
            border-color: #0d6efd;
            background: #f8f9ff;
        }
        .payment-type-btn.active {
            border-color: #0d6efd;
            background: #f8f9ff;
            color: #0d6efd;
        }
        .payment-type-btn i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #6c757d;
        }
        .payment-type-btn.active i {
            color: #0d6efd;
        }
        .payment-form {
            display: none;
        }
        .payment-form.active {
            display: block;
        }
        .form-label {
            font-weight: bold;
            color: #495057;
        }
        .payment-history {
            margin-top: 30px;
        }
        .payment-badge {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 15px;
        }
        .payment-badge.cash { background: #d1e7dd; color: #0f5132; }
        .payment-badge.card { background: #cfe2ff; color: #084298; }
        .payment-badge.cheque { background: #fff3cd; color: #664d03; }
        .payment-badge.installment { background: #e2e3e5; color: #41464b; }
        
        /* Installment Table Styles */
        .installment-table {
            display: none;
            margin-top: 20px;
        }
        .installment-table.active {
            display: table;
        }
        .installment-table th {
            background: #f8f9fa;
            font-weight: normal;
        }
        
        /* Custom Input Styles */
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
        }
        
        /* Loading Spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .spinner-overlay.active {
            display: flex;
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="spinner-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">در حال پردازش...</span>
        </div>
    </div>

    <div class="container">
        <div class="payment-container">
<!-- دکمه برگشت و عنوان -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">ثبت پرداخت فاکتور</h4>
    <a href="invoices.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right"></i>
        بازگشت به لیست فاکتورها
    </a>
</div>
            <!-- اطلاعات فاکتور -->
            <div class="invoice-info">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">اطلاعات فاکتور</h5>
                        <div>شماره فاکتور: <?php echo $invoice['invoice_number']; ?></div>
                        <div>مشتری: <?php echo $invoice['first_name'] . ' ' . $invoice['last_name']; ?></div>
                        <?php if ($invoice['company']): ?>
                            <div>شرکت: <?php echo $invoice['company']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div>مبلغ کل: <?php echo number_format($invoice['final_amount']); ?> ریال</div>
                        <div>مبلغ پرداخت شده: <?php echo number_format($totalPaid); ?> ریال</div>
                        <div>مانده قابل پرداخت: <?php echo number_format($remainingAmount); ?> ریال</div>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- انتخاب نوع پرداخت -->
            <div class="payment-type-selector">
                <div class="payment-type-btn active" data-type="cash">
                    <i class="bi bi-cash-coin"></i>
                    <div>نقدی</div>
                </div>
                <div class="payment-type-btn" data-type="card">
                    <i class="bi bi-credit-card"></i>
                    <div>کارت به کارت</div>
                </div>
                <div class="payment-type-btn" data-type="cheque">
                    <i class="bi bi-newspaper"></i>
                    <div>چک</div>
                </div>
                <div class="payment-type-btn" data-type="installment">
                    <i class="bi bi-calendar-check"></i>
                    <div>اقساطی</div>
                </div>
            </div>

            <!-- فرم پرداخت نقدی -->
            <form action="" method="post" class="payment-form active" id="cash-form">
                <input type="hidden" name="payment_type" value="cash">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تاریخ پرداخت</label>
                            <input type="date" name="payment_date" class="form-control" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">مبلغ (ریال)</label>
                            <input type="text" name="amount" class="form-control amount-input" required
                                   value="<?php echo number_format($remainingAmount); ?>"
                                   data-max="<?php echo $remainingAmount; ?>">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">ثبت پرداخت</button>
            </form>

            <!-- فرم پرداخت کارت به کارت -->
            <form action="" method="post" class="payment-form" id="card-form">
                <input type="hidden" name="payment_type" value="card">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تاریخ پرداخت</label>
                            <input type="date" name="payment_date" class="form-control" required
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">مبلغ (ریال)</label>
                            <input type="text" name="amount" class="form-control amount-input" required
                                   value="<?php echo number_format($remainingAmount); ?>"
                                   data-max="<?php echo $remainingAmount; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">شماره کارت</label>
                            <input type="text" name="card_number" class="form-control" required
                                   pattern="\d{16}" title="شماره کارت باید 16 رقم باشد">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">شماره پیگیری</label>
                            <input type="text" name="tracking_number" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">نام بانک</label>
                            <select name="bank_name" class="form-control" required>
                                <option value="">انتخاب کنید</option>
                                <?php foreach ($banks as $key => $name): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">ثبت پرداخت</button>
            </form>

            <!-- فرم پرداخت چک -->
            <form action="" method="post" class="payment-form" id="cheque-form">
                <input type="hidden" name="payment_type" value="cheque">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تاریخ دریافت چک</label>
                            <input type="date" name="payment_date" class="form-control" required
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">مبلغ (ریال)</label>
                            <input type="text" name="amount" class="form-control amount-input" required
                                   value="<?php echo number_format($remainingAmount); ?>"
                                   data-max="<?php echo $remainingAmount; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">شماره چک</label>
                            <input type="text" name="cheque_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تاریخ سررسید</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">نام بانک</label>
                            <select name="bank_name" class="form-control" required>
                                <option value="">انتخاب کنید</option>
                                <?php foreach ($banks as $key => $name): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">نام شعبه</label>
                            <input type="text" name="branch_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">شماره حساب</label>
                            <input type="text" name="account_number" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">ثبت پرداخت</button>
            </form>

            <!-- فرم پرداخت اقساطی -->
            <form action="" method="post" class="payment-form" id="installment-form">
                <input type="hidden" name="payment_type" value="installment">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تاریخ شروع اقساط</label>
                            <input type="date" name="start_date" class="form-control" required
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">مبلغ کل (ریال)</label>
                            <input type="text" name="amount" class="form-control amount-input" required
                                   value="<?php echo number_format($remainingAmount); ?>"
                                   data-max="<?php echo $remainingAmount; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تعداد اقساط</label>
                            <input type="number" name="installment_count" class="form-control" required min="2" value="2">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">فاصله اقساط</label>
                            <select name="interval" class="form-control" required>
                                <option value="monthly">ماهانه</option>
                                <option value="weekly">هفتگی</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <!-- جدول پیش‌نمایش اقساط -->
                <table class="table table-bordered installment-table">
                    <thead>
                        <tr>
                            <th>شماره قسط</th>
                            <th>تاریخ سررسید</th>
                            <th>مبلغ (ریال)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- با JavaScript پر می‌شود -->
                    </tbody>
                </table>
                
                <button type="submit" class="btn btn-primary">ثبت پرداخت</button>
            </form>

            <!-- تاریخچه پرداخت‌ها -->
            <?php if (!empty($previousPayments)): ?>
                <div class="payment-history">
                    <h5 class="mb-3">تاریخچه پرداخت‌ها</h5>
                    <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>نوع پرداخت</th>
                                <th>مبلغ (ریال)</th>
                                <th>وضعیت</th>
                                <th>توضیحات</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php foreach ($previousPayments as $payment): ?>
                                    <tr>
                                        <td><?php echo jdate('Y/m/d', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <?php
                                            $badges = [
                                                'cash' => ['text' => 'نقدی', 'class' => 'cash'],
                                                'card' => ['text' => 'کارت', 'class' => 'card'],
                                                'cheque' => ['text' => 'چک', 'class' => 'cheque'],
                                                'installment' => ['text' => 'اقساطی', 'class' => 'installment']
                                            ];
                                            $badge = $badges[$payment['payment_type']];
                                            ?>
                                            <span class="payment-badge <?php echo $badge['class']; ?>">
                                                <?php echo $badge['text']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($payment['amount']); ?></td>
                                        <td>
                                            <?php
                                            $paymentStatus = '';
                                            switch ($payment['payment_type']) {
                                                case 'cheque':
                                                    $sql = "SELECT status FROM payment_cheque_details WHERE payment_id = ?";
                                                    $chequeStatus = $db->query($sql, [$payment['id']])->fetchColumn();
                                                    $paymentStatus = $chequeStatus ?? 'در انتظار';
                                                    break;
                                                case 'installment':
                                                    $sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid 
                                                        FROM payment_installments WHERE payment_id = ?";
                                                    $installmentStatus = $db->query($sql, [$payment['id']])->fetch();
                                                    if ($installmentStatus) {
                                                        $paymentStatus = $installmentStatus['paid'] . ' از ' . $installmentStatus['total'] . ' قسط';
                                                    }
                                                    break;
                                                default:
                                                    $paymentStatus = 'پرداخت شده';
                                            }
                                            echo $paymentStatus;
                                            ?>
                                        </td>
                                        <td><?php echo $payment['description']; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info btn-sm"
                                                        onclick="showPaymentDetails(<?php echo $payment['id']; ?>)">
                                                    <i class="bi bi-eye"></i>
                                                    جزئیات
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm"
                                                        onclick="editPayment(<?php echo $payment['id']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                    ویرایش
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="editPaymentModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">ویرایش پرداخت</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- فرم ویرایش با Ajax لود می‌شود -->
                                    </div>
                                </div>
                                          </div>
                        </div>  
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Modal جزئیات پرداخت -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">جزئیات پرداخت</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- محتوا با Ajax لود می‌شود -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تابع فرمت کردن اعداد
        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
        
        // تابع تبدیل اعداد فارسی به انگلیسی
        function toEnglishNumbers(str) {
            const farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            
            for(let i=0; i<10; i++) {
                str = str.replaceAll(farsiDigits[i], i).replaceAll(arabicDigits[i], i);
            }
            return str;
        }

        // اعتبارسنجی و فرمت کردن فیلدهای مبلغ
        $('.amount-input').on('input', function() {
            let value = toEnglishNumbers($(this).val().replace(/,/g, ''));
            if(isNaN(value)) value = 0;
            
            const max = parseInt($(this).data('max'));
            if(value > max) value = max;
            
            $(this).val(formatNumber(value));
        });

                // تغییر نوع پرداخت
                $('.payment-type-btn').click(function() {
            $('.payment-type-btn').removeClass('active');
            $(this).addClass('active');
            
            const type = $(this).data('type');
            $('.payment-form').removeClass('active');
            $(`#${type}-form`).addClass('active');
            
            // اگر اقساطی باشد، جدول پیش‌نمایش را نمایش می‌دهیم
            if(type === 'installment') {
                updateInstallmentTable();
            }
        });

        // بروزرسانی جدول پیش‌نمایش اقساط
        function updateInstallmentTable() {
            const amount = parseInt($('#installment-form [name="amount"]').val().replace(/,/g, '')) || 0;
            const count = parseInt($('#installment-form [name="installment_count"]').val()) || 2;
            const startDate = $('#installment-form [name="start_date"]').val();
            const interval = $('#installment-form [name="interval"]').val();
            
            if(!amount || !count || !startDate) return;
            
            const installmentAmount = Math.floor(amount / count);
            const remainder = amount - (installmentAmount * count);
            
            let html = '';
            let currentDate = new Date(startDate);
            
            for(let i = 0; i < count; i++) {
                const rowAmount = i === 0 ? installmentAmount + remainder : installmentAmount;
                
                html += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${currentDate.toLocaleDateString('fa-IR')}</td>
                        <td>${formatNumber(rowAmount)}</td>
                    </tr>
                `;
                
                // محاسبه تاریخ قسط بعدی
                if(interval === 'monthly') {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                } else {
                    currentDate.setDate(currentDate.getDate() + 7);
                }
            }
            
            $('.installment-table tbody').html(html);
            $('.installment-table').addClass('active');
        }

        // بروزرسانی جدول اقساط با تغییر هر فیلد
        $('#installment-form [name="amount"], #installment-form [name="installment_count"], #installment-form [name="start_date"], #installment-form [name="interval"]').on('change input', function() {
            updateInstallmentTable();
        });

        // نمایش جزئیات پرداخت
        function showPaymentDetails(paymentId) {
            $('.spinner-overlay').addClass('active');
            
            $.ajax({
                url: 'ajax/get-payment-details.php',
                data: { payment_id: paymentId },
                success: function(response) {
                    $('#paymentDetailsModal .modal-body').html(response);
                    new bootstrap.Modal('#paymentDetailsModal').show();
                },
                error: function() {
                    alert('خطا در دریافت اطلاعات');
                },
                complete: function() {
                    $('.spinner-overlay').removeClass('active');
                }
            });
        }

        // اعتبارسنجی فرم‌ها قبل از ارسال
        $('form').on('submit', function(e) {
            const amount = parseInt($(this).find('[name="amount"]').val().replace(/,/g, ''));
            const max = parseInt($(this).find('[name="amount"]').data('max'));
            
            if(amount <= 0 || amount > max) {
                e.preventDefault();
                alert('مبلغ وارد شده نامعتبر است');
                return false;
            }
            
            $('.spinner-overlay').addClass('active');
        });

        // فرمت کردن شماره کارت
        $('[name="card_number"]').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if(value.length > 16) value = value.slice(0, 16);
            $(this).val(value);
        });

        // تنظیم حداقل تاریخ برای فیلدهای تاریخ
        const today = new Date().toISOString().split('T')[0];
        $('[name="payment_date"], [name="start_date"]').attr('min', today);
        $('[name="due_date"]').attr('min', today);
        // تابع ویرایش پرداخت
function editPayment(paymentId) {
    $('.spinner-overlay').addClass('active');
    $.ajax({
        url: 'ajax/edit-payment.php',
        data: { payment_id: paymentId },
        success: function(response) {
            $('#editPaymentModal .modal-body').html(response);
            new bootstrap.Modal('#editPaymentModal').show();
        },
        error: function() {
            alert('خطا در دریافت اطلاعات');
        },
        complete: function() {
            $('.spinner-overlay').removeClass('active');
        }
    });
}

// تابع به‌روزرسانی وضعیت چک یا قسط
function updatePaymentStatus(paymentId, newStatus, type) {
    $.ajax({
        url: 'ajax/update-payment-status.php',
        method: 'POST',
        data: {
            payment_id: paymentId,
            status: newStatus,
            type: type
        },
        success: function(response) {
            if (response.success) {
                alert('وضعیت با موفقیت به‌روزرسانی شد');
                location.reload();
            } else {
                alert('خطا در به‌روزرسانی وضعیت');
            }
        },
        error: function() {
            alert('خطا در ارتباط با سرور');
        }
    });
}
    </script>
</body>
</html>