<?php
// Veritabanından veri çekme
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE visible = 1")->fetchColumn();
?>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card p-4 bg-primary text-white">
      <h3><?= $totalProducts ?></h3>
      <p>Toplam Ürün</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-4 bg-warning text-dark">
      <h3><?= $totalCategories ?></h3>
      <p>Toplam Kategori</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-4 bg-success text-white">
      <h3><?= $activeProducts ?></h3>
      <p>Aktif Ürün</p>
    </div>
  </div>
</div>
