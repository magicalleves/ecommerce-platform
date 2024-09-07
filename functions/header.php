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
        <a href="../index.php" class="nav-links-a" style="background-color: white;">
            <img src="../images/Logo.png" alt="Logo" class="logo" style="height: 50px; width: 50px;">
        </a>

        <div class="nav-links">
            <a href="../index.php" class="nav-links-a">For New Clients</a>
            <a href="../index.php" class="nav-links-a">Order Conditions</a>
            <a href="../index.php" class="nav-links-a">Delivery</a>
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
    <a href="cart.php" class="cart" style="display: flex; align-items: center; text-decoration: none;">
        <i class="fa-solid fa-cart-shopping" style="font-size: 20px;color: #bababa; margin-left: 10px;"></i>
        <?php if ($cart_count > 0) : ?>
            <p style="padding-left: 10px; font-size: 18px;color: #bababa">Cart (<?php echo $cart_count; ?>)</p>
        <?php endif; ?>
    </a>
</div>

<hr style="border-color: #f1f1f1;border: 1px solid #f1f1f1">