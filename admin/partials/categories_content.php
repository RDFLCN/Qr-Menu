<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sort = (int)$_POST['sort_order'];
    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO categories (name, sort_order, visible) VALUES (?, ?, 1)");
        $stmt->execute([$name, $sort]);
        header("Location: categories.php?success=1");
        exit;
    }
}

// Silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    header("Location: categories.php?deleted=1");
    exit;
}



$categories = getCategories();
$success = '';
$error = '';

if (isset($_GET['success'])) $success = "Kategori başarıyla eklendi.";
if (isset($_GET['deleted'])) $success = "Kategori silindi.";
if (isset($_GET['updated'])) $success = "Kategori güncellendi.";
?>

<div class="container">
  <h2 class="mb-4">Kategori Yönetimi</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="post" action="categories.php" class="mb-4">
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