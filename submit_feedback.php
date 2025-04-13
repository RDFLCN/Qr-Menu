<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

$rating = $_POST['rating'] ?? '';
$comment = $_POST['comment'] ?? '';
$table_id = $_POST['table_id'] ?? '';

if (!$rating || !$table_id) {
    echo json_encode(['success' => false, 'message' => 'Zorunlu alanlar eksik.']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO feedbacks (table_id, rating, comment, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$table_id, $rating, $comment]);

echo json_encode(['success' => true, 'message' => 'Görüşünüz kaydedildi. Teşekkür ederiz!']);
