<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ürün ID'si geçerli değil.");
}

$id = (int)$_GET['id'];
$product = getProductById($id);
$categories = getCategories();

if (!$product) {
    die("Ürün bulunamadı.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_url = $product['image_url'];

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array(strtolower($ext), $allowed)) {
            $newName = uniqid('img_') . '.' . $ext;
            $uploadPath = '../assets/images/' . $newName;

            if (!file_exists('../assets/images/')) {
                mkdir('../assets/images/', 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $image_url = $newName;
            } else {
                $error = "Resim yüklenemedi.";
            }
        } else {
            $error = "Geçersiz dosya türü.";
        }
    }

    if (!$error) {
        $data = [
            'category_id' => $_POST['category_id'],
            'name'        => trim($_POST['name']),
            'price'       => $_POST['price'],
            'description' => trim($_POST['description']),
            'image_url'   => $image_url,
            'visible'     => isset($_POST['visible']) ? 1 : 0,
            'sort_order'  => $_POST['sort_order']
        ];

        if (updateProduct($id, $data)) {
                header("Location: products.php?updated=1");
            $product = getProductById($id); // güncel halini tekrar al
        } else {
            $error = "Güncelleme başarısız.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ürün Düzenle</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <h2 class="mb-4">Ürünü Düzenle</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label>Kategori</label>
      <select name="category_id" class="form-select" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Ürün Adı</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Açıklama</label>
      <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="mb-3">
      <label>Fiyat (₺)</label>
      <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
    </div>

    <div class="mb-3">
      <label>Mevcut Görsel:</label><br>
      <?php if ($product['image_url']): ?>
        <img src="../assets/images/<?= $product['image_url'] ?>" alt="resim" width="100" class="mb-2">
      <?php else: ?>
        <span class="text-muted">Görsel yok</span>
      <?php endif; ?>
      <input type="file" name="image" class="form-control mt-2" accept="image/*">
    </div>

    <div class="mb-3">
      <label>Sıra Numarası</label>
      <input type="number" name="sort_order" class="form-control" value="<?= $product['sort_order'] ?>">

    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="visible" <?= $product['visible'] ? 'checked' : '' ?>>
      <label class="form-check-label">Menüde Göster</label>
    </div>

    <button type="submit" class="btn btn-primary">Kaydet</button>
    <a href="products.php" class="btn btn-secondary">Geri Dön</a>
  </form>
</div>

</body>
</html>
