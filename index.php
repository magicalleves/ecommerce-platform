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
// $productsQuery = "SELECT * FROM products";
// $productsResult = mysqli_query($conn, $productsQuery);

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




if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['increment'])) {
        $product_id = intval($_POST['product_id']);

        // Check if the product exists in the carpartsdatabase table
        $checkProductQuery = $conn->prepare("SELECT id FROM carpartsdatabase WHERE id = ?");
        $checkProductQuery->bind_param("i", $product_id);
        $checkProductQuery->execute();
        $productResult = $checkProductQuery->get_result();

        if ($productResult->num_rows > 0) {
            // If the product exists, increment the quantity
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();

            echo "<script>showPopup('Added 1 product to cart');</script>";
        } else {
            echo "<p style='color: red; text-align: center;'>Product does not exist.</p>";
        }
        $checkProductQuery->close();
    }

    if (isset($_POST['decrement'])) {
        $product_id = intval($_POST['product_id']);

        // Check if the product exists in the carpartsdatabase table
        $checkProductQuery = $conn->prepare("SELECT id FROM carpartsdatabase WHERE id = ?");
        $checkProductQuery->bind_param("i", $product_id);
        $checkProductQuery->execute();
        $productResult = $checkProductQuery->get_result();

        if ($productResult->num_rows > 0) {
            // If the product exists, decrement the quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ? AND quantity > 0");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();

            echo "<script>showPopup('Removed 1 product from cart');</script>";
        } else {
            echo "<p style='color: red; text-align: center;'>Product does not exist.</p>";
        }
        $checkProductQuery->close();
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
    <link rel="icon" href="images/Logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="images/Logo.png" type="image/x-icon">
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
        <div class="search-container">
            <form method="POST" action="index.php">
                <input type="text" name="search_part_number" placeholder="Search for part number" class="search-bar" required>
                <button type="submit" name="search_button" class="search-button">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
        </div>
        <a href="functions/cart.php" class="cart" style="display: flex; align-items: center; text-decoration: none; margin-left: 1vw">
            <i class="fa-solid fa-cart-shopping" style="font-size: 20px;color: #bababa; margin-left: 10px;"></i>
            <p style="margin-left: 10px; color: #bababa">Cart
                <?php if ($cart_count > 0) : ?>
                    <?php echo $cart_count; ?>
                <?php endif; ?>
            </p>

        </a>
    </div>



    <hr style="border-color: #f1f1f1;border: 1px solid #f1f1f1">

    <main>

        <?php
        if (isset($_POST['search_button'])) {
            $partNumber = mysqli_real_escape_string($conn, $_POST['search_part_number']);

            $searchQuery = $conn->prepare("SELECT * FROM carpartsdatabase WHERE Number = ?");
            if ($searchQuery) {
                $searchQuery->bind_param("s", $partNumber);
                $searchQuery->execute();
                $searchResult = $searchQuery->get_result();

                if ($searchResult->num_rows > 0) {
                    $part = $searchResult->fetch_assoc();
                    echo "<div class='part-details'>";

                    // Placeholder image; replace src with the path to the actual image if available
                    echo "<img src='images/placeholder.png' alt='Part Image'>";

                    echo "<div class='part-info'>";
                    echo "<h2>" . htmlspecialchars($part['Description']) . " " . htmlspecialchars($part['Number']) . "</h2>";
                    echo "<p><strong>Model:</strong> " . htmlspecialchars($part['Model']) . "</p>";
                    echo "<p><strong>Number:</strong> " . htmlspecialchars($part['Number']) . "</p>";
                    echo "<p><strong>Description:</strong> " . htmlspecialchars($part['Description']) . "</p>";
                    echo "<p><strong>Price:</strong> $" . htmlspecialchars($part['Price']) . "</p>";

                    // Add form for increment/decrement buttons
                    if (isset($_SESSION['user_id'])) {
                        echo "<form method='POST' action='index.php'>";
                        echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($part['id']) . "'>";
                        echo "<button type='submit' name='decrement' class='quantity-btn'>-</button>";
                        echo "<input type='text' name='quantity' value='0' readonly>";
                        echo "<button type='submit' name='increment' class='quantity-btn'>+</button>";
                        echo "</form>";
                    }

                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<p style='color: red; text-align: center;'>Sorry, the part is unavailable.</p>";
                }
                $searchQuery->close();
            } else {
                echo "<p style='color: red; text-align: center;'>Failed to prepare the search query.</p>";
            }
        }
        ?>

        <!-- Other Sections -->
        <section class="news">
            <br>
            <br>

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

    <script>
        function updateCart(productId, action) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_cart.php", true); // PHP endpoint to handle cart updates
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            // Send the product ID and action (increment or decrement)
            xhr.send("product_id=" + productId + "&action=" + action);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Parse the JSON response
                    const response = JSON.parse(xhr.responseText);

                    // Update the cart count on the page
                    document.querySelector('.cart p').innerText = 'Cart ' + (response.cart_count > 0 ? response.cart_count : '');

                    // Show popup message
                    showPopup(response.message);
                } else {
                    console.error("Error updating cart:", xhr.statusText);
                }
            };
        }

        function showPopup(message) {
            const popup = document.createElement('div');
            popup.className = 'popup-message';
            popup.innerText = message;
            document.body.appendChild(popup);

            setTimeout(() => {
                popup.style.opacity = '0';
            }, 2000);

            setTimeout(() => {
                document.body.removeChild(popup);
            }, 3000);
        }
    </script>";
    </script>

    <script src="script.js"></script>
    <script src="https://kit.fontawesome.com/6f6ccaa3be.js" crossorigin="anonymous"></script>
</body>

</html>