<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../includes/db.php';
require_once '../includes/phpqrcode/qrlib.php';

$qrPath = '';
$qrData = '';

// Masa oluşturma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $token = bin2hex(random_bytes(8));
    $status = 'boş';

    $qrData = "https://crabpubcafe.com/Qr-Menu/menu.php?table=$token";
    $qrDir = '../assets/qrcodes/';
    if (!file_exists($qrDir)) {
        mkdir($qrDir, 0777, true);
    }
    $qrPath = $qrDir . "masa_" . $token . ".png";

    QRcode::png($qrData, $qrPath, QR_ECLEVEL_L, 4);

    $stmt = $pdo->prepare("INSERT INTO `tables` (name, token, status) VALUES (?, ?, ?)");
    $stmt->execute([$name, $token, $status]);

    header("Location: add_table.php?success=1&name=" . urlencode($name) . "&token=$token");
    exit;
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $qrData = "https://crabpubcafe.com/Qr-Menu/menu.php?table=" . $_GET['token'];
    $qrPath = "../assets/qrcodes/masa_" . $_GET['token'] . ".png";
    $success = true;
    $name = $_GET['name'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yeni Masa Oluştur</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: #f5f6f9;
      color: #333;
      padding: 40px 20px;
    }
    h1 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 30px;
    }
    .wrapper {
      max-width: 1100px;
      margin: auto;
      display: flex;
      gap: 40px;
      justify-content: center;
      align-items: flex-start;
      flex-wrap: wrap;
    }
    .form-box {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      flex: 1;
      min-width: 300px;
    }
    .form-box label {
      display: block;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .form-box input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 16px;
      margin-bottom: 20px;
    }
    .form-box button {
      width: 100%;
      background: #ffc107;
      border: none;
      color: #000;
      font-weight: bold;
      padding: 14px;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .form-box button:hover {
      background: #e0a800;
    }
    .qr-box {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      flex: 1;
      min-width: 300px;
      text-align: center;
    }
    .qr-box .success {
      background: #e6f9ed;
      border: 1px solid #b1e2c1;
      padding: 12px;
      border-radius: 10px;
      color: #2b7a4b;
      font-weight: 600;
      margin-bottom: 15px;
    }
    .qr-box img {
      width: 200px;
      margin: 15px 0;
      border: 1px solid #ddd;
      border-radius: 12px;
    }
    .qr-box a {
      display: block;
      color: #007bff;
      margin: 10px 0;
      font-weight: 500;
    }
    .copy-btn {
      padding: 10px 18px;
      background: #28a745;
      color: white;
      font-size: 14px;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .copy-btn:hover {
      background: #218838;
    }
    @media screen and (max-width: 768px) {
      .wrapper { flex-direction: column; }
    }
  </style>
</head>
<body>
  <h1>Yeni Masa Oluştur</h1>
  <div class="wrapper">
    <div class="form-box">
      <form method="POST">
        <label for="name">Masa Adı</label>
        <input type="text" name="name" id="name" placeholder="Örn: Masa 1" required>
        <button type="submit">Masa Oluştur</button>
      </form>
    </div>

    <?php if (!empty($qrPath)): ?>
    <div class="qr-box">
      <div class="success">✅ Masa başarıyla oluşturuldu!</div>
      <p><strong>Menü Linki:</strong></p>
      <a id="menuLink" href="<?= $qrData ?>" target="_blank"><?= $qrData ?></a>
      <button class="copy-btn" onclick="copyToClipboard()">🔗 Bağlantıyı Kopyala</button>
      <img src="<?= $qrPath ?>" alt="QR Kodu">
    </div>
    <?php endif; ?>
  </div>

  <script>
    function copyToClipboard() {
      const text = document.getElementById('menuLink').href;
      navigator.clipboard.writeText(text).then(() => {
        alert("Link kopyalandı!");
      });
    }
  </script>
</body>
</html>