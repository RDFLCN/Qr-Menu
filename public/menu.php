<?php
require_once '../includes/db.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    die("Geçersiz bağlantı.");
}

// Token ile masa bul
$stmt = $pdo->prepare("SELECT * FROM tables WHERE token = ?");
$stmt->execute([$token]);
$masa = $stmt->fetch();

if (!$masa) {
    die("Masa bulunamadı.");
}

// Ürünleri çek
$urunler = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Menü - <?= htmlspecialchars($masa['name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        h2 { color: #333; }
        .urun { background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); padding: 15px; margin-bottom: 15px; }
        .urun img { width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; }
        .btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
        .btn:hover { background: #218838; }
    </style>
</head>
<body>

<h2><?= htmlspecialchars($masa['name']) ?> Masası - Dijital Menü</h2>

<?php if (empty($urunler)): ?>
    <p>Henüz menüye ürün eklenmemiş.</p>
<?php else: ?>
    <?php foreach ($urunler as $urun): ?>
        <div class="urun">
            <?php if ($urun['image']): ?>
                <img src="/Qr-Menu/uploads/<?= htmlspecialchars($urun['image']) ?>" alt="<?= htmlspecialchars($urun['name']) ?>">
            <?php endif; ?>
            <h3><?= htmlspecialchars($urun['name']) ?></h3>
            <p><?= htmlspecialchars($urun['description']) ?></p>
            <strong>₺<?= number_format($urun['price'], 2, ',', '.') ?></strong><br><br>

            <form action="submit_order.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $urun['id'] ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <button type="submit" class="btn">Sipariş Ver</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
