<?php
include 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $action = $_POST['action'];
    $userId = $_SESSION['user_id']; // Assuming the user is logged in

    if ($action === 'add') {
        // Add or update product in the cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = ?");
        $stmt->bind_param("iiii", $userId, $productId, $quantity, $quantity);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'remove' && $quantity >= 0) {
        // Remove or update product in the cart
        if ($quantity == 0) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $userId, $productId);
            $stmt->execute();
        }
        $stmt->close();
    }

    echo json_encode(['success' => true]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
