<?php
// get-cart-summary.php - Get cart summary for AJAX updates
require_once 'config.php';

$response = ['success' => false, 'subtotal' => 0, 'shipping' => 0, 'total' => 0];

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$conn = getDBConnection();

$subtotal = 0;
$total_items = 0;

if ($user_id) {
    // Logged in user
    $cartSql = "SELECT c.quantity, p.price 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
    $stmt = $conn->prepare($cartSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartResult = $stmt->get_result();
    
    while ($item = $cartResult->fetch_assoc()) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }
} else {
    // Guest user
    if (isset($_SESSION['cart_items']) && !empty($_SESSION['cart_items'])) {
        $product_ids = array_keys($_SESSION['cart_items']);
        
        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            
            $cartSql = "SELECT p.id, p.price FROM products p WHERE p.id IN ($placeholders)";
            $types = str_repeat('i', count($product_ids));
            $stmt = $conn->prepare($cartSql);
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $cartResult = $stmt->get_result();
            
            while ($item = $cartResult->fetch_assoc()) {
                $quantity = $_SESSION['cart_items'][$item['id']];
                $subtotal += $item['price'] * $quantity;
                $total_items += $quantity;
            }
        }
    }
}

// Calculate shipping
$shipping = 0;
if ($subtotal > 0 && $subtotal < 499) {
    $shipping = 40;
}

// Calculate total
$total = $subtotal + $shipping;

$response['success'] = true;
$response['subtotal'] = number_format($subtotal, 2);
$response['shipping'] = number_format($shipping, 2);
$response['total'] = number_format($total, 2);
$response['item_count'] = $total_items;

echo json_encode($response);
$conn->close();
?>