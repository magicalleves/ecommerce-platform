<?php
include '../functions/db.php';

// Fetch data from database for the dashboard metrics
$total_orders_query = "SELECT COUNT(*) as total_orders FROM orders";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = $total_orders_result->fetch_assoc()['total_orders'];

$active_orders_query = "SELECT COUNT(*) as active_orders FROM orders WHERE status = 'Pending'";
$active_orders_result = $conn->query($active_orders_query);
$active_orders = $active_orders_result->fetch_assoc()['active_orders'];

$completed_orders_query = "SELECT COUNT(*) as completed_orders FROM orders WHERE status = 'Delivered'";
$completed_orders_result = $conn->query($completed_orders_query);
$completed_orders = $completed_orders_result->fetch_assoc()['completed_orders'];

$return_orders_query = "SELECT COUNT(*) as return_orders FROM orders WHERE status = 'Returned'";
$return_orders_result = $conn->query($return_orders_query);
$return_orders = $return_orders_result->fetch_assoc()['return_orders'];

// Fetch best sellers
$best_sellers_query = "
    SELECT product_id, SUM(quantity) as total_sales 
    FROM order_items 
    GROUP BY product_id 
    ORDER BY total_sales DESC 
    LIMIT 3";
$best_sellers_result = $conn->query($best_sellers_query);

// Fetch recent orders
$recent_orders_query = "
    SELECT o.id, o.total, o.status, o.created_at, u.email, oi.product_id, c.Model 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN carpartsdatabase c ON oi.product_id = c.id
    ORDER BY o.created_at DESC
    LIMIT 6";
$recent_orders_result = $conn->query($recent_orders_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
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

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .dashboard-header h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stats-box {
            width: 24%;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stats-box h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .stats-box p {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
        }

        .graph-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .orders-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .orders-container h3 {
            margin-bottom: 20px;
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

        table tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">All Products</a></li>
                <li><a href="#">Order List</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
            </div>

            <!-- Stats -->
            <div class="stats-container">
                <div class="stats-box">
                    <h3>Total Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
                <div class="stats-box">
                    <h3>Active Orders</h3>
                    <p><?php echo $active_orders; ?></p>
                </div>
                <div class="stats-box">
                    <h3>Completed Orders</h3>
                    <p><?php echo $completed_orders; ?></p>
                </div>
                <div class="stats-box">
                    <h3>Return Orders</h3>
                    <p><?php echo $return_orders; ?></p>
                </div>
            </div>

            <!-- Sales Graph -->
            <div class="graph-container">
                <h3>Sale Graph</h3>
                <!-- Implement chart here using Chart.js or any graph library -->
            </div>

            <?php
            // Updated query to fetch recent orders with total quantity of products
            $recent_orders_query = "
                SELECT o.id, o.total, o.status, o.created_at, u.email, 
                    SUM(oi.quantity) as total_quantity
                FROM orders o
                JOIN users u ON o.user_id = u.id
                JOIN order_items oi ON o.id = oi.order_id
                GROUP BY o.id
                ORDER BY o.created_at DESC
                ";  // Fetch recent 6 unique orders

            $recent_orders_result = $conn->query($recent_orders_query);
            ?>

            <div class="orders-container">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total Quantity</th> <!-- Show total quantity of products -->
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Action</th> <!-- New column for view details -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders_result->fetch_assoc()) { ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td> <!-- Show Order ID -->
                                <td><?php echo $row['total_quantity']; ?></td> <!-- Show total quantity -->
                                <td><?php echo date("M j, Y", strtotime($row['created_at'])); ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo ucfirst($row['status']); ?></td>
                                <td>₼<?php echo number_format($row['total'], 2); ?></td> <!-- Changed currency to Azeri Manat (₼) -->
                                <td><a href="order_details.php?order_id=<?php echo $row['id']; ?>">View Order Details</a></td> <!-- View Order Details link -->
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>



        </div>
    </div>

</body>

</html>