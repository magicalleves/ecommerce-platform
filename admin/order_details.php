<?php

include '../functions/db.php';
// Get the order ID from the URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id > 0) {
    // Fetch order details
    $order_query = "
        SELECT o.id, o.total, o.status, o.created_at, o.updated_at, u.email, u.phone, u.email, u.phone 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";

    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();

    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();
    } else {
        die("Order not found.");
    }
} else {
    die("Invalid order ID.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .order-header,
        .order-info,
        .products-table {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h3 {
            margin: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .order-info .info-box {
            display: inline-block;
            width: 30%;
            padding: 10px;
            background-color: #f4f4f4;
            margin-right: 10px;
            border-radius: 10px;
        }

        .info-box p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
        }

        .total-summary {
            text-align: right;
            padding: 10px 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="products.php">All Products</a></li>
                <li><a href="orders.php">Order List</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Order Header -->
            <div class="order-header">
                <h1>Order Details</h1>
                <p>Order ID: #<?php echo $order['id']; ?> | Status: <strong><?php echo ucfirst($order['status']); ?></strong></p>
                <p>Ordered on: <?php echo date("M j, Y", strtotime($order['created_at'])); ?> | Last Updated: <?php echo date("M j, Y", strtotime($order['updated_at'])); ?></p>
            </div>

            <!-- Order Info -->
            <div class="order-info">
                <div class="info-box">
                    <h3>Customer Info</h3>
                    <p>Name: <?php echo $order['email']; ?></p>
                    <p>Phone: <?php echo $order['phone']; ?></p>
                </div>
                <div class="info-box">
                    <h3>Payment Info</h3>
                    <p>Payment Method: MasterCard **** 5557</p>
                    <p>Phone: <?php echo $order['phone']; ?></p>
                </div>
                <div class="info-box">
                    <h3>Shipping Info</h3>
                    <p>Shipping Method: Next Express</p>
                    <p>Status: <?php echo ucfirst($order['status']); ?></p>
                </div>
            </div>

            <!-- Products Table -->
            <div class="products-table">
                <h3>Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Order ID</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all products associated with this order
                        $product_query = "
                        SELECT oi.order_id, oi.quantity, oi.price, c.Model 
                        FROM order_items oi
                        JOIN carpartsdatabase c ON oi.product_id = c.id
                        WHERE oi.order_id = ?";
                        $stmt = $conn->prepare($product_query);
                        $stmt->bind_param("i", $order_id);
                        $stmt->execute();
                        $product_result = $stmt->get_result();
                        $subtotal = 0;

                        while ($product = $product_result->fetch_assoc()) {
                            $product_total = $product['quantity'] * $product['price'];
                            $subtotal += $product_total;
                        ?>
                            <tr>
                                <td><?php echo $product['Model']; ?></td>
                                <td>#<?php echo $product['order_id']; ?></td>
                                <td><?php echo $product['quantity']; ?></td>
                                <td>₹<?php echo number_format($product_total, 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Total Summary -->
            <div class="total-summary">
                <p>Subtotal: ₹<?php echo number_format($subtotal, 2); ?></p>
                <p>Tax (20%): ₹<?php echo number_format($subtotal * 0.20, 2); ?></p>
                <p><strong>Total: ₹<?php echo number_format($subtotal * 1.20, 2); ?></strong></p>
            </div>
        </div>
    </div>

</body>

</html>