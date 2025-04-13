<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz ürün ID.");
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT visible FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Ürün bulunamadı.");
}

$newStatus = $product['visible'] ? 0 : 1;

$stmt = $pdo->prepare("UPDATE products SET visible = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);

header("Location: products.php");
exit;
