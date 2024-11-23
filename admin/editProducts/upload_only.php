<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

include '../../functions/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $file = $_FILES['excelFile']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheetData = $spreadsheet->getActiveSheet()->toArray();

    foreach ($sheetData as $index => $row) {
        if ($index === 0) continue;

        $model = $conn->real_escape_string($row[1]);
        $number = $conn->real_escape_string($row[2]);
        $description = $conn->real_escape_string($row[3]);
        $quantity = $conn->real_escape_string($row[4]);
        $price = $conn->real_escape_string($row[5]);
        $bought_price = $conn->real_escape_string($row[6]);
        $category = $conn->real_escape_string($row[7]);

        $check_query = "SELECT Quantity FROM carpartsdatabase WHERE Number = '$number'";
        $result = $conn->query($check_query);

        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            if ($existing['Quantity'] != $quantity) {
                $update_query = "
                    UPDATE carpartsdatabase
                    SET Quantity = '$quantity', Model = '$model', Description = '$description', Price = '$price', bought_price = '$bought_price', Category = '$category'
                    WHERE Number = '$number'
                ";
                $conn->query($update_query);
            }
        } else {
            $insert_query = "
                INSERT INTO carpartsdatabase (Model, Number, Description, Quantity, Price, bought_price, Category)
                VALUES ('$model', '$number', '$description', '$quantity', '$price', '$bought_price', '$category')
            ";
            $conn->query($insert_query);
        }
    }

    header("Location: ../products_page.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Only</title>
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

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-size: 16px;
            font-weight: bold;
        }

        input {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        button {
            padding: 10px 20px;
            background-color: #003F62;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #002b47;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="../admin_dashboard.php" data-icon="dashboard"><img src="../../images/icons/dashboardB.svg" alt="Dashboard Icon">Dashboard</a></li>
                <li><a href="../products_page.php" class="active" data-icon="product"><img src="../../images/icons/productW.svg" alt="Products Icon">All Products</a></li>
                <li><a href="../orders_list.php" data-icon="order"><img src="../../images/icons/orderB.svg" alt="Orders Icon">Order List</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Upload Excel File</h1>
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <label for="excelFile">Select Excel File:</label>
                    <input type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                    <button type="submit">Upload</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>