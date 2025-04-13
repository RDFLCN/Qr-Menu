<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$products = getAllProductsWithCategory();
$success = '';

if (isset($_GET['success'])) $success = "Ürün başarıyla eklendi.";
if (isset($_GET['updated'])) $success = "Ürün başarıyla güncellendi.";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ürün Yönetimi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f7f9fc; }
    .table-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <h2 class="mb-4 text-center">Ürün Yönetimi</h2>
  <?php if ($success): ?>
    <div class="alert alert-success text-center"><?= $success ?></div>
  <?php endif; ?>

  <div class="text-end mb-3">
    <a href="product_add.php" class="btn btn-success">+ Yeni Ürün Ekle</a>
  </div>

  <table class="table table-hover table-bordered bg-white shadow-sm">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Resim</th>
        <th>Adı</th>
        <th>Açıklama</th>
        <th>Fiyat</th>
        <th>Kategori</th>
        <th>Durum</th>
        <th>Sıra</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
        <tr>
          <td><?= $product['id'] ?></td>
          <td>
            <?php if ($product['image_url']): ?>
              <img src="../assets/images/<?= $product['image_url'] ?>" class="table-img" alt="">
            <?php else: ?>
              <span class="text-muted">Yok</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($product['name']) ?></td>
          <td><?= htmlspecialchars($product['description']) ?></td>
          <td><?= number_format($product['price'], 2) ?> ₺</td>
          <td><?= htmlspecialchars($product['category_name']) ?></td>
          <td>
            <a href="product_toggle.php?id=<?= $product['id'] ?>" class="badge <?= $product['visible'] ? 'bg-success' : 'bg-secondary' ?>">
              <?= $product['visible'] ? 'Aktif' : 'Pasif' ?>
            </a>
          </td>
          <td><?= $product['sort_order'] ?></td>
          <td>
            <a href="product_edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
            <a href="product_delete.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>
