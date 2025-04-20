<?php
// توابع عمومی

function sanitize($input) {
    if (is_array($input)) {
        foreach($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
    } else {
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

function redirect($location) {
    header("Location: {$location}");
    exit;
}

function formatNumber($number) {
    return number_format($number, 0, '.', ',');
}

function jalaliDate($timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    return jdate("Y/m/d", $timestamp);
}

function flashMessage($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function showFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $message = $_SESSION['flash']['message'];
        $type = $_SESSION['flash']['type'];
        unset($_SESSION['flash']);
        
        return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                    {$message}
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return '';
}

function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function createPagination($total, $perPage, $currentPage, $url) {
    $totalPages = ceil($total / $perPage);
    
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="صفحه‌بندی"><ul class="pagination justify-content-center">';
    
    // قبلی
    $html .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">
                <a class="page-link" href="' . ($currentPage > 1 ? $url . ($currentPage - 1) : '#') . '">قبلی</a>
              </li>';
    
    // شماره صفحات
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . $i . '">' . $i . '</a></li>';
        }
    }
    
    // بعدی
    $html .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">
                <a class="page-link" href="' . ($currentPage < $totalPages ? $url . ($currentPage + 1) : '#') . '">بعدی</a>
              </li>';
    
    $html .= '</ul></nav>';
    
    return $html;
}

function uploadImage($file) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $targetFile = $targetDir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $check = getimagesize($file["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        }
    }
    return false;
}

function logActivity($userId, $action) {
    global $db;
    $db->insert('activity_log', [
        'user_id' => $userId,
        'action' => $action,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function getLowStockProducts() {
    global $db;
    return $db->query("SELECT * FROM products WHERE quantity <= min_quantity AND status = 'active'")->fetchAll();
}

function getUserRole($userId) {
    global $db;
    return $db->query("SELECT role FROM users WHERE id = ?", [$userId])->fetchColumn();
}

function formatCurrency($number) {
    return number_format($number, 0, '.', ',') . ' تومان';
}