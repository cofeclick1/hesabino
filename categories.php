<?php
require_once 'includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$db = Database::getInstance();

// دریافت لیست دسته‌بندی‌ها
$categories = $db->query("SELECT * FROM categories ORDER BY parent_id, name")->fetchAll();

// تبدیل دسته‌بندی‌ها به ساختار درختی
function buildTree($categories, $parent_id = 0, $level = 0) {
    $tree = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $category['level'] = $level;
            $tree[] = $category;
            $tree = array_merge($tree, buildTree($categories, $category['id'], $level + 1));
        }
    }
    return $tree;
}

$categoryTree = buildTree($categories);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت دسته‌بندی‌ها - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .category-level-0 { font-weight: bold; }
        .category-level-1 { margin-right: 20px; }
        .category-level-2 { margin-right: 40px; }
        .category-level-3 { margin-right: 60px; }
        .category-name::before {
            content: "|";
            margin-left: 10px;
            color: #ccc;
        }
        .category-level-1 .category-name::before {
            content: "|----- ";
        }
        .category-level-2 .category-name::before {
            content: "|-------- ";
        }
        .category-level-3 .category-name::before {
            content: "|----------- ";
        }
    </style>
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
                <div class="row align-items-center g-4 mb-4">
                    <div class="col">
                        <h4 class="mb-0">مدیریت دسته‌بندی‌ها</h4>
                    </div>
                    <div class="col-auto">
                        <a href="category-add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            افزودن دسته‌بندی جدید
                        </a>
                    </div>
                </div>

                <!-- لیست دسته‌بندی‌ها -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>نام دسته‌بندی</th>
                                    <th>تصویر</th>
                                    <th width="150">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categoryTree as $category): ?>
                                <tr>
                                    <td class="category-level-<?php echo $category['level']; ?>">
                                        <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                                    </td>
                                    <td>
                                        <img src="<?php echo !empty($category['image']) ? htmlspecialchars($category['image']) : 'assets/images/default-category.png'; ?>" 
                                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                             width="50">
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="category-edit.php?id=<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-info" 
                                               data-bs-toggle="tooltip" 
                                               title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-category" 
                                                    data-id="<?php echo $category['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                    data-bs-toggle="tooltip" 
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box fa-3x mb-3"></i>
                                            <p>هیچ دسته‌بندی یافت نشد!</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال حذف دسته‌بندی -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">حذف دسته‌بندی</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>آیا از حذف دسته‌بندی <strong id="categoryName"></strong> اطمینان دارید؟</p>
                    <p class="text-danger small">این عملیات قابل بازگشت نیست!</p>
                </div>
                <div class="modal-footer">
                    <form action="ajax/category-delete.php" method="POST">
                        <input type="hidden" name="category_id" id="categoryId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // فعال‌سازی tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // مدیریت حذف دسته‌بندی
        $('.delete-category').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#categoryId').val(id);
            $('#categoryName').text(name);
            $('#deleteModal').modal('show');
        });
    });
    </script>
</body>
</html>