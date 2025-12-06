<?php
session_start();
if (!isset($_SESSION['first_name'])) {
    header("Location: ../index.php");
    exit();
}

$db = new mysqli("localhost", "root", "", "pos_system");
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// Fetch products
$products = $db->query("SELECT * FROM products ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Twin Bites POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body { 
  font-family: 'Nunito', sans-serif;
  background: linear-gradient(135deg, #FFD95A 0%, #FF9B50 50%, #FF6B6B 100%);
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
}

body::before {
  content: '';
  position: fixed;
  top: 50%;
  left: 50%;
  width: 200%;
  height: 200%;
  background: repeating-conic-gradient(from 0deg at 50% 50%, rgba(255, 255, 255, 0.03) 0deg, rgba(255, 255, 255, 0.03) 5deg, transparent 5deg, transparent 10deg);
  transform: translate(-50%, -50%) rotate(0deg);
  animation: rotateSun 60s linear infinite;
  pointer-events: none;
  z-index: 0;
}

@keyframes rotateSun {
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

.top-bar {
  background: linear-gradient(90deg, #FF6B6B 0%, #FF9B50 50%, #FFD95A 100%);
  padding: 1rem 2rem;
  box-shadow: 0 6px 25px rgba(255, 107, 107, 0.4);
  position: relative;
  z-index: 100;
  border-bottom: 4px solid rgba(255, 255, 255, 0.3);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pos-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 2.2rem;
  color: white;
  text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
  letter-spacing: 1px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.pos-title i {
  font-size: 2.5rem;
  filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
}

.btn-dashboard {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  padding: 0.7rem 2rem;
  border-radius: 50px;
  border: 3px solid white;
  background: white;
  color: #FF6B6B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-dashboard:hover {
  background: #FF6B6B;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.main-container {
  max-width: 1600px;
  margin: 0 auto;
  padding: 2rem;
  position: relative;
  z-index: 10;
}

.pos-layout {
  display: grid;
  grid-template-columns: 1fr 500px;
  gap: 2rem;
  height: calc(100vh - 150px);
}

.products-section {
  background: white;
  border-radius: 30px;
  padding: 2rem;
  box-shadow: 0 15px 50px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  max-height: calc(100vh - 150px);
}

.section-header {
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 1.8rem;
  color: #FF6B6B;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 1rem;
  border-bottom: 3px solid #FFD95A;
}

.section-header i {
  font-size: 2rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
  overflow-y: auto;
  padding: 1rem 0.5rem;
  flex: 1;
}

.products-grid::-webkit-scrollbar,
.cart-items::-webkit-scrollbar {
  width: 10px;
}

.products-grid::-webkit-scrollbar-track,
.cart-items::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.products-grid::-webkit-scrollbar-thumb,
.cart-items::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #FFD95A, #FF9B50);
  border-radius: 10px;
}

.products-grid::-webkit-scrollbar-thumb:hover,
.cart-items::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
}

.product-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  border: 3px solid transparent;
  position: relative;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
  border-color: #FFD95A;
}

.product-card.out-of-stock {
  opacity: 0.6;
  cursor: not-allowed;
  filter: grayscale(50%);
}

.product-card.out-of-stock:hover {
  transform: none;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  border-color: #ccc;
}

.product-card.out-of-stock::before {
  content: 'OUT OF STOCK';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-15deg);
  background: rgba(255, 107, 107, 0.95);
  color: white;
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 1.2rem;
  padding: 0.8rem 2rem;
  border-radius: 10px;
  z-index: 10;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
  letter-spacing: 1px;
  border: 3px solid white;
}

.product-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
  background: linear-gradient(135deg, rgba(255, 215, 90, 0.1), rgba(255, 155, 80, 0.1));
}

.product-info {
  padding: 1rem;
}

.product-name {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  color: #333;
  margin-bottom: 0.5rem;
  line-height: 1.3;
  height: 2.6em;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-price {
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 1.3rem;
  color: #FF6B6B;
  margin-bottom: 0.8rem;
}

.stock-indicator {
  font-family: 'Nunito', sans-serif;
  font-weight: 700;
  font-size: 0.85rem;
  margin-bottom: 0.8rem;
  padding: 0.3rem 0.6rem;
  border-radius: 8px;
  display: inline-block;
}

.stock-indicator.in-stock {
  background: rgba(76, 175, 80, 0.1);
  color: #4CAF50;
}

.stock-indicator.low-stock {
  background: rgba(255, 155, 80, 0.15);
  color: #FF9B50;
}

.stock-indicator.out-of-stock {
  background: rgba(255, 107, 107, 0.15);
  color: #FF6B6B;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.8rem;
}

.qty-btn {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  border: 2px solid #FFD95A;
  background: white;
  color: #FF9B50;
  font-weight: 700;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.qty-btn:hover {
  background: linear-gradient(135deg, #FFD95A, #FF9B50);
  color: white;
  border-color: #FF9B50;
}

.qty-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  background: #f0f0f0;
  border-color: #ccc;
  color: #999;
}

.qty-btn:disabled:hover {
  background: #f0f0f0;
  color: #999;
}

.qty-input {
  width: 60px;
  height: 35px;
  border: 2px solid #FFD95A;
  border-radius: 10px;
  text-align: center;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  color: #333;
}

.qty-input:focus {
  outline: none;
  border-color: #FF9B50;
}

.qty-input:disabled {
  background: #f0f0f0;
  color: #999;
  cursor: not-allowed;
}

.btn-add-product {
  width: 100%;
  padding: 0.7rem;
  border-radius: 15px;
  border: none;
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
  color: white;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

.btn-add-product:hover {
  background: linear-gradient(135deg, #FF6B6B, #E85D5D);
  transform: scale(1.05);
  box-shadow: 0 6px 18px rgba(255, 107, 107, 0.4);
}

.btn-add-product:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
  opacity: 0.6;
}

.cart-section {
  background: white;
  border-radius: 30px;
  padding: 2rem;
  box-shadow: 0 15px 50px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 1.5rem;
}

.cart-item {
  background: linear-gradient(135deg, rgba(255, 215, 90, 0.05), rgba(255, 155, 80, 0.05));
  border-radius: 15px;
  padding: 1rem;
  margin-bottom: 1rem;
  border: 2px solid #FFD95A;
  transition: all 0.3s ease;
}

.cart-item:hover {
  border-color: #FF9B50;
  box-shadow: 0 4px 15px rgba(255, 155, 80, 0.2);
}

.cart-item-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.8rem;
}

.cart-item-name {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  color: #333;
  flex: 1;
}

.btn-remove {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: none;
  background: #FF6B6B;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9rem;
}

.btn-remove:hover {
  background: #E85D5D;
  transform: scale(1.1);
}

.cart-item-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Nunito', sans-serif;
  font-weight: 700;
}

.cart-item-qty {
  color: #666;
  font-size: 0.95rem;
}

.cart-item-price {
  color: #FF9B50;
  font-size: 1rem;
}

.cart-item-total {
  color: #FF6B6B;
  font-size: 1.2rem;
  font-family: 'Poppins', sans-serif;
}

.cart-empty {
  text-align: center;
  padding: 3rem 1rem;
  color: #999;
}

.cart-empty i {
  font-size: 4rem;
  color: #ddd;
  margin-bottom: 1rem;
}

.cart-empty-text {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 1.2rem;
}

.cart-summary {
  background: linear-gradient(135deg, #FF6B6B, #FF9B50);
  border-radius: 20px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.cart-summary-label {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.3rem;
  color: white;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.cart-summary-amount {
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 2.5rem;
  color: white;
  text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
  text-align: right;
}

.btn-checkout {
  width: 100%;
  padding: 1.2rem;
  border-radius: 20px;
  border: none;
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 1.3rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
}

.btn-checkout:hover {
  background: linear-gradient(135deg, #45a049, #3d8b40);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(76, 175, 80, 0.5);
}

.btn-checkout:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.modal-content {
  border-radius: 30px;
  border: none;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
  background: linear-gradient(135deg, #4CAF50, #45a049);
  border: none;
  padding: 2rem;
  color: white;
}

.modal-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 1.8rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-body {
  padding: 2rem;
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.05), rgba(69, 160, 73, 0.05));
}

.checkout-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 2px solid #f0f0f0;
  font-family: 'Poppins', sans-serif;
}

.checkout-label {
  font-weight: 700;
  font-size: 1.2rem;
  color: #666;
}

.checkout-value {
  font-weight: 800;
  font-size: 1.5rem;
  color: #FF6B6B;
}

.cash-input-group {
  margin: 1.5rem 0;
}

.cash-input {
  width: 100%;
  padding: 1rem 1.5rem;
  border: 3px solid #4CAF50;
  border-radius: 15px;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.3rem;
  text-align: center;
  color: #333;
}

.cash-input:focus {
  outline: none;
  border-color: #45a049;
  box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

.change-display {
  background: linear-gradient(135deg, rgba(255, 215, 90, 0.2), rgba(255, 155, 80, 0.2));
  border-radius: 15px;
  padding: 1.5rem;
  text-align: center;
  margin-top: 1rem;
}

.change-label {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.change-amount {
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 2.5rem;
  color: #4CAF50;
}

.modal-footer {
  border: none;
  padding: 1.5rem 2rem;
  background: white;
}

.btn-proceed {
  width: 100%;
  padding: 1rem;
  border-radius: 20px;
  border: none;
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 1.2rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.btn-proceed:hover {
  background: linear-gradient(135deg, #45a049, #3d8b40);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
}

@media (max-width: 1200px) {
  .pos-layout {
    grid-template-columns: 1fr 400px;
  }
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 992px) {
  .pos-layout {
    grid-template-columns: 1fr;
    height: auto;
  }
  .products-section {
    height: 500px;
  }
  .cart-section {
    height: 600px;
  }
}

@media (max-width: 768px) {
  .pos-title {
    font-size: 1.5rem;
  }
  .btn-dashboard {
    padding: 0.5rem 1.2rem;
    font-size: 0.9rem;
  }
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
  }
  .section-header {
    font-size: 1.4rem;
  }
}
</style>
</head>
<body>

<div class="top-bar">
  <div class="pos-title">
    <i class="fas fa-cash-register"></i>
    Twin Bites POS
  </div>
  <a href="dashboard.php" class="btn-dashboard">
    <i class="fas fa-tachometer-alt"></i>
    Dashboard
  </a>
</div>

<div class="main-container">
  <div class="pos-layout">
    
    <div class="products-section">
      <div class="section-header">
        <i class="fas fa-shopping-basket"></i>
        Available Products
      </div>
      <div class="products-grid">
        <?php while($p = $products->fetch_assoc()): 
          $isOutOfStock = $p['end_stock'] <= 0;
          $isLowStock = $p['end_stock'] > 0 && $p['end_stock'] <= 5;
        ?>
        <div class="product-card <?= $isOutOfStock ? 'out-of-stock' : '' ?>" data-stock="<?= $p['end_stock'] ?>">
          <img src="../assets/images/<?= $p['image'] ?>" class="product-image" alt="<?= $p['name'] ?>">
          <div class="product-info">
            <div class="product-name"><?= $p['name'] ?></div>
            <div class="product-price">₱<?= number_format($p['price'],2) ?></div>
            
            <?php if($isOutOfStock): ?>
              <div class="stock-indicator out-of-stock">
                <i class="fas fa-times-circle"></i> Out of Stock
              </div>
            <?php elseif($isLowStock): ?>
              <div class="stock-indicator low-stock">
                <i class="fas fa-exclamation-triangle"></i> Only <?= $p['end_stock'] ?> left
              </div>
            <?php else: ?>
              <div class="stock-indicator in-stock">
                <i class="fas fa-check-circle"></i> In Stock (<?= $p['end_stock'] ?>)
              </div>
            <?php endif; ?>
            
            <div class="quantity-control">
              <button class="qty-btn qty-minus" <?= $isOutOfStock ? 'disabled' : '' ?>>−</button>
              <input type="number" class="qty-input quantity" min="1" max="<?= $p['end_stock'] ?>" value="1" <?= $isOutOfStock ? 'disabled' : '' ?>>
              <button class="qty-btn qty-plus" <?= $isOutOfStock ? 'disabled' : '' ?>>+</button>
            </div>
            <button class="btn-add-product add-to-cart" 
                    data-id="<?= $p['product_id'] ?>" 
                    data-name="<?= htmlspecialchars($p['name']) ?>" 
                    data-price="<?= $p['price'] ?>"
                    data-stock="<?= $p['end_stock'] ?>"
                    <?= $isOutOfStock ? 'disabled' : '' ?>>
              <i class="fas fa-cart-plus"></i> <?= $isOutOfStock ? 'Out of Stock' : 'Add to Cart' ?>
            </button>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="cart-section">
      <div class="section-header">
        <i class="fas fa-shopping-cart"></i>
        Order Cart
        <span id="cartCount" style="margin-left: auto; background: #FF6B6B; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 1rem;">0</span>
      </div>
      
      <div class="cart-items" id="cartItemsContainer">
        <div class="cart-empty">
          <i class="fas fa-cart-arrow-down"></i>
          <div class="cart-empty-text">Cart is empty</div>
        </div>
      </div>
      
      <div class="cart-summary">
        <div class="d-flex justify-content-between align-items-center">
          <div class="cart-summary-label">TOTAL:</div>
          <div class="cart-summary-amount" id="grandTotal">₱0.00</div>
        </div>
      </div>
      
      <button class="btn-checkout" id="checkoutBtn" data-bs-toggle="modal" data-bs-target="#checkoutModal" disabled>
        <i class="fas fa-credit-card"></i> CHECKOUT
      </button>
    </div>

  </div>
</div>

<div class="modal fade" id="checkoutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-cash-register"></i>
          Checkout Payment
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="checkout-row">
          <span class="checkout-label">Grand Total:</span>
          <span class="checkout-value" id="modalGrandTotal">₱0.00</span>
        </div>
        
        <div class="cash-input-group">
          <label style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #666; margin-bottom: 0.5rem; display: block;">
            <i class="fas fa-money-bill-wave"></i> Enter Cash Amount:
          </label>
          <input type="number" class="cash-input" id="cashInput" placeholder="₱ 0.00" step="0.01" min="0">
        </div>
        
        <div class="change-display">
          <div class="change-label">Change:</div>
          <div class="change-amount" id="changeAmount">₱0.00</div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-proceed" id="proceedPayment">
          <i class="fas fa-check-circle"></i> COMPLETE PAYMENT
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let cart = [];

$(document).on('click', '.qty-plus', function(){
    if($(this).is(':disabled')) return;
    const input = $(this).closest('.quantity-control').find('.quantity');
    const maxStock = parseInt(input.attr('max'));
    const currentVal = parseInt(input.val());
    if(currentVal < maxStock) {
        input.val(currentVal + 1);
    }
});

$(document).on('click', '.qty-minus', function(){
    if($(this).is(':disabled')) return;
    const input = $(this).closest('.quantity-control').find('.quantity');
    const val = parseInt(input.val());
    if(val > 1) input.val(val - 1);
});

$(document).on('input', '.quantity', function(){
    const maxStock = parseInt($(this).attr('max'));
    const val = parseInt($(this).val());
    if(val > maxStock) {
        $(this).val(maxStock);
        alert('Maximum available stock is ' + maxStock);
    }
    if(val < 1) {
        $(this).val(1);
    }
});

$(document).on('click', '.add-to-cart', function(){
    if($(this).is(':disabled')) return;
    const id = $(this).data('id');
    const name = $(this).data('name');
    const price = parseFloat($(this).data('price'));
    const availableStock = parseInt($(this).data('stock'));
    const qtyInput = $(this).closest('.product-info').find('.quantity');
    const qty = parseInt(qtyInput.val());

    if(isNaN(qty) || qty <= 0) {
        alert('Invalid quantity');
        return;
    }
    
    if(availableStock <= 0) {
        alert('This product is out of stock!');
        return;
    }

    const index = cart.findIndex(item => item.id === id);
    let totalInCart = 0;
    if(index >= 0) {
        totalInCart = cart[index].qty;
    }
    
    if(totalInCart + qty > availableStock) {
        alert(`Cannot add ${qty} items. Only ${availableStock - totalInCart} more available (${totalInCart} already in cart)`);
        return;
    }

    if(index >= 0){
        cart[index].qty += qty;
        cart[index].total = cart[index].qty * cart[index].price;
    } else {
        cart.push({id, name, price, qty, total: price*qty, availableStock});
    }
    
    qtyInput.val(1);
    renderCart();
});

function renderCart(){
    let html = '';
    let grandTotal = 0;
    
    if(cart.length === 0){
        html = `<div class="cart-empty">
                  <i class="fas fa-cart-arrow-down"></i>
                  <div class="cart-empty-text">Cart is empty</div>
                </div>`;
        $('#checkoutBtn').prop('disabled', true);
    } else {
        cart.forEach((item,i)=>{
            html += `<div class="cart-item">
                       <div class="cart-item-header">
                         <div class="cart-item-name">${item.name}</div>
                         <button class="btn-remove remove-item" data-index="${i}">
                           <i class="fas fa-times"></i>
                         </button>
                       </div>
                       <div class="cart-item-details">
                         <span class="cart-item-qty">${item.qty}x</span>
                         <span class="cart-item-price">₱${item.price.toFixed(2)}</span>
                         <span class="cart-item-total">₱${item.total.toFixed(2)}</span>
                       </div>
                     </div>`;
            grandTotal += item.total;
        });
        $('#checkoutBtn').prop('disabled', false);
    }
    
    $('#cartItemsContainer').html(html);
    $('#cartCount').text(cart.length);
    $('#grandTotal').text('₱'+grandTotal.toFixed(2));
    $('#modalGrandTotal').text('₱'+grandTotal.toFixed(2));
}

$(document).on('click', '.remove-item', function(){
    const i = $(this).data('index');
    cart.splice(i,1);
    renderCart();
});

$('#cashInput').on('input', function(){
    const cash = parseFloat($(this).val()) || 0;
    const grandTotal = parseFloat($('#modalGrandTotal').text().replace('₱','').replace(/,/g,''));
    const change = cash - grandTotal;
    $('#changeAmount').text('₱'+ (change >= 0 ? change.toFixed(2) : '0.00'));
});

$('#proceedPayment').click(function(){
    const cash = parseFloat($('#cashInput').val());
    const grandTotal = parseFloat($('#modalGrandTotal').text().replace('₱','').replace(/,/g,''));
    
    if(isNaN(cash) || cash < grandTotal){
        return alert('Insufficient cash amount!');
    }
    if(cart.length === 0){
        return alert('Cart is empty!');
    }

    $.ajax({
        url: 'pos_process.php',
        method: 'POST',
        data: { cart: JSON.stringify(cart), grandTotal: grandTotal },
        success: function(response){
            const data = JSON.parse(response);
            if(data.success){
                alert('Payment successful!');
                window.location.href = 'POS-print.php?tid=' + data.transaction_id + '&cash=' + cash;
            } else {
                alert('Error: ' + data.error);
            }
        },
        error: function(){
            alert('An error occurred. Please try again.');
        }
    });
});

$('#checkoutModal').on('hidden.bs.modal', function(){
    $('#cashInput').val('');
    $('#changeAmount').text('₱0.00');
});
</script>
</body>
</html>