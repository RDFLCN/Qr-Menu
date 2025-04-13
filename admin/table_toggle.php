<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz masa ID.");
}

$id = (int)$_GET['id'];

// Mevcut durumu al
$stmt = $pdo->prepare("SELECT visible FROM tables WHERE id = ?");
$stmt->execute([$id]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    die("Masa bulunamadı.");
}

// Durumu tersine çevir
$newStatus = $table['visible'] ? 0 : 1;

// Güncelle
$stmt = $pdo->prepare("UPDATE tables SET visible = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);

// Liste sayfasına geri dön
header("Location: tables.php");
exit;
