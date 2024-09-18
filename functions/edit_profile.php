<?php
// edit_profile.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php'; // Include the database connection

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $email, $phone, $user_id);

    if ($stmt->execute()) {
        header('Location: profile.php?success=1'); // Redirect with success message
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
