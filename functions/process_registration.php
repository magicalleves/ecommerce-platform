<?php
session_start();
include 'db.php'; // Include the database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Include PHPMailer autoload if not already included

// Initialize error and success messages
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Validation
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'All fields are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors[] = 'Invalid phone number format.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for duplicate entries in the database
    $duplicateCheck = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $duplicateCheck->bind_param("ss", $email, $phone);
    $duplicateCheck->execute();
    $result = $duplicateCheck->get_result();

    if ($result->num_rows > 0) {
        $errors[] = 'Email or phone number already exists.';
    }
    $duplicateCheck->close();

    // If no errors, proceed with registration
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verification_code = bin2hex(random_bytes(16)); // Generate a unique verification code

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, middle_name, phone, email, password, email_verification_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss", $first_name, $last_name, $middle_name, $phone, $email, $hashedPassword, $verification_code);

        if ($stmt->execute()) {
            // Send verification email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'eva.gadzhieva@gmail.com'; // Your Gmail address
                $mail->Password = 'wsvh goci pmtg sxuq'; // Your App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('eva.gadzhieva@gmail.com', 'Your Website');
                $mail->addAddress($email, $first_name . ' ' . $last_name);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email Address';
                $verification_link = "http://avtodetallari.az/functions/verify_email.php?code=$verification_code";
                $mail->Body = "Hello $first_name,<br><br>Please verify your email by clicking the link below:<br><br>
                <a href='$verification_link'>Verify Email</a><br><br>
                If you did not register, please ignore this email.<br><br>Thanks,<br>Your Website Team";

                $mail->send();
                $successMessage = 'Registration successful! A verification email has been sent to your email address.';
            } catch (Exception $e) {
                $errors[] = "Could not send verification email. Error: " . $mail->ErrorInfo;
            }
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Process</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
        }

        .error-messages {
            color: red;
            margin-bottom: 15px;
        }

        .error-messages ul {
            padding: 0;
            list-style: none;
        }

        .error-messages ul li {
            margin-bottom: 5px;
        }

        a {
            color: #003F62;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Registration Status</h1>
        <?php if (!empty($errors)) : ?>
            <div class="error-messages">
                <p>There were errors during registration:</p>
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (!empty($successMessage)) : ?>
            <div class="success-message">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        <p><a href="../index.php">Go back to home page</a></p>
    </div>
</body>

</html>