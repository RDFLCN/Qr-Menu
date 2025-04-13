<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Masalar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Stil ayarları */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            animation: fadeIn 0.5s ease;
        }
        h2 {
            margin-bottom: 20px;
            color: #222;
        }
        .table-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .table-card {
            background-color: #fefefe;
            border: 1px solid #ddd;
            border-radius: 16px;
            padding: 20px;
            width: calc(33.333% - 20px);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .table-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .status {
            font-weight: bold;
            color: #009578;
        }
        .amount {
            font-size: 14px;
            color: #666;
        }
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 99;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            position: relative;
            animation: fadeIn 0.4s ease;
        }
        .modal-close {
            position: absolute;
            top: 12px; right: 12px;
            background: #ccc;
            border: none;
            border-radius: 50%;
            width: 28px; height: 28px;
            cursor: pointer;
            font-weight: bold;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Masalar</h2>
        <div class="table-list">
            <?php
            $stmt = $pdo->query("SELECT * FROM tables ORDER BY id ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                $stmtTotal = $pdo->prepare("SELECT SUM(p.price * o.quantity) FROM orders o JOIN products p ON o.product_id = p.id WHERE o.table_id = ? AND o.paid = 0 AND o.status != 'iptal'");
                $stmtTotal->execute([$row['id']]);
                $totalAmount = $stmtTotal->fetchColumn();
            ?>
                <div class="table-card" data-id="<?= $row['id'] ?>" onclick="openTableModal(<?= $row['id'] ?>)">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="status">Durum: <?= $row['status'] ?></p>
                    <p class="amount">Tutar: ₺<?= number_format($totalAmount ?? 0, 2, ',', '.') ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Yapısı -->
    <div class="modal" id="modal">
        <div class="modal-content" id="modalContent">
            <button class="modal-close" onclick="closeModal()">×</button>
            <h2>Sipariş Detayları</h2>
            <div id="modalBody">Yükleniyor...</div>
            <!-- Ürün Ekleme Bölümü -->
            <div class="add-product">
                <h3>Ürün Ekle</h3>
                <select id="productSelect">
                    <option value="">Lütfen bir ürün seçin</option>
                    <!-- Ürünler burada dinamik olarak listeleyeceğiz -->
                </select>
                <button id="addProductBtn" onclick="addProductToOrder()" disabled>Ürün Ekle</button>
            </div>
        </div>
    </div>

    <script>
        function openTableModal(tableId) {
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modalBody');
            const productSelect = document.getElementById('productSelect');
            const addProductBtn = document.getElementById('addProductBtn');

            modal.style.display = 'flex';
            modalBody.innerHTML = "<p>Yükleniyor...</p>";

            // Masa detaylarını al
            fetch('get_table_details.php?table_id=' + tableId)
                .then(res => res.text())
                .then(data => {
                    modalBody.innerHTML = data;
                });

            // Ürünleri al ve listele
            fetch('get_all_products.php')  // Ürünlerin listeyi getiren PHP dosyası
                .then(res => res.json())
                .then(products => {
                    productSelect.innerHTML = `<option value="">Lütfen bir ürün seçin</option>`; // Mevcut ürünleri sıfırla
                    if (products.length > 0) {
                        products.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.id;
                            option.textContent = product.name;
                            productSelect.appendChild(option);
                        });
                        // Butonu etkinleştir
                        addProductBtn.disabled = false;
                    } else {
                        alert('Ürün bulunamadı!');
                        addProductBtn.disabled = true;
                    }
                })
                .catch(err => {
                    console.error("Ürünleri alırken hata oluştu", err);
                    addProductBtn.disabled = true;
                });
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            modal.style.display = 'none';
        }

        function addProductToOrder() {
    const productId = document.getElementById('productSelect').value;
    const tableId = document.querySelector('.table-card[data-id]').getAttribute('data-id');

    if (productId) {
        // Ürün eklemek için doğru URL
        fetch('https://crabpubcafe.com/Qr-Menu/siparis_ver.php', {  // Doğru URL'yi belirttik
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                table_token: tableId,
                cart: JSON.stringify([{ id: productId, quantity: 1 }])  // 1 adet ekle
            })
        })
        .then(res => res.json())  // Sunucudan JSON verisi bekliyoruz
        .then(data => {
            if (data.success) {
                alert('Ürün başarıyla eklendi!');
                closeModal();  // Modalı kapat
                location.reload();  // Sayfayı yenile
            } else {
                alert('Bir hata oluştu: ' + data.message);  // Hata mesajı varsa göster
            }
        })
        .catch(error => {
            console.error("Error adding product:", error);  // Hata durumunda console.log ile bilgi alalım
            alert("Ürün eklenirken bir hata oluştu.");
        });
    } else {
        alert("Lütfen bir ürün seçin.");
    }
}


    </script>
    
    <audio id="orderSound" src="../assets/ding.mp3" preload="auto"></audio>
    <script>
    let notified = false;
    function checkOrders() {
        fetch('check_new_orders.php')
            .then(res => res.json())
            .then(data => {
                if (data.new_orders > 0 && !notified) {
                    document.getElementById('orderSound').play();
                    alert('Yeni sipariş var!');
                    notified = true;
                    fetch('mark_orders_notified.php');
                }
            });
    }
    setInterval(checkOrders, 5000);
    </script>
    
    
    
    <!--  Garson Bildirimi -->
    
    <!-- Bildirim sesini içeren audio etiketi (dosya yolunu kontrol edin) -->
<audio id="orderSound" src="../assets/ding.mp3" preload="auto"></audio>

<script>
// Daha önce var olan checkOrders() gibi yeni bir fonksiyon ekliyoruz.
function checkCalls() {
    fetch('check_new_calls.php')
        .then(res => res.json())
        .then(data => {
            const calls = data.calls;
            if (calls.length > 0) {
                // Her çağrının bilgisine göre bildirim gösterelim.
                calls.forEach(call => {
                    // Bildirim sesini çalalım
                    const audio = document.getElementById('orderSound');
                    // Bazı tarayıcılar otomatik ses çalmayı engelleyebilir: 
                    // Kullanıcı etkileşimi sonrası bu kodun çalıştığından emin olun.
                    audio.play().catch(e => console.error("Ses oynatılamadı:", e));

                    // Örneğin uyarı ile çağrının hangi masadan geldiğini belirtebiliriz:
                    alert("Yeni garson çağrısı var! Masa: " + call.table_name);

                    // İlgili masa kartının yanında bildirim ikonu ekleyelim.
                    // Masa kartları ".table-card" sınıfıyla ve "data-id" attribute'u ile eşleştirilmiş.
                    const tableCard = document.querySelector(`.table-card[data-id='${call.table_id}']`);
                    if (tableCard) {
                        // Eğer daha önce eklenmemişse, ikon ekleyelim.
                        if (!tableCard.querySelector('.call-notification')) {
                            // Bildirim ikonu için bir span oluşturuyoruz.
                            const badge = document.createElement('span');
                            badge.className = 'call-notification';
                            badge.textContent = 'Yeni Çağrı';
                            // Konumlandırma için table card'a relative pozisyon verelim (CSS'te eklenmeli)
                            tableCard.style.position = 'relative';
                            // İkonu masa kartına ekleyin (örneğin sağ üst köşeye)
                            badge.style.position = 'absolute';
                            badge.style.top = '10px';
                            badge.style.right = '10px';
                            badge.style.background = 'red';
                            badge.style.color = 'white';
                            badge.style.padding = '2px 6px';
                            badge.style.borderRadius = '50%';
                            badge.style.fontSize = '12px';
                            tableCard.appendChild(badge);
                        }
                    }
                });

                // Çağrıları bildirdikten sonra; tekrar tekrar uyarı gelmemesi için 
                // çağrıları notified olarak işaretleyebilirsiniz.
                fetch('mark_calls_notified.php'); // Bu dosya, pending çağrıları notified yapmalı.
            } else {
                // Eğer çağrı yoksa, tüm masa kartlarındaki bildirim ikonlarını kaldıralım.
                document.querySelectorAll('.table-card .call-notification').forEach(badge => badge.remove());
            }
        })
        .catch(err => {
            console.error("Garson çağrılarını kontrol ederken hata oluştu:", err);
        });
}

// 5 saniyede bir çağrıları kontrol edelim
setInterval(checkCalls, 5000);
</script>

    
    
</body>
</html>
