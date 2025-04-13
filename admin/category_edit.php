<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz kategori ID");
}

$id = (int)$_GET['id'];

// Kategori verisini al
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Kategori bulunamadı.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sort_order = (int)$_POST['sort_order'];
    $visible = isset($_POST['visible']) ? 1 : 0;

    if ($name === '') {
        $error = "Kategori adı boş olamaz.";
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, sort_order = ?, visible = ? WHERE id = ?");
        if ($stmt->execute([$name, $sort_order, $visible, $id])) {
            header("Location: categories.php?updated=1");
            exit;
        } else {
            $error = "Kategori güncellenemedi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kategori Düzenle</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <h2 class="mb-4">Kategori Düzenle</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label>Kategori Adı</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Sıra Numarası</label>
      <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?>">
    </div>

    <div class="form-check mb-3">
      <input type="checkbox" name="visible" class="form-check-input" <?= $category['visible'] ? 'checked' : '' ?>>
      <label class="form-check-label">Menüde Göster</label>
    </div>

    <button type="submit" class="btn btn-primary">Kaydet</button>
    <a href="categories.php" class="btn btn-secondary">İptal</a>
  </form>
</div>

</body>
</html>
