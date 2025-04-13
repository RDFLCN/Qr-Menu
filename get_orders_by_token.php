<?php
require_once 'includes/db.php';

$table_token = $_GET['token'] ?? '';
if (!$table_token) {
    echo json_encode([]);
    exit;
}

// Masanın ID'sini al
$stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
$stmt->execute([$table_token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$table) {
    echo json_encode([]);
    exit;
}

$table_id = $table['id'];

// Siparişleri getir (grup halinde ve toplam adetle)
$stmt = $pdo->prepare("
    SELECT 
        o.product_id,
        p.name AS product_name,
        o.status,
        SUM(o.quantity) AS quantity,
        o.order_placed_at,
        o.prepared_at,
        o.delivered_at
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.table_id = ? AND o.is_archived = 0
    GROUP BY o.product_id, o.status
    ORDER BY FIELD(o.status, 'hazırlanıyor', 'hazırlandı', 'teslim edildi', 'iptal'), o.order_placed_at DESC
");
$stmt->execute([$table_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatlama işlemi
foreach ($orders as &$order) {
    $order['order_placed_at'] = date('Y-m-d H:i:s', strtotime($order['order_placed_at']));
    $order['prepared_at'] = $order['prepared_at'] ? date('Y-m-d H:i:s', strtotime($order['prepared_at'])) : null;
    $order['delivered_at'] = $order['delivered_at'] ? date('Y-m-d H:i:s', strtotime($order['delivered_at'])) : null;
}

echo json_encode($orders);
?>
