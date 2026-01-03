<?php
// add-to-cart.php - Handle AJAX add to cart requests
require_once 'config.php';

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['product_id'])) {
        $response['message'] = 'Product ID is required';
        echo json_encode($response);
        exit();
    }
    
    $product_id = (int)sanitize($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? (int)sanitize($_POST['quantity']) : 1;
    
    $conn = getDBConnection();
    
    // Check if product exists and is in stock
    $productSql = "SELECT * FROM products WHERE id = ? AND stock_quantity > 0";
    $stmt = $conn->prepare($productSql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $productResult = $stmt->get_result();
    
    if ($productResult->num_rows == 0) {
        $response['message'] = 'Product not available or out of stock';
        $conn->close();
        echo json_encode($response);
        exit();
    }
    
    if (isset($_SESSION['user_id'])) {
        // Logged in user - store in database
        $user_id = $_SESSION['user_id'];
        
        // Check if product already in cart
        $checkSql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $cartResult = $stmt->get_result();
        
        if ($cartResult->num_rows > 0) {
            // Update quantity
            $updateSql = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt->execute();
        } else {
            // Add new item to cart
            $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $stmt->execute();
        }
        
        // Get updated cart count
        $countSql = "SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($countSql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $countResult = $stmt->get_result();
        $countData = $countResult->fetch_assoc();
        $_SESSION['cart_count'] = $countData['cart_count'] ?? 0;
        
    } else {
        // Guest user - store in session
        if (!isset($_SESSION['cart_items'])) {
            $_SESSION['cart_items'] = [];
        }
        
        if (isset($_SESSION['cart_items'][$product_id])) {
            $_SESSION['cart_items'][$product_id] += $quantity;
        } else {
            $_SESSION['cart_items'][$product_id] = $quantity;
        }
        
        // Calculate total cart count
        $_SESSION['cart_count'] = array_sum($_SESSION['cart_items']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Product added to cart successfully!';
    $response['cart_count'] = $_SESSION['cart_count'];
    
    $conn->close();
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>