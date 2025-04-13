<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$categories = getCategories();
$success = '';
$error = '';
if (isset($_GET['updated'])) $success = "Kategori güncellendi.";

// Yeni kategori ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

    if ($name === '') {
        $error = "Kategori adı boş olamaz.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, sort_order) VALUES (?, ?)");
        if ($stmt->execute([$name, $sort_order])) {
            header("Location: categories.php?success=1");
            exit;
        } else {
            $error = "Kategori eklenemedi.";
        }
    }
}

// Silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categories.php?deleted=1");
    exit;
}

if (isset($_GET['success'])) $success = "Kategori başarıyla eklendi.";
if (isset($_GET['deleted'])) $success = "Kategori silindi.";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kategori Yönetimi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <h2 class="mb-4">Kategori Yönetimi</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="post" class="mb-4">
    <div class="input-group mb-3">
      <input type="text" name="name" class="form-control" placeholder="Kategori adı" required>
      <input type="number" name="sort_order" class="form-control" placeholder="Sıra" value="0">
      <button type="submit" class="btn btn-success">Ekle</button>
    </div>
  </form>

  <table class="table table-bordered bg-white shadow-sm">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Kategori Adı</th>
        <th>Sıra</th>
        <th>Durum</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $cat): ?>
        <tr>
          <td><?= $cat['id'] ?></td>
          <td><?= htmlspecialchars($cat['name']) ?></td>
          <td><?= $cat['sort_order'] ?></td>
          <td>
            <a href="category_toggle.php?id=<?= $cat['id'] ?>" class="badge <?= $cat['visible'] ? 'bg-success' : 'bg-secondary' ?>">
              <?= $cat['visible'] ? 'Aktif' : 'Pasif' ?>
            </a>
          </td>
          <td>
            <a href="category_edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
            <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">
               Sil
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>
