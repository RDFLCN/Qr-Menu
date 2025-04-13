<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_id = intval($_POST['table_id'] ?? 0);

    if ($table_id > 0) {
        // Siparişin ödemesini al
        $pdo->prepare("UPDATE orders SET paid = 1 WHERE table_id = ?")->execute([$table_id]);

        // Masayı kapat
        $pdo->prepare("UPDATE tables SET status = 'boş', total_amount = 0, left_at = NOW() WHERE id = ?")->execute([$table_id]);

        // Masa kapanma zamanını kaydet
        $pdo->prepare("UPDATE orders SET table_closed_at = NOW() WHERE table_id = ?")->execute([$table_id]);
    }

    header('Location: tables.php');
    exit;
}
?>
