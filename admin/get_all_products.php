<?php
require_once '../includes/db.php';

// Aktif ürünleri çek
$stmt = $pdo->query("SELECT id, name FROM products WHERE is_active = 1 ORDER BY name ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// JSON formatında döndür
echo json_encode($products);
?>
