<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? '';

    if (!$category_id) {
        die("Geçersiz kategori ID'si.");
    }

    // Kategori durumunu değiştir
    $stmt = $pdo->prepare("UPDATE categories SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$category_id]);

    // Kategoriyi güncelledikten sonra listeye yönlendir
    header("Location: categories.php?status_changed=1");
    exit;
}
?>
