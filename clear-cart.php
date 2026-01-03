<?php
// clear-cart.php - Clear entire cart
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['user_id'])) {
        // Logged in user - clear from database
        $user_id = $_SESSION['user_id'];
        $conn = getDBConnection();
        
        $deleteSql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['cart_count'] = 0;
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully';
        } else {
            $response['message'] = 'Error clearing cart: ' . $conn->error;
        }
        
        $conn->close();
    } else {
        // Guest user - clear from session
        $_SESSION['cart_items'] = [];
        $_SESSION['cart_count'] = 0;
        $response['success'] = true;
        $response['message'] = 'Cart cleared successfully';
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>