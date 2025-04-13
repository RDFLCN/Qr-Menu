<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz ürün ID.");
}

$id = (int)$_GET['id'];

// Ürünü veritabanından sil
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

// Silme sonrası geri yönlendir
header("Location: products.php");
exit;
