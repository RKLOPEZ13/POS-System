<?php
session_start();
if (!isset($_SESSION['first_name'])) {
    header("Location: ../index.php");
    exit();
}

$db = new mysqli("localhost", "root", "", "pos_system");
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// ---------- AJAX HANDLERS ----------

// Add Stock
if (isset($_POST['action']) && $_POST['action'] === 'add_stock' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']);

    // Update total_stock and end_stock
    $db->query("UPDATE products 
                SET total_stock = total_stock + $quantity,
                    end_stock   = end_stock + $quantity
                WHERE product_id = $product_id");

    // Log the action
    $user_id = $_SESSION['user_id'];
    $product_name = $db->query("SELECT name FROM products WHERE product_id = $product_id")->fetch_assoc()['name'];
    $db->query("INSERT INTO logs (user_id, action) VALUES ($user_id, 'Added stock ($quantity) for product: $product_name')");

    echo "Stock added successfully!";
    exit();
}


// Product Details
if (isset($_GET['action']) && $_GET['action'] === 'get_product' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $db->query("SELECT * FROM products WHERE product_id = $id");
    if ($row = $res->fetch_assoc()) {
        echo "<p><strong>ID:</strong> {$row['product_id']}</p>";
        echo "<p><strong>Name:</strong> {$row['name']}</p>";
        echo "<p><strong>Description:</strong> {$row['description']}</p>";
        echo "<p><strong>Beginning Stock:</strong> {$row['beginning_stock']}</p>";
        echo "<p><strong>Total Stock:</strong> {$row['total_stock']}</p>";
        echo "<p><strong>End Stock:</strong> {$row['end_stock']}</p>";
        echo "<p><strong>Price:</strong> ₱{$row['price']}</p>";
        echo "<p><strong>Image:</strong><br><img src='../assets/images/{$row['image']}' style='max-width:200px;'></p>";
        echo "<p><strong>Created At:</strong> {$row['created_at']}</p>";
    } else {
        echo "<p>Product not found.</p>";
    }
    exit();
}

// Insert Product
if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = $db->real_escape_string($_POST['name']);
    $description = $db->real_escape_string($_POST['description']);
    $beginning_stock = intval($_POST['beginning_stock']);
    $price = floatval($_POST['price']);

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "../assets/images/";
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $image);
    }

    $total_stock = $beginning_stock;
    $end_stock = $beginning_stock;

    $db->query("INSERT INTO products (name, description, beginning_stock, total_stock, end_stock, price, image) 
                VALUES ('$name', '$description', $beginning_stock, $total_stock, $end_stock, $price, '$image')");

    // Log the action
    $user_id = $_SESSION['user_id'];
    $db->query("INSERT INTO logs (user_id, action) VALUES ($user_id, 'Added new product: $name')");

    echo "Product added successfully!";
    exit();
}

// Insert Employee
if (isset($_POST['action']) && $_POST['action'] === 'add_employee') {
    $first_name = $db->real_escape_string($_POST['first_name']);
    $last_name  = $db->real_escape_string($_POST['last_name']);
    $role       = in_array($_POST['role'], ['admin','cashier']) ? $_POST['role'] : 'cashier';
    $password   = $db->real_escape_string($_POST['password']);
    $hired_at   = date('Y-m-d H:i:s');

    $db->query("INSERT INTO users (first_name, last_name, role, password, hired_at) 
                VALUES ('$first_name', '$last_name', '$role', '$password', '$hired_at')");

    // Log the action
    $user_id = $_SESSION['user_id'];
    $db->query("INSERT INTO logs (user_id, action) VALUES ($user_id, 'Added new employee: $first_name $last_name with role $role')");

    echo "Employee added successfully!";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Twin Bites Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
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

/* Animated Sunburst Background */
body::before {
  content: '';
  position: fixed;
  top: 50%;
  left: 50%;
  width: 200%;
  height: 200%;
  background: 
    repeating-conic-gradient(from 0deg at 50% 50%, 
      rgba(255, 255, 255, 0.03) 0deg, 
      rgba(255, 255, 255, 0.03) 5deg, 
      transparent 5deg, 
      transparent 10deg);
  transform: translate(-50%, -50%) rotate(0deg);
  animation: rotateSun 60s linear infinite;
  pointer-events: none;
  z-index: 0;
}

@keyframes rotateSun {
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Navbar */
.tropical-navbar {
  background: linear-gradient(90deg, #FF6B6B 0%, #FF9B50 50%, #FFD95A 100%);
  padding: 1.2rem 2rem;
  box-shadow: 0 8px 32px rgba(255, 107, 107, 0.4);
  position: relative;
  z-index: 100;
  border-bottom: 4px solid rgba(255, 255, 255, 0.3);
}

.brand-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 2rem;
  color: white;
  text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
  letter-spacing: 1px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-icon {
  font-size: 2.2rem;
  filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
}

.nav-buttons .btn {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  padding: 0.6rem 1.5rem;
  border-radius: 50px;
  border: 3px solid white;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.btn-nav-pos {
  background: white;
  color: #FF6B6B;
}

.btn-nav-pos:hover {
  background: #FF6B6B;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.btn-nav-trans {
  background: white;
  color: #FF9B50;
}

.btn-nav-trans:hover {
  background: #FF9B50;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.btn-nav-logout {
  background: rgba(255,255,255,0.2);
  color: white;
  backdrop-filter: blur(10px);
}

.btn-nav-logout:hover {
  background: rgba(0,0,0,0.3);
  color: white;
  transform: translateY(-2px);
}

/* Main Container */
.main-container {
  max-width: 1400px;
  margin: 3rem auto;
  padding: 0 2rem;
  position: relative;
  z-index: 10;
}

/* Tab Navigation */
.tropical-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.tropical-tab-btn {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  padding: 1rem 2.5rem;
  border: none;
  border-radius: 20px 20px 0 0;
  background: rgba(255, 255, 255, 0.3);
  color: white;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.tropical-tab-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s;
}

.tropical-tab-btn:hover::before {
  left: 100%;
}

.tropical-tab-btn:hover {
  background: rgba(255, 255, 255, 0.5);
  transform: translateY(-3px);
}

.tropical-tab-btn.active {
  background: white;
  color: #FF6B6B;
  text-shadow: none;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
}

/* Content Card */
.content-card {
  background: white;
  border-radius: 0 30px 30px 30px;
  padding: 2.5rem;
  box-shadow: 0 15px 50px rgba(0,0,0,0.2);
  position: relative;
  overflow: hidden;
}

.content-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 215, 90, 0.1) 0%, transparent 70%);
  animation: pulseGlow 4s ease-in-out infinite;
}

@keyframes pulseGlow {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

/* Section Headers */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #FFD95A;
}

.section-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.8rem;
  color: #FF6B6B;
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-title i {
  font-size: 2rem;
}

/* Action Buttons */
.btn-tropical {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  padding: 0.7rem 1.8rem;
  border-radius: 50px;
  border: none;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  position: relative;
  overflow: hidden;
}

.btn-tropical::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255,255,255,0.4);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.btn-tropical:hover::after {
  width: 300px;
  height: 300px;
}

.btn-add-product {
  background: linear-gradient(135deg, #FFD95A, #FF9B50);
  color: white;
}

.btn-add-product:hover {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 155, 80, 0.4);
}

.btn-add-employee {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
  color: white;
}

.btn-add-employee:hover {
  background: linear-gradient(135deg, #FF6B6B, #E85D5D);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
}

/* Tables */
.tropical-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px;
  position: relative;
  z-index: 1;
}

.tropical-table thead tr {
  background: linear-gradient(135deg, #FF6B6B, #FF9B50);
  color: white;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 0.9rem;
}

.tropical-table thead th {
  padding: 1.2rem 1rem;
  text-align: left;
  box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
}

.tropical-table thead th:first-child {
  border-radius: 15px 0 0 15px;
}

.tropical-table thead th:last-child {
  border-radius: 0 15px 15px 0;
}

.tropical-table tbody tr {
  background: white;
  transition: all 0.3s ease;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.tropical-table tbody tr:hover {
  background: linear-gradient(135deg, rgba(255, 215, 90, 0.1), rgba(255, 155, 80, 0.1));
  transform: scale(1.02);
  box-shadow: 0 5px 20px rgba(255, 107, 107, 0.2);
}

.tropical-table tbody td {
  padding: 1.2rem 1rem;
  font-family: 'Nunito', sans-serif;
  font-weight: 600;
  color: #333;
  vertical-align: middle;
}

.tropical-table tbody tr td:first-child {
  border-radius: 15px 0 0 15px;
}

.tropical-table tbody tr td:last-child {
  border-radius: 0 15px 15px 0;
}

/* Table Action Buttons */
.btn-table-action {
  font-family: 'Nunito', sans-serif;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  border: none;
  font-size: 0.85rem;
  margin: 0 0.25rem;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-details {
  background: linear-gradient(135deg, #FFD95A, #FFC300);
  color: white;
  box-shadow: 0 3px 10px rgba(255, 215, 90, 0.3);
}

.btn-details:hover {
  background: linear-gradient(135deg, #FFC300, #FF9B50);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 195, 0, 0.4);
}

.btn-add-stock {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
  color: white;
  box-shadow: 0 3px 10px rgba(255, 155, 80, 0.3);
}

.btn-add-stock:hover {
  background: linear-gradient(135deg, #FF6B6B, #E85D5D);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
}

/* Modals */
.modal-content {
  border-radius: 30px;
  border: none;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
  border: none;
  padding: 2rem;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.5rem;
  color: white;
  position: relative;
}

.modal-header.bg-add-product {
  background: linear-gradient(135deg, #FFD95A, #FF9B50);
}

.modal-header.bg-add-employee {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
}

.modal-header.bg-details {
  background: linear-gradient(135deg, #FFC300, #FF9B50);
}

.modal-body {
  padding: 2rem;
  background: linear-gradient(135deg, rgba(255, 215, 90, 0.05), rgba(255, 155, 80, 0.05));
}

.form-control, .form-select {
  border: 2px solid #FFD95A;
  border-radius: 15px;
  padding: 0.8rem 1rem;
  font-family: 'Nunito', sans-serif;
  font-weight: 600;
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: #FF9B50;
  box-shadow: 0 0 0 0.2rem rgba(255, 155, 80, 0.25);
}

.modal-footer {
  border: none;
  padding: 1.5rem 2rem;
  background: white;
}

.btn-modal-submit {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  padding: 0.8rem 2.5rem;
  border-radius: 50px;
  border: none;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.btn-modal-product {
  background: linear-gradient(135deg, #FFD95A, #FF9B50);
  color: white;
}

.btn-modal-product:hover {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 155, 80, 0.4);
}

.btn-modal-employee {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B);
  color: white;
}

.btn-modal-employee:hover {
  background: linear-gradient(135deg, #FF6B6B, #E85D5D);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
}

/* Product Details Styling */
#detailsContent p {
  font-family: 'Nunito', sans-serif;
  font-size: 1rem;
  margin-bottom: 0.8rem;
  color: #555;
}

#detailsContent strong {
  color: #FF6B6B;
  font-weight: 700;
}

#detailsContent img {
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.15);
  margin-top: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .brand-title {
    font-size: 1.5rem;
  }
  
  .nav-buttons .btn {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
  }
  
  .tropical-tab-btn {
    padding: 0.8rem 1.5rem;
    font-size: 0.95rem;
  }
  
  .content-card {
    padding: 1.5rem;
  }
  
  .section-title {
    font-size: 1.4rem;
  }
  
  .tropical-table {
    font-size: 0.85rem;
  }
  
  .btn-table-action {
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
  }
}

/* Hide inactive tabs */
.tab-pane {
  display: none;
}

.tab-pane.active {
  display: block;
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="tropical-navbar">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="brand-title">
      <i class="fas fa-store brand-icon"></i>
      Twin Bites Dashboard
    </div>
    <div class="nav-buttons d-flex gap-2">
      <a href="POS.php" class="btn btn-nav-pos">
        <i class="fas fa-cash-register"></i> POS
      </a>
      <a href="Transactions.php" class="btn btn-nav-trans">
        <i class="fas fa-receipt"></i> Transactions
      </a>
      <a href="../logout.php" class="btn btn-nav-logout">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="main-container">
  <!-- Tab Navigation -->
  <div class="tropical-tabs">
    <button class="tropical-tab-btn active" data-tab="products">
      <i class="fas fa-box"></i> Manage Products
    </button>
    <button class="tropical-tab-btn" data-tab="employees">
      <i class="fas fa-users"></i> Manage Employees
    </button>
  </div>

  <!-- Content Card -->
  <div class="content-card">

    <!-- PRODUCTS TAB -->
    <div class="tab-pane active" id="products">
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-boxes"></i>
          Product Inventory
        </h2>
        <button class="btn-tropical btn-add-product" data-bs-toggle="modal" data-bs-target="#addProductModal">
          <i class="fas fa-plus-circle"></i> Add Product
        </button>
      </div>
      <table class="tropical-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $db->query("SELECT * FROM products ORDER BY product_id DESC");
          while ($p = $res->fetch_assoc()) {
              echo "<tr>
                      <td>#{$p['product_id']}</td>
                      <td>{$p['name']}</td>
                      <td>₱{$p['price']}</td>
                      <td>{$p['end_stock']}</td>
                      <td>
                        <button class='btn-table-action btn-details' data-bs-toggle='modal' data-bs-target='#detailsModal' data-id='{$p['product_id']}'>
                          <i class='fas fa-info-circle'></i> Details
                        </button>
                        <button class='btn-table-action btn-add-stock add-stock-btn' data-id='{$p['product_id']}'>
                          <i class='fas fa-plus'></i> Add Stock
                        </button>
                      </td>
                    </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- EMPLOYEES TAB -->
    <div class="tab-pane" id="employees">
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-user-tie"></i>
          Employee Accounts
        </h2>
        <button class="btn-tropical btn-add-employee" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
          <i class="fas fa-user-plus"></i> Add Employee
        </button>
      </div>
      <table class="tropical-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Role</th>
            <th>Hired At</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $db->query("SELECT * FROM users ORDER BY user_id DESC");
          while ($u = $res->fetch_assoc()) {
              echo "<tr>
                      <td>#{$u['user_id']}</td>
                      <td>{$u['first_name']}</td>
                      <td>{$u['last_name']}</td>
                      <td><span style='color: #FF6B6B; font-weight: 700; text-transform: uppercase;'>{$u['role']}</span></td>
                      <td>{$u['hired_at']}</td>
                    </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- MODALS -->

<!-- Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addProductForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header bg-add-product">
        <h5><i class="fas fa-box-open"></i> Add New Product</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" name="name" class="form-control mb-3" placeholder="Product Name" required>
        <textarea name="description" class="form-control mb-3" placeholder="Description" rows="3" required></textarea>
        <input type="number" name="beginning_stock" class="form-control mb-3" placeholder="Beginning Stock" required>
        <input type="number" step="0.01" name="price" class="form-control mb-3" placeholder="Price (₱)" required>
        <input type="file" name="image" class="form-control mb-3" accept="image/*" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-modal-submit btn-modal-product">
          <i class="fas fa-check-circle"></i> Add Product
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Add Employee -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addEmployeeForm" class="modal-content">
      <div class="modal-header bg-add-employee">
        <h5><i class="fas fa-id-badge"></i> Add Employee</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" name="first_name" class="form-control mb-3" placeholder="First Name" required>
        <input type="text" name="last_name" class="form-control mb-3" placeholder="Last Name" required>
        <input type="text" name="password" class="form-control mb-3" placeholder="Password" required>
        <select name="role" class="form-select mb-3">
          <option value="cashier">Cashier</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-modal-submit btn-modal-employee">
          <i class="fas fa-user-check"></i> Add Employee
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Product Details -->
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-details">
        <h5><i class="fas fa-clipboard-list"></i> Product Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailsContent">Loading...</div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tab Switching
$('.tropical-tab-btn').on('click', function(){
    $('.tropical-tab-btn').removeClass('active');
    $(this).addClass('active');
    
    const tab = $(this).data('tab');
    $('.tab-pane').removeClass('active');
    $('#' + tab).addClass('active');
});

// Add Stock
$('.add-stock-btn').on('click', function(){
    const productId = $(this).data('id');
    const qty = prompt("Enter quantity to add:");
    if (!qty || isNaN(qty) || qty <= 0) return alert("Invalid quantity");

    $.post(window.location.href, { action: 'add_stock', product_id: productId, quantity: qty }, function(res){
        alert(res);
        location.reload();
    });
});

// Add Product
$('#addProductForm').on('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('action', 'add_product');

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res){
            alert(res);
            location.reload();
        }
    });
});

// Add Employee
$('#addEmployeeForm').on('submit', function(e){
    e.preventDefault();
    $.post(window.location.href, $(this).serialize() + '&action=add_employee', function(res){
        alert(res);
        location.reload();
    });
});

// Product Details Modal
$('#detailsModal').on('show.bs.modal', function(e){
    const id = $(e.relatedTarget).data('id');
    if (!id) { $('#detailsContent').html("Invalid product ID"); return; }
    $('#detailsContent').html("Loading...");
    $.get(window.location.href, { action: 'get_product', id: id }, function(data){
        $('#detailsContent').html(data);
    });
});
</script>
</body>
</html>