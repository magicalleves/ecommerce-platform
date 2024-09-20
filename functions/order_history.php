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

// Fetch user orders
$orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
if ($orders) {
    $orders->bind_param("i", $user_id);
    $orders->execute();
    $orderResult = $orders->get_result();
} else {
    echo "Error preparing statement: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <main>
        <h1>Your Order History</h1>
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background-color: #f5f5f5; color: #333;">
                <tr>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: left">Order ID</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: left">Total</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: left">Status</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: left">Created At</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: left">View Details</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orderResult->fetch_assoc()) : ?>
                    <tr style="background-color: #f9f9f9;">
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($order['id']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;">$<?php echo htmlspecialchars($order['total']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($order['status']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($order['created_at']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;">
                            <a href="order_details.php?order_id=<?php echo $order['id']; ?>" style="color: #0054ad; text-decoration: none;">View Order Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

</body>

</html>