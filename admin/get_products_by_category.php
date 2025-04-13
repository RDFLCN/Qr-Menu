<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db.php';

if (!isset($_GET['category_id'])) {
    echo json_encode(['error' => 'Kategori ID parametresi eksik.']);
    exit;
}

$category_id = intval($_GET['category_id']);

// Kategoriyi baz alarak ürünleri getir
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND is_active = 1");

$stmt->execute([$category_id]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($products)) {
    echo json_encode(['error' => 'Kategoriye ait ürün bulunamadı.']);
    exit;
}

// JSON formatında döndür
echo json_encode($products);
