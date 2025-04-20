<?php
// تنظیمات پایگاه داده
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'accounting_db');

// تنظیمات برنامه
define('SITE_NAME', 'حسابینو');
define('SITE_URL', 'http://localhost/hesabino');
define('BASE_PATH', '/hesabino');
define('APP_VERSION', '1.0.0');
define('VERSION', APP_VERSION); // برای سازگاری با کدهای قدیمی

// تنظیمات امنیتی
define('ENCRYPTION_KEY', 'your-secret-key-here');
define('SESSION_TIMEOUT', 3600); // 1 hour

// تنظیمات زمانی
date_default_timezone_set('Asia/Tehran');