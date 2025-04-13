<?php
require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;

// Kategoriyi silme işlemi
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

// Kategoriyi sildikten sonra geri yönlendir
header("Location: categories.php?deleted=1");
exit;
?>
