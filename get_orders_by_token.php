<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if (!$token) {
    echo json_encode([]);
    exit;
}

// Token ile masa ID'si alınır
$stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
$stmt->execute([$token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    echo json_encode([]);
    exit;
}

$table_id = $table['id'];

// Siparişler çekilir (iptal edilen hariç)
$stmt = $pdo->prepare("
    SELECT 
        o.id,
        o.product_id,
        p.name AS product_name,
        o.quantity,
        o.status,
        o.order_placed_at,
        o.prepared_at,
        o.delivered_at
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.table_id = ?
    AND o.status != 'iptal'
    ORDER BY o.order_placed_at ASC
");
$stmt->execute([$table_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($orders);
