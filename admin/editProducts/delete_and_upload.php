<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
include '../../functions/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $file = $_FILES['excelFile']['tmp_name'];

    // Disable foreign key checks
    $conn->query('SET FOREIGN_KEY_CHECKS = 0');

    // Truncate the existing carpartsdatabase table
    $truncate_query = "TRUNCATE TABLE carpartsdatabase";
    $conn->query($truncate_query);

    // Re-enable foreign key checks
    $conn->query('SET FOREIGN_KEY_CHECKS = 1');

    // Load the uploaded file
    $spreadsheet = IOFactory::load($file);
    $sheetData = $spreadsheet->getActiveSheet()->toArray();

    // Skip the header row and insert data into the database
    foreach ($sheetData as $index => $row) {
        if ($index === 0) continue;

        $model = $conn->real_escape_string($row[1]);
        $number = $conn->real_escape_string($row[2]);
        $description = $conn->real_escape_string($row[3]);
        $quantity = $conn->real_escape_string($row[4]);
        $price = $conn->real_escape_string($row[5]);
        $bought_price = $conn->real_escape_string($row[6]);
        $category = $conn->real_escape_string($row[7]);

        $insert_query = "
            INSERT INTO carpartsdatabase (Model, Number, Description, Quantity, Price, bought_price, Category)
            VALUES ('$model', '$number', '$description', '$quantity', '$price', '$bought_price', '$category')
        ";
        $conn->query($insert_query);
    }

    header('Location: ../products_page.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete and Upload</title>
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
        }

        .sidebar ul li a {
            text-decoration: none;
            font-size: 21px;
            padding: 8px 16px;
            display: block;
            border-radius: 8px;
            color: #333;
        }

        .sidebar ul li a.active {
            background-color: #003F62;
            color: white;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .upload-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="file"] {
            margin: 10px 0;
            display: block;
            width: 100%;
        }

        button {
            padding: 10px 20px;
            background-color: #003F62;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
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
                <li><a href="../admin_dashboard.php">Dashboard</a></li>
                <li><a href="../products_page.php" class="active">All Products</a></li>
                <li><a href="../orders_list.php">Order List</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="upload-container">
                <h1>Delete and Upload</h1>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="excelFile" accept=".xlsx, .xls" required>
                    <button type="submit">Upload</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>