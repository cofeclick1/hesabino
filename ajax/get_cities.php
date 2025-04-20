<?php
require_once '../includes/init.php';

if (!isset($_POST['province_id'])) {
    die('خطا در دریافت اطلاعات');
}

$provinceId = intval($_POST['province_id']);
$db = Database::getInstance();

try {
    $cities = $db->query("SELECT id, name FROM cities WHERE province_id = ? ORDER BY name", [$provinceId])->fetchAll();
    
    echo '<option value="">انتخاب کنید</option>';
    foreach ($cities as $city) {
        echo '<option value="' . $city['id'] . '">' . $city['name'] . '</option>';
    }
} catch (Exception $e) {
    echo '<option value="">خطا در دریافت شهرها</option>';
}