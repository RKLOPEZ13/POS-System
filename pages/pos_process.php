<?php
session_start();
if (!isset($_SESSION['user_id'])) exit(json_encode(['success'=>false,'error'=>'Unauthorized']));

$db = new mysqli("localhost", "root", "", "pos_system");
if ($db->connect_error) die(json_encode(['success'=>false,'error'=>'DB Connection Failed']));

$cart = json_decode($_POST['cart'], true);
$grandTotal = floatval($_POST['grandTotal']);
$user_id = $_SESSION['user_id'];

if(!$cart) exit(json_encode(['success'=>false,'error'=>'Invalid cart']));

$db->begin_transaction();
try {
    // First, validate all products have sufficient stock
    foreach($cart as $item){
        $pid = intval($item['id']);
        $qty = intval($item['qty']);
        
        // Check current stock
        $check = $db->prepare("SELECT end_stock, name FROM products WHERE product_id = ?");
        $check->bind_param("i", $pid);
        $check->execute();
        $result = $check->get_result();
        
        if($result->num_rows === 0){
            throw new Exception("Product not found");
        }
        
        $product = $result->fetch_assoc();
        
        if($product['end_stock'] < $qty){
            throw new Exception("Insufficient stock for {$product['name']}. Available: {$product['end_stock']}, Requested: {$qty}");
        }
    }
    
    // If all validations pass, process the transactions
    $last_transaction_id = 0;
    foreach($cart as $item){
        $pid = intval($item['id']);
        $qty = intval($item['qty']);
        $total = floatval($item['total']);

        $stmt = $db->prepare("INSERT INTO transactions (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $user_id, $pid, $qty, $total);
        $stmt->execute();

        $last_transaction_id = $db->insert_id;

        $stmt2 = $db->prepare("UPDATE products SET end_stock = end_stock - ? WHERE product_id = ?");
        $stmt2->bind_param("ii", $qty, $pid);
        $stmt2->execute();
    }

    $action = "Processed sale, total: ₱$grandTotal";
    $stmt3 = $db->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $stmt3->bind_param("is", $user_id, $action);
    $stmt3->execute();

    $db->commit();
    echo json_encode(['success'=>true,'transaction_id'=>$last_transaction_id]);
} catch(Exception $e){
    $db->rollback();
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>