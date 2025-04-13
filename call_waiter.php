<?php
require_once 'includes/db.php';

$table_token = $_GET['table_token'] ?? '';

if (!$table_token) {
  echo json_encode(['success' => false]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
$stmt->execute([$table_token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
  echo json_encode(['success' => false]);
  exit;
}

$table_id = $table['id'];

// Garson çağırma işlemi yapılacak
// Burada masaya bağlı bir bildirim veya durum değişikliği yapılabilir
$stmt = $pdo->prepare("UPDATE tables SET waiter_called = 1 WHERE id = ?");
$stmt->execute([$table_id]);

echo json_encode(['success' => true]);
?>
