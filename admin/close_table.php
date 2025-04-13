<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_id = intval($_POST['table_id'] ?? 0);

    if ($table_id > 0) {
        // Siparişleri ödendi ve arşivli olarak işaretle
        $pdo->prepare("UPDATE orders SET paid = 1, is_archived = 1 WHERE table_id = ?")->execute([$table_id]);

        // Masayı boşalt
        $pdo->prepare("UPDATE tables SET status = 'boş', total_amount = 0, seated_at = NULL, left_at = NOW() WHERE id = ?")->execute([$table_id]);
    }

    header('Location: tables.php?success=1');
    exit;
}
