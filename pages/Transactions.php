<?php
session_start();
if (!isset($_SESSION['first_name'])) {
    header("Location: ../index.php");
    exit();
}

$db = new mysqli("localhost", "root", "", "pos_system");
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// Summary stats
$stats = $db->query("SELECT 
                        COUNT(*) AS total_transactions,
                        SUM(quantity) AS total_items,
                        SUM(total_price) AS total_sales
                     FROM transactions")->fetch_assoc();

// Transactions for table
$transactions = $db->query("SELECT t.transaction_id, u.first_name AS user_first, u.last_name AS user_last,
                                    p.name AS product_name, t.quantity, t.total_price, t.sale_date
                            FROM transactions t
                            JOIN users u ON t.user_id = u.user_id
                            JOIN products p ON t.product_id = p.product_id
                            ORDER BY t.sale_date DESC");

// Sales summary per product
$sales_summary = $db->query("SELECT p.name AS product_name, SUM(t.quantity) AS units_sold, p.price, SUM(t.total_price) AS total_sales
                             FROM transactions t
                             JOIN products p ON t.product_id = p.product_id
                             GROUP BY t.product_id, p.name, p.price
                             ORDER BY total_sales DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Twin Bites Transactions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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

.btn-nav-dash {
  background: white;
  color: #FF9B50;
}

.btn-nav-dash:hover {
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

/* Page Title */
.page-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 2.5rem;
  color: white;
  text-shadow: 3px 3px 8px rgba(0,0,0,0.3);
  text-align: center;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.page-title i {
  font-size: 2.8rem;
}

/* Summary Cards */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: white;
  border-radius: 25px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.summary-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 215, 90, 0.15) 0%, transparent 70%);
  animation: pulseGlow 3s ease-in-out infinite;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.25);
}

.summary-card-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
}

.summary-card-1 {
  border-top: 5px solid #FFD95A;
}

.summary-card-1 .summary-card-icon {
  color: #FFD95A;
}

.summary-card-2 {
  border-top: 5px solid #FF9B50;
}

.summary-card-2 .summary-card-icon {
  color: #FF9B50;
}

.summary-card-3 {
  border-top: 5px solid #FF6B6B;
}

.summary-card-3 .summary-card-icon {
  color: #FF6B6B;
}

.summary-card-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 0.5rem;
}

.summary-card-value {
  font-family: 'Poppins', sans-serif;
  font-weight: 800;
  font-size: 2.5rem;
  color: #333;
  position: relative;
  z-index: 1;
}

/* Print Button */
.print-section {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
}

.btn-print {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  padding: 1rem 3rem;
  border-radius: 50px;
  border: none;
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  font-size: 1.1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn-print::after {
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

.btn-print:hover::after {
  width: 300px;
  height: 300px;
}

.btn-print:hover {
  background: linear-gradient(135deg, #45a049, #3d8b40);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(76, 175, 80, 0.5);
}

/* Content Cards */
.content-card {
  background: white;
  border-radius: 30px;
  padding: 2.5rem;
  box-shadow: 0 15px 50px rgba(0,0,0,0.2);
  margin-bottom: 2rem;
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

.card-header-custom {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.8rem;
  color: white;
  padding: 1.5rem 2rem;
  border-radius: 20px;
  margin: -2.5rem -2.5rem 2rem -2.5rem;
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
  z-index: 1;
}

.card-header-transactions {
  background: linear-gradient(135deg, #FF6B6B, #FF9B50);
  box-shadow: 0 6px 20px rgba(255, 107, 107, 0.3);
}

.card-header-sales {
  background: linear-gradient(135deg, #FFD95A, #FF9B50);
  box-shadow: 0 6px 20px rgba(255, 215, 90, 0.3);
}

/* Tables */
.tropical-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
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
  font-size: 0.85rem;
}

.tropical-table thead th {
  padding: 1rem;
  text-align: left;
  border: none;
}

.tropical-table thead th:first-child {
  border-radius: 12px 0 0 0;
}

.tropical-table thead th:last-child {
  border-radius: 0 12px 0 0;
}

.tropical-table tbody tr {
  background: white;
  transition: all 0.3s ease;
  border-bottom: 2px solid #f5f5f5;
}

.tropical-table tbody tr:hover {
  background: linear-gradient(135deg, rgba(255, 215, 90, 0.1), rgba(255, 155, 80, 0.1));
  transform: scale(1.01);
}

.tropical-table tbody td {
  padding: 1rem;
  font-family: 'Nunito', sans-serif;
  font-weight: 600;
  color: #333;
  vertical-align: middle;
  border: none;
}

/* DataTables Custom Styling */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
  font-family: 'Nunito', sans-serif;
  font-weight: 600;
  color: #666;
}

.dataTables_wrapper .dataTables_filter input {
  border: 2px solid #FFD95A;
  border-radius: 20px;
  padding: 0.5rem 1rem;
  font-family: 'Nunito', sans-serif;
  transition: all 0.3s ease;
}

.dataTables_wrapper .dataTables_filter input:focus {
  border-color: #FF9B50;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(255, 155, 80, 0.25);
}

.dataTables_wrapper .dataTables_length select {
  border: 2px solid #FFD95A;
  border-radius: 15px;
  padding: 0.4rem 0.8rem;
  font-family: 'Nunito', sans-serif;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  border-radius: 10px;
  padding: 0.5rem 1rem;
  margin: 0 0.2rem;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: linear-gradient(135deg, #FF9B50, #FF6B6B) !important;
  color: white !important;
  border: none !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: linear-gradient(135deg, #FFD95A, #FF9B50) !important;
  color: white !important;
  border: none !important;
}

/* Responsive Design */
@media (max-width: 768px) {
  .brand-title {
    font-size: 1.5rem;
  }
  
  .page-title {
    font-size: 1.8rem;
  }
  
  .nav-buttons .btn {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
  }
  
  .summary-grid {
    grid-template-columns: 1fr;
  }
  
  .content-card {
    padding: 1.5rem;
  }
  
  .card-header-custom {
    font-size: 1.3rem;
    padding: 1rem 1.5rem;
    margin: -1.5rem -1.5rem 1.5rem -1.5rem;
  }
  
  .tropical-table {
    font-size: 0.85rem;
  }
  
  .tropical-table thead th,
  .tropical-table tbody td {
    padding: 0.7rem 0.5rem;
  }
}

@keyframes pulseGlow {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="tropical-navbar">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="brand-title">
      <i class="fas fa-receipt brand-icon"></i>
      Twin Bites Transactions
    </div>
    <div class="nav-buttons d-flex gap-2">
      <a href="POS.php" class="btn btn-nav-pos">
        <i class="fas fa-cash-register"></i> POS
      </a>
      <a href="dashboard.php" class="btn btn-nav-dash">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
      <a href="../logout.php" class="btn btn-nav-logout">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="main-container">
  
  <!-- Page Title -->
  <h1 class="page-title">
    <i class="fas fa-chart-line"></i>
    Sales & Transaction Reports
  </h1>

  <!-- Summary Cards -->
  <div class="summary-grid">
    <div class="summary-card summary-card-1">
      <i class="fas fa-shopping-cart summary-card-icon"></i>
      <div class="summary-card-title">Total Transactions</div>
      <div class="summary-card-value"><?= $stats['total_transactions'] ?? 0 ?></div>
    </div>
    
    <div class="summary-card summary-card-2">
      <i class="fas fa-boxes summary-card-icon"></i>
      <div class="summary-card-title">Total Items Sold</div>
      <div class="summary-card-value"><?= $stats['total_items'] ?? 0 ?></div>
    </div>
    
    <div class="summary-card summary-card-3">
      <i class="fas fa-peso-sign summary-card-icon"></i>
      <div class="summary-card-title">Total Sales</div>
      <div class="summary-card-value">₱<?= number_format($stats['total_sales'] ?? 0, 2) ?></div>
    </div>
  </div>

  <!-- Print Button -->
  <div class="print-section">
    <button class="btn-print" id="printBtn">
      <i class="fas fa-print"></i> PRINT REPORT
    </button>
  </div>

  <!-- Transactions Table -->
  <div class="content-card">
    <div class="card-header-custom card-header-transactions">
      <i class="fas fa-history"></i>
      Transaction History
    </div>
    <table id="transactionsTable" class="tropical-table table-hover">
      <thead>
        <tr>
          <th>Receipt ID</th>
          <th>User</th>
          <th>Product</th>
          <th>Quantity</th>
          <th>Total Price</th>
          <th>Sale Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $transactions->fetch_assoc()): ?>
          <tr>
            <td>#<?= $row['transaction_id'] ?></td>
            <td><?= $row['user_first'] . ' ' . $row['user_last'] ?></td>
            <td><?= $row['product_name'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td>₱<?= number_format($row['total_price'], 2) ?></td>
            <td><?= $row['sale_date'] ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Sales Summary -->
  <div class="content-card">
    <div class="card-header-custom card-header-sales">
      <i class="fas fa-chart-pie"></i>
      Sales Summary by Product
    </div>
    <table class="tropical-table table-hover">
      <thead>
        <tr>
          <th>Product</th>
          <th>Units Sold</th>
          <th>Price</th>
          <th>Total Sales</th>
        </tr>
      </thead>
      <tbody>
        <?php while($s = $sales_summary->fetch_assoc()): ?>
          <tr>
            <td><?= $s['product_name'] ?></td>
            <td><?= $s['units_sold'] ?></td>
            <td>₱<?= number_format($s['price'], 2) ?></td>
            <td><strong style="color: #FF6B6B;">₱<?= number_format($s['total_sales'], 2) ?></strong></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
// Initialize DataTables
$(document).ready(function() {
  $('#transactionsTable').DataTable({
    order: [[0, 'desc']],
    pageLength: 10,
    searching: false,
    lengthChange: false,
    language: {
      info: "Showing _START_ to _END_ of _TOTAL_ transactions",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Prev"
      }
    }
  });
});

// Print Button
document.getElementById('printBtn').addEventListener('click', function() {
    window.location.href = 'printReport.php';
});
</script>
</body>
</html>