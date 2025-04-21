<?php
// بررسی دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// تابع کمکی برای نمایش کلاس active در منوی جاری
function isActiveMenu($path) {
    $current_path = $_SERVER['REQUEST_URI'];
    return strpos($current_path, $path) !== false ? 'active' : '';
}

// تابع کمکی برای نمایش باز بودن زیرمنو
function isSubmenuOpen($path) {
    $current_path = $_SERVER['REQUEST_URI'];
    return strpos($current_path, $path) !== false ? 'open' : '';
}
?>

<div class="sidebar" id="mainSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="sidebar-brand">
            <img src="<?php echo BASE_PATH; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="brand-logo">
            <span class="brand-text"><?php echo SITE_NAME; ?></span>
        </a>
    </div>

    <!-- User Profile -->
    <div class="sidebar-profile">
        <div class="profile-info">
            <img src="<?php echo !empty($user['profile_image']) ? BASE_PATH . '/' . $user['profile_image'] : BASE_PATH . '/assets/images/default-avatar.png'; ?>" 
                 alt="<?php echo htmlspecialchars($user['username']); ?>" 
                 class="profile-image">
            <div class="profile-details">
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
                <div class="profile-role"><?php echo htmlspecialchars($user['role'] ?? 'کاربر'); ?></div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="sidebar-nav">
        <!-- داشبورد -->
        <li class="nav-item">
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="nav-link <?php echo isActiveMenu('/dashboard.php'); ?>" data-title="داشبورد">
                <i class="fas fa-home"></i>
                <span class="nav-text">داشبورد</span>
            </a>
        </li>

        <!-- اشخاص -->
        <li class="nav-item has-submenu <?php echo isSubmenuOpen('/people/'); ?>">
            <a href="#" class="nav-link <?php echo isActiveMenu('/people/'); ?>" data-title="اشخاص">
                <i class="fas fa-users"></i>
                <span class="nav-text">اشخاص</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="<?php echo BASE_PATH; ?>/people/people_list.php" class="nav-link <?php echo isActiveMenu('/people/people_list.php'); ?>">
                        <i class="fas fa-list"></i>
                        <span class="nav-text">لیست اشخاص</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/people/new_person.php" class="nav-link <?php echo isActiveMenu('/people/new_person.php'); ?>">
                        <i class="fas fa-user-plus"></i>
                        <span class="nav-text">افزودن شخص</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/people/receive.php" class="nav-link <?php echo isActiveMenu('/people/receive.php'); ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">دریافت</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/people/receive_list.php" class="nav-link <?php echo isActiveMenu('/people/receive_list.php'); ?>">
                        <i class="fas fa-list-alt"></i>
                        <span class="nav-text">لیست دریافت‌ها</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/people/pay.php" class="nav-link <?php echo isActiveMenu('/people/pay.php'); ?>">
                        <i class="fas fa-money-check"></i>
                        <span class="nav-text">پرداخت</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/people/pay_list.php" class="nav-link <?php echo isActiveMenu('/people/pay_list.php'); ?>">
                        <i class="fas fa-list-ol"></i>
                        <span class="nav-text">لیست پرداخت‌ها</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- بانکداری -->
        <li class="nav-item has-submenu <?php echo isSubmenuOpen('/banking/'); ?>">
            <a href="#" class="nav-link <?php echo isActiveMenu('/banking/'); ?>" data-title="بانکداری">
                <i class="fas fa-university"></i>
                <span class="nav-text">بانکداری</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="<?php echo BASE_PATH; ?>/banking/banks.php" class="nav-link <?php echo isActiveMenu('/banking/banks.php'); ?>">
                        <i class="fas fa-building"></i>
                        <span class="nav-text">بانک‌ها</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/banking/transfer.php" class="nav-link <?php echo isActiveMenu('/banking/transfer.php'); ?>">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-text">انتقال وجه</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/banking/checks.php" class="nav-link <?php echo isActiveMenu('/banking/checks.php'); ?>">
                        <i class="fas fa-money-check-alt"></i>
                        <span class="nav-text">مدیریت چک‌ها</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- تنظیمات -->
        <?php if ($_SESSION['is_super_admin']): ?>
        <li class="nav-item has-submenu <?php echo isSubmenuOpen('/settings/'); ?>">
            <a href="#" class="nav-link <?php echo isActiveMenu('/settings/'); ?>" data-title="تنظیمات">
                <i class="fas fa-cog"></i>
                <span class="nav-text">تنظیمات</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="<?php echo BASE_PATH; ?>/settings/users.php" class="nav-link <?php echo isActiveMenu('/settings/users.php'); ?>">
                        <i class="fas fa-users-cog"></i>
                        <span class="nav-text">مدیریت کاربران</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/settings/roles.php" class="nav-link <?php echo isActiveMenu('/settings/roles.php'); ?>">
                        <i class="fas fa-user-shield"></i>
                        <span class="nav-text">نقش‌ها و دسترسی‌ها</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>/settings/general.php" class="nav-link <?php echo isActiveMenu('/settings/general.php'); ?>">
                        <i class="fas fa-sliders-h"></i>
                        <span class="nav-text">تنظیمات عمومی</span>
                    </a>
                </li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- خروج -->
        <li class="nav-item">
            <a href="<?php echo BASE_PATH; ?>/logout.php" class="nav-link" data-title="خروج">
                <i class="fas fa-sign-out-alt"></i>
                <span class="nav-text">خروج</span>
            </a>
        </li>
    </ul>

    <!-- آمار و اطلاعات -->
    <div class="sidebar-stats">
        <?php if ($lowStock > 0): ?>
        <div class="stat-item text-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo $lowStock; ?> کالا در حد نصاب</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- نمایش نسخه -->
    <div class="sidebar-footer">
        <div class="version-info">
            <small>نسخه <?php echo APP_VERSION; ?></small>
        </div>
    </div>
</div>

<!-- Overlay for mobile -->
<div class="sidebar-overlay"></div>