<?php
include 'db.php'; // Include your database connection
session_start();

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Prepare the SQL query to get the total number of items in the cart for the logged-in user
$cart_query = $conn->prepare("SELECT SUM(quantity) as cart_total FROM cart WHERE user_id = ?");
if ($cart_query) {
    $cart_query->bind_param("i", $userId);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_data = $cart_result->fetch_assoc();
    $cart_count = $cart_data['cart_total'] ?? 0;
    $cart_query->close();

    // Return the cart count in JSON format
    echo json_encode(['success' => true, 'cart_count' => $cart_count]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error fetching cart total: ' . $conn->error]);
    exit;
}
