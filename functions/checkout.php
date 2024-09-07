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

// Fetch cart items
$cartItems = $conn->prepare("SELECT cart.id as cart_id, products.*, cart.quantity FROM cart JOIN products ON cart.product_id = products.id WHERE cart.user_id = ?");
$cartItems->bind_param("i", $user_id);
$cartItems->execute();
$cartResult = $cartItems->get_result();

$total = 0;
while ($item = mysqli_fetch_assoc($cartResult)) {
    $total += $item['price'] * $item['quantity'];
}

// Handle order submission
if (isset($_POST['place_order'])) {
    // Create an order
    $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $orderStmt->bind_param("id", $user_id, $total);
    $orderStmt->execute();
    $order_id = $orderStmt->insert_id;
    $orderStmt->close();

    // Insert order items
    $cartResult->data_seek(0); // Reset result pointer
    while ($item = mysqli_fetch_assoc($cartResult)) {
        $orderItemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $orderItemStmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $orderItemStmt->execute();
        $orderItemStmt->close();
    }

    // Clear the cart
    $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearCart->bind_param("i", $user_id);
    $clearCart->execute();
    $clearCart->close();

    header('Location: order_confirmation.php?order_id=' . $order_id);
    exit();
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
        <p>Total: $<?php echo $total; ?></p>
        <form method="POST" action="checkout.php">
            <button type="submit" name="place_order">Place Order</button>
        </form>
    </main>
</body>

</html>