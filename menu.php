<?php
require_once 'includes/db.php';

$table_token = $_GET['token'] ?? '';
if (!$table_token) {
    die("Masa bilgisi bulunamadı.");
}

$stmt = $pdo->prepare("SELECT id FROM tables WHERE token = ?");
$stmt->execute([$table_token]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$table) {
    die("Geçersiz masa bağlantısı.");
}
$table_id = $table['id'];

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Menü</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #f9f9f9;
      color: #333;
    }
    header {
      background: #ffc107;
      padding: 16px;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .container {
      max-width: 1024px;
      margin: auto;
      padding: 20px;
    }
    .category-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
      justify-content: center;
    }
    .category-buttons button {
      padding: 10px 18px;
      border: none;
      border-radius: 20px;
      background: #e0e0e0;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s ease;
    }
    .category-buttons button.active,
    .category-buttons button:hover {
      background: #ffc107;
      color: #fff;
    }
    .products {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
    }
    .product-card {
      background: #fff;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    .product-card img {
      width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 10px;
    }
    .product-card h4 {
      margin: 10px 0 5px;
    }
    .product-card p {
      font-size: 14px;
    }
    .product-card .price {
      font-weight: bold;
      color: #28a745;
      margin-top: 8px;
    }
    .product-card button {
      margin-top: auto;
      padding: 8px 16px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
    }
    #cart, #order-status {
      background: #fff;
      margin-top: 30px;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .btns {
      margin-top: 20px;
      display: flex;
      gap: 10px;
      justify-content: space-between;
    }
    .btns button {
      flex: 1;
      padding: 12px;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    .btn-order { background: #007bff; color: #fff; }
    .btn-call { background: #dc3545; color: #fff; }
  </style>
</head>
<body>
  <header>QR Menü</header>
  <div class="container">
    <div class="category-buttons">
      <?php foreach ($categories as $category): ?>
        <button onclick="loadProducts(<?= $category['id'] ?>)"><?= htmlspecialchars($category['name']) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="products" id="product-list">Kategori seçiniz...</div>

    <div id="cart">
      <h3>Sepet</h3>
      <ul id="cartList"></ul>
      <p><strong>Toplam:</strong> ₺<span id="total">0.00</span></p>
    </div>

    <div class="btns">
      <button class="btn-order" onclick="submitOrder()">Siparişi Ver</button>
      <button class="btn-call" onclick="callWaiter()">Garson Çağır</button>
    </div>

    <div id="order-status">
      <h3>Sipariş Takibi</h3>
      <div id="orderList"><p>Henüz siparişiniz yok.</p></div>
    </div>
  </div>

  <script>
    const cart = {};
    const tableToken = "<?= $table_token ?>";

    function loadProducts(categoryId) {
      fetch('admin/get_products_by_category.php?category_id=' + categoryId)
        .then(res => res.json())
        .then(data => {
          const list = document.getElementById('product-list');
          list.innerHTML = '';
          data.forEach(p => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
              <img src="assets/product_images/${p.image}" alt="${p.name}" />
              <h4>${p.name}</h4>
              <p>${p.description}</p>
              <div class="price">₺${p.price}</div>
              <button onclick='addToCart(${p.id}, "${p.name}", ${p.price})'>Ekle</button>
            `;
            list.appendChild(card);
          });
        });
    }

    function addToCart(id, name, price) {
      if (!cart[id]) {
        cart[id] = { name, price, quantity: 1 };
      } else {
        cart[id].quantity++;
      }
      renderCart();
    }

    function renderCart() {
      const ul = document.getElementById('cartList');
      const total = document.getElementById('total');
      ul.innerHTML = '';
      let sum = 0;
      Object.values(cart).forEach(i => {
        const li = document.createElement('li');
        li.textContent = `${i.name} x${i.quantity} - ₺${(i.price * i.quantity).toFixed(2)}`;
        ul.appendChild(li);
        sum += i.price * i.quantity;
      });
      total.textContent = sum.toFixed(2);
    }

    function submitOrder() {
      const cartItems = [];
      Object.keys(cart).forEach(id => {
        cartItems.push({
          id: parseInt(id),
          name: cart[id].name,
          price: cart[id].price,
          quantity: cart[id].quantity
        });
      });

      if (cartItems.length === 0) return alert('Sepet boş!');

      fetch('siparis_ver.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          table_token: tableToken,
          cart: JSON.stringify(cartItems)
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Sipariş gönderildi!");
          Object.keys(cart).forEach(k => delete cart[k]);
          renderCart();
          fetchOrders();
        }
      });
    }

    function callWaiter() {
      const tableId = <?= $table_id ?>;
      fetch('admin/handle_garson_call.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          table_id: tableId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert("Garson çağırıldı.");
        } else {
          alert("Garson çağırma işlemi başarısız oldu.");
        }
      })
      .catch(error => {
        console.error("Hata:", error);
        alert("Bir hata oluştu.");
      });
    }

    function fetchOrders() {
      fetch('get_orders_by_token.php?token=' + tableToken)
        .then(res => res.json())
        .then(data => {
          const orderList = document.getElementById('orderList');
          orderList.innerHTML = '';
          if (!data || data.length === 0) {
            orderList.innerHTML = '<p>Henüz siparişiniz yok.</p>';
            return;
          }

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
              const orderTime = new Date(o.order_placed_at);
              const now = new Date();
              const minutesAgo = Math.floor((now - orderTime) / 60000);

              if (o.status === 'hazırlanıyor') {
                statuses.push(`${o.quantity}x hazırlanıyor - Sipariş vereli ${minutesAgo} dk oldu`);
              } else if (o.status === 'hazırlandı') {
                const preparedTime = o.prepared_at ? new Date(o.prepared_at) : null;
                const preparedMinutesAgo = preparedTime ? Math.floor((now - preparedTime) / 60000) : null;
                statuses.push(`${o.quantity}x hazırlandı ${preparedTime ? `(${preparedTime.toLocaleTimeString('tr-TR')} - ${preparedMinutesAgo} dk önce)` : ''}`);
              } else if (o.status === 'teslim edildi') {
                const deliveredTime = o.delivered_at ? new Date(o.delivered_at) : null;
                const deliveredMinutesAgo = deliveredTime ? Math.floor((now - deliveredTime) / 60000) : null;
                statuses.push(`${o.quantity}x teslim edildi (${deliveredTime ? deliveredTime.toLocaleTimeString('tr-TR') : ''} - ${deliveredMinutesAgo ? `${deliveredMinutesAgo} dk önce` : ''})`);
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
    fetchOrders();
  </script>
</body>
</html>
