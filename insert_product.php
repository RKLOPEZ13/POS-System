<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

$db = new mysqli("localhost", "root", "", "pos_system");
if ($db->connect_error) die("DB Connection Failed: " . $db->connect_error);

// Get POST data
$name = trim($_POST['name']);
$price = floatval($_POST['price']);
$stock = intval($_POST['stock_quantity']);

if ($name === '' || $price <= 0 || $stock < 0) {
    exit("Invalid input");
}

// Insert into products
$stmt = $db->prepare("INSERT INTO products (name, price, stock_quantity) VALUES (?, ?, ?)");
$stmt->bind_param("sdi", $name, $price, $stock);
if ($stmt->execute()) {
    // Log the action
    $user_id = $_SESSION['user_id'];
    $action = "Added product";
    $target = $name;
    $log_stmt = $db->prepare("INSERT INTO logs (action, user_id, target) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sis", $action, $user_id, $target);
    $log_stmt->execute();

    echo "Product added successfully!";
} else {
    echo "Error adding product: " . $stmt->error;
}
?>
