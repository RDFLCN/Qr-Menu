<?php
require_once '../includes/db.php';

// "pending" durumundaki tüm garson çağrılarının detaylarını çekiyoruz.
$stmt = $pdo->prepare("
    SELECT gc.id, gc.table_id, t.name AS table_name, gc.created_at 
    FROM garson_calls gc 
    JOIN tables t ON gc.table_id = t.id 
    WHERE gc.status = 'pending' 
    ORDER BY gc.created_at ASC
");
$stmt->execute();
$calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['calls' => $calls]);
