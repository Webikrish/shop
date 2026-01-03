<?php
// nav.php - Add this code after session_start()
require_once 'config.php';

// Update cart count from database if user is logged in
if (isset($_SESSION['user_id'])) {
    $conn = getDBConnection();
    
    // Get cart count
    $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartData = $result->fetch_assoc();
    $_SESSION['cart_count'] = $cartData['cart_count'] ?? 0;
    
    $stmt->close();
    $conn->close();
} else {
    // For guests, use session cart
    if (!isset($_SESSION['cart_count'])) {
        $_SESSION['cart_count'] = 0;
    }
    if (!isset($_SESSION['cart_items'])) {
        $_SESSION['cart_items'] = [];
    }
}
?>

<!-- In the HTML header, update cart icon -->
<a href="cart.php" class="cart-icon">
    <i class="fas fa-shopping-cart"></i>
    <span class="cart-count"><?php echo $_SESSION['cart_count'] ?? 0; ?></span>
</a>