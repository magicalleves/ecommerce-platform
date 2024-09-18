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

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Check if product is already in the cart
    $checkCart = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    if ($checkCart) {
        $checkCart->bind_param("ii", $user_id, $product_id);
        $checkCart->execute();
        $cartResult = $checkCart->get_result();

        if ($cartResult->num_rows > 0) {
            // Update quantity if already in cart
            $updateCart = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $updateCart->bind_param("iii", $quantity, $user_id, $product_id);
            $updateCart->execute();
            $updateCart->close();
        } else {
            // Insert new item to cart
            $insertCart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insertCart->bind_param("iii", $user_id, $product_id, $quantity);
            $insertCart->execute();
            $insertCart->close();
        }
        $checkCart->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
    header('Location: cart.php');
    exit();
}

// Remove product from cart
if (isset($_POST['remove'])) {
    $cart_id = intval($_POST['cart_id']);
    $removeCart = $conn->prepare("DELETE FROM cart WHERE id = ?");
    if ($removeCart) {
        $removeCart->bind_param("i", $cart_id);
        $removeCart->execute();
        $removeCart->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
    header('Location: cart.php');
    exit();
}

// Fetch cart items - Correct the table name and use correct column names
$cartItems = $conn->prepare("SELECT cart.id as cart_id, carpartsdatabase.Model, carpartsdatabase.Price, carpartsdatabase.Description, cart.quantity FROM cart JOIN carpartsdatabase ON cart.product_id = carpartsdatabase.id WHERE cart.user_id = ?");
if ($cartItems) {
    $cartItems->bind_param("i", $user_id);
    $cartItems->execute();
    $cartResult = $cartItems->get_result();
} else {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Include the header with cart count and search bar
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" href="../images/Logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="../images/Logo.png" type="image/x-icon">
</head>

<body>
    <main>
        <div class="cart-container">
            <div class="cart-items">
                <h2>Your Cart</h2>
                <?php if ($cartResult->num_rows > 0) : ?>
                    <?php
                    $total = 0;
                    while ($item = $cartResult->fetch_assoc()) :
                        $subtotal = $item['Price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <div class="cart-item">
                            <div class="cart-item-details">
                                <p><strong>Model:</strong> <?php echo htmlspecialchars($item['Model']); ?></p>
                                <p><?php echo htmlspecialchars($item['Description']); ?></p>
                                <p>Qty: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                <p>Price: $<?php echo htmlspecialchars($item['Price']); ?></p>
                                <p>Subtotal: $<?php echo number_format($subtotal, 2); ?></p>
                            </div>
                            <div class="cart-item-actions">
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                                    <button type="submit" name="remove" class="remove-btn">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p>Your cart is empty.</p>
                <?php endif; ?>
            </div>

            <div class="cart-summary">
                <h2>Summary</h2>
                <p>Total (excluding delivery): $<?php echo number_format($total, 2); ?></p>
                <a href="checkout.php" class="checkout-btn">Checkout securely</a>
            </div>
        </div>
    </main>
</body>

</html>