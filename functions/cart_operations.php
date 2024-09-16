<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$action = $data['action'];

if ($action === 'add') {
    // Add item to cart
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();

    // Get new quantity
    $quantityStmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $quantityStmt->bind_param("ii", $user_id, $product_id);
    $quantityStmt->execute();
    $result = $quantityStmt->get_result();
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'];
    $quantityStmt->close();

    echo json_encode(['success' => true, 'new_quantity' => $new_quantity, 'product_name' => 'Product Name']); // Replace 'Product Name' with dynamic product name if needed
} elseif ($action === 'remove') {
    // Remove item from cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'product_name' => 'Product Name']); // Replace 'Product Name' with dynamic product name if needed
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
