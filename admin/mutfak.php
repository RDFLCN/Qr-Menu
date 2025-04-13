<?php
require_once '../includes/db.php';

// Masaların ve siparişlerin alınması
$tables = $pdo->query("SELECT * FROM tables")->fetchAll(PDO::FETCH_ASSOC);
$orders = $pdo->query("SELECT * FROM orders WHERE status != 'iptal' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mutfak Yönetimi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f4f4f4;
            color: #333;
            padding: 20px;
        }

        header {
            background: #ffc107;
            padding: 16px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 1024px;
            margin: auto;
            padding: 20px;
        }

        .table-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .table-card {
            background-color: #fefefe;
            border: 1px solid #ddd;
            border-radius: 16px;
            padding: 20px;
            width: calc(33.333% - 20px);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
        }

        .table-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .table-card h3 {
            margin-bottom: 10px;
            color: #009578;
        }

        .status {
            font-weight: bold;
            color: #009578;
        }

        .amount {
            font-size: 14px;
            color: #666;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 99;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
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
            top: 12px;
            right: 12px;
            background: #ccc;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .table-card {
                width: 100%;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .order-status, .order-list {
            margin-top: 20px;
        }

        .order-status p {
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>Mutfak Yönetimi</header>

    <div class="container">
        <!-- Masa Listesi -->
        <div class="table-list">
            <?php foreach ($tables as $table): ?>
                <div class="table-card" data-id="<?= $table['id'] ?>" onclick="openTableModal(<?= $table['id'] ?>)">
                    <h3><?= htmlspecialchars($table['name']) ?></h3>
                    <p class="status">Durum: <?= $table['status'] ?></p>
                    <p class="amount">Tutar: ₺<span id="total-<?= $table['id'] ?>">0.00</span></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sipariş Takibi -->
        <div class="order-status">
            <h2>Sipariş Takibi</h2>
            <div id="orderList">
                <p>Yükleniyor...</p>
            </div>
        </div>
    </div>

    <!-- Modal - Masa Detayı ve Sipariş Yönetimi -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">×</button>
            <h2>Sipariş Detayları</h2>
            <div id="modalBody">Yükleniyor...</div>

            <!-- Sipariş Kontrol -->
            <div class="order-list" id="order-list">
                <p>Yükleniyor...</p>
            </div>
            <div class="order-status">
                <h3>Garson Çağırma</h3>
                <button onclick="callWaiter()">Garsonu Çağır</button>
            </div>
        </div>
    </div>

    <script>
        // Masa Tıklama ve Modal Açma
        const cards = document.querySelectorAll('.table-card');
        const modal = document.getElementById('modal');
        const modalBody = document.getElementById('modalBody');
        const orderList = document.getElementById('orderList');

        cards.forEach(card => {
            card.addEventListener('click', () => {
                const tableId = card.getAttribute('data-id');
                modal.style.display = 'flex';
                modalBody.innerHTML = "<p>Yükleniyor...</p>";
                orderList.innerHTML = "<p>Yükleniyor...</p>";

                // Masa Detaylarını Al
                fetch('get_table_details.php?table_id=' + tableId)
                    .then(res => res.text())
                    .then(data => {
                        modalBody.innerHTML = data;
                    });

                // Siparişleri Al
                fetch('get_orders_by_token.php?token=' + tableId)
                    .then(res => res.json())
                    .then(data => {
                        orderList.innerHTML = '';
                        if (data && data.length > 0) {
                            data.forEach(order => {
                                const div = document.createElement('div');
                                div.innerHTML = `<p><strong>${order.product_name}</strong> x${order.quantity} - Durum: ${order.status}</p>`;
                                orderList.appendChild(div);
                            });
                        } else {
                            orderList.innerHTML = "<p>Henüz sipariş verilmedi.</p>";
                        }
                    });
            });
        });

        function closeModal() {
            modal.style.display = 'none';
        }

        // Garson Çağırma
        function callWaiter() {
            const tableId = document.querySelector('.table-card[data-id]').getAttribute('data-id');
            fetch('handle_garson_call.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ table_id: tableId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Garson çağrıldı!');
                }
            });
        }

        // Sipariş Takibi
        function fetchOrders() {
            fetch('get_orders_by_token.php?token=<?= $table_token ?>')
                .then(res => res.json())
                .then(data => {
                    const orderList = document.getElementById('orderList');
                    orderList.innerHTML = '';
                    if (!data || data.length === 0) {
                        orderList.innerHTML = '<p>Henüz siparişiniz yok.</p>';
                        return;
                    }

                    // 🧠 Burada ürünleri grupla ve durumlarını say
                    const grouped = {};
                    data.forEach(order => {
                        const key = order.product_name;
                        if (!grouped[key]) grouped[key] = [];
                        grouped[key].push(order);
                    });

                    for (const product in grouped) {
                        const orders = grouped[product];
                        let totalQty = 0;
                        let statuses = [];

                        orders.forEach(o => {
                            totalQty += parseInt(o.quantity);
                            if (o.status === 'hazırlanıyor') {
                                statuses.push(`${o.quantity}x hazırlanıyor`);
                            } else if (o.status === 'hazırlandı') {
                                const preparedTime = o.prepared_at ? new Date(o.prepared_at).toLocaleTimeString('tr-TR') : 'saatsiz';
                                statuses.push(`${o.quantity}x hazırlandı (${preparedTime})`);
                            } else if (o.status === 'teslim edildi') {
                                const deliveredTime = o.delivered_at ? new Date(o.delivered_at).toLocaleTimeString('tr-TR') : 'saatsiz';
                                statuses.push(`${o.quantity}x teslim edildi (${deliveredTime})`);
                            } else if (o.status === 'iptal') {
                                statuses.push(`${o.quantity}x iptal edildi`);
                            }
                        });

                        const div = document.createElement('div');
                        div.innerHTML = `<p><strong>${product}</strong> x${totalQty} - ${statuses.join(', ')}</p>`;
                        orderList.appendChild(div);
                    }
                });
        }

        setInterval(fetchOrders, 5000);
    </script>

</body>
</html>
