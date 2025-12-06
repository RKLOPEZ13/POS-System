<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

$db = new mysqli("localhost", "root", "", "pos_system");
if ($db->connect_error) die("DB Connection Failed: " . $db->connect_error);

// Get POST data
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$role = $_POST['role'];

if ($username === '' || $password === '' || !in_array($role, ['admin','cashier'])) {
    exit("Invalid input");
}

// Check if username exists
$check = $db->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    exit("Username already exists");
}

// Insert into users
$stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $password, $role);
if ($stmt->execute()) {
    // Log the action
    $user_id = $_SESSION['user_id'];
    $action = "Added employee";
    $target = $username;
    $log_stmt = $db->prepare("INSERT INTO logs (action, user_id, target) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sis", $action, $user_id, $target);
    $log_stmt->execute();

    echo "Employee added successfully!";
} else {
    echo "Error adding employee: " . $stmt->error;
}
?>
