<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['tid'])) {
    header("Location: ../index.php");
    exit();
}

$tid = intval($_GET['tid']); 
$cash = floatval($_GET['cash'] ?? 0);

$db = new mysqli("localhost", "root", "", "pos_system");
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

$gtotal = 0;
$totalitems = 0;

$trans_res = $db->query("
    SELECT t.*, p.name, p.price 
    FROM transactions t 
    JOIN products p ON t.product_id = p.product_id 
    WHERE t.transaction_id = $tid
");

if($trans_res->num_rows == 0) exit('Transaction not found.');
$rows = $trans_res->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>POS Receipt</title>
<style>
@page { size: 40mm auto; margin: 0; }
body { 
    font-family: monospace; 
    font-size: 9px; 
    line-height: 1.1; 
    width: 40mm; 
    margin: 0; 
    color: #000;       /* pure black */
    font-weight: bold; /* thicker text */
}
#receipt { 
    padding: 2px; 
}
h3 { 
    text-align: center; 
    font-size: 11px; 
    margin: 1px 0; 
    font-weight: bold; 
    color: #000; 
}
p { 
    margin: 1px 0; 
    font-weight: bold; 
    color: #000; 
}
hr { 
    border: none; 
    border-top: 1px dashed #000; 
    margin: 2px 0; 
}
.product-line { 
    width: 100%; 
    font-weight: bold; 
    color: #000; 
}
.left { 
    float: left; 
    font-weight: bold; 
    color: #000; 
}
.right { 
    float: right; 
    font-weight: bold; 
    color: #000; 
}
.clear { 
    clear: both; 
}
</style>
</head>
<body>

<div id="receipt" align="center">
    <h3>Twin Bites Snack Corner</h3>
    <p>Kapitan Ponso St.</p>
    <p>Bauan, Batangas 4201</p>
    <p>+639975228553</p>
    <p>TwinBites@gmail.com</p>
    <hr>
    <p>Receipt #: <?= $tid ?></p>
    <p>Cashier: <?= htmlspecialchars($_SESSION['first_name']) ?></p>
    <p>Date: <?= date("Y-m-d H:i") ?></p>
    <hr>

    <?php foreach($rows as $row): 
        $total = $row['quantity'] * $row['price'];
        $gtotal += $total;
        $totalitems += $row['quantity'];
        $prodName = strlen($row['name'])>18 ? substr($row['name'],0,18).'…' : $row['name'];
    ?>
        <p class="product-line">
            <span class="left"><?= $prodName ?></span>
            <div class="clear"></div>
            <span class="right"><?= $row['quantity'] ?> × <?= number_format($row['price'],2) ?> → <?= number_format($total,2) ?></span>
            <div class="clear"></div>
        </p>
    <?php endforeach; ?>

    <hr>
    <div>
      <p>Total Items: <span class="right"><?= $totalitems ?></span><div class="clear"></div></p>
      <p>Total: <span class="right">₱<?= number_format($gtotal,2) ?></span><div class="clear"></div></p>
      <p>Cash: <span class="right">₱<?= number_format($cash,2) ?></span><div class="clear"></div></p>
      <p>Change: <span class="right">₱<?= number_format($cash - $gtotal,2) ?></span><div class="clear"></div></p>
      <hr>
      <p style="text-align:center;">Thank you for your purchase!</p>
    </div>
</div>

<script>
window.onload = function() {
    window.print();
    window.onafterprint = function(){ window.location.href = 'pos.php'; }
}
</script>
</body>
</html>