<?php
require_once '../includes/db.php';

// Kategorileri sıralı olarak getir
$stmt = $pdo->query("SELECT * FROM categories ORDER BY `order` ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategoriler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f1f1f1;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            color: #0056b3;
        }

        button {
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #c82333;
        }

        .add-category {
            margin-top: 20px;
            text-align: center;
        }

        .status {
            color: #28a745;
            font-weight: bold;
        }

        .toggle-status {
            background-color: #ffc107;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .toggle-status:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Kategoriler</h2>
        <table>
            <thead>
                <tr>
                    <th>Kategori Adı</th>
                    <th>Sıra</th>
                    <th>Açıklama</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td><?= $category['order'] ?></td>
                        <td><?= htmlspecialchars($category['description']) ?></td>
                        <td>
                            <?php if ($category['is_active']): ?>
                                <span class="status">✅ Aktif</span>
                            <?php else: ?>
                                <span class="status">⛔ Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-primary">🖋 Düzenle</a>
                            <a href="delete_category.php?id=<?= $category['id'] ?>" onclick="return confirm('Silmek istediğinizden emin misiniz?')" class="btn btn-danger">🗑 Sil</a>

                            <!-- Aktif/Pasif butonu -->
                            <form method="POST" action="toggle_category_status.php" style="display:inline;">
                                <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                <button type="submit" class="toggle-status">
                                    <?= $category['is_active'] ? 'Pasif Yap' : 'Aktif Yap' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="add-category">
            <a href="add_category.php" class="btn btn-success">+ Yeni Kategori Ekle</a>
        </div>
    </div>
</body>
</html>
