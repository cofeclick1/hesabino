<?php
require_once 'includes/init.php';




// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// دریافت اطلاعات کاربر
$auth = new Auth();
$user = $auth->getCurrentUser();

// دریافت آمار کلی
$db = Database::getInstance();

// تعداد کل محصولات فعال
$totalProducts = $db->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'")->fetch()['total'];

// تعداد محصولات کم موجود
$lowStock = $db->query("SELECT COUNT(*) as total FROM products WHERE quantity <= min_quantity AND status = 'active'")->fetch()['total'];

// مجموع فروش امروز
$todaySales = $db->query(
    "SELECT COALESCE(SUM(final_amount), 0) as total FROM invoices 
    WHERE DATE(created_at) = CURDATE() AND status = 'confirmed'"
)->fetch()['total'];

// تعداد فاکتورهای امروز
$todayInvoices = $db->query(
    "SELECT COUNT(*) as total FROM invoices 
    WHERE DATE(created_at) = CURDATE() AND status = 'confirmed'"
)->fetch()['total'];

// محصولات پرفروش (10 تای اول)
$topProducts = $db->query(
    "SELECT p.name, p.code, SUM(i.quantity) as total_sold
    FROM products p
    JOIN invoice_items i ON p.id = i.product_id
    JOIN invoices inv ON i.invoice_id = inv.id
    WHERE inv.status = 'confirmed'
    AND inv.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10"
)->fetchAll();

// آخرین فاکتورها
$recentInvoices = $db->query(
    "SELECT i.*, u.username as created_by_name, CONCAT(c.first_name, ' ', c.last_name) as customer_name
    FROM invoices i
    JOIN users u ON i.created_by = u.id
    JOIN customers c ON i.customer_id = c.id
    WHERE i.status = 'confirmed'
    ORDER BY i.created_at DESC
    LIMIT 5"
)->fetchAll();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
                <div class="row g-4 my-4">
                    <!-- آمار کلی -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-card-inner">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <h3><?php echo number_format($totalProducts); ?></h3>
                                    <p class="mb-0">محصولات فعال</p>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-card-inner">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h3><?php echo number_format($lowStock); ?></h3>
                                    <p class="mb-0">کم موجود</p>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: <?php echo ($lowStock/$totalProducts)*100; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-card-inner">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <h3><?php echo number_format($todaySales); ?></h3>
                                    <p class="mb-0">فروش امروز</p>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-card-inner">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div>
                                    <h3><?php echo number_format($todayInvoices); ?></h3>
                                    <p class="mb-0">فاکتور امروز</p>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- نمودار فروش -->
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">نمودار فروش</h5>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary active" data-period="week">هفته</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-period="month">ماه</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-period="year">سال</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="salesChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- نمودار نقدینگی -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">نمودار نقدینگی</h5>
                            </div>
                            <div class="card-body">
                                <div id="cashFlowChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- نمودار درآمد و هزینه‌ها -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">درآمد و هزینه‌ها</h5>
                            </div>
                            <div class="card-body">
                                <div id="incomeExpenseChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- بدهکاران -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">بدهکاران</h5>
                            </div>
                            <div class="card-body">
                                <div id="debtorsChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- سود یا زیان -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">سود یا زیان</h5>
                            </div>
                            <div class="card-body">
                                <div id="profitLossChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- چک های دریافتی -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">چک های دریافتی</h5>
                            </div>
                            <div class="card-body">
                                <div id="receivedChequesChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- نرخ ارز -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">نرخ ارز</h5>
                            </div>
                            <div class="card-body">
                                <div id="currencyRate"></div>
                            </div>
                        </div>
                    </div>

                    <!-- آخرین فاکتورها -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">آخرین فاکتورها</h5>
                                <a href="invoices.php" class="btn btn-sm btn-primary">
                                    مشاهده همه
                                    <i class="fas fa-arrow-left ms-1"></i>
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>شماره فاکتور</th>
                                                <th>مشتری</th>
                                                <th>مبلغ</th>
                                                <th>تاریخ</th>
                                                <th>کاربر</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentInvoices as $invoice): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['customer_name'] ?? 'نامشخص'); ?></td>
                                                <td><?php echo number_format($invoice['final_amount']); ?></td>
                                                <td><?php echo jdate("Y/m/d H:i", strtotime($invoice['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['created_by_name']); ?></td>
                                                <td>
                                                    <a href="invoice-view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="invoice-print.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>