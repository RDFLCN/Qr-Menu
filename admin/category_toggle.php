<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz kategori ID.");
}

$id = (int)$_GET['id'];

// Mevcut durumu al
$stmt = $pdo->prepare("SELECT visible FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Kategori bulunamadı.");
}

$newStatus = $category['visible'] ? 0 : 1;

$stmt = $pdo->prepare("UPDATE categories SET visible = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);

header("Location: categories.php");
exit;
