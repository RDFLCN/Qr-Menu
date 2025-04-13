<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!$order_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
        exit;
    }

    // Siparişi güncelle
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, status_updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $order_id]);

    // Eğer sipariş "hazırlanıyor" ise, hazırlanma saati ekleyelim
    if ($status == 'hazırlanıyor') {
        $stmt = $pdo->prepare("UPDATE orders SET prepared_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
    }

    // Eğer sipariş "teslim edildi" ise, teslimat saati ekleyelim
    if ($status == 'teslim edildi') {
        $stmt = $pdo->prepare("UPDATE orders SET delivered_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
    }

    // Eğer sipariş iptal edildiyse, masa durumu kontrol edilip boşaltılabilir
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


    // Sipariş durumu başarıyla güncellendiyse yönlendirme yapılacak
    header("Location: tables.php"); // Yönlendirme yapılacak sayfa
    exit;
}
?>
