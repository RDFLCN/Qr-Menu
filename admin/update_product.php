<?php
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Ürünü veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Ürün bulunamadı.");
    }

    // Kategorileri al
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} else {
    die("Geçersiz ürün ID'si.");
}

// Ürün güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Resim işlemi
    $image = $product['image']; // Varsayılan resim
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = '../assets/product_images/';
        $image = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
    }

    // Ürünü güncelle
    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$name, $description, $price, $category_id, $image, $is_active, $product_id]);

    echo "Ürün başarıyla güncellendi.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ürün Güncelle</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Stil dosyası */
  </style>
</head>
<body>
  <div class="container">
    <h2>Ürün Güncelle</h2>
    <form method="POST" enctype="multipart/form-data">
      <label for="name">Ürün Adı</label>
      <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>
      
      <label for="description">Açıklama</label>
      <textarea name="description" id="description" required><?= htmlspecialchars($product['description']) ?></textarea>
      
      <label for="price">Fiyat</label>
      <input type="number" name="price" id="price" value="<?= $product['price'] ?>" required>
      
      <label for="category_id">Kategori</label>
      <select name="category_id" id="category_id" required>
        <?php foreach ($categories as $category): ?>
          <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($category['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="image">Ürün Resmi</label>
      <input type="file" name="image" id="image">
      
      <label for="is_active">Aktif mi?</label>
      <input type="checkbox" name="is_active" id="is_active" <?= $product['is_active'] ? 'checked' : '' ?>>

      <button type="submit">Güncelle</button>
    </form>
  </div>
</body>
</html>
