<?php
require_once '../includes/db.php';

$table_id = intval($_GET['table_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT t.name AS table_name, t.total_amount, o.*, p.name AS product_name
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN tables t ON o.table_id = t.id
    WHERE o.table_id = ? AND o.paid = 0
    ORDER BY o.created_at ASC
");
$stmt->execute([$table_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    echo "<p style='padding: 20px;'>Bu masaya ait aktif sipariş bulunamadı.</p>";
    exit;
}
?>

<style>
    .order-list {
        max-height: 400px;
        overflow-y: auto;
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .order-card {
        border: 1px solid #ddd;
        border-radius: 12px;
        padding: 15px;
        background: #fefefe;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .order-card h4 {
        margin-bottom: 8px;
        font-size: 16px;
    }
    .order-card form {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 10px;
    }
    .order-card select {
        padding: 6px 8px;
        border-radius: 8px;
        border: 1px solid #ccc;
    }
    .order-card button {
        padding: 6px 12px;
        background-color: #198754;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    .total-area {
        margin-top: 20px;
        text-align: right;
        font-weight: bold;
        font-size: 18px;
        color: #444;
    }
    .close-table {
        margin-top: 20px;
        text-align: center;
    }
    .close-table button {
        padding: 12px 20px;
        background-color: #dc3545;
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
</style>

<h2><?= htmlspecialchars($orders[0]['table_name']) ?> - Siparişler</h2>

<div class="order-list">
    <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <h4><?= htmlspecialchars($order['product_name']) ?> x<?= (int)$order['quantity'] ?></h4>
            <p>Durum: <em><?= htmlspecialchars($order['status']) ?></em></p>
            <p>Adet: <?= (int)$order['quantity'] ?></p>
            <p>Sipariş Saati: <?= date('H:i', strtotime($order['created_at'])) ?></p>

            <form method="post" action="update_order_status.php">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <select name="status">
                    <option value="hazırlanıyor" <?= $order['status'] === 'hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                    <option value="hazırlandı" <?= $order['status'] === 'hazırlandı' ? 'selected' : '' ?>>Hazırlandı</option>
                    <option value="teslim edildi" <?= $order['status'] === 'teslim edildi' ? 'selected' : '' ?>>Teslim Edildi</option>
                    <option value="iptal" <?= $order['status'] === 'iptal' ? 'selected' : '' ?>>İptal Edildi</option>
                </select>
                <button type="submit">Güncelle</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<div class="total-area">
    Toplam Tutar: ₺<?= number_format($orders[0]['total_amount'], 2, ',', '.') ?>
</div>

<?php
// Tüm siparişler teslim/iptal edildiyse masayı kapat butonunu göster
$kapatilabilir = true;
foreach ($orders as $o) {
    if (!in_array($o['status'], ['teslim edildi', 'iptal'])) {
        $kapatilabilir = false;
        break;
    }
}
?>

<?php if ($kapatilabilir): ?>
    <div class="close-table">
        <form method="post" action="close_table.php">
            <input type="hidden" name="table_id" value="<?= $table_id ?>">
            <button type="submit">Masayı Kapat ve Ödemeyi Al</button>
        </form>
    </div>
<?php endif; ?>
