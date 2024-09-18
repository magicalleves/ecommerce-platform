<?php
include 'db.php'; // Include your database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id']);
    $action = $_POST['action'];
    $userId = $_SESSION['user_id'];

    if (empty($productId) || !in_array($action, ['increment', 'decrement'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or action.']);
        exit;
    }

    $quantity = 0; // Default quantity to return
    $cart_count = 0; // Default cart count to return

    // Check if the product exists in the cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->bind_result($quantity);
    $stmt->fetch();
    $stmt->close();

    // Fetch quantity (stock limit) from the product table
    $stockStmt = $conn->prepare("SELECT Quantity FROM carpartsdatabase WHERE id = ?");
    $stockStmt->bind_param("i", $productId);
    $stockStmt->execute();
    $stockResult = $stockStmt->get_result();
    $stockData = $stockResult->fetch_assoc();
    $stock = $stockData['Quantity'] ?? 0;
    $stockStmt->close();

    if ($action === 'increment' && $quantity < $stock) {
        if ($quantity > 0) {
            // If the item exists in the cart, increment the quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        } else {
            // If the item does not exist in the cart, insert it with quantity 1
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, 1, NOW())");
        }

        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Error preparing increment statement: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("ii", $userId, $productId);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to increment product in cart: ' . $stmt->error]);
            exit;
        }
        $stmt->close();

        // Set the new quantity
        $quantity++;
    } elseif ($action === 'decrement' && $quantity > 0) {
        // Decrement the product quantity by 1 if greater than 0, or delete if it becomes 0
        if ($quantity > 1) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Error preparing decrement statement: ' . $conn->error]);
                exit;
            }

            $stmt->bind_param("ii", $userId, $productId);
            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Failed to decrement product in cart: ' . $stmt->error]);
                exit;
            }
            $stmt->close();

            // Set the new quantity
            $quantity--;
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Error preparing delete statement: ' . $conn->error]);
                exit;
            }

            $stmt->bind_param("ii", $userId, $productId);
            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Failed to delete product from cart: ' . $stmt->error]);
                exit;
            }
            $stmt->close();
            $quantity = 0; // Set quantity to 0 since it's removed
        }
    }

    // Get the updated total number of items in the cart
    $cart_query = $conn->prepare("SELECT SUM(quantity) as cart_total FROM cart WHERE user_id = ?");
    if ($cart_query) {
        $cart_query->bind_param("i", $userId);
        $cart_query->execute();
        $cart_result = $cart_query->get_result();
        $cart_data = $cart_result->fetch_assoc();
        $cart_count = $cart_data['cart_total'] ?? 0;
        $cart_query->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error fetching cart total: ' . $conn->error]);
        exit;
    }

    echo json_encode(['success' => true, 'quantity' => $quantity, 'cart_count' => $cart_count, 'message' => 'Cart updated successfully']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
