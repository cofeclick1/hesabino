<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// تنظیم تاریخ و زمان
$current_datetime = '2025-03-25 01:34:31';


// دریافت لیست محصولات
$products = $db->query("SELECT * FROM products")->fetchAll();

// دریافت لیست تراکنش‌های انبار
$transactions = $db->query("
    SELECT t.*, p.name as product_name 
    FROM inventory_transactions t 
    JOIN products p ON t.product_id = p.id 
    ORDER BY t.created_at DESC
")->fetchAll();

// افزودن تراکنش جدید
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_transaction'])) {
    $product_id = $_POST['product_id'];
    $type = $_POST['type'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    $errors = [];
    if (empty($product_id)) {
        $errors[] = 'انتخاب محصول الزامی است.';
    }
    if (empty($type)) {
        $errors[] = 'نوع تراکنش الزامی است.';
    }
    if (empty($quantity) || !is_numeric($quantity)) {
        $errors[] = 'مقدار معتبر نیست.';
    }

    if (empty($errors)) {
        // بررسی موجودی در صورت برداشت
        if ($type == 'out') {
            $current_stock = $db->get('products', 'quantity', ['id' => $product_id]);
            if ($current_stock < $quantity) {
                flashMessage('موجودی کافی نیست', 'danger');
                header('Location: inventory.php');
                exit;
            }
        }

        $db->insert('inventory_transactions', [
            'product_id' => $product_id,
            'type' => $type,
            'quantity' => $quantity,
            'description' => $description,
            'created_by' => $_SESSION['user_id'],
            'created_at' => $current_datetime
        ]);

        // به‌روزرسانی موجودی محصول
        if ($type == 'in') {
            $db->query("UPDATE products SET quantity = quantity + ? WHERE id = ?", [$quantity, $product_id]);
        } else {
            $db->query("UPDATE products SET quantity = quantity - ? WHERE id = ?", [$quantity, $product_id]);
        }

        flashMessage('تراکنش با موفقیت ثبت شد', 'success');
        header('Location: inventory.php');
        exit;
    } else {
        foreach ($errors as $error) {
            flashMessage($error, 'danger');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت انبار - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
</head>
<body>
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
                        <div class="card">
                            <div class="card-header card-header-primary">
                                <h4 class="card-title">مدیریت انبار</h4>
                                <p class="card-category">ثبت ورود و خروج کالا</p>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="product_id">محصول</label>
                                                <select name="product_id" id="product_id" class="form-select" required>
                                                    <option value="">انتخاب محصول...</option>
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?> (موجودی: <?php echo $product['quantity']; ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="type">نوع تراکنش</label>
                                                <select name="type" id="type" class="form-select" required>
                                                    <option value="">انتخاب نوع تراکنش...</option>
                                                    <option value="in">ورود به انبار</option>
                                                    <option value="out">خروج از انبار</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="quantity">تعداد</label>
                                                <input type="number" name="quantity" id="quantity" class="form-control" required min="1">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="description">توضیحات</label>
                                                <input type="text" name="description" id="description" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="submit" name="add_transaction" class="btn btn-primary">ثبت تراکنش</button>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                                <h4>لیست تراکنش‌ها</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>تاریخ</th>
                                                <th>محصول</th>
                                                <th>نوع تراکنش</th>
                                                <th>تعداد</th>
                                                <th>توضیحات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transactions as $transaction): ?>
                                                <tr>
                                                    <td><?php echo $transaction['created_at']; ?></td>
                                                    <td><?php echo $transaction['product_name']; ?></td>
                                                    <td>
                                                        <?php if ($transaction['type'] == 'in'): ?>
                                                            <span class="badge bg-success">ورود به انبار</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">خروج از انبار</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $transaction['quantity']; ?></td>
                                                    <td><?php echo $transaction['description']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
    $(document).ready(function() {
        $('#product_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'انتخاب محصول...',
            language: {
                noResults: function() {
                    return "نتیجه‌ای یافت نشد";
                }
            }
        });

        $('#type').select2({
            theme: 'bootstrap-5',
            placeholder: 'انتخاب نوع تراکنش...',
            minimumResultsForSearch: Infinity
        });
    });
    </script>
</body>
</html>