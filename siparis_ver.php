<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_token = $_POST['table_token'] ?? '';
    $cart = json_decode($_POST['cart'], true);

    if (!$table_token || empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
        exit;
    }

    // 1. Masa ID, toplam ve saat bilgisi al
    $stmt = $pdo->prepare("SELECT id, total_amount, seated_at FROM tables WHERE token = ?");
    $stmt->execute([$table_token]);
    $table = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$table) {
        echo json_encode(['success' => false, 'message' => 'Masa bulunamadı.']);
        exit;
    }

    $table_id = $table['id'];
    $current_total = floatval($table['total_amount']);
    $new_total = 0;

    foreach ($cart as $item) {
    $product_id = $item['id'];
    $price = floatval($item['price']);
    $quantity = intval($item['quantity'] ?? 1);
    $total_price = $price * $quantity;
    $new_total += $total_price;

    // Daha önce bu ürün bu masada "hazırlanıyor" durumunda var mı?
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE table_id = ? AND product_id = ? AND status = 'hazırlanıyor' AND paid = 0");
    $stmt->execute([$table_id, $product_id]);
    $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_order) {
        // Varsa quantity'yi artır
        $stmt = $pdo->prepare("UPDATE orders SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $existing_order['id']]);
    } else {
        // Yoksa yeni kayıt oluştur
        $stmt = $pdo->prepare("INSERT INTO orders (table_id, product_id, status, quantity, order_placed_at) VALUES (?, ?, 'hazırlanıyor', ?, NOW())");
        $stmt->execute([$table_id, $product_id, $quantity]);
    }

    // İptal edilen siparişlerin masa tutarından düşürülmesi
    if ($status == 'iptal') {
        // İptal edilen ürünün tutarını masa toplamından çıkaralım
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $price = floatval($product['price']);
            $total_deduction = $price * $quantity; // İptal edilen siparişin tutarını hesapla
            $stmt = $pdo->prepare("UPDATE tables SET total_amount = total_amount - ? WHERE id = ?");
            $stmt->execute([$total_deduction, $table_id]);
        }
    }
}


    $updated_total = $current_total + $new_total;

    if (empty($table['seated_at'])) {
        $stmt = $pdo->prepare("UPDATE tables SET status = 'dolu', total_amount = ?, seated_at = NOW() WHERE id = ?");
        $stmt->execute([$updated_total, $table_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE tables SET total_amount = ? WHERE id = ?");
        $stmt->execute([$updated_total, $table_id]);
    }

if ($status == 'iptal') {
    // İptal edilen ürünün tutarını masa toplamından çıkaralım
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $price = floatval($product['price']);
        $total_deduction = $price * $quantity; // İptal edilen siparişin tutarını hesapla
        $stmt = $pdo->prepare("UPDATE tables SET total_amount = total_amount - ? WHERE id = ?");
        $stmt->execute([$total_deduction, $table_id]);
    }
}

    echo json_encode(['success' => true, 'message' => 'Sipariş başarıyla alındı.']);
    exit;
}
