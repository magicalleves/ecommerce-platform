<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'functions/db.php'; // Include the database connection

// Initialize variables for error messages
$loginErrorEmail = '';
$loginErrorPhone = '';
$registerError = '';

// Handle email login form submission
if (isset($_POST['login_email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Using prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            // Update last login time
            $user_id = $user['id'];
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("i", $user_id);
                $updateStmt->execute();
                $updateStmt->close();
            }

            header('Location: index.php'); // Redirect to the main page after login
            exit(); // Ensure no further code execution
        } else {
            $loginErrorEmail = 'Invalid email or password.';
        }
        $stmt->close();
    } else {
        $loginErrorEmail = 'Error preparing statement: ' . $conn->error;
    }
}

// Handle phone login form submission
if (isset($_POST['login_phone'])) {
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Using prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    if ($stmt) {
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            // Update last login time
            $user_id = $user['id'];
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("i", $user_id);
                $updateStmt->execute();
                $updateStmt->close();
            }

            header('Location: index.php'); // Redirect to the main page after login
            exit(); // Ensure no further code execution
        } else {
            $loginErrorPhone = 'Invalid phone or password.';
        }
        $stmt->close();
    } else {
        $loginErrorPhone = 'Error preparing statement: ' . $conn->error;
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

        // Using prepared statement for secure insertion
        $stmt = $conn->prepare("INSERT INTO users (email, phone, password, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("sss", $email, $phone, $hashedPassword);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_email'] = $email;
                header('Location: index.php'); // Redirect to the main page after registration
                exit(); // Ensure no further code execution
            } else {
                $registerError = 'Error in registration: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $registerError = 'Error preparing statement: ' . $conn->error;
        }
    } else {
        $registerError = 'Passwords do not match.';
    }
}

// Fetch products from the database to display
$productsQuery = "SELECT * FROM products";
$productsResult = mysqli_query($conn, $productsQuery);

// Count items in the cart for the logged-in user
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = $conn->prepare("SELECT SUM(quantity) as cart_total FROM cart WHERE user_id = ?");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_data = $cart_result->fetch_assoc();
    $cart_count = $cart_data['cart_total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Parts Store</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Ensure the modal stays open on error and opens the correct tab
        window.onload = function() {
            var loginErrorEmail = <?php echo json_encode(!empty($loginErrorEmail)); ?>;
            var loginErrorPhone = <?php echo json_encode(!empty($loginErrorPhone)); ?>;

            if (loginErrorEmail) {
                document.getElementById('loginModal').style.display = 'block';
                showContent('email'); // Open email tab if there's an error in email login
            } else if (loginErrorPhone) {
                document.getElementById('loginModal').style.display = 'block';
                showContent('phone'); // Open phone tab if there's an error in phone login
            }
        };

        function showContent(type) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));

            // Add active class to the selected tab and its content
            document.getElementById(`tab-${type}`).classList.add('active');
            document.getElementById(`content-${type}`).classList.add('active');
        }
    </script>
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
                    <a href="functions/logout.php" class="nav-links-a">Logout</a>
                <?php else : ?>
                    <a href="#" id="loginBtn" class="nav-links-a" style="background-color: white;">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="logo-search">
        <input type="text" placeholder="Search for parts" class="search-bar">
        <a href="functions/cart.php" class="cart" style="display: flex; align-items: center; text-decoration: none;">
            <i class="fa-solid fa-cart-shopping" style="font-size: 20px;color: #bababa; margin-left: 10px;"></i>
            <?php if ($cart_count > 0) : ?>
                <p style="padding-left: 10px; font-size: 18px;color: #bababa">Cart (<?php echo $cart_count; ?>)</p>
            <?php endif; ?>
        </a>
    </div>

    <hr style="border-color: #f1f1f1;border: 1px solid #f1f1f1">

    <main>
        <!-- Products Section -->
        <section class="products-section">
            <h1>Available Car Parts</h1>
            <div class="products-grid">
                <?php while ($product = mysqli_fetch_assoc($productsResult)) : ?>
                    <div class="product-card">
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                        <?php if (isset($_SESSION['user_id'])) : ?>
                            <form method="POST" action="functions/cart.php">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                <button type="submit" name="add_to_cart">Add to Cart</button>
                            </form>
                        <?php else : ?>
                            <p><a href="#" id="loginBtn" style="color: red;">Login to add to cart</a></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Other Sections -->
        <section class="news">
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
            <!-- Other footer sections remain the same -->
        </div>
        <div class="footer-bottom" style="text-align: center;">
            <p>&copy; 2017, LLC "TradeSoft"</p>
            <p class="disclaimer">Information on the selection of similar parts is for reference only and does not constitute an unconditional reason for return.</p>
        </div>
    </footer>

    <!-- Background of Login modal -->
    <!-- Modal content remains the same as provided earlier -->
    <div id="loginModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <!-- Close button -->
            <span class="close" onclick="document.getElementById('loginModal').style.display='none'">&times;</span>
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
                    <?php if (!empty($loginErrorEmail)) : ?>
                        <p style="color: red;text-align: center"><?= $loginErrorEmail; ?></p>
                    <?php endif; ?>
                    <button type="submit" class="login-btn input-field" name="login_email">Login</button>

                    <p style="color: #0054ad; text-align: center; margin-top: 5%;">No account? <u><span id="reg-open" onclick="showRegistration()">You can register here</span></u></p>
                </form>
            </div>

            <!-- Login form content for Phone -->
            <div class="content" id="content-phone">
                <form method="POST" action="index.php">
                    <input type="text" class="input-field" placeholder="Enter your phone number" name="phone" required>
                    <input type="password" class="input-field" placeholder="Enter your password" name="password" required>
                    <?php if (!empty($loginErrorPhone)) : ?>
                        <p style="color: red; text-align: center"><?= $loginErrorPhone; ?></p>
                    <?php endif; ?>
                    <button type="submit" class="login-btn input-field" name="login_phone">Login</button>

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