<?php
require_once 'includes/db.php';

// Masa ID'sini al (QR kodu ile gelen)
if (!isset($_GET['table'])) {
    echo "Geçersiz masa.";
    exit;
}

$table_token = $_GET['table'];

// Masayı bul
$stmt = $pdo->prepare("SELECT * FROM tables WHERE token = ?");
$stmt->execute([$table_token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    echo "Masa bulunamadı.";
    exit;
}

// Siparişin durumunu çek
$stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE table_id = (SELECT id FROM tables WHERE token = ?) ORDER BY created_at DESC LIMIT 1");
$stmtOrder->execute([$table_token]);
$order = $stmtOrder->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sipariş Durumu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center;
        }
        .status {
            padding: 15px;
            background-color: #f2f2f2;
            border-radius: 8px;
            font-size: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sipariş Durumu</h1>

        <?php if ($order): ?>
            <div class="status">
                Sipariş Durumu: <?= htmlspecialchars($order['status']) ?>
            </div>
        <?php else: ?>
            <div class="status">
                Henüz sipariş verilmedi.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
