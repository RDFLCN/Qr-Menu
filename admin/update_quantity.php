<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($order_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare("UPDATE orders SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $order_id]);
    }

    header('Location: tables.php');
    exit;
}
