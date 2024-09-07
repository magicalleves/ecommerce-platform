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
$orders->bind_param("i", $user_id);
$orders->execute();
$orderResult = $orders->get_result();
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
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orderResult)) : ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td>$<?php echo $order['total']; ?></td>
                        <td><?php echo $order['status']; ?></td>
                        <td><?php echo $order['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </main>

</body>

</html>