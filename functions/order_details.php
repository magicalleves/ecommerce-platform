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

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    echo "Invalid order ID.";
    exit();
}

// Fetch order information to validate it's the user's order
$order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
if ($order) {
    $order->bind_param("ii", $order_id, $user_id);
    $order->execute();
    $orderResult = $order->get_result();
    $orderData = $orderResult->fetch_assoc();

    if (!$orderData) {
        echo "No order found for this user.";
        exit();
    }

    $order->close();
} else {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Fetch order items
$orderItemsQuery = $conn->prepare("SELECT oi.*, p.Model, p.Number, p.Description 
                                   FROM order_items oi 
                                   JOIN carpartsdatabase p ON oi.product_id = p.id 
                                   WHERE oi.order_id = ?");
if ($orderItemsQuery) {
    $orderItemsQuery->bind_param("i", $order_id);
    $orderItemsQuery->execute();
    $orderItemsResult = $orderItemsQuery->get_result();
} else {
    echo "Error preparing statement: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <main>
        <h1>Order Details for Order #<?php echo htmlspecialchars($order_id); ?></h1>
        <br>

        <h2>Order Summary</h2>
        <br>
        <p><strong>Total:</strong> $<?php echo htmlspecialchars($orderData['total']); ?></p>
        <br>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($orderData['status']); ?></p>
        <br>
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($orderData['created_at']); ?></p>
        <br>

        <h2>Items Ordered</h2>
        <br>
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background-color: #f5f5f5; color: #333;">
                <tr>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;text-align: left">Model</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;text-align: left">Number</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;text-align: left">Description</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;text-align: left">Quantity</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;text-align: left">Price</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd;text-align: left">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $orderTotal = 0;

                while ($item = $orderItemsResult->fetch_assoc()) :
                    $subtotal = $item['quantity'] * $item['price'];
                    $orderTotal += $subtotal;
                ?>
                    <tr style="background-color: #f9f9f9;">
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($item['Model']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($item['Number']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($item['Description']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;">$<?php echo htmlspecialchars($item['price']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;">$<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f5f5f5;">
                    <td colspan="5" style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;"><strong>Total:</strong></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>$<?php echo number_format($orderTotal, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </main>

</body>

</html>