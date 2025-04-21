<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!$auth->hasPermission('receipts_view')) {
    $_SESSION['error'] = 'شما مجوز دسترسی به این بخش را ندارید';
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

// تنظیمات صفحه‌بندی و فیلترها
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$personId = isset($_GET['person_id']) ? intval($_GET['person_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// ساخت کوئری
$query = "SELECT r.*, 
                 p.name as project_name,
                 COALESCE(u.username, '-') as creator_name,
                 GROUP_CONCAT(DISTINCT CONCAT(pe.first_name, ' ', pe.last_name) SEPARATOR ', ') as persons,
                 COUNT(DISTINCT ri.id) as items_count,
                 COUNT(DISTINCT rt.id) as transactions_count
          FROM receipts r 
          LEFT JOIN projects p ON r.project_id = p.id
          LEFT JOIN users u ON r.created_by = u.id
          LEFT JOIN receipt_items ri ON r.id = ri.receipt_id
          LEFT JOIN people pe ON ri.person_id = pe.id
          LEFT JOIN receipt_transactions rt ON r.id = rt.receipt_id";

$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(r.receipt_number LIKE ? OR r.description LIKE ? OR 
                     pe.first_name LIKE ? OR pe.last_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, array_fill(0, 4, $searchParam));
}

if (!empty($dateFrom)) {
    $fromDate = convertJalaliToGregorian($dateFrom);
    $conditions[] = "DATE(r.date) >= ?";
    $params[] = $fromDate;
}

if (!empty($dateTo)) {
    $toDate = convertJalaliToGregorian($dateTo);
    $conditions[] = "DATE(r.date) <= ?";
    $params[] = $toDate;
}

if ($projectId > 0) {
    $conditions[] = "r.project_id = ?";
    $params[] = $projectId;
}

if ($personId > 0) {
    $conditions[] = "ri.person_id = ?";
    $params[] = $personId;
}

if (!empty($status)) {
    $conditions[] = "r.status = ?";
    $params[] = $status;
}

$conditions[] = "r.deleted_at IS NULL";

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " GROUP BY r.id ORDER BY r.created_at DESC";

// محاسبه تعداد کل رکوردها
$countQuery = "SELECT COUNT(DISTINCT r.id) as total FROM receipts r 
               LEFT JOIN receipt_items ri ON r.id = ri.receipt_id";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}
$totalRecords = $db->query($countQuery, $params)->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// محدود کردن نتایج برای صفحه فعلی
$offset = ($page - 1) * $perPage;
$query .= " LIMIT $perPage OFFSET $offset";

// دریافت لیست دریافت‌ها
$receipts = $db->query($query, $params)->fetchAll();

// گرفتن آمار کلی
$stats = $db->query("
    SELECT 
        COUNT(*) as total_receipts,
        COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_receipts,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_receipts,
        COUNT(CASE WHEN status = 'canceled' THEN 1 END) as canceled_receipts,
        COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END), 0) as total_amount
    FROM receipts
    WHERE deleted_at IS NULL
")->fetch();

// دریافت لیست پروژه‌ها
$projects = $db->query("SELECT id, name FROM projects WHERE deleted_at IS NULL ORDER BY name")->fetchAll();

$pageTitle = 'لیست دریافت‌ها';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    
    <!-- استایل‌ها -->
    <link href="<?php echo BASE_PATH; ?>/assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/sidebar.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <!-- Persian DatePicker -->
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <style>
        .stats-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .stats-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.5rem;
        }
        .table-hover tbody tr {
            transition: all 0.2s ease;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(13,110,253,0.05) !important;
            transform: scale(1.01);
        }
        .filter-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Navbar -->
            <?php include '../includes/navbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4 py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4 class="mb-0">
                            <i class="fas fa-file-invoice me-2"></i>
                            لیست دریافت‌ها
                        </h4>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="<?php echo BASE_PATH; ?>/dashboard.php">داشبورد</a>
                                </li>
                                <li class="breadcrumb-item active">لیست دریافت‌ها</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="receive.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            دریافت جدید
                        </a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-2">کل دریافت‌ها</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_receipts']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-2">پیش‌نویس</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['draft_receipts']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-check"></i>
                                    </div>
                                                                        <div class="ms-3">
                                        <h6 class="mb-2">تایید شده</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['confirmed_receipts']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-2">مجموع دریافتی</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_amount']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card filter-card">
                    <div class="card-body">
                        <form method="GET" id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">جستجو</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="شماره، توضیحات و..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">پروژه</label>
                                <select name="project_id" class="form-select select2">
                                    <option value="">همه</option>
                                    <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>" 
                                            <?php echo $projectId == $project['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">وضعیت</label>
                                <select name="status" class="form-select">
                                    <option value="">همه</option>
                                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>پیش‌نویس</option>
                                    <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>تایید شده</option>
                                    <option value="canceled" <?php echo $status === 'canceled' ? 'selected' : ''; ?>>لغو شده</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">از تاریخ</label>
                                <input type="text" name="date_from" class="form-control datepicker" 
                                       value="<?php echo $dateFrom; ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">تا تاریخ</label>
                                <input type="text" name="date_to" class="form-control datepicker" 
                                       value="<?php echo $dateTo; ?>" readonly>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">تعداد</label>
                                <select name="per_page" class="form-select">
                                    <option value="10" <?php echo $perPage === 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo $perPage === 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Receipts List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="receiptsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 100px">شماره</th>
                                        <th>تاریخ</th>
                                        <th>پروژه</th>
                                        <th>اشخاص</th>
                                        <th>مبلغ</th>
                                        <th>وضعیت</th>
                                        <th>ایجاد کننده</th>
                                        <th style="width: 150px">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($receipts as $receipt): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($receipt['receipt_number']); ?></td>
                                        <td><?php echo toJalali($receipt['date']); ?></td>
                                        <td><?php echo htmlspecialchars($receipt['project_name'] ?? '-'); ?></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($receipt['persons']); ?></small>
                                            <div class="small text-muted">
                                                <?php echo $receipt['items_count']; ?> شخص،
                                                <?php echo $receipt['transactions_count']; ?> تراکنش
                                            </div>
                                        </td>
                                        <td><?php echo number_format($receipt['total_amount']); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'draft' => 'warning',
                                                'confirmed' => 'success',
                                                'canceled' => 'danger'
                                            ][$receipt['status']] ?? 'secondary';
                                            
                                            $statusText = [
                                                'draft' => 'پیش‌نویس',
                                                'confirmed' => 'تایید شده',
                                                'canceled' => 'لغو شده'
                                            ][$receipt['status']] ?? 'نامشخص';
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($receipt['creator_name']); ?></div>
                                            <small class="text-muted">
                                                <?php echo toJalali($receipt['created_at']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="receive.php?id=<?php echo $receipt['id']; ?>" 
                                                   class="btn btn-primary" title="مشاهده">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($receipt['status'] === 'draft'): ?>
                                                <a href="receive.php?id=<?php echo $receipt['id']; ?>&action=edit" 
                                                   class="btn btn-warning" title="ویرایش">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-info print-btn" 
                                                        data-id="<?php echo $receipt['id']; ?>" title="چاپ">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>

                                    <?php if (empty($receipts)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">هیچ دریافتی یافت نشد</div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $perPage; ?>&search=<?php echo urlencode($search); ?>&project_id=<?php echo $projectId; ?>&status=<?php echo $status; ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start = max(1, min($page - 2, $totalPages - 4));
                                    $end = min($totalPages, max(5, $page + 2));
                                    
                                    if ($start > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?page=1&per_page=' . $perPage . '&search=' . urlencode($search) . '&project_id=' . $projectId . '&status=' . $status . '&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '">1</a></li>';
                                        if ($start > 2) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                    }

                                    for ($i = $start; $i <= $end; $i++) {
                                        echo '<li class="page-item' . ($page == $i ? ' active' : '') . '">
                                                <a class="page-link" href="?page=' . $i . '&per_page=' . $perPage . '&search=' . urlencode($search) . '&project_id=' . $projectId . '&status=' . $status . '&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '">' . $i . '</a>
                                              </li>';
                                    }

                                    if ($end < $totalPages) {
                                        if ($end < $totalPages - 1) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&per_page=' . $perPage . '&search=' . urlencode($search) . '&project_id=' . $projectId . '&status=' . $status . '&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '">' . $totalPages . '</a></li>';
                                    }
                                    ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $perPage; ?>&search=<?php echo urlencode($search); ?>&project_id=<?php echo $projectId; ?>&status=<?php echo $status; ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>/assets/js/sidebar.js"></script>

    <script>
        $(document).ready(function() {
            // تنظیمات Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                language: {
                    noResults: function() {
                        return "نتیجه‌ای یافت نشد";
                    }
                }
            });

            // تنظیمات تقویم شمسی
            $('.datepicker').persianDatepicker({
                format: 'YYYY/MM/DD',
                initialValue: false,
                autoClose: true,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                }
            });

            // اعمال فیلترها به صورت خودکار
            $('#filterForm select, #filterForm input[type="checkbox"]').change(function() {
                $('#filterForm').submit();
            });

            // تاخیر در ارسال فرم هنگام تایپ در فیلد جستجو
            let searchTimeout;
            $('input[name="search"]').keyup(function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    $('#filterForm').submit();
                }, 500);
            });

            // چاپ دریافت
            $('.print-btn').click(function() {
                const id = $(this).data('id');
                window.open('print_receipt.php?id=' + id, '_blank');
            });
        });
    </script>
</body>
</html>