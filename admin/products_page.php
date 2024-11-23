<?php
// products_page.php
include '../functions/db.php';

// Fetch data from carpartsdatabase table
$product_query = "
    SELECT id, Model, Number, Description, Price, Quantity, bought_price 
    FROM carpartsdatabase";
$product_result = $conn->query($product_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products</title>
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

        .action-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .action-buttons button {
            padding: 10px 20px;
            background-color: #003F62;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .action-buttons button:hover {
            background-color: #002b47;
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

        .edit-button {
            padding: 10px 20px;
            background-color: #003F62;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
            font-size: 14px;
        }

        .edit-button:hover {
            background-color: #002b47;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php" data-icon="dashboard"><img src="../images/icons/dashboardB.svg" alt="Dashboard Icon">Dashboard</a></li>
                <li><a href="products_page.php" class="active" data-icon="product"><img src="../images/icons/productW.svg" alt="Products Icon">All Products</a></li>
                <li><a href="orders_list.php" data-icon="order"><img src="../images/icons/orderB.svg" alt="Orders Icon">Order List</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>All Products</h1>

            <div class="action-buttons">
                <button onclick="location.href='editProducts/delete_and_upload.php'">Delete and Upload New List</button>
                <button onclick="location.href='editProducts/upload_only.php'">Add New Items</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Manufacturer</th>
                            <th>Part Number</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Bought Price</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($product_result->num_rows > 0) {
                            while ($row = $product_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['Model']}</td>
                                        <td>{$row['Number']}</td>
                                        <td>{$row['Description']}</td>
                                        <td>₼" . number_format($row['Price'], 2) . "</td>
                                        <td>₼" . number_format($row['bought_price'], 2) . "</td>
                                        <td>{$row['Quantity']}</td>
                                        <td><a href='edit_product.php?id={$row['id']}' class='edit-button'>Edit</a></td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No products found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>