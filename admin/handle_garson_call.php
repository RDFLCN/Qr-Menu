<?php
require_once '../includes/db.php';

// POST üzerinden table_id bekliyoruz
$table_id = $_POST['table_id'] ?? '';

if ($table_id) {
    // Yeni garson çağrısı ekle (status 'pending' olarak)
    $stmt = $pdo->prepare("INSERT INTO garson_calls (table_id, status) VALUES (?, 'pending')");
    if ($stmt->execute([$table_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Garson çağrısı oluşturulamadı.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz masa bilgisi.']);
}
?>
