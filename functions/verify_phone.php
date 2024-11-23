<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = mysqli_real_escape_string($conn, $_POST['verification_code']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT phone_verification_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($storedCode);
    $stmt->fetch();
    $stmt->close();

    if ($code === $storedCode) {
        $stmt = $conn->prepare("UPDATE users SET phone_verified = 1, phone_verification_code = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Phone Verification</title>
</head>

<body>
    <h1>Phone Verification</h1>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Enter the code sent to your phone:</label>
        <input type="text" name="verification_code" required>
        <button type="submit">Verify</button>
    </form>
</body>

</html>