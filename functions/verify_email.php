<?php
session_start();
include 'db.php'; // Database connection

if (isset($_GET['code'])) {
    $verification_code = mysqli_real_escape_string($conn, $_GET['code']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email_verification_code = ? AND email_verified = 0");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Mark email as verified
        $updateStmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
        $updateStmt->bind_param("i", $user['id']);
        $updateStmt->execute();
        $updateStmt->close();

        // Redirect to home page with a success message
        $_SESSION['success_message'] = 'Your email has been successfully verified. You can now log in.';
        header("Location: ../index.php");
        exit();
    } else {
        // Redirect to home page with an error message
        $_SESSION['error_message'] = 'Invalid or expired verification link.';
        header("Location: ../index.php");
        exit();
    }
    $stmt->close();
} else {
    // Redirect to home page if no code is provided
    $_SESSION['error_message'] = 'No verification code provided.';
    header("Location: ../index.php");
    exit();
}
