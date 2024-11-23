<?php
// Include the database connection file
include 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

<header>
    <div class="top-bar">
        <span class="address">Moscow, Krasnodonskaya, 48 (Entrance from the yard, Between the 1st and 2nd entrances)</span>
        <span class="phone">8-499-444-53-95</span>
    </div>

    <nav style="background-color: #f5f5f5; display: flex; align-items: center; justify-content: space-between; padding: 10px 20px;">
        <a href="../index.php" class="nav-links-a" style="background-color: #f5f5f5;margin:0; padding: 0; height: 50px; width: 50px; margin-bottom: 20px">
            <img src="../images/download.webp" alt="Logo" class="logo" style="height: 70px; width: 70px;">
        </a>

        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])) : ?>
                <a href="profile.php" class="nav-links-a">
                    <i class="fa-solid fa-user" style="margin-right: 10px"></i> Profile
                </a>
            <?php endif; ?>

            <!-- <a href="#" class="nav-links-a">For New Clients</a> -->
            <!-- <a href="#" class="nav-links-a">Order Conditions</a> -->
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
    <div class="search-container">
        <form method="POST" action="../index.php">
            <input type="text" name="search_part_number" placeholder="Search for part number" class="search-bar" required>
            <button type="submit" name="search_button" class="search-button">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
    </div>

    <a href="cart.php" class="cart" style="display: flex; align-items: center; text-decoration: none; margin-left: 1vw">
        <i class="fa-solid fa-cart-shopping" style="font-size: 20px;color: #bababa; margin-left: 10px;"></i>
        <p id="cart-count" style="margin-left: 10px; color: #bababa; font-size: 19px">
            Cart
            <?php if ($cart_count > 0) : ?>
                (<?php echo $cart_count; ?>)
            <?php endif; ?>
        </p>
    </a>
</div>

<hr style="border-color: #f1f1f1;border: 1px solid #f1f1f1">

<script src="https://kit.fontawesome.com/6f6ccaa3be.js" crossorigin="anonymous"></script>