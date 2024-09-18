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
    <title>Product Page</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <main>
        <h1>Product Details</h1>
        <!-- Add your product details here -->

    </main>
</body>

</html>