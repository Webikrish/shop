<?php
// get-cart-count.php - Return current cart count
require_once 'config.php';

$response = ['success' => true, 'cart_count' => 0];

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

$response['cart_count'] = $_SESSION['cart_count'] ?? 0;

echo json_encode($response);
?>