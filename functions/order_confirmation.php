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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <main>
        <h1>Thank you for your order!</h1>
        <p>Your order has been placed successfully.</p>
        <a href="order_history.php">View Order History</a>
    </main>

</body>

</html>