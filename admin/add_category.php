<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $order = $_POST['order'] ?? 0;
    $description = $_POST['description'] ?? '';  // Açıklama alanı
    $is_active = isset($_POST['is_active']) ? 1 : 0; // Aktif/Pasif durumu

    if (!$name) {
        die("Kategori adı boş olamaz.");
    }

    // Kategoriyi ekle
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, `order`, is_active) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $order, $is_active]);

    header("Location: categories.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategori Ekle</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f8f8;
            color: #333;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #222;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: #218838;
        }

        .icon {
            font-size: 20px;
            margin-right: 10px;
        }

        .checkbox {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🌟 Kategori Ekle</h2>
        <form method="POST">
            <label for="name">Kategori Adı:</label>
            <input type="text" name="name" id="name" required>

            <label for="order">Sıra (öncelik):</label>
            <input type="number" name="order" id="order" value="0" required>

            <label for="description">Açıklama:</label>
            <textarea name="description" id="description" rows="3" placeholder="Kategori hakkında açıklama"></textarea>

            <div class="checkbox">
                <input type="checkbox" name="is_active" id="is_active" checked>
                <label for="is_active">Menüde Görünsün</label>
            </div>

            <button type="submit">✅ Ekle</button>
        </form>
    </div>
</body>
</html>
