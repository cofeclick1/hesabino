<?php
// بررسی دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}
?>
<!-- شروع نوار بالایی -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <button class="btn btn-link sidebar-toggle d-md-none">
            <i class="fas fa-bars"></i>
        </button>

        <div class="navbar-brand">
            <?php echo $pageTitle ?? SITE_NAME; ?>
        </div>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <!-- اعلان‌ها -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if (isset($notifications_count) && $notifications_count > 0): ?>
                            <span class="badge bg-danger"><?php echo $notifications_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- محتوای اعلان‌ها -->
                    </div>
                </li>

                <!-- پروفایل کاربر -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?php echo ASSETS_URL; ?>/images/avatars/<?php echo $_SESSION['user_avatar'] ?? 'default.png'; ?>" 
                             alt="<?php echo htmlspecialchars($_SESSION['username']); ?>" 
                             class="rounded-circle" 
                             width="32">
                        <span class="d-none d-md-inline ms-2">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/profile.php">
                            <i class="fas fa-user fa-fw me-2"></i>پروفایل
                        </a>
                        <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/settings.php">
                            <i class="fas fa-cog fa-fw me-2"></i>تنظیمات
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/logout.php">
                            <i class="fas fa-sign-out-alt fa-fw me-2"></i>خروج
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- پایان نوار بالایی -->