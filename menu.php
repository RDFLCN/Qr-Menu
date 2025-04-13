<?php
require_once 'includes/db.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die('<h2 style="text-align:center; margin-top: 100px;">Geçersiz bağlantı.</h2>');
}

$stmt = $pdo->prepare("SELECT * FROM tables WHERE token = ? AND visible = 1");
$stmt->execute([$token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    die('<h2 style="text-align:center; margin-top: 100px;">Masa bulunamadı veya pasif durumda.</h2>');
}

// Aktif kategorileri sıraya göre al
$categories = $pdo->query("SELECT * FROM categories WHERE visible = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ürünleri kategori bazlı grupla
$products = $pdo->query("SELECT * FROM products WHERE visible = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$productMap = [];
foreach ($products as $p) {
    $productMap[$p['category_id']][] = $p;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Menü</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .product-card img { max-height: 150px; object-fit: cover; }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="text-center mb-4">
      <h3 class="fw-bold">Kenarlı Ziraat QR Menü</h3>
      <p class="text-muted">Masa: <?= htmlspecialchars($table['name']) ?></p>
    </div>

    <?php foreach ($categories as $cat): ?>
      <div class="mb-4">
        <h5 class="text-primary border-bottom pb-1">🍽 <?= htmlspecialchars($cat['name']) ?></h5>
        <div class="row g-3">
          <?php if (isset($productMap[$cat['id']])): ?>
            <?php foreach ($productMap[$cat['id']] as $product): ?>
              <div class="col-md-4">
                <div class="card product-card h-100">
                  <?php if ($product['image_url']): ?>
                    <img src="assets/images/<?= $product['image_url'] ?>" class="card-img-top" alt="">
                  <?php endif; ?>
                  <div class="card-body">
                    <h6 class="card-title mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                    <p class="small text-muted mb-1"><?= htmlspecialchars($product['description']) ?></p>
                    <div class="fw-semibold">₺<?= number_format($product['price'], 2) ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 text-muted small">Bu kategoride ürün yok.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</body>
</html>
