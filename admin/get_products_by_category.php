<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['category_id']) || !is_numeric($_GET['category_id'])) {
    echo json_encode([]);
    exit;
}

$category_id = (int)$_GET['category_id'];

$stmt = $pdo->prepare("SELECT id, name, description, price, image_url AS image FROM products WHERE category_id = ? AND visible = 1 ORDER BY sort_order ASC");
$stmt->execute([$category_id]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($products);
