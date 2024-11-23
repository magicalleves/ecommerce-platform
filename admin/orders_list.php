<?php
// orders_list.php
include '../functions/db.php';

// Fetch order data from database
$order_query = "
    SELECT o.id, o.total, o.status, o.created_at, u.email AS customer_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC";
$order_result = $conn->query($order_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List</title>
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
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a {
            text-decoration: none;
            font-size: 21px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            flex: 1;
            color: #333;
        }

        .sidebar ul li a.active {
            background-color: #003F62;
            color: white;
        }

        .sidebar ul li a img {
            margin-right: 10px;
            height: 24px;
            width: 24px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
        }

        .pagination button {
            padding: 10px 20px;
            margin: 0 5px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        .pagination button.active {
            background-color: #003F62;
            color: white;
        }

        .pagination button:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php" data-icon="dashboard"><img src="../images/icons/dashboardB.svg" alt="Dashboard Icon">Dashboard</a></li>
                <li><a href="products_page.php" data-icon="product"><img src="../images/icons/productB.svg" alt="Products Icon">All Products</a></li>
                <li><a href="orders_list.php" class="active" data-icon="order"><img src="../images/icons/orderW.svg" alt="Orders Icon">Order List</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Order List</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($order_result->num_rows > 0) {
                            while ($row = $order_result->fetch_assoc()) {
                                $status_color = $row['status'] == 'Delivered' ? 'green' : ($row['status'] == 'Canceled' ? 'red' : 'orange');
                                echo "<tr>
                                        <td>#{$row['id']}</td>
                                        <td>" . date("M j, Y", strtotime($row['created_at'])) . "</td>
                                        <td>{$row['customer_name']}</td>
                                        <td style='color: $status_color; font-weight: bold;'>{$row['status']}</td>
                                        <td>₼" . number_format($row['total'], 2) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No orders found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button>4</button>
                <button>Next</button>
            </div>
        </div>
    </div>
</body>

</html>