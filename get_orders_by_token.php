<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if (!$token) {
    echo json_encode([]);
    exit;
}

// Token ile table_id alınır
$stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
$stmt->execute([$token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    echo json_encode([]);
    exit;
}

$table_id = $table['id'];

// Aktif masa oturumu kontrolü
$sessionCheck = $pdo->prepare("SELECT * FROM table_sessions WHERE table_id = ? AND ended_at IS NULL");
$sessionCheck->execute([$table_id]);
$activeSession = $sessionCheck->fetch(PDO::FETCH_ASSOC);

if (!$activeSession) {
    echo json_encode(['error' => 'session_closed']);
    exit;
}

// Siparişleri getir
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
