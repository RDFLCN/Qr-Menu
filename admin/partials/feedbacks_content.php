<?php
$stmt = $pdo->query("
  SELECT f.*, t.name AS table_name 
  FROM feedbacks f 
  LEFT JOIN tables t ON f.table_id = t.id 
  ORDER BY f.created_at DESC
");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ortalama puan hesapla
$avgStmt = $pdo->query("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_votes FROM feedbacks");
$ratingStats = $avgStmt->fetch(PDO::FETCH_ASSOC);
$average = round($ratingStats['avg_rating'], 1);
$totalVotes = $ratingStats['total_votes'];
$labels = [
  5 => '🌟 Harika',
  4 => '😊 Çok iyi',
  3 => '😐 Orta',
  2 => '😕 Kötü',
  1 => '😡 Berbat'
];
?>

<div class="container">
  <h2 class="mb-4">🎯 Müşteri Geri Bildirimleri</h2>

  <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
    <div>
      <strong>⭐ Ortalama Puan:</strong> <?= $average ?> / 5
      <span class="ms-2 text-muted">(<?= $totalVotes ?> oy)</span>
    </div>
    <div style="font-size: 1.2rem">
      <?= str_repeat('⭐', (int)round($average)) ?>
    </div>
  </div>

  <table class="table table-bordered bg-white shadow-sm">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Masa</th>
        <th>Puan</th>
        <th>Yorum</th>
        <th>Tarih</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($feedbacks as $fb): ?>
        <tr>
          <td><?= $fb['id'] ?></td>
          <td><?= $fb['table_name'] ?? 'Masa #' . $fb['table_id'] ?></td>
          <td><?= $labels[(int)$fb['rating']] ?? '❌' ?></td>
          <td><?= htmlspecialchars($fb['comment']) ?></td>
          <td><?= date('d.m.Y H:i', strtotime($fb['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
