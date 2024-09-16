<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items with correct table name
$cartItems = $conn->prepare("SELECT cart.id as cart_id, carpartsdatabase.*, cart.quantity FROM cart JOIN carpartsdatabase ON cart.product_id = carpartsdatabase.id WHERE cart.user_id = ?");
if ($cartItems) {
    $cartItems->bind_param("i", $user_id);
    $cartItems->execute();
    $cartResult = $cartItems->get_result();

    $total = 0;
    $items = [];  // To store items for reuse
    while ($item = $cartResult->fetch_assoc()) {
        $total += $item['Price'] * $item['quantity'];
        $items[] = $item;  // Store each item for reuse
    }
} else {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Handle order submission
if (isset($_POST['place_order'])) {
    // Create an order
    $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    if ($orderStmt) {
        $orderStmt->bind_param("id", $user_id, $total);
        $orderStmt->execute();
        $order_id = $orderStmt->insert_id;
        $orderStmt->close();

        // Insert order items
        foreach ($items as $item) {
            $orderItemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            if ($orderItemStmt) {
                $orderItemStmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['Price']);
                $orderItemStmt->execute();
                $orderItemStmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
                exit();
            }
        }

        // Clear the cart
        $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        if ($clearCart) {
            $clearCart->bind_param("i", $user_id);
            $clearCart->execute();
            $clearCart->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
            exit();
        }

        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit();
    } else {
        echo "Error preparing order statement: " . $conn->error;
        exit();
    }
}

// Include the header with cart count and search bar
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <main>
        <h1>Checkout</h1>
        <p>Total: $<?php echo htmlspecialchars($total); ?></p>
        <form method="POST" action="checkout.php">
            <button type="submit" name="place_order">Place Order</button>
        </form>
    </main>
</body>

</html>