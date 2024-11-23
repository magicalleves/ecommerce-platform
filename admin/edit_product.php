<?php
// edit_product.php
include '../functions/db.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product_query = "SELECT id, Model, Number, Description, Price, bought_price, Quantity FROM carpartsdatabase WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $model = $_POST['model'];
    $number = $_POST['number'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $bought_price = $_POST['bought_price'];
    $quantity = $_POST['quantity'];

    $update_query = "UPDATE carpartsdatabase SET Model = ?, Number = ?, Description = ?, Price = ?, bought_price = ?, Quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssdiii", $model, $number, $description, $price, $bought_price, $quantity, $product_id);

    if ($stmt->execute()) {
        header("Location: products_page.php?success=Product updated successfully");
        exit();
    } else {
        $error = "Failed to update product.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $delete_query = "DELETE FROM carpartsdatabase WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header("Location: products_page.php?success=Product deleted successfully");
        exit();
    } else {
        $error = "Failed to delete product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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

        .button-container {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
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

        button.delete {
            background-color: red;
        }

        button.cancel {
            background-color: gray;
        }

        button:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php" data-icon="dashboard"><img src="../images/icons/dashboardB.svg" alt="Dashboard Icon">Dashboard</a></li>
                <li><a href="products_page.php" class="active" data-icon="product"><img src="../images/icons/productB.svg" alt="Products Icon">All Products</a></li>
                <li><a href="order_list.php" data-icon="order"><img src="../images/icons/orderB.svg" alt="Orders Icon">Order List</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Edit Product</h1>
            <div class="form-container">
                <form method="POST">
                    <div>
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($product['Model']); ?>" required>
                    </div>
                    <div>
                        <label for="number">Part Number</label>
                        <input type="text" id="number" name="number" value="<?php echo htmlspecialchars($product['Number']); ?>" required>
                    </div>
                    <div>
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($product['Description']); ?>" required>
                    </div>
                    <div>
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['Price']); ?>" required>
                    </div>
                    <div>
                        <label for="bought_price">Bought Price</label>
                        <input type="number" id="bought_price" name="bought_price" step="0.01" value="<?php echo htmlspecialchars($product['bought_price']); ?>" required>
                    </div>
                    <div>
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['Quantity']); ?>" required>
                    </div>
                    <div class="button-container">
                        <button type="submit" name="update">Update</button>
                        <button type="submit" name="delete" class="delete">Delete</button>
                        <a href="products_page.php" class="cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>