<?php
require_once '../includes/auth.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #00c6ff, #007bff);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            margin-top: 80px;
        }
        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-align: center;
        }
        .logout-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="dashboard-card">
        <h2>Hoş geldin, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></h2>
        <p>QR Menü Yönetim Paneline giriş yaptınız.</p>
        <a href="logout.php" class="btn btn-danger logout-btn">Çıkış Yap</a>
    </div>
</div>

</body>
</html>
