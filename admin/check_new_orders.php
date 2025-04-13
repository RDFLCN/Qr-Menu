<?php
require_once '../includes/db.php';

$table_token = $_GET['token'] ?? '';

// Masa ID'sini al
if ($table_token) {
    $stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
    $stmt->execute([$table_token]);
    $table = $stmt->fetch(PDO::FETCH_ASSOC);
    $table_id = $table['id'] ?? null;
}

if ($table_id) {
    // Masa için yeni siparişleri say
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE table_id = ? AND paid = 0 AND notified = 0");
    $stmt->execute([$table_id]);
    $new_orders = $stmt->fetchColumn();
} else {
    // Tüm siparişler için yeni bildirilmemiş siparişleri say
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE notified = 0");
    $new_orders = $stmt->fetchColumn();
}

echo json_encode(['new_orders' => $new_orders]);
?>
