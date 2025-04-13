<?php
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    die("Ürün ID'si bulunamadı.");
}

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

// Ürün düzenlemesi gönderildiyse işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $image_name = $product['image']; // Eski görseli koruyalım

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

    // Ürünü güncelle
    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, image = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$category_id, $name, $description, $image_name, $is_active, $product_id]);

    header("Location: products.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Düzenle</title>
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
        input[type="text"], textarea, select {
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
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Ürün Düzenle</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Ürün Adı</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label for="category">Kategori</label>
            <select name="category_id" id="category" required>
                <option value="">Seçiniz</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="description">Açıklama</label>
            <textarea name="description" id="description" rows="3" placeholder="Ürün açıklaması..." required><?= htmlspecialchars($product['description']) ?></textarea>

            <label for="image">Ürün Görseli</label>
            <input type="file" name="image" id="image" accept="image/*">
            <?php if ($product['image']): ?>
                <p>Mevcut Görsel: <img src="../assets/product_images/<?= $product['image'] ?>" width="100" alt="Ürün Görseli"></p>
            <?php endif; ?>

            <div class="checkbox">
                <input type="checkbox" name="is_active" id="is_active" <?= $product['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Menüde Görünsün</label>
            </div>

            <button type="submit">Ürünü Güncelle</button>
        </form>
    </div>
</body>
</html>
