<?php
require_once '../includes/db.php';
// Tüm "pending" durumundaki çağrıları "notified" yapıyoruz.
$stmt = $pdo->prepare("UPDATE garson_calls SET status = 'notified' WHERE status = 'pending'");
$stmt->execute();
echo json_encode(['success' => true]);
?>
