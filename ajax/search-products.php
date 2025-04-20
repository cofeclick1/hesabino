<?php
require_once '../includes/init.php';
// پاک کردن هر گونه خروجی قبلی
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    // بررسی وجود پارامتر جستجو
    if (!isset($_GET['query'])) {
        // اگر کوئری خالی باشد، همه محصولات را برمی‌گرداند (حداکثر 10 مورد)
        $sql = "SELECT 
                p.*, 
                c.name as category_name,
                CASE 
                    WHEN p.quantity = 0 THEN 'ناموجود'
                    WHEN p.quantity <= p.min_quantity THEN CONCAT(p.quantity, ' ', COALESCE(p.unit, 'عدد'), ' (کم)')
                    ELSE CONCAT(p.quantity, ' ', COALESCE(p.unit, 'عدد'))
                END as stock_status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            ORDER BY p.name ASC
            LIMIT 10";
        $results = $db->query($sql)->fetchAll();
    } else {
        $query = trim($_GET['query']);
        
        // جستجو بر اساس نام محصول، کد محصول و نام دسته‌بندی
        $sql = "SELECT 
                p.*, 
                c.name as category_name,
                CASE 
                    WHEN p.quantity = 0 THEN 'ناموجود'
                    WHEN p.quantity <= p.min_quantity THEN CONCAT(p.quantity, ' ', COALESCE(p.unit, 'عدد'), ' (کم)')
                    ELSE CONCAT(p.quantity, ' ', COALESCE(p.unit, 'عدد'))
                END as stock_status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            AND (
                p.name LIKE :name_like
                OR p.name LIKE :name_start
                OR p.code LIKE :code_like
                OR c.name LIKE :category_like
                OR p.name = :exact_match
            )
            ORDER BY 
                CASE
                    WHEN p.name = :exact_name THEN 1
                    WHEN p.name LIKE :name_prefix THEN 2
                    WHEN p.code = :exact_code THEN 3
                    ELSE 4
                END,
                p.quantity > 0 DESC,
                p.name ASC
            LIMIT 15";

        $params = [
            ':name_like' => '%' . $query . '%',
            ':name_start' => $query . '%',
            ':code_like' => '%' . $query . '%',
            ':category_like' => '%' . $query . '%',
            ':exact_match' => $query,
            ':exact_name' => $query,
            ':name_prefix' => $query . '%',
            ':exact_code' => $query
        ];

        $results = $db->query($sql, $params)->fetchAll();
    }

    // افزودن اطلاعات نمایشی به نتایج
    foreach ($results as &$product) {
        // تنظیم متن نمایشی محصول
        $displayText = $product['name'];
        if (!empty($product['code'])) {
            $displayText .= ' (کد: ' . $product['code'] . ')';
        }
        if (!empty($product['category_name'])) {
            $displayText .= ' - ' . $product['category_name'];
        }
        $product['display_text'] = $displayText;

        // تنظیم قیمت فرمت‌شده
        $product['price_formatted'] = number_format($product['sale_price']) . ' تومان';
        $product['price'] = $product['sale_price'];

        // افزودن مسیر تصویر محصول (اگر موجود باشد)
        if (!empty($product['image'])) {
            $product['image_url'] = '../uploads/products/' . $product['image'];
        }

        // افزودن موجودی
        $product['stock'] = $product['quantity'];

        // اضافه کردن اطلاعات اضافی برای نمایش بهتر
        $product['formatted_purchase_price'] = number_format($product['purchase_price']) . ' تومان';
        $product['formatted_sale_price'] = number_format($product['sale_price']) . ' تومان';
        $product['profit'] = $product['sale_price'] - $product['purchase_price'];
        $product['formatted_profit'] = number_format($product['profit']) . ' تومان';
        $product['profit_percentage'] = $product['purchase_price'] > 0 
            ? round(($product['profit'] / $product['purchase_price']) * 100, 1) 
            : 0;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $results,
        'query' => $query ?? '',
        'count' => count($results)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('خطا در جستجوی محصولات: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطا در جستجوی محصولات. لطفاً دوباره تلاش کنید.',
        'debug' => DEBUG ? $e->getMessage() : null
    ], JSON_UNESCAPED_UNICODE);
}
