<?php
require_once '../includes/phpqrcode/qrlib.php';
$tables = $pdo->query("SELECT * FROM tables ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$success = '';
$error = '';

if (isset($_GET['success'])) $success = "Masa başarıyla eklendi.";
if (isset($_GET['deleted'])) $success = "Masa silindi.";
if (isset($_GET['updated'])) $success = "Masa güncellendi.";

// Yeni masa ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sort_order = (int)$_POST['sort_order'];
    $visible = isset($_POST['visible']) ? 1 : 0;
    $token = bin2hex(random_bytes(16));

    if ($name === '') {
        $error = "Masa adı boş olamaz.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tables (name, sort_order, visible, token) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $sort_order, $visible, $token])) {
            $tableId = $pdo->lastInsertId();

            // QR kod oluştur
            $qrText = "https://seninsite.com/menu.php?token=" . $token;
            $qrFileName = "table_" . $tableId . ".png";
            $qrPath = "../assets/qrcodes/" . $qrFileName;

            if (!file_exists("../assets/qrcodes/")) {
                mkdir("../assets/qrcodes/", 0777, true);
            }

            QRcode::png($qrText, $qrPath, QR_ECLEVEL_L, 4);

            // QR dosyasını veritabanına kaydet
            $pdo->prepare("UPDATE tables SET qr_code = ? WHERE id = ?")->execute([$qrFileName, $tableId]);

            header("Location: tables.php?success=1");
            exit;
        } else {
            $error = "Masa eklenemedi.";
        }
    }
}

// Masa silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM tables WHERE id = ?")->execute([$id]);
    header("Location: tables.php?deleted=1");
    exit;
}
?>

<div class="container">
  <h2 class="mb-4">Masa Yönetimi</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="post" class="mb-4">
    <div class="input-group mb-3">
      <input type="text" name="name" class="form-control" placeholder="Masa adı" required>
      <input type="number" name="sort_order" class="form-control" placeholder="Sıra" value="0">
      <div class="input-group-text">
        <input type="checkbox" name="visible" checked>
        <label class="ms-2">Aktif</label>
      </div>
      <button type="submit" class="btn btn-success">Ekle</button>
    </div>
  </form>

  <table class="table table-bordered bg-white shadow-sm">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Adı</th>
        <th>QR</th>
        <th>Sıra</th>
        <th>Durum</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tables as $table): ?>
        <tr>
          <td><?= $table['id'] ?></td>
          <td><?= htmlspecialchars($table['name']) ?></td>
          <td>
            <?php if (isset($table['qr_code']) && $table['qr_code']): ?>
              <img src="../assets/qrcodes/<?= $table['qr_code'] ?>" alt="qr" width="60">
            <?php endif; ?>
          </td>
          <td><?= $table['sort_order'] ?></td>
          <td>
            <a href="table_toggle.php?id=<?= $table['id'] ?>" class="badge <?= $table['visible'] ? 'bg-success' : 'bg-secondary' ?>">
              <?= $table['visible'] ? 'Aktif' : 'Pasif' ?>
            </a>
          </td>
          <td>
            <a href="table_edit.php?id=<?= $table['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
            <a href="?delete=<?= $table['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Bu masayı silmek istediğinize emin misiniz?')">Sil</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>