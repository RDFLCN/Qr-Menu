<?php
require_once 'includes/db.php';
require_once 'phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $token = bin2hex(random_bytes(4)); // rastgele token üret

    // 1. Veritabanına ekle
    $stmt = $pdo->prepare("INSERT INTO tables (name, token, status, total_amount) VALUES (?, ?, 'boş', 0)");
    $stmt->execute([$name, $token]);

    $link = "https://crabpubcafe.com/Qr-Menu/menu.php?table=" . $token;

    // 2. QR üret
    $qrPath = "assets/qrcodes/masa_$token.png";
    QRcode::png($link, $qrPath, QR_ECLEVEL_L, 4);

    echo "<h3>Masa başarıyla oluşturuldu!</h3>";
    echo "<p><strong>Masa Adı:</strong> $name</p>";
    echo "<p><strong>QR Kodu:</strong><br><img src='$qrPath' width='200' /></p>";
    echo "<p><strong>Menü Linki:</strong> <a href='$link' target='_blank'>$link</a></p>";
}
?>

<form method="POST">
  <label>Masa Adı:</label><br>
  <input type="text" name="name" required><br><br>
  <button type="submit">Masa Oluştur</button>
</form>
