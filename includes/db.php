<?php
$host = 'localhost';
$dbname = 'crabpubcafe_qr_menuss';         // Veritabanı adı
$username = 'crabpubcafe_qr_kamenu';     // Veritabanı kullanıcı adı
$password = 'B5opE);4Cd+y-$g{GE';        // Veritabanı şifresi

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Veritabanı bağlantı hatası: ' . $e->getMessage());
}
?>
