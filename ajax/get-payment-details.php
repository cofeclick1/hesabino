<?php
require_once '../includes/init.php';

if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
    die('شناسه پرداخت نامعتبر است');
}

$paymentId = (int)$_GET['payment_id'];

// دریافت اطلاعات پرداخت
$sql = "SELECT p.*, u.full_name as created_by_name 
        FROM payments p
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.id = ?";
$payment = $db->query($sql, [$paymentId])->fetch();

if (!$payment) {
    die('پرداخت مورد نظر یافت نشد');
}

// دریافت اطلاعات تکمیلی بر اساس نوع پرداخت
$additionalInfo = [];
switch ($payment['payment_type']) {
    case 'card':
        $sql = "SELECT * FROM payment_card_details WHERE payment_id = ?";
        $additionalInfo = $db->query($sql, [$paymentId])->fetch();
        break;
    
    case 'cheque':
        $sql = "SELECT * FROM payment_cheque_details WHERE payment_id = ?";
        $additionalInfo = $db->query($sql, [$paymentId])->fetch();
        break;
    
    case 'installment':
        $sql = "SELECT * FROM payment_installments WHERE payment_id = ? ORDER BY installment_number";
        $additionalInfo = $db->query($sql, [$paymentId])->fetchAll();
        break;
}

// نمایش اطلاعات
?>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <p><strong>تاریخ پرداخت:</strong> <?php echo jdate('Y/m/d', strtotime($payment['payment_date'])); ?></p>
            <p><strong>مبلغ:</strong> <?php echo number_format($payment['amount']); ?> ریال</p>
            <p><strong>نوع پرداخت:</strong> 
                <?php
                $types = [
                    'cash' => 'نقدی',
                    'card' => 'کارت به کارت',
                    'cheque' => 'چک',
                    'installment' => 'اقساطی'
                ];
                echo $types[$payment['payment_type']];
                ?>
            </p>
        </div>
        <div class="col-md-6">
            <p><strong>ثبت کننده:</strong> <?php echo $payment['created_by_name']; ?></p>
            <p><strong>تاریخ ثبت:</strong> <?php echo jdate('Y/m/d H:i', strtotime($payment['created_at'])); ?></p>
        </div>
    </div>

    <?php if ($payment['payment_type'] === 'card' && $additionalInfo): ?>
        <hr>
        <h6 class="mb-3">اطلاعات کارت به کارت</h6>
        <div class="row">
            <div class="col-md-6">
                <p><strong>شماره کارت:</strong> <?php echo $additionalInfo['card_number']; ?></p>
                <p><strong>شماره پیگیری:</strong> <?php echo $additionalInfo['tracking_number']; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>نام بانک:</strong> 
                    <?php
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
                    echo $banks[$additionalInfo['bank_name']] ?? $additionalInfo['bank_name'];
                    ?>
                </p>
            </div>
        </div>
    
    <?php elseif ($payment['payment_type'] === 'cheque' && $additionalInfo): ?>
        <hr>
        <h6 class="mb-3">اطلاعات چک</h6>
        <div class="row">
            <div class="col-md-6">
                <p><strong>شماره چک:</strong> <?php echo $additionalInfo['cheque_number']; ?></p>
                <p><strong>تاریخ سررسید:</strong> <?php echo jdate('Y/m/d', strtotime($additionalInfo['due_date'])); ?></p>
                <p><strong>نام بانک:</strong>
                    <?php
                    echo $banks[$additionalInfo['bank_name']] ?? $additionalInfo['bank_name'];
                    ?>
                </p>
            </div>
            <div class="col-md-6">
                <p><strong>نام شعبه:</strong> <?php echo $additionalInfo['branch_name']; ?></p>
                <p><strong>شماره حساب:</strong> <?php echo $additionalInfo['account_number']; ?></p>
                <p><strong>وضعیت:</strong> 
                    <?php
                    $statuses = [
                        'pending' => '<span class="badge bg-warning">در انتظار وصول</span>',
                        'passed' => '<span class="badge bg-success">وصول شده</span>',
                        'returned' => '<span class="badge bg-danger">برگشت خورده</span>'
                    ];
                    echo $statuses[$additionalInfo['status']] ?? 'نامشخص';
                    ?>
                </p>
            </div>
        </div>
    
    <?php elseif ($payment['payment_type'] === 'installment' && $additionalInfo): ?>
        <hr>
        <h6 class="mb-3">اطلاعات اقساط</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>شماره قسط</th>
                        <th>مبلغ</th>
                        <th>تاریخ سررسید</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($additionalInfo as $installment): ?>
                        <tr>
                            <td><?php echo $installment['installment_number']; ?></td>
                            <td><?php echo number_format($installment['amount']); ?></td>
                            <td><?php echo jdate('Y/m/d', strtotime($installment['due_date'])); ?></td>
                            <td>
                                <?php
                                $installmentStatuses = [
                                    'pending' => '<span class="badge bg-warning">در انتظار</span>',
                                    'paid' => '<span class="badge bg-success">پرداخت شده</span>',
                                    'overdue' => '<span class="badge bg-danger">معوق</span>'
                                ];
                                echo $installmentStatuses[$installment['status']] ?? 'نامشخص';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($payment['description']): ?>
        <hr>
        <div>
            <strong>توضیحات:</strong>
            <p class="mt-2"><?php echo nl2br(htmlspecialchars($payment['description'])); ?></p>
        </div>
    <?php endif; ?>

    <hr>
    <div class="text-end">
        <button type="button" class="btn btn-danger" onclick="deletePayment(<?php echo $payment['id']; ?>)">
            <i class="bi bi-trash"></i>
            حذف پرداخت
        </button>
    </div>
</div>

<script>
function deletePayment(paymentId) {
    if (confirm('آیا از حذف این پرداخت اطمینان دارید؟')) {
        $.ajax({
            url: 'ajax/delete-payment.php',
            method: 'POST',
            data: { payment_id: paymentId },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'خطا در حذف پرداخت');
                }
            },
            error: function() {
                alert('خطا در ارتباط با سرور');
            }
        });
    }
}
</script>