<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

// Gelen veriler
$table_token = $_POST['table_token'] ?? '';
$cart = json_decode($_POST['cart'] ?? '[]', true);
$note = trim($_POST['note'] ?? '');
$device = 'web'; // veya mobil/ipad vb.

if (!$table_token || empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri']);
    exit;
}

// Token ile masa ID'si bulunur
$stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
$stmt->execute([$table_token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    echo json_encode(['success' => false, 'message' => 'Masa bulunamadı']);
    exit;
}

$table_id = $table['id'];
$order_group_id = uniqid('grp_'); // Aynı anda verilen siparişleri grupla

// Sipariş öncesi aktif session var mı kontrol et
$stmt = $pdo->prepare("SELECT id FROM table_sessions WHERE table_id = ? AND ended_at IS NULL");
$stmt->execute([$table_id]);
$activeSession = $stmt->fetch(PDO::FETCH_ASSOC);

// Yoksa başlat
if (!$activeSession) {
    $pdo->prepare("INSERT INTO table_sessions (table_id, total_guests) VALUES (?, 1)")
        ->execute([$table_id]);
}


try {
    $pdo->beginTransaction();

    foreach ($cart as $item) {
        $product_id = (int)$item['id'];
        $qty = (int)$item['quantity'];

        if ($qty < 1) continue;

        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (order_group_id, table_id, product_id, quantity, note, status, order_placed_at, notified, device, is_canceled_by_customer, is_paid)
            VALUES 
            (?, ?, ?, ?, ?, 'hazırlanıyor', NOW(), 0, ?, 0, 0)
        ");
        $stmt->execute([
            $order_group_id,
            $table_id,
            $product_id,
            $qty,
            $note,
            $device
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Sipariş kaydedilemedi']);
}
