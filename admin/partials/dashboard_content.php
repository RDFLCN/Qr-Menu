<?php
// İstatistik verileri
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE visible = 1")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalTables = $pdo->query("SELECT COUNT(*) FROM tables")->fetchColumn();
$activeTables = $pdo->query("SELECT COUNT(*) FROM tables WHERE visible = 1")->fetchColumn();
$rating = $pdo->query("SELECT ROUND(AVG(rating), 1) AS avg, COUNT(*) AS total FROM feedbacks")->fetch(PDO::FETCH_ASSOC);

// Bugünkü sipariş sayısı (created_at kullandık çünkü order_placed_at yokmuş)
$today = date('Y-m-d');
$todayOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
$todayOrders->execute([$today]);
$todayOrderCount = $todayOrders->fetchColumn();

// Son 7 gün sipariş istatistikleri
$orderStats = $pdo->query("
  SELECT DATE(created_at) as day, COUNT(*) as count 
  FROM orders 
  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY day 
  ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
foreach ($orderStats as $row) {
  $labels[] = date('d M', strtotime($row['day']));
  $data[] = $row['count'];
}
?>

<div class="row g-4">
  <div class="col-md-3">
    <div class="card p-4 bg-primary text-white text-center shadow-sm">
      <h3><?= $totalProducts ?></h3>
      <p class="mb-0">Toplam Ürün</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-4 bg-success text-white text-center shadow-sm">
      <h3><?= $activeProducts ?></h3>
      <p class="mb-0">Aktif Ürün</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-4 bg-warning text-dark text-center shadow-sm">
      <h3><?= $totalCategories ?></h3>
      <p class="mb-0">Toplam Kategori</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-4 bg-light text-center shadow-sm border">
      <h3><?= $rating['avg'] ?? '0.0' ?> / 5</h3>
      <p class="mb-1 text-muted"><?= $rating['total'] ?> oylama</p>
      <div style="font-size: 1.3rem;"><?= str_repeat('⭐', (int)round($rating['avg'])) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-4 bg-dark text-white text-center shadow-sm">
      <h3><?= $totalTables ?></h3>
      <p class="mb-0">Toplam Masa</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-4 bg-secondary text-white text-center shadow-sm">
      <h3><?= $activeTables ?></h3>
      <p class="mb-0">Aktif Masa</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-4 bg-info text-white text-center shadow-sm">
      <h3><?= $todayOrderCount ?></h3>
      <p class="mb-0">Bugünkü Sipariş</p>
    </div>
  </div>
</div>

<!-- Sipariş Grafiği -->
<div class="card mt-5">
  <div class="card-body">
    <h5 class="card-title text-center mb-3">📈 Son 7 Günlük Sipariş Grafiği</h5>
    <canvas id="orderChart" height="100"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('orderChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Sipariş Sayısı',
        data: <?= json_encode($data) ?>,
        backgroundColor: 'rgba(255, 193, 7, 0.8)'
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>
