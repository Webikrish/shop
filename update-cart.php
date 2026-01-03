<?php
// update-cart.php - Handle cart quantity updates
require_once 'config.php';

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        $response['message'] = 'Product ID and quantity are required';
        echo json_encode($response);
        exit();
    }
    
    $product_id = (int)sanitize($_POST['product_id']);
    $quantity = (int)sanitize($_POST['quantity']);
    
    $conn = getDBConnection();
    
    if (isset($_SESSION['user_id'])) {
        // Logged in user
        $user_id = $_SESSION['user_id'];
        
        if ($quantity <= 0) {
            // Remove item
            $deleteSql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($deleteSql);
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
        } else {
            // Check stock availability
            $stockSql = "SELECT stock_quantity FROM products WHERE id = ?";
            $stockStmt = $conn->prepare($stockSql);
            $stockStmt->bind_param("i", $product_id);
            $stockStmt->execute();
            $stockResult = $stockStmt->get_result();
            
            if ($stockResult->num_rows > 0) {
                $product = $stockResult->fetch_assoc();
                
                if ($quantity > $product['stock_quantity']) {
                    $response['message'] = 'Only ' . $product['stock_quantity'] . ' items available in stock';
                    $conn->close();
                    echo json_encode($response);
                    exit();
                }
                
                // Update quantity
                $updateSql = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                
                if (!$stmt->execute()) {
                    // Insert if not exists
                    $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($insertSql);
                    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                    $stmt->execute();
                }
            }
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
        // Guest user
        if (!isset($_SESSION['cart_items'])) {
            $_SESSION['cart_items'] = [];
        }
        
        // Check stock for guest users
        $stockSql = "SELECT stock_quantity FROM products WHERE id = ?";
        $stockStmt = $conn->prepare($stockSql);
        $stockStmt->bind_param("i", $product_id);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        
        if ($stockResult->num_rows > 0) {
            $product = $stockResult->fetch_assoc();
            
            if ($quantity > $product['stock_quantity']) {
                $response['message'] = 'Only ' . $product['stock_quantity'] . ' items available in stock';
                $conn->close();
                echo json_encode($response);
                exit();
            }
        }
        
        if ($quantity <= 0) {
            unset($_SESSION['cart_items'][$product_id]);
        } else {
            $_SESSION['cart_items'][$product_id] = $quantity;
        }
        
        $_SESSION['cart_count'] = array_sum($_SESSION['cart_items']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Cart updated successfully';
    $response['cart_count'] = $_SESSION['cart_count'];
    
    $conn->close();
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>