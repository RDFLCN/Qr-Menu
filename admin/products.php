<?php
require_once '../includes/db.php';

// Yayından kaldır ya da aktif yap
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->query("UPDATE products SET is_active = NOT is_active WHERE id = $id");
    header("Location: products.php");
    exit;
}

// Silme işlemi
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    try {
        // Ürün sipariş geçmişinde varsa silme
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE product_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo "<script>alert('Bu ürün geçmişte sipariş verilmiş. Silinemez.'); window.location.href='products.php';</script>";
            exit;
        }

        // Varyantları sil (önce ilişkili veriler)
        $pdo->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);

        // Ürünü sil
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

        header("Location: products.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        echo "<pre>HATA: ".$e->getMessage()."</pre>";
        exit;
    }
}

$products = $pdo->query("SELECT p.*, c.name AS category FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ürünlerim</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f8f8;
      padding: 40px;
    }
    .container {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #f1f1f1;
    }
    img.thumb {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }
    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }
    .edit { background: #007bff; color: #fff; }
    .delete { background: #dc3545; color: #fff; }
    .toggle { background: #ffc107; color: #000; }
  </style>
</head>
<body>
<div class="container">
  <h2>Ürün Listesi</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Görsel</th>
        <th>Adı</th>
        <th>Kategori</th>
        <th>Durum</th>
        <th>İşlemler</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td>
          <?php if (!empty($p['image'])): ?>
            <img src="../assets/product_images/<?= $p['image'] ?>" class="thumb">
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['category']) ?></td>
        <td><?= $p['is_active'] ? '✅ Aktif' : '⛔ Pasif' ?></td>
        <td>
          <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn edit">Düzenle</a>
          <a href="?toggle=<?= $p['id'] ?>" class="btn toggle"><?= $p['is_active'] ? 'Yayından Kaldır' : 'Yayına Al' ?></a>
          <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')" class="btn delete">Sil</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
