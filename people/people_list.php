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
$province = isset($_GET['province']) ? $_GET['province'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';

// ساخت کوئری
$query = "SELECT p.*, 
                 COALESCE(u.username, '-') as creator_name,
                 pr.name as province_name,
                 c.name as city_name,
                 COUNT(i.id) as invoice_count,
                 COALESCE(SUM(i.final_amount), 0) as total_purchases
          FROM people p 
          LEFT JOIN users u ON p.created_by = u.id
          LEFT JOIN provinces pr ON p.province = pr.id
          LEFT JOIN cities c ON p.city = c.id
          LEFT JOIN invoices i ON p.id = i.customer_id AND i.status = 'confirmed'";

$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR p.mobile LIKE ? OR 
                     p.national_code LIKE ? OR p.company LIKE ? OR p.email LIKE ? OR 
                     p.phone LIKE ? OR p.card_number LIKE ? OR p.tags LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, array_fill(0, 9, $searchParam));
}

if (!empty($type)) {
    $conditions[] = "p.type = ?";
    $params[] = $type;
}

if (!empty($province)) {
    $conditions[] = "p.province = ?";
    $params[] = $province;
}

if (!empty($dateFrom)) {
    $fromDate = convertJalaliToGregorian($dateFrom);
    $conditions[] = "DATE(p.created_at) >= ?";
    $params[] = $fromDate;
}

if (!empty($dateTo)) {
    $toDate = convertJalaliToGregorian($dateTo);
    $conditions[] = "DATE(p.created_at) <= ?";
    $params[] = $toDate;
}

if (!$showDeleted) {
    $conditions[] = "p.deleted_at IS NULL";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " GROUP BY p.id";

// اضافه کردن ترتیب
$allowedSortFields = [
    'first_name', 'last_name', 'mobile', 'type', 'created_at', 
    'total_purchases', 'invoice_count', 'credit_limit'
];
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
        COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as deleted_people,
        SUM(CASE WHEN deleted_at IS NULL THEN credit_limit ELSE 0 END) as total_credit_limit
    FROM people
")->fetch();

// دریافت لیست استان‌ها
$provinces = $db->query("SELECT id, name FROM provinces ORDER BY name")->fetchAll();

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
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <!-- Persian DatePicker -->
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    
    <style>
        /* استایل‌های اختصاصی صفحه */
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
        .avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        .filter-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .filter-card .card-body {
            padding: 1.5rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0d6efd !important;
            color: #fff !important;
            border: 1px solid #0d6efd;
            border-radius: 5px;
        }
        .social-badge {
            font-size: 1.2rem;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 2px;
            transition: all 0.3s ease;
        }
        .social-badge:hover {
            transform: translateY(-2px);
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        .person-info {
            display: flex;
            align-items: center;
        }
        .person-details {
            margin-right: 10px;
        }
        .person-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }
        .person-company {
            font-size: 0.875rem;
            color: #666;
        }
        .table td {
            vertical-align: middle;
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
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="<?php echo BASE_PATH; ?>/dashboard.php">داشبورد</a>
                                </li>
                                <li class="breadcrumb-item active">لیست اشخاص</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-toolbar justify-content-end">
                            <a href="new_person.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>
                                افزودن شخص جدید
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-2">کل اشخاص</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_people']); ?></h4>
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
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-2">اشخاص حقیقی</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['real_people']); ?></h4>
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
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-2">اشخاص حقوقی</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['legal_people']); ?></h4>
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
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-2">مجموع اعتبار</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_credit_limit']); ?></h4>
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
                                       placeholder="نام، موبایل، کد ملی و..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">نوع شخص</label>
                                <select name="type" class="form-select select2">
                                    <option value="">همه</option>
                                    <option value="real" <?php echo $type === 'real' ? 'selected' : ''; ?>>حقیقی</option>
                                    <option value="legal" <?php echo $type === 'legal' ? 'selected' : ''; ?>>حقوقی</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">استان</label>
                                <select name="province" class="form-select select2">
                                    <option value="">همه</option>
                                    <?php foreach ($provinces as $prov): ?>
                                    <option value="<?php echo $prov['id']; ?>" 
                                            <?php echo $province == $prov['id'] ? 'selected' : ''; ?>>
                                        <?php echo $prov['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
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

                <!-- People List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="peopleTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th style="width: 60px"></th>
                                        <th>مشخصات</th>
                                        <th>اطلاعات تماس</th>
                                        <th>نوع</th>
                                        <th>آمار</th>
                                        <th>موقعیت</th>
                                        <th>تاریخ ثبت</th>
                                        <th style="width: 150px">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($people as $index => $person): ?>
                                    <tr class="<?php echo $person['deleted_at'] ? 'table-danger' : ''; ?>">
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <img src="<?php echo !empty($person['profile_image']) ? 
                                                     BASE_PATH . '/' . $person['profile_image'] : 
                                                     BASE_PATH . '/assets/images/default-avatar.png'; ?>" 
                                                 class="avatar-sm" alt="تصویر پروفایل">
                                        </td>
                                        <td>
                                            <div class="person-info">
                                                <div class="person-details">
                                                    <div class="person-name">
                                                        <?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>
                                                    </div>
                                                    <?php if (!empty($person['company'])): ?>
                                                    <div class="person-company">
                                                        <i class="fas fa-building me-1"></i>
                                                        <?php echo htmlspecialchars($person['company']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($person['tags'])): ?>
                                                    <div class="mt-1">
                                                        <?php foreach (explode(',', $person['tags']) as $tag): ?>
                                                        <span class="badge bg-light text-dark">
                                                            <?php echo htmlspecialchars(trim($tag)); ?>
                                                        </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div dir="ltr" class="text-start">
                                                <?php if (!empty($person['mobile'])): ?>
                                                <div>
                                                    <i class="fas fa-mobile-alt me-1"></i>
                                                    <?php echo htmlspecialchars($person['mobile']); ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($person['phone'])): ?>
                                                <div>
                                                    <i class="fas fa-phone me-1"></i>
                                                    <?php echo htmlspecialchars($person['phone']); ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($person['email'])): ?>
                                                <div>
                                                    <i class="fas fa-envelope me-1"></i>
                                                    <?php echo htmlspecialchars($person['email']); ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-2">
                                                <?php if (!empty($person['instagram_id'])): ?>
                                                <a href="https://instagram.com/<?php echo $person['instagram_id']; ?>" 
                                                   class="social-badge bg-danger bg-opacity-10 text-danger" 
                                                   target="_blank">
                                                    <i class="fab fa-instagram"></i>
                                                </a>
                                                <?php endif; ?>
                                                <?php if (!empty($person['telegram_id'])): ?>
                                                <a href="https://t.me/<?php echo $person['telegram_id']; ?>" 
                                                   class="social-badge bg-info bg-opacity-10 text-info" 
                                                   target="_blank">
                                                    <i class="fab fa-telegram"></i>
                                                </a>
                                                <?php endif; ?>
                                                <?php if (!empty($person['whatsapp_number'])): ?>
                                                <a href="https://wa.me/<?php echo $person['whatsapp_number']; ?>" 
                                                   class="social-badge bg-success bg-opacity-10 text-success" 
                                                   target="_blank">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-<?php echo $person['type'] === 'real' ? 'success' : 'warning'; ?>">
                                                <?php echo $person['type'] === 'real' ? 'حقیقی' : 'حقوقی'; ?>
                                            </span>
                                            <?php if (!empty($person['national_code'])): ?>
                                            <div class="small mt-1">
                                                <?php echo $person['type'] === 'real' ? 'کد ملی: ' : 'شناسه ملی: '; ?>
                                                <?php echo htmlspecialchars($person['national_code']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($person['economic_code'])): ?>
                                            <div class="small">
                                                کد اقتصادی: <?php echo htmlspecialchars($person['economic_code']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div>
                                                    <i class="fas fa-file-invoice me-1"></i>
                                                    تعداد فاکتور: <?php echo number_format($person['invoice_count']); ?>
                                                </div>
                                                <div>
                                                    <i class="fas fa-money-bill-wave me-1"></i>
                                                    مجموع خرید: <?php echo number_format($person['total_purchases']); ?>
                                                </div>
                                                <div>
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    سقف اعتبار: <?php echo number_format($person['credit_limit']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($person['province_name'])): ?>
                                            <div>
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($person['province_name']); ?>
                                                <?php if (!empty($person['city_name'])): ?>
                                                    <br>
                                                    <small><?php echo htmlspecialchars($person['city_name']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <?php echo toJalali($person['created_at']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($person['creator_name']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($person['deleted_at']): ?>
                                                <button type="button" class="btn btn-sm btn-success restore-btn" 
                                                        data-id="<?php echo $person['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>">
                                                    <i class="fas fa-trash-restore"></i>
                                                    بازیابی
                                                </button>
                                            <?php else: ?>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit_person.php?id=<?php echo $person['id']; ?>" 
                                                       class="btn btn-primary" title="ویرایش">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger delete-btn" 
                                                            data-id="<?php echo $person['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_