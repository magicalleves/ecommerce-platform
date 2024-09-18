<?php
// profile.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php'; // Include the database connection

// Include the header
include 'header.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = [];

// Fetch user details
$stmt = $conn->prepare("SELECT email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}
$stmt->close();

// Handle profile update success message
$successMessage = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = 'Profile updated successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" href="../images/Logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="../images/Logo.png" type="image/x-icon">
</head>

<body>

    <main>

        <div class="profile-container">
            <h1>Edit Profile</h1>

            <?php if ($successMessage) : ?>
                <p class="success-message"><?= $successMessage; ?></p>
            <?php endif; ?>

            <div class="form-section">
                <h3>Update Email and Phone</h3>
                <form action="edit_profile.php" method="POST">
                    <label for="email">Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

                    <label for="phone">Phone:</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>

                    <button type="submit" name="update_profile">Update Profile</button>
                </form>
            </div>

            <div class="form-section">
                <h3>Change Password</h3>
                <form action="edit_password.php" method="POST">
                    <label for="current_password">Current Password:</label>
                    <input type="password" name="current_password" required>

                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" required>

                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" name="confirm_password" required>

                    <button type="submit" name="change_password">Change Password</button>
                </form>
            </div>

            <a href="order_history.php" class="order-history-link">View Order History</a>
        </div>

    </main>
    <script src="script.js"></script>
</body>

</html>