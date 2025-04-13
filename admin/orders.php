<?php
// Hata mesajlarını ekrana yazdırma
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';  // Dosya yolunu doğru verdik

// Siparişleri çek
try {
    $stmt = $pdo->query("SELECT o.*, p.name AS product_name, t.token AS table_token, t.id AS table_id 
                         FROM orders o 
                         JOIN products p ON o.product_id = p.id 
                         JOIN tables t ON o.table_id = t.id 
                         ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Veritabanı sorgusu hatası: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Siparişler</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Siparişler</h1>

        <table>
            <thead>
                <tr>
                    <th>Ürün Adı</th>
                    <th>Masa Adı / Numarası</th>
                    <th>Sipariş Durumu</th>
                    <th>Sipariş Zamanı</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($orders) && !empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                            <td><?= "Masa #" . htmlspecialchars($order['table_id']) ?></td>  <!-- Masa ID'si yerine masa adı ya da numarası -->
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Henüz sipariş bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
