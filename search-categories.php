<?php
require_once 'includes/init.php';

if (!isAjax()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$searchTerm = sanitize($_GET['q'] ?? '');
if (empty($searchTerm)) {
    echo json_encode([]);
    exit;
}

$db = Database::getInstance();
$stmt = $db->query("SELECT id, name FROM categories WHERE name LIKE ? LIMIT 10", ["%$searchTerm%"]);
$categories = $stmt->fetchAll();

echo json_encode($categories);