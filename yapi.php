<?php
// Klasör Yapısını Listeleme Fonksiyonu
function listFiles($dir) {
    $result = [];
    $files = scandir($dir);
    foreach($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $result[$file] = listFiles($path); // Alt dizinleri de listele
            } else {
                $result[] = $file; // Dosyayı listele
            }
        }
    }
    return $result;
}

// Veritabanı Yapısını Listeleme Fonksiyonu
function getDatabaseStructure($pdo) {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $structure = [];
    foreach ($tables as $table) {
        $stmt = $pdo->query("DESCRIBE " . $table);
        $structure[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $structure;
}

// Klasör Yapısını Al
$projectDir = '/home/crabpubcafe/public_html/Qr-Menu'; // Kendi dizin yolunuzu buraya ekleyin
$filesStructure = listFiles($projectDir);

// Veritabanı Bağlantısı
try {
    $pdo = new PDO('mysql:host=localhost;dbname=crabpubcafe_qr_menu', 'crabpubcafe_qr_kamenu', 'B5opE);4Cd+y-$g{GE'); // DB ayarlarını buraya yazın
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Veritabanı yapısını al
    $dbStructure = getDatabaseStructure($pdo);
} catch (PDOException $e) {
    echo "Veritabanı bağlantısı başarısız: " . $e->getMessage();
    die();
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proje Durum Özeti</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        pre {
            background-color: #eee;
            padding: 20px;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h3 {
            color: #444;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <h2>Proje Durum Özeti</h2>

    <!-- Klasör Yapısı -->
    <div class="section">
        <h3>Klasör Yapısı:</h3>
        <pre><?php print_r($filesStructure); ?></pre>
    </div>

    <!-- Veritabanı Yapısı -->
    <div class="section">
        <h3>Veritabanı Yapısı:</h3>
        <pre><?php print_r($dbStructure); ?></pre>
    </div>

</body>
</html>
