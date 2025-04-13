<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';

// Kategorileri çek
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ürün eklendiyse işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $image_name = '';

    // Görsel yüklendiyse işle
    if (!empty($_FILES['image']['name'])) {
        $target_dir = '../assets/product_images/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('urun_') . '.' . $ext;
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    // Ürünü kaydet (price ve portion alanı artık kullanılmıyor, kaldırıldı)
    $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, image, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $name, $description, $image_name, $is_active]);
    $product_id = $pdo->lastInsertId();

    // Varyantları ekle
    if (!empty($_POST['variant_title'])) {
        $titles = $_POST['variant_title'];
        $prices = $_POST['variant_price'];

        foreach ($titles as $i => $title) {
            $title = trim($title);
            $price = floatval($prices[$i]);
            if ($title && $price > 0) {
                $pdo->prepare("INSERT INTO product_variants (product_id, title, price) VALUES (?, ?, ?)")
                    ->execute([$product_id, $title, $price]);
            }
        }
    }

    header("Location: products.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ürün Ekle</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      padding: 40px;
    }
    .form-container {
      background: #fff;
      padding: 30px;
      max-width: 600px;
      margin: auto;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 20px;
      text-align: center;
    }
    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
    }
    input[type="text"], textarea, select, input[type="number"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-top: 5px;
    }
    input[type="file"] {
      margin-top: 10px;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .checkbox {
      margin-top: 10px;
    }
    button {
      padding: 12px 20px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 20px;
      width: 100%;
    }
    button:hover {
      background: #218838;
    }
    .variant-group {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    .variant-group input {
      flex: 1;
    }
    #variants-wrapper {
      margin-top: 10px;
    }
    .add-variant-btn {
      margin-top: 10px;
      background: #007bff;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Yeni Ürün Ekle</h2>
    <form method="POST" enctype="multipart/form-data">
      <label for="name">Ürün Adı</label>
      <input type="text" name="name" id="name" required>

      <label for="category">Kategori</label>
      <select name="category_id" id="category" required>
        <option value="">Seçiniz</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="description">Açıklama</label>
      <textarea name="description" id="description" rows="3" placeholder="Ürün açıklaması..." required></textarea>

      <label for="image">Ürün Görseli</label>
      <input type="file" name="image" id="image" accept="image/*">

      <div id="variants-wrapper">
        <label>Varyantlar (Porsiyon + Fiyat)</label>
        <div class="variant-group">
          <input type="text" name="variant_title[]" placeholder="Örn: 100gr">
          <input type="number" name="variant_price[]" placeholder="Fiyat" step="0.01">
        </div>
      </div>
      <button type="button" class="add-variant-btn" onclick="addVariant()">+ Varyant Ekle</button>

      <div class="checkbox">
        <input type="checkbox" name="is_active" id="is_active" checked>
        <label for="is_active">Menüde Görünsün</label>
      </div>

      <button type="submit">Ürünü Kaydet</button>
    </form>
  </div>

  <script>
    function addVariant() {
      const wrapper = document.getElementById('variants-wrapper');
      const div = document.createElement('div');
      div.className = 'variant-group';
      div.innerHTML = `
        <input type="text" name="variant_title[]" placeholder="Örn: 200gr">
        <input type="number" name="variant_price[]" placeholder="Fiyat" step="0.01">
      `;
      wrapper.appendChild(div);
    }
  </script>
</body>
</html>