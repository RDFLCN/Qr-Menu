<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// admin/layout.php
require_once '../includes/auth.php';

// Sayfa başlığı ve içerik dosyası parametre olarak alınır
define('PAGE_TITLE', isset($pageTitle) ? $pageTitle : 'Yönetim Paneli');
define('PAGE_FILE', isset($pageFile) ? $pageFile : 'dashboard.php');

// Sayfa başında ortak kodlar burada
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title><?= PAGE_TITLE ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      width: 240px;
      height: 100vh;
      background: #1f2937;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      padding: 30px 15px;
    }
    .sidebar h4 {
      font-weight: bold;
      color: #fff;
      margin-bottom: 30px;
    }
    .sidebar a {
      display: block;
      color: #d1d5db;
      text-decoration: none;
      padding: 10px 0;
      transition: 0.2s;
    }
    .sidebar a:hover {
      color: white;
    }
    .main-content {
      margin-left: 260px;
      padding: 30px;
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .topbar {
      background: white;
      padding: 15px 30px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      margin-bottom: 30px;
    }
	  .table-img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
}

  </style>
</head>
<body>

<div class="sidebar">
  <h4>CRABPUB</h4>
  <a href="dashboard.php">📊 Dashboard</a>
  <a href="products.php">🛍 Ürünler</a>
  <a href="categories.php">📁 Kategoriler</a>
  <a href="tables.php">🪑 Masalar</a>
  <a href="logout.php">🚪 Çıkış</a>
</div>


<div class="main-content">
  <div class="topbar d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><?= PAGE_TITLE ?></h5>
    <span>👤 <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
  </div>

  <?php include PAGE_FILE; ?>
</div>

</body>
</html>
