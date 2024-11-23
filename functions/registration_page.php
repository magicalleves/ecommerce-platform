<?php
session_start();
include '../functions/db.php'; // Database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
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

        header {
            background-color: #fff;
            padding: 10px 20px;
            border-bottom: 1px solid #ddd;
        }

        header .top-bar {
            font-size: 14px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .top-bar .address,
        header .top-bar .phone {
            margin: 0;
        }

        header nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
        }

        header nav .logo {
            height: 70px;
        }

        header .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        header .nav-links a {
            text-decoration: none;
            font-size: 16px;
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
        }

        header .nav-links a:hover {
            background-color: #f5f5f5;
        }

        main {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            max-width: 1200px;
            margin: auto;
        }

        .form-container {
            flex: 3;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .form-container p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-container form label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }

        .form-container form input[type="text"],
        .form-container form input[type="email"],
        .form-container form input[type="password"] {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        .form-container form input[type="radio"] {
            margin-right: 5px;
        }

        .form-container form .radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .form-container form button {
            background-color: #003F62;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .form-container form button:hover {
            background-color: #002b47;
        }

        .terms-container {
            font-size: 12px;
            color: #555;
            line-height: 1.5;
            margin-top: 10px;
        }

        .sidebar {
            flex: 1;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .sidebar p {
            font-size: 14px;
            margin-bottom: 10px;
            color: #555;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }
    </style>
    <link rel="stylesheet" href="../styles.css">

</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <div class="form-container">
            <h1>Registration</h1>
            <p>To register in the online store, please fill out this form. If you encounter any problems with registration, please email us at <strong>opt.mos.parts@yandex.ru</strong>.</p>
            <form method="POST" action="process_registration.php">

                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>

                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middle_name">

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit">Complete Registration</button>

                <div class="terms-container">
                    <p>By registering, you agree to the terms and conditions.</p>
                </div>
            </form>
        </div>

        <div class="sidebar">
            <h2>Conditions of Cooperation</h2>
            <ul>
                <li>We are not responsible for the applicability of ordered parts to your vehicle.</li>
                <li>Cooperation is carried out based on a contract.</li>
                <li>Delivery is carried out by our company with agreed parameters.</li>
            </ul>
            <h2>Working with Us</h2>
            <ul>
                <li>Various payment methods available.</li>
                <li>Fast delivery across Russia and CIS countries.</li>
                <li>Access to special prices for products.</li>
            </ul>
        </div>
    </main>
</body>

</html>