<?php
// cart-ajax.php - Handle all cart AJAX requests
require_once 'config.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$conn = getDBConnection();

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

switch ($action) {
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $product_id = (int)sanitize($_POST['product_id']);
            $quantity = (int)sanitize($_POST['quantity']);
            
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
                    echo json_encode($response);
                    exit();
                }
            }
            
            if ($quantity <= 0) {
                // Remove item
                if ($user_id) {
                    $deleteSql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                    $stmt = $conn->prepare($deleteSql);
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                } else {
                    unset($_SESSION['cart_items'][$product_id]);
                }
            } else {
                // Update quantity
                if ($user_id) {
                    // Check if item exists
                    $checkSql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
                    $checkStmt = $conn->prepare($checkSql);
                    $checkStmt->bind_param("ii", $user_id, $product_id);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();
                    
                    if ($checkResult->num_rows > 0) {
                        $updateSql = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
                        $stmt = $conn->prepare($updateSql);
                        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                        $stmt->execute();
                    } else {
                        $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($insertSql);
                        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                        $stmt->execute();
                    }
                } else {
                    $_SESSION['cart_items'][$product_id] = $quantity;
                }
            }
            
            // Update cart count
            updateCartCount($user_id, $conn);
            
            $response['success'] = true;
            $response['message'] = 'Cart updated successfully';
            $response['cart_count'] = $_SESSION['cart_count'] ?? 0;
        }
        break;
        
    case 'remove':
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
            $product_id = (int)sanitize($_POST['product_id']);
            
            if ($user_id) {
                $deleteSql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                $stmt = $conn->prepare($deleteSql);
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
            } else {
                unset($_SESSION['cart_items'][$product_id]);
            }
            
            // Update cart count
            updateCartCount($user_id, $conn);
            
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
            $response['cart_count'] = $_SESSION['cart_count'] ?? 0;
        }
        break;
        
    case 'clear':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($user_id) {
                $deleteSql = "DELETE FROM cart WHERE user_id = ?";
                $stmt = $conn->prepare($deleteSql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            } else {
                $_SESSION['cart_items'] = [];
            }
            
            $_SESSION['cart_count'] = 0;
            
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully';
            $response['cart_count'] = 0;
        }
        break;
        
    case 'summary':
        $subtotal = 0;
        $total_items = 0;
        
        if ($user_id) {
            // Logged in user
            $cartSql = "SELECT c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
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
        break;
        
    default:
        $response['message'] = 'Invalid action';
        break;
}

echo json_encode($response);
$conn->close();

// Function to update cart count
function updateCartCount($user_id, $conn) {
    if ($user_id) {
        $countSql = "SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($countSql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $countResult = $stmt->get_result();
        $countData = $countResult->fetch_assoc();
        $_SESSION['cart_count'] = $countData['cart_count'] ?? 0;
    } else {
        $_SESSION['cart_count'] = isset($_SESSION['cart_items']) ? array_sum($_SESSION['cart_items']) : 0;
    }
}
?>