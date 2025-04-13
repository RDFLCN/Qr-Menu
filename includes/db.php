<?php
// includes/db.php

$host = 'localhost';
$dbname = 'qr_menu';
$username = 'root';
$password = ''; // XAMPP kullanıyorsan boş bırakılır

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Veritabanı bağlantısı başarılı.";
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
