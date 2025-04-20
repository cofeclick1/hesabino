<?php
require_once '../includes/init.php';

// تنظیم عنوان صفحه
$pageTitle = 'لیست اشخاص';

// بررسی اکشن‌های درخواستی
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($action === 'delete' && $id > 0) {
        try {
            // حذف نرم رکورد با بروزرسانی deleted_at
            $db->update('people', 
                ['deleted_at' => date('Y-m-d H:i:s')], 
                ['id' => $id]
            );
            $_SESSION['success'] = 'شخص مورد نظر با موفقیت حذف شد';
        } catch (Exception $e) {
            $_SESSION['error'] = 'خطا در حذف شخص: ' . $e->getMessage();
        }
        header('Location: ' . BASE_PATH . '/people/people_list.php');
        exit;
    }
    
    if ($action === 'restore' && $id > 0) {
        try {
            // بازیابی رکورد حذف شده
            $db->update('people', 
                ['deleted_at' => null], 
                ['id' => $id]
            );
            $_SESSION['success'] = 'شخص مورد نظر با موفقیت بازیابی شد';
        } catch (Exception $e) {
            $_SESSION['error'] = 'خطا در بازیابی شخص: ' . $e->getMessage();
        }
        header('Location: ' . BASE_PATH . '/people/people_list.php');
        exit;
    }
}

// تنظیمات صفحه‌بندی و فیلترها
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';

// ساخت کوئری
$query = "SELECT p.*, 
                 COALESCE(u.username, '-') as creator_name,
                 COUNT(i.id) as invoice_count,
                 COALESCE(SUM(i.final_amount), 0) as total_purchases
          FROM people p 
          LEFT JOIN users u ON p.created_by = u.id
          LEFT JOIN invoices i ON p.id = i.customer_id AND i.status = 'confirmed'";

$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR p.mobile LIKE ? OR p.national_code LIKE ? OR p.company LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($type)) {
    $conditions[] = "p.type = ?";
    $params[] = $type;
}

if (!$showDeleted) {
    $conditions[] = "p.deleted_at IS NULL";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " GROUP BY p.id";

// اضافه کردن ترتیب
$allowedSortFields = ['first_name', 'last_name', 'mobile', 'type', 'created_at', 'total_purchases'];
if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'created_at';
}
$query .= " ORDER BY " . $sortBy . " " . ($sortOrder === 'ASC' ? 'ASC' : 'DESC');

// محاسبه تعداد کل رکوردها
$countQuery = "SELECT COUNT(DISTINCT p.id) as total FROM people p";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}
$totalRecords = $db->query($countQuery, $params)->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// محدود کردن نتایج برای صفحه فعلی
$offset = ($page - 1) * $perPage;
$query .= " LIMIT $offset, $perPage";

// دریافت لیست اشخاص
$people = $db->query($query, $params)->fetchAll();

// آمار کلی
$stats = $db->query("
    SELECT 
        COUNT(*) as total_people,
        COUNT(CASE WHEN type = 'real' THEN 1 END) as real_people,
        COUNT(CASE WHEN type = 'legal' THEN 1 END) as legal_people,
        COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as deleted_people
    FROM people
")->fetch();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    
    <!-- فونت‌ها و استایل‌ها -->
    <link href="<?php echo BASE_PATH; ?>/assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    
    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .table > :not(caption) > * > * {
            padding: 0.75rem;
        }
        .badge-soft-success {
            color: #0ab39c;
            background-color: rgba(10,179,156,.1);
        }
        .badge-soft-danger {
            color: #f06548;
            background-color: rgba(240,101,72,.1);
        }
        .badge-soft-warning {
            color: #f7b84b;
            background-color: rgba(247,184,75,.1);
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        }
        .stats-card {
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.04);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0d6efd !important;
            color: #fff !important;
            border: 1px solid #0d6efd;
        }
        .filter-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content w-100">
            <!-- Navbar -->
            <?php include '../includes/navbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4 py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            لیست اشخاص
                        </h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="new_person.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>
                            افزودن شخص جدید
                        </a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card bg-primary bg-opacity-10 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-2">کل اشخاص</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_people']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-success bg-opacity-10 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-user fa-2x text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-2">اشخاص حقیقی</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['real_people']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-info bg-opacity-10 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-building fa-2x text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-2">اشخاص حقوقی</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['legal_people']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-danger bg-opacity-10 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-user-slash fa-2x text-danger"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-2">اشخاص حذف شده</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['deleted_people']); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form method="GET" id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">جستجو</label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="نام، نام خانوادگی، موبایل و..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">نوع شخص</label>
                                    <select name="type" class="form-select">
                                        <option value="">همه</option>
                                        <option value="real" <?php echo $type === 'real' ? 'selected' : ''; ?>>حقیقی</option>
                                        <option value="legal" <?php echo $type === 'legal' ? 'selected' : ''; ?>>حقوقی</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">ترتیب براساس</label>
                                    <select name="sort" class="form-select">
                                        <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>تاریخ ثبت</option>
                                        <option value="first_name" <?php echo $sortBy === 'first_name' ? 'selected' : ''; ?>>نام</option>
                                        <option value="total_purchases" <?php echo $sortBy === 'total_purchases' ? 'selected' : ''; ?>>مجموع خرید</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">نحوه نمایش</label>
                                    <select name="order" class="form-select">
                                        <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>نزولی</option>
                                        <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>صعودی</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">تعداد نمایش</label>
                                    <select name="per_page" class="form-select">
                                        <option value="10" <?php echo $perPage === 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="25" <?php echo $perPage === 25 ? 'selected' : ''; ?>>25</option>
                                        <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50</option>
                                        <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="show_deleted" value="1" class="form-check-input" 
                                           id="showDeleted" <?php echo $showDeleted ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="showDeleted">حذف شده</label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- People List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="peopleTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th style="width: 60px"></th>
                                        <th>نام و نام خانوادگی</th>
                                        <th>موبایل</th>
                                        <th>نوع</th>
                                        <th>تعداد فاکتور</th>
                                        <th>مجموع خرید (ریال)</th>
                                        <th>تاریخ ثبت</th>
                                        <th>ثبت کننده</th>
                                        <th style="width: 150px">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($people as $index => $person): ?>
                                    <tr class="align-middle <?php echo $person['deleted_at'] ? 'table-danger' : ''; ?>">
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <img src="<?php echo !empty($person['profile_image']) ? BASE_PATH . '/' . $person['profile_image'] : BASE_PATH . '/assets/images/default-avatar.png'; ?>" 
                                                 class="avatar-sm" alt="تصویر پروفایل">
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></div>
                                            <?php if (!empty($person['company'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($person['company']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td dir="ltr" class="text-start"><?php echo htmlspecialchars($person['mobile']); ?></td>
                                        <td>
                                            <span class="badge badge-soft-<?php echo $person['type'] === 'real' ? 'success' : 'warning'; ?>">
                                                <?php echo $person['type'] === 'real' ? 'حقیقی' : 'حقوقی'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($person['invoice_count']); ?></td>
                                        <td><?php echo number_format($person['total_purchases']); ?></td>
                                        <td><?php echo toJalali($person['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($person['creator_name']); ?></td>
                                        <td>
                                            <?php if ($person['deleted_at']): ?>
                                                <button type="button" class="btn btn-sm btn-success restore-btn" 
                                                        data-id="<?php echo $person['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>">
                                                    <i class="fas fa-trash-restore"></i>
                                                    بازیابی
                                                </button>
                                            <?php else: ?>
                                                <a href="edit_person.php?id=<?php echo $person['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                    ویرایش
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                        data-id="<?php echo $person['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                    حذف
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($people)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">هیچ شخصی یافت نشد</div>
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
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $perPage; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&show_deleted=<?php echo $showDeleted ? '1' : '0'; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php
                                $start = max(1, min($page - 2, $totalPages - 4));
                                $end = min($totalPages, max(5, $page + 2));
                                
                                if ($start > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1&per_page=' . $perPage . '&search=' . urlencode($search) . '&type=' . $type . '&sort=' . $sortBy . '&order=' . $sortOrder . '&show_deleted=' . ($showDeleted ? '1' : '0') . '">1</a></li>';
                                    if ($start > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }

                                for ($i = $start; $i <= $end; $i++) {
                                    echo '<li class="page-item' . ($page == $i ? ' active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . '&per_page=' . $perPage . '&search=' . urlencode($search) . '&type=' . $type . '&sort=' . $sortBy . '&order=' . $sortOrder . '&show_deleted=' . ($showDeleted ? '1' : '0') . '">' . $i . '</a>
                                          </li>';
                                }

                                if ($end < $totalPages) {
                                    if ($end < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&per_page=' . $perPage . '&search=' . urlencode($search) . '&type=' . $type . '&sort=' . $sortBy . '&order=' . $sortOrder . '&show_deleted=' . ($showDeleted ? '1' : '0') . '">' . $totalPages . '</a></li>';
                                }
                                ?>

                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $perPage; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&show_deleted=<?php echo $showDeleted ? '1' : '0'; ?>">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>/assets/js/sidebar.js"></script>
    
    <script>
    $(document).ready(function() {
        // نمایش پیام‌های موفقیت و خطا
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                title: 'عملیات موفق',
                text: '<?php echo $_SESSION['success']; ?>',
                icon: 'success',
                confirmButtonText: 'تایید',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    popup: 'rtl'
                }
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                title: 'خطا',
                text: '<?php echo $_SESSION['error']; ?>',
                icon: 'error',
                confirmButtonText: 'تایید',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    popup: 'rtl'
                }
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // تنظیمات Select2
        $('select').select2({
            theme: 'bootstrap-5',
            language: {
                noResults: function() {
                    return "نتیجه‌ای یافت نشد";
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

        // تایید حذف
        $('.delete-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: `آیا از حذف "${name}" اطمینان دارید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'خیر',
                customClass: {
                    confirmButton: 'btn btn-danger me-3',
                    cancelButton: 'btn btn-secondary',
                    popup: 'rtl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'people_list.php?action=delete&id=' + id;
                }
            });
        });

        // تایید بازیابی
        $('.restore-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: `آیا از بازیابی "${name}" اطمینان دارید؟`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'بله، بازیابی شود',
                cancelButtonText: 'خیر',
                customClass: {
                    confirmButton: 'btn btn-success me-3',
                                        cancelButton: 'btn btn-secondary',
                    popup: 'rtl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'people_list.php?action=restore&id=' + id;
                }
            });
        });

        // تنظیمات DataTables
        $('#peopleTable').DataTable({
            paging: false,
            searching: false,
            ordering: false,
            info: false,
            language: {
                emptyTable: "هیچ شخصی یافت نشد",
                loadingRecords: "در حال بارگذاری...",
                processing: "در حال پردازش...",
                zeroRecords: "هیچ شخصی یافت نشد"
            }
        });
    });
    </script>
</body>
</html>

                