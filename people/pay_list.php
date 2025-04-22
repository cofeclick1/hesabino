<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!$auth->hasPermission('payments_view') && !$_SESSION['is_super_admin']) {
    $_SESSION['error'] = 'شما دسترسی لازم برای مشاهده این صفحه را ندارید';
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

// پارامترهای فیلتر
$filters = [
    'search' => $_GET['search'] ?? '',
    'from_date' => $_GET['from_date'] ?? '',
    'to_date' => $_GET['to_date'] ?? '',
    'project_id' => $_GET['project_id'] ?? '',
    'currency_code' => $_GET['currency_code'] ?? '',
    'status' => $_GET['status'] ?? '',
    'sort' => $_GET['sort'] ?? 'payment_date', // تغییر از 'date' به 'payment_date'
    'order' => $_GET['order'] ?? 'desc',
    'page' => max(1, intval($_GET['page'] ?? 1)),
    'per_page' => 20
];

// پارامترهای کوئری
$params = [];
$whereConditions = ['1=1']; // شرط پیش‌فرض که همیشه درسته

// اعمال فیلترها به شرط‌ها
if (!empty($filters['search'])) {
    $whereConditions[] = "(
        p.document_number LIKE ? OR
        p.description LIKE ?
    )";
    $searchTerm = '%' . $filters['search'] . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm]);
}

if (!empty($filters['from_date'])) {
    $whereConditions[] = "p.payment_date >= ?";
    $params[] = $filters['from_date'];
}

if (!empty($filters['to_date'])) {
    $whereConditions[] = "p.payment_date <= ?";
    $params[] = $filters['to_date'];
}

if (!empty($filters['project_id'])) {
    $whereConditions[] = "p.project_id = ?";
    $params[] = $filters['project_id'];
}

if (!empty($filters['currency_code'])) {
    $whereConditions[] = "p.currency_code = ?";
    $params[] = $filters['currency_code'];
}

if (!empty($filters['status'])) {
    $whereConditions[] = "COALESCE(p.status, 'pending') = ?";
    $params[] = $filters['status'];
}

// ساخت WHERE clause
$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// مرتب‌سازی
$validSortColumns = ['payment_date', 'document_number', 'amount', 'status']; // تغییر 'date' به 'payment_date'
$sort = in_array($filters['sort'], $validSortColumns) ? $filters['sort'] : 'payment_date';
$order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
$orderClause = "ORDER BY p.{$sort} {$order}";

// کوئری شمارش کل رکوردها
$countQuery = "
    SELECT COUNT(*) as total 
    FROM payments p
    LEFT JOIN currencies c ON p.currency_code = c.code
    {$whereClause}
";

$totalRows = $db->query($countQuery, $params)->fetch()['total'];
$totalPages = ceil($totalRows / $filters['per_page']);

// صفحه‌بندی
$offset = ($filters['page'] - 1) * $filters['per_page'];
$limitClause = "LIMIT {$filters['per_page']} OFFSET {$offset}";

// کوئری اصلی
$query = "
    SELECT 
        p.*,
        c.symbol as currency_symbol,
        c.name as currency_name
    FROM payments p
    LEFT JOIN currencies c ON p.currency_code = c.code
    {$whereClause}
    {$orderClause}
    {$limitClause}
";

// دریافت لیست پرداخت‌ها
$payments = $db->query($query, $params)->fetchAll();

// دریافت لیست پروژه‌ها برای فیلتر
$projects = $db->query("
    SELECT id, name, logo_path 
    FROM projects 
    WHERE status = 'active' 
    ORDER BY name
")->fetchAll();

// دریافت لیست ارزها برای فیلتر
$currencies = $db->query("
    SELECT code, symbol, name 
    FROM currencies 
    WHERE is_active = 1 
    ORDER BY is_default DESC, name
")->fetchAll();

$pageTitle = 'لیست پرداخت‌ها';
$customCss = [
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
    'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css',
    'https://unpkg.com/persian-datepicker@latest/dist/css/persian-datepicker.min.css'
];

require_once '../includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php require_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content p-4 flex-grow-1">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <h4 class="mb-0"><?php echo $pageTitle ?></h4>
                </div>
                <?php if ($auth->hasPermission('payments_add')): ?>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_PATH ?>/people/pay.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        پرداخت جدید
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" id="filterForm" class="row g-3">
                        <!-- جستجو -->
                        <div class="col-md-3">
                            <label class="form-label">جستجو</label>
                            <input type="text" name="search" class="form-control" 
                                   value="<?php echo htmlspecialchars($filters['search']) ?>" 
                                   placeholder="شماره سند، توضیحات و...">
                        </div>

                        <!-- از تاریخ -->
                        <div class="col-md-2">
                            <label class="form-label">از تاریخ</label>
                            <input type="text" name="from_date" class="form-control date-picker" 
                                   value="<?php echo htmlspecialchars($filters['from_date']) ?>">
                        </div>

                        <!-- تا تاریخ -->
                        <div class="col-md-2">
                            <label class="form-label">تا تاریخ</label>
                            <input type="text" name="to_date" class="form-control date-picker" 
                                   value="<?php echo htmlspecialchars($filters['to_date']) ?>">
                        </div>

                        <!-- پروژه -->
                        <div class="col-md-2">
                            <label class="form-label">پروژه</label>
                            <select name="project_id" class="form-select select2">
                                <option value="">همه</option>
                                <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id'] ?>" 
                                    <?php echo $filters['project_id'] == $project['id'] ? 'selected' : '' ?>>
                                    <?php echo htmlspecialchars($project['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- واحد پول -->
                        <div class="col-md-2">
                            <label class="form-label">واحد پول</label>
                            <select name="currency_code" class="form-select select2">
                                <option value="">همه</option>
                                <?php foreach ($currencies as $currency): ?>
                                <option value="<?php echo $currency['code'] ?>" 
                                    <?php echo $filters['currency_code'] == $currency['code'] ? 'selected' : '' ?>>
                                    <?php echo htmlspecialchars($currency['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- وضعیت -->
                        <div class="col-md-2">
                            <label class="form-label">وضعیت</label>
                            <select name="status" class="form-select select2">
                                <option value="">همه</option>
                                <option value="pending" <?php echo $filters['status'] == 'pending' ? 'selected' : '' ?>>
                                    در انتظار
                                </option>
                                <option value="completed" <?php echo $filters['status'] == 'completed' ? 'selected' : '' ?>>
                                    تکمیل شده
                                </option>
                                <option value="canceled" <?php echo $filters['status'] == 'canceled' ? 'selected' : '' ?>>
                                    لغو شده
                                </option>
                            </select>
                        </div>

                        <!-- دکمه‌ها -->
                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>
                                اعمال فیلتر
                            </button>
                            <a href="<?php echo BASE_PATH ?>/people/pay_list.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                حذف فیلتر
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- لیست پرداخت‌ها -->
            <div class="card">
                <div class="card-body">
                    <?php if (count($payments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>
                                            <a href="?<?php echo http_build_query(array_merge($filters, ['sort' => 'document_number', 'order' => $sort === 'document_number' && $order === 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                               class="text-decoration-none text-dark">
                                                شماره سند
                                                <?php if ($sort === 'document_number'): ?>
                                                    <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?<?php echo http_build_query(array_merge($filters, ['sort' => 'payment_date', 'order' => $sort === 'payment_date' && $order === 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                               class="text-decoration-none text-dark">
                                                تاریخ
                                                <?php if ($sort === 'payment_date'): ?>
                                                    <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>توضیحات</th>
                                        <th>
                                            <a href="?<?php echo http_build_query(array_merge($filters, ['sort' => 'amount', 'order' => $sort === 'amount' && $order === 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                               class="text-decoration-none text-dark">
                                                مبلغ
                                                <?php if ($sort === 'amount'): ?>
                                                    <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?<?php echo http_build_query(array_merge($filters, ['sort' => 'status', 'order' => $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                               class="text-decoration-none text-dark">
                                                وضعیت
                                                <?php if ($sort === 'status'): ?>
                                                    <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['document_number'] ?? '') ?></td>
                                            <td><?php echo formatJalaliDate($payment['payment_date']) ?></td>
                                            <td><?php echo htmlspecialchars($payment['description'] ?? '') ?></td>
                                            <td>
                                                <span class="text-nowrap">
                                                    <?php echo number_format($payment['amount'] ?? 0) ?>
                                                    <?php echo htmlspecialchars($payment['currency_symbol'] ?? '') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'badge bg-warning',
                                                    'completed' => 'badge bg-success',
                                                    'canceled' => 'badge bg-danger'
                                                ][$payment['status'] ?? 'pending'] ?? 'badge bg-secondary';
                                                
                                                $statusText = [
                                                    'pending' => 'در انتظار',
                                                    'completed' => 'تکمیل شده',
                                                    'canceled' => 'لغو شده'
                                                ][$payment['status'] ?? 'pending'] ?? 'نامشخص';
                                                ?>
                                                <span class="<?php echo $statusClass ?>">
                                                    <?php echo $statusText ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <?php if ($auth->hasPermission('payments_edit')): ?>
                                                    <a href="<?php echo BASE_PATH ?>/people/pay.php?id=<?php echo $payment['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="ویرایش">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if ($auth->hasPermission('payments_delete')): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-payment" 
                                                            data-id="<?php echo $payment['id'] ?>" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $filters['page'] == $i ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                                <?php echo $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="<?php echo BASE_PATH ?>/assets/images/no-data.svg" alt="بدون داده" 
                                 style="max-width: 200px;">
                            <p class="text-muted mt-3">هیچ پرداختی یافت نشد</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$customJs = [
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fa.js',
    'https://unpkg.com/persian-date@latest/dist/persian-date.min.js',
    'https://unpkg.com/persian-datepicker@latest/dist/js/persian-datepicker.min.js',
    BASE_PATH . '/assets/js/pay-list.js'
];

require_once '../includes/footer.php';
?>