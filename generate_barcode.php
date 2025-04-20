<?php
require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$prefix = sanitize($_POST['prefix'] ?? '');
if (empty($prefix)) {
    http_response_code(400);
    echo json_encode(['error' => 'Prefix is required']);
    exit;
}

$db = Database::getInstance();
$barcode = generateUniqueBarcode($db, $prefix);

echo json_encode(['barcode' => $barcode]);

function generateUniqueBarcode($db, $prefix) {
    do {
        $barcode = $prefix . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $db->query("SELECT id FROM products WHERE store_barcode = ?", [$barcode]);
    } while ($stmt->rowCount() > 0);
    return $barcode;
}