<?php
require_once '../includes/db.php';

$order_id = $_POST['order_id'] ?? '';
$action = $_POST['action'] ?? ''; // "increase" veya "decrease"

if ($order_id && $action) {
    if ($action == 'increase') {
        $stmt = $pdo->prepare("UPDATE orders SET quantity = quantity + 1 WHERE id = ?");
    } else if ($action == 'decrease') {
        $stmt = $pdo->prepare("UPDATE orders SET quantity = quantity - 1 WHERE id = ?");
    }

    $stmt->execute([$order_id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri']);
}
?>
