<?php
include '../functions/db.php';

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

$best_sellers_query = "
    SELECT product_id, SUM(quantity) as total_sales 
    FROM order_items 
    GROUP BY product_id 
    ORDER BY total_sales DESC 
    LIMIT 3";
$best_sellers_result = $conn->query($best_sellers_query);

$sales_data_query = "
    SELECT MONTHNAME(STR_TO_DATE(month_num, '%m')) AS month, IFNULL(SUM(o.total), 0) AS total_sales
    FROM (
        SELECT 1 AS month_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) AS months
    LEFT JOIN orders o ON MONTH(o.created_at) = months.month_num AND YEAR(o.created_at) = YEAR(CURRENT_DATE)
    GROUP BY months.month_num
    ORDER BY months.month_num";
$sales_data_result = $conn->query($sales_data_query);

$months = [];
$sales = [];
while ($row = $sales_data_result->fetch_assoc()) {
    $months[] = $row['month'];
    $sales[] = $row['total_sales'];
}

$recent_orders_query = "
    SELECT o.id, o.total, o.status, o.created_at, u.email, 
        COALESCE(SUM(oi.quantity), 0) as total_quantity
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id, u.email, o.total, o.status, o.created_at
    ORDER BY o.created_at DESC
    ";
$recent_orders_result = $conn->query($recent_orders_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.0/dist/chart.umd.js"></script>
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Rubik", sans-serif;
            font-optical-sizing: auto;
            font-weight: 300;
            font-style: normal;
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
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php" class="active" data-icon="dashboard"><img src="../images/icons/dashboardW.svg" alt="Dashboard Icon">Dashboard</a></li>
                <li><a href="products_page.php" data-icon="product"><img src="../images/icons/productB.svg" alt="Products Icon">All Products</a></li>
                <li><a href="orders_list.php" data-icon="order"><img src="../images/icons/orderB.svg" alt="Orders Icon">Order List</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
            </div>

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

            <div class="graph-container">
                <h3>Sales Graph</h3>
                <canvas id="salesGraph"></canvas>
            </div>

            <div class="orders-container">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total Quantity</th>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders_result->fetch_assoc()) { ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['total_quantity']; ?></td>
                                <td><?php echo date("M j, Y", strtotime($row['created_at'])); ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo ucfirst($row['status']); ?></td>
                                <td>₼<?php echo number_format($row['total'], 2); ?></td>
                                <td><a href="order_details.php?order_id=<?php echo $row['id']; ?>">View Order Details</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const months = <?php echo json_encode($months); ?>;
            const sales = <?php echo json_encode($sales); ?>;

            const ctx = document.getElementById('salesGraph').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Sales',
                        data: sales,
                        borderColor: '#003F62',
                        backgroundColor: 'rgba(0, 63, 98, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Months'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Sales (₼)'
                            }
                        }
                    }
                }
            });
        });
    </script>

</body>

</html>