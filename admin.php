<?php
// Start a session
session_start();

include 'functions/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $db_password);
            $stmt->fetch();

            // Compare the entered password with the database password
            if ($password === $db_password) {
                $_SESSION['admin_id'] = $id;
                header("Location: admin/admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No user found with that email!";
        }

        $stmt->close();
    } else {
        $error = "Please fill in all fields!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .image-section {
            width: 50%;
            background: url('images/car.jpg') no-repeat center center;
            background-size: cover;
        }

        .form-section {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
        }

        .login-form {
            width: 80%;
            max-width: 400px;
        }

        .login-form h2 {
            text-align: left;
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .login-form input[type="email"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .login-form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #0056b3;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-form input[type="submit"]:hover {
            background-color: #003d80;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
        }

        .checkbox-container input {
            margin-right: 10px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .login-form a {
            color: #0056b3;
            text-decoration: none;
        }

        .login-form a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="image-section">
            <!-- Background image is handled in CSS -->
        </div>
        <div class="form-section">
            <div class="login-form">
                <h2>Login</h2>
                <?php if (isset($error)) {
                    echo '<div class="error">' . $error . '</div>';
                } ?>
                <form action="" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <div class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember" checked>
                        <label for="remember">Keep me logged in</label>
                    </div>
                    <input type="submit" value="EMAIL LOGIN">
                </form>
                <a href="#">Forgot your password?</a>
            </div>
        </div>
    </div>

</body>

</html>