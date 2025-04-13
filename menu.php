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

$categories = $pdo->query("SELECT * FROM categories WHERE visible = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$baseUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/Qr/assets/images/";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Menü</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      background: #fafafa;
      font-family: 'Segoe UI', sans-serif;
    }
    header {
      background: #ffc107;
      padding: 1rem;
      font-size: 1.25rem;
      font-weight: bold;
      text-align: center;
    }
    .category-btn {
      border: none;
      border-radius: 50px;
      padding: 10px 20px;
      margin: 5px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: 0.3s;
    }
    .category-btn.active,
    .category-btn:hover {
      background-color: #ffc107;
      color: #fff;
    }
    .product-card {
      display: flex;
      flex-direction: column;
      height: 100%;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      overflow: hidden;
      transition: all 0.3s ease;
    }
    .product-card img {
      width: 100%;
      height: 180px;
      object-fit: contain;
      background: #fff;
    }
    .product-card .card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      text-align: center;
    }
    .product-card h5 {
      font-weight: 600;
    }
    .price-label {
      color: #28a745;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .btn-add {
      border-radius: 12px;
      font-weight: bold;
    }
    .fixed-bottom-bar {
      position: fixed;
      bottom: 0;
      width: 100%;
      background: #fff;
      border-top: 1px solid #ddd;
      box-shadow: 0 -1px 6px rgba(0,0,0,0.05);
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 999;
    }
    .fixed-bottom-bar button {
      width: 48%;
      border-radius: 12px;
      font-size: 16px;
    }
    #cart-status, #order-status {
      padding: 20px;
      background: #fff;
      margin: 20px;
      border-radius: 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    #order-status { display: none; }
  </style>
</head>
<body>
  <header>QR Menü</header>
  <div class="container py-3">
    <div class="d-flex flex-wrap justify-content-center">
      <?php foreach ($categories as $index => $category): ?>
        <button class="category-btn<?= $index === 0 ? ' active' : '' ?>" onclick="loadProducts(<?= $category['id'] ?>, this)">
          <?= htmlspecialchars($category['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>
    <div class="row mt-4" id="product-list">
      <div class="text-center">Lütfen bir kategori seçiniz.</div>
    </div>

    <div id="cart-status" style="display: none">
      <h5>Sepetiniz</h5>
      <ul id="cartList" class="list-group mb-3"></ul>
      <div class="mb-2">
        <label for="orderNote" class="form-label">Sipariş Notunuz:</label>
        <textarea id="orderNote" class="form-control" rows="2" placeholder="Varsa özel isteğiniz..."></textarea>
      </div>
      <p><strong>Toplam:</strong> ₺<span id="total">0.00</span></p>
    </div>

    <div id="order-status">
      <h5>Sipariş Takibi</h5>
      <div id="orderList">Yükleniyor...</div>
    </div>
  </div>

  <div class="fixed-bottom-bar">
    <button class="btn btn-dark" onclick="submitOrder()">Siparişi Ver</button>
    <button class="btn btn-warning" onclick="callWaiter()">Garson Çağır</button>
  </div>

  <!-- Görüş ve Puan Modalı -->
  <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-bottom">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="feedbackLabel">Görüşünüzü Bildirin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <form id="feedbackForm">
            <div class="mb-2">
              <label class="form-label">Puan:</label>
              <select class="form-select" name="rating" required>
                <option value="">Seçiniz</option>
                <option value="5">5 - Harika</option>
                <option value="4">4 - Çok iyi</option>
                <option value="3">3 - Orta</option>
                <option value="2">2 - Kötü</option>
                <option value="1">1 - Berbat</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Yorum:</label>
              <textarea class="form-control" name="comment" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Gönder</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const baseImagePath = "<?= $baseUrl ?>";
    const cart = {};
    const tableToken = "<?= $table_token ?>";
    const tableId = <?= $table_id ?>;

    function loadProducts(categoryId, el) {
      document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
      el.classList.add('active');

      fetch('admin/get_products_by_category.php?category_id=' + categoryId)
        .then(res => res.json())
        .then(data => {
          const list = document.getElementById('product-list');
          list.innerHTML = '';

          if (data.length === 0) {
            list.innerHTML = '<div class="text-center">Bu kategoride ürün bulunmamaktadır.</div>';
            return;
          }

          data.forEach(p => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 mb-4';

            const card = document.createElement('div');
            card.className = 'card product-card';

            const imagePath = p.image ? baseImagePath + p.image : 'https://via.placeholder.com/250x130?text=G%C3%B6rsel+Yok';

            card.innerHTML = `
              <img src="${imagePath}" alt="${p.name}">
              <div class="card-body">
                <h5>${p.name}</h5>
                <p class="text-muted small">${p.description || ''}</p>
                <div class="price-label">₺${parseFloat(p.price).toFixed(2)}</div>
                <button class="btn btn-outline-warning btn-add" onclick='addToCart(${p.id}, "${p.name}", ${p.price})'>Ekle</button>
              </div>
            `;

            col.appendChild(card);
            list.appendChild(col);
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
      const list = document.getElementById('cartList');
      const total = document.getElementById('total');
      const cartBox = document.getElementById('cart-status');
      list.innerHTML = '';
      let sum = 0;
      Object.keys(cart).forEach(id => {
        const item = cart[id];
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
          <div>
            <strong>${item.name}</strong><br>
            <small>${item.quantity} x ₺${item.price.toFixed(2)}</small>
          </div>
          <div>
            <button class="btn btn-sm btn-outline-secondary me-1" onclick="decreaseItem(${id})">-</button>
            <button class="btn btn-sm btn-outline-secondary me-1" onclick="increaseItem(${id})">+</button>
            <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${id})">x</button>
          </div>`;
        list.appendChild(li);
        sum += item.price * item.quantity;
      });
      total.textContent = sum.toFixed(2);
      cartBox.style.display = sum > 0 ? 'block' : 'none';
    }

    function increaseItem(id) {
      if (cart[id]) {
        cart[id].quantity++;
        renderCart();
      }
    }

    function decreaseItem(id) {
      if (cart[id]) {
        cart[id].quantity--;
        if (cart[id].quantity <= 0) delete cart[id];
        renderCart();
      }
    }

    function removeItem(id) {
      delete cart[id];
      renderCart();
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

      const note = document.getElementById('orderNote').value;

      fetch('siparis_ver.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          table_token: tableToken,
          cart: JSON.stringify(cartItems),
          note: note
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Sipariş gönderildi!");
          Object.keys(cart).forEach(k => delete cart[k]);
          renderCart();
          document.getElementById('order-status').style.display = 'block';
          fetchOrders();
          showFeedbackPopup();
        }
      });
    }

    function callWaiter() {
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
          alert("Çağrı başarısız oldu.");
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

      // Ürün bazlı gruplama
      const grouped = {};
      data.forEach(order => {
        const key = order.product_name;
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(order);
      });

      // Siparişleri göster
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
            statuses.push(`${o.quantity}x hazırlanıyor - ${minutesAgo} dk önce`);
          } else if (o.status === 'hazırlandı') {
            statuses.push(`${o.quantity}x hazırlandı`);
          } else if (o.status === 'teslim edildi') {
            statuses.push(`${o.quantity}x teslim edildi`);
          } else if (o.status === 'iptal') {
            statuses.push(`${o.quantity}x iptal edildi`);
          }
        });

        const div = document.createElement('div');
        div.className = "mb-2";
        div.innerHTML = `<p><strong>${product}</strong> x${totalQty} → ${statuses.join(', ')}</p>`;
        orderList.appendChild(div);
      }
    });
}


    function showFeedbackPopup() {
      const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
      modal.show();
    }

    document.getElementById('feedbackForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('table_id', tableId);
      fetch('submit_feedback.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
      });
    });

    setInterval(fetchOrders, 5000);
  </script>
	<!-- Yorum Butonu -->
<button id="feedbackButton" 
        class="btn btn-primary position-fixed" 
        style="bottom: 90px; right: 20px; z-index:999; display: none;"
        onclick="showFeedbackPopup()">
  ⭐ Yorum Yap
</button>

</body>
</html>