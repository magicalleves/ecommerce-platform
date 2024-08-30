<?php
session_start();
include 'db.php'; // Include the database connection

// Initialize variables for error messages
$loginError = '';
$registerError = '';

// Handle login form submission
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        header('Location: index.php'); // Redirect to the main page after login
    } else {
        $loginError = 'Invalid email or password.';
    }
}

// Handle registration form submission
if (isset($_POST['register'])) {
    $email = mysqli_real_escape_string($conn, $_POST['reg_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['reg_password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($password === $confirmPassword) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (email, phone, password) VALUES ('$email', '$phone', '$hashedPassword')";

        if (mysqli_query($conn, $query)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['user_email'] = $email;
            header('Location: index.php'); // Redirect to the main page after registration
        } else {
            $registerError = 'Error in registration. Try again.';
        }
    } else {
        $registerError = 'Passwords do not match.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Parts Store</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <div class="top-bar">
            <span class="address">Moscow, Krasnodonskaya, 48 (Entrance from the yard, Between the 1st and 2nd entrances)</span>
            <span class="phone">8-499-444-53-95</span>
        </div>

        <nav style="background-color: #f5f5f5; display: flex; align-items: center; justify-content: space-between; padding: 10px 20px;">
            <img src="images/Logo.png" alt="Logo" class="logo" style="height: 50px; width: 50px;">

            <div class="nav-links">
                <a href="#" class="nav-links-a">For New Clients</a>
                <a href="#" class="nav-links-a">Order Conditions</a>
                <a href="#" class="nav-links-a">Delivery</a>
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <a href="logout.php" class="nav-links-a">Logout</a>
                <?php else : ?>
                    <a href="#" id="loginBtn" class="nav-links-a" style="background-color: white;">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="logo-search">
        <input type="text" placeholder="Search for parts" class="search-bar">
        <div class="cart">
            <i class="fa-solid fa-cart-shopping" style="font-size: 20px;color: #bababa; margin-left: 10px;"></i>
            <p style="padding-left: 10px; font-size: 18px;color: #bababa">Cart</p>
        </div>
    </div>

    <hr style="border-color: #f1f1f1;border: 1px solid #f1f1f1">

    <main>
        <section class="news">
            <h1>Affordable Car Parts Store</h1>
            <h2>Latest News</h2>
            <p>01.04.2023</p>
            <br>
            <p>
                <strong>Office working hours:</strong>
                <br>
            <p style="margin-left: 10px; font-size: 15px;">
                Mon, Tue, Wed, Thu, Fri - 10:00 - 19:00
                <br>
                Sat, Sun - closed
            </p>
            </p>
            <br>
            <p>
                <strong>Pickup point working hours:</strong>
            <p style="margin-left: 10px; font-size: 15px;">
                Mon, Tue, Wed, Thu, Fri - 10:00 - 20:00
                <br>
                Sat - 12:00 - 18:00
                <br>
                Sun - closed
            </p>
            </p>
            <a href="#">All News</a>
        </section>
        <br><br>
        <section class="contacts">
            <h2>Contacts</h2>
            <p><strong>Krasnodonskaya</strong></p>
            <br>
            <p>Moscow, Krasnodonskaya, 48 (Entrance from the yard, Between the 1st and 2nd entrances)</p>
            <p>opt.mos.parts@yandex.ru</p>
            <p><strong>+8-499-444-53-95</strong></p>
            <div class="map">
                <iframe src="https://storage.googleapis.com/maps-solutions-pdmvemtnbt/locator-plus/wol5/locator-plus.html" width="100%" height="100%" style="border:0; height: 300px" loading="lazy">
                </iframe>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <img src="images/logo.png" alt="Company Logo" class="footer-logo">
                <p>Moscow, Krasnodonskaya, 39</p>
                <br>
                <p><strong>Email:</strong> opt.mos.parts@yandex.ru</p>
                <p><strong>Phone:</strong> 8-499-444-53-95</p>
            </div>
            <div class="footer-section">
                <h4>For Clients</h4>
                <ul>
                    <li><a href="#">How to Find a Part</a></li>
                    <li><a href="#">How to Place an Order</a></li>
                    <li><a href="#">Card Payment</a></li>
                    <li><a href="#">Delivery</a></li>
                    <li><a href="#">Offer Agreement</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Information</h4>
                <ul>
                    <li><a href="#">Contacts</a></li>
                    <li><a href="#">News</a></li>
                    <li><a href="#">Details</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Online Catalogs</h4>
                <ul>
                    <li><a href="#">Original Parts</a></li>
                    <li><a href="#">Catcar Aftermarket</a></li>
                    <li><a href="#">Original USA</a></li>
                    <li><a href="#">Non-Original USA</a></li>
                    <li><a href="#">Motorcycle Catalogs</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom" style="text-align: center;">
            <p>&copy; 2017, LLC "TradeSoft"</p>
            <p class="disclaimer">Information on the selection of similar parts is for reference only and does not constitute an unconditional reason for return.</p>
        </div>
    </footer>

    <!-- Background of Login modal -->
    <div id="loginModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <!-- Close button -->
            <span class="close">&times;</span>
            <h2>Login</h2>

            <div class="tab-container">
                <div class="tab active" id="tab-email" onclick="showContent('email')">Email</div>
                <div class="tab" id="tab-phone" onclick="showContent('phone')">Phone</div>
            </div>

            <!-- Login form content for Email -->
            <div class="content active" id="content-email">
                <form method="POST" action="index.php">
                    <input type="email" class="input-field" placeholder="Enter your email" name="email" required>
                    <input type="password" class="input-field" placeholder="Enter your password" name="password" required>
                    <button type="submit" class="login-btn input-field" name="login">Login</button>
                    <p style="color: #0054ad; text-align: center; margin-top: 5%;">No account? <u><span id="reg-open" onclick="showRegistration()">You can register here</span></u></p>
                </form>
            </div>

            <!-- Login form content for Phone -->
            <div class="content" id="content-phone">
                <form method="POST" action="index.php">
                    <input type="text" class="input-field" placeholder="Enter your phone number" name="phone" required>
                    <input type="password" class="input-field" placeholder="Enter your password" name="password" required>
                    <button type="submit" class="login-btn input-field" name="login">Login</button>
                    <p style="color: #0054ad; text-align: center; margin-top: 5%;">No account? <u><span id="reg-open" onclick="showRegistration()">You can register here</span></u></p>
                </form>
            </div>

            <!-- Registration form content -->
            <div class="content" id="content-register">
                <form method="POST" action="index.php">
                    <input type="email" class="input-field" placeholder="Enter your email" name="reg_email" required>
                    <input type="text" class="input-field" placeholder="Enter your phone number" name="phone" required>
                    <input type="password" class="input-field" placeholder="Enter your password" name="reg_password" required>
                    <input type="password" class="input-field" placeholder="Confirm your password" name="confirm_password" required>
                    <button type="submit" class="login-btn input-field" name="register">Register</button>
                    <p style="color: #0054ad; text-align: center; margin-top: 5%;">Already have an account? <u><span onclick="showLogin()">Login here</span></u></p>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script src="https://kit.fontawesome.com/6f6ccaa3be.js" crossorigin="anonymous"></script>
</body>

</html>