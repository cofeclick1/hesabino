<?php
require_once '../includes/init.php';

if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
    die('شناسه پرداخت نامعتبر است');
}

$paymentId = (int)$_GET['payment_id'];

// دریافت اطلاعات پرداخت
$sql = "SELECT * FROM payments WHERE id = ?";
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

?>

<form id="editPaymentForm" class="needs-validation" novalidate>
    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
    
    <?php if ($payment['payment_type'] === 'cheque'): ?>
        <div class="mb-3">
            <label class="form-label">وضعیت چک</label>
            <select name="cheque_status" class="form-control" required>
                <option value="pending" <?php echo $additionalInfo['status'] === 'pending' ? 'selected' : ''; ?>>در انتظار</option>
                <option value="passed" <?php echo $additionalInfo['status'] === 'passed' ? 'selected' : ''; ?>>وصول شده</option>
                <option value="returned" <?php echo $additionalInfo['status'] === 'returned' ? 'selected' : ''; ?>>برگشت خورده</option>
            </select>
        </div>
        
    <?php elseif ($payment['payment_type'] === 'installment'): ?>
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
                                <select name="installment_status[<?php echo $installment['id']; ?>]" class="form-select form-select-sm">
                                    <option value="pending" <?php echo $installment['status'] === 'pending' ? 'selected' : ''; ?>>در انتظار</option>
                                    <option value="paid" <?php echo $installment['status'] === 'paid' ? 'selected' : ''; ?>>پرداخت شده</option>
                                    <option value="overdue" <?php echo $installment['status'] === 'overdue' ? 'selected' : ''; ?>>معوق</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <label class="form-label">توضیحات جدید</label>
        <textarea name="description" class="form-control" rows="3"><?php echo $payment['description']; ?></textarea>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
    </div>
</form>

<script>
// ارسال فرم با Ajax
$('#editPaymentForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'ajax/update-payment-status.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                alert('تغییرات با موفقیت ذخیره شد');
                location.reload();
            } else {
                alert('خطا در ذخیره تغییرات');
            }
        },
        error: function() {
            alert('خطا در ارتباط با سرور');
        }
    });
});
</script>