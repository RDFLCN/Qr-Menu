<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_token = $_POST['table_token'];
    $cart = json_decode($_POST['cart'], true);

    if (!empty($table_token) && !empty($cart)) {
        // Masanın ID'sini alalım
        $stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
        $stmt->execute([$table_token]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);
        $table_id = $table['id'];

        // Sepetteki her ürünü veritabanına ekleyelim
        foreach ($cart as $item) {
            $stmt = $pdo->prepare("INSERT INTO orders (table_id, product_id, status, order_placed_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$table_id, $item['id'], 'hazırlanıyor']);
        }

        // Başarılı mesaj
        echo json_encode(['success' => true, 'message' => 'Sipariş başarıyla verildi.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sepet boş veya geçersiz masa.']);
    }
}
?>
