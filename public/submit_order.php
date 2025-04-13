<?php
require_once '../includes/db.php';

$product_id = $_POST['product_id'] ?? null;
$token = $_POST['token'] ?? null;

if (!$product_id || !$token) {
    die("Eksik veri.");
}

// Token ile masa ID’sini bul
$stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
$stmt->execute([$token]);
$masa = $stmt->fetch();

if (!$masa) {
    die("Masa bulunamadı.");
}

$table_id = $masa['id'];

// Siparişi kaydet
$insert = $pdo->prepare("INSERT INTO orders (table_id, product_id, status) VALUES (?, ?, ?)");
$insert->execute([$table_id, $product_id, 'hazırlanıyor']);

// Admin’e WhatsApp mesaj linki oluştur
header("Location: https://wa.me/905303895233?text=" . urlencode("Yeni Sipariş!\nMasa: {$token}\nÜrün ID: {$product_id}"));
exit;
