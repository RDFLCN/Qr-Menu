<?php
require_once '../includes/db.php';

$order_id = $_POST['order_id'] ?? '';

if ($order_id) {
    // Siparişi "iptal" olarak işaretle
    $stmt = $pdo->prepare("UPDATE orders SET status = 'iptal' WHERE id = ?");
    $stmt->execute([$order_id]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz sipariş ID\'si']);
}
?>
