<?php
// checkout.php - Checkout Page
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Handle address addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    // Validate address fields
    $required = ['name', 'phone', 'address_line1', 'city', 'state', 'zip_code', 'country'];
    $errors = [];
    
    foreach ($required as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Validate phone number
    if (!empty($_POST['phone']) && !preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
        $errors[] = "Phone number must be 10 digits";
    }
    
    // Validate ZIP code
    if (!empty($_POST['zip_code']) && !preg_match('/^[0-9]{6}$/', $_POST['zip_code'])) {
        $errors[] = "ZIP code must be 6 digits";
    }
    
    if (empty($errors)) {
        // Sanitize and store POST values
        $name = $conn->real_escape_string(trim($_POST['name']));
        $phone = $conn->real_escape_string(trim($_POST['phone']));
        $address_line1 = $conn->real_escape_string(trim($_POST['address_line1']));
        $address_line2 = isset($_POST['address_line2']) ? $conn->real_escape_string(trim($_POST['address_line2'])) : '';
        $city = $conn->real_escape_string(trim($_POST['city']));
        $state = $conn->real_escape_string(trim($_POST['state']));
        $country = $conn->real_escape_string(trim($_POST['country']));
        $zip_code = $conn->real_escape_string(trim($_POST['zip_code']));
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // If setting as default, remove default from other addresses
        if ($is_default) {
            $update_sql = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        // Insert new address
        $sql = "INSERT INTO addresses (user_id, name, phone, address_line1, address_line2, 
                                       city, state, country, zip_code, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssi", 
            $user_id,
            $name,
            $phone,
            $address_line1,
            $address_line2,
            $city,
            $state,
            $country,
            $zip_code,
            $is_default
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Address added successfully!';
            header('Location: checkout.php');
            exit();
        } else {
            $_SESSION['error_message'] = 'Failed to add address: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
    
    // Store form data in session to repopulate form
    $_SESSION['form_data'] = $_POST;
    header('Location: checkout.php');
    exit();
}

// Get user details
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Get cart items for the user
$cart_sql = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity, 
                    (p.price * c.quantity) as item_total 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart_items = [];
$subtotal = 0;
$total_items = 0;
$has_out_of_stock = false;

while ($item = $cart_result->fetch_assoc()) {
    $cart_items[] = $item;
    $subtotal += $item['item_total'];
    $total_items += $item['quantity'];
    
    if ($item['stock_quantity'] < $item['quantity']) {
        $has_out_of_stock = true;
    }
}
$stmt->close();

// If cart is empty, redirect to cart page
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate shipping
$shipping = 0;
if ($subtotal > 0 && $subtotal < 499) {
    $shipping = 40;
}

// Calculate total
$total = $subtotal + $shipping;

// Get user addresses if any
$address_sql = "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC";
$stmt = $conn->prepare($address_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$address_result = $stmt->get_result();
$addresses = $address_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate required fields
    $required_fields = ['address_id', 'payment_method'];
    
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    if (!$has_out_of_stock && empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get selected address
            $address_id = intval($_POST['address_id']);
            $address_stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
            $address_stmt->bind_param("ii", $address_id, $user_id);
            $address_stmt->execute();
            $address_result = $address_stmt->get_result();
            $selected_address = $address_result->fetch_assoc();
            $address_stmt->close();
            
            if (!$selected_address) {
                throw new Exception("Invalid address selected");
            }
            
            // Generate order number
            $order_number = 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            
            // Create order
            $order_sql = "INSERT INTO orders (order_number, user_id, total_amount, shipping_amount, 
                                             payment_method, payment_status, order_status, 
                                             shipping_address, billing_address, notes) 
                          VALUES (?, ?, ?, ?, ?, 'pending', 'processing', ?, ?, ?)";
            
            $shipping_address = json_encode([
                'name' => $selected_address['name'],
                'phone' => $selected_address['phone'],
                'address_line1' => $selected_address['address_line1'],
                'address_line2' => $selected_address['address_line2'],
                'city' => $selected_address['city'],
                'state' => $selected_address['state'],
                'country' => $selected_address['country'],
                'zip_code' => $selected_address['zip_code']
            ]);
            
            $notes = isset($_POST['notes']) ? $conn->real_escape_string(trim($_POST['notes'])) : '';
            $payment_method = $conn->real_escape_string($_POST['payment_method']);
            
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("siddssss", 
                $order_number, 
                $user_id, 
                $total, 
                $shipping,
                $payment_method,
                $shipping_address,
                $shipping_address,
                $notes
            );
            
            if (!$order_stmt->execute()) {
                throw new Exception("Failed to create order: " . $conn->error);
            }
            
            $order_id = $conn->insert_id;
            $order_stmt->close();
            
            // Create order items and update product stock
            foreach ($cart_items as $item) {
                // Insert order item
                $order_item_sql = "INSERT INTO order_items (order_id, product_id, product_name, 
                                                           quantity, unit_price, total_price) 
                                   VALUES (?, ?, ?, ?, ?, ?)";
                $order_item_stmt = $conn->prepare($order_item_sql);
                $order_item_stmt->bind_param("iisidd", 
                    $order_id,
                    $item['product_id'],
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['item_total']
                );
                
                if (!$order_item_stmt->execute()) {
                    throw new Exception("Failed to add order item: " . $conn->error);
                }
                $order_item_stmt->close();
                
                // Update product stock
                $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_stock_sql);
                $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update product stock: " . $conn->error);
                }
                $update_stmt->close();
            }
            
            // Clear user's cart
            $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
            $clear_stmt = $conn->prepare($clear_cart_sql);
            $clear_stmt->bind_param("i", $user_id);
            
            if (!$clear_stmt->execute()) {
                throw new Exception("Failed to clear cart: " . $conn->error);
            }
            $clear_stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to order confirmation
            $_SESSION['order_success'] = true;
            $_SESSION['order_number'] = $order_number;
            header('Location: order-confirmation.php?order_id=' . $order_id);
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error_message'] = "Error processing order: " . $e->getMessage();
            header('Location: checkout.php');
            exit();
        }
    } else {
        $error_message = "Please fix the errors below:";
        if ($has_out_of_stock) {
            $errors[] = "Some items in your cart are out of stock or have insufficient quantity";
        }
        $_SESSION['error_message'] = $error_message . "<br>" . implode('<br>', $errors);
        header('Location: checkout.php');
        exit();
    }
}

// Display messages
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

// Clear messages after displaying
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
unset($_SESSION['form_data']);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ShopEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --dark: #2d3047;
            --light: #f7f9fc;
            --text: #333;
            --text-light: #666;
            --border: #e1e5eb;
            --success: #06d6a0;
            --warning: #ffd166;
            --danger: #ef476f;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            --radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text);
            line-height: 1.6;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .checkout-header {
            margin-bottom: 40px;
            text-align: center;
        }
        
        .checkout-header h1 {
            font-size: 36px;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .checkout-header p {
            color: var(--text-light);
            font-size: 18px;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        
        .step {
            display: flex;
            align-items: center;
            color: var(--text-light);
            margin: 0 20px;
        }
        
        .step.active {
            color: var(--primary);
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light);
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .step-label {
            font-weight: 500;
            display: none;
        }
        
        .step-line {
            width: 100px;
            height: 2px;
            background: var(--border);
            margin: 0 15px;
        }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        /* Messages */
        .message {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Checkout Form */
        .checkout-form {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
        }
        
        .section-title {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(78, 205, 196, 0.2);
        }
        
        .form-control.error {
            border-color: var(--danger);
        }
        
        .form-text {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .radio-option {
            flex: 1;
            min-width: 150px;
        }
        
        .radio-input {
            display: none;
        }
        
        .radio-label {
            display: block;
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .radio-input:checked + .radio-label {
            border-color: var(--primary);
            background: rgba(255, 107, 107, 0.05);
        }
        
        .radio-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-light);
        }
        
        .radio-input:checked + .radio-label .radio-icon {
            color: var(--primary);
        }
        
        .radio-text {
            font-weight: 500;
        }
        
        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .address-card {
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .address-card:hover {
            border-color: var(--secondary);
        }
        
        .address-card.selected {
            border-color: var(--primary);
            background: rgba(255, 107, 107, 0.05);
        }
        
        .address-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .address-card-title {
            font-weight: 600;
            color: var(--dark);
        }
        
        .address-card-default {
            background: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .address-card-body {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.5;
        }
        
        .address-card-body p {
            margin-bottom: 5px;
        }
        
        .add-address-btn {
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            color: var(--text-light);
        }
        
        .add-address-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .add-address-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 10px;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius);
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .order-item-meta {
            display: flex;
            justify-content: space-between;
            color: var(--text-light);
            font-size: 13px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .summary-label {
            color: var(--text-light);
        }
        
        .summary-value {
            font-weight: 500;
        }
        
        .summary-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--border);
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .summary-total .summary-value {
            color: var(--primary);
            font-size: 24px;
        }
        
        .place-order-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .place-order-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(255, 107, 107, 0.2);
        }
        
        .place-order-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .place-order-btn:disabled:hover {
            background: var(--primary);
            transform: none;
            box-shadow: none;
        }
        
        .back-to-cart {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-to-cart:hover {
            color: #ff5252;
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        .alert li {
            margin-bottom: 5px;
        }
        
        .stock-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: var(--radius);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 20px;
            color: var(--dark);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-light);
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #ff5252;
        }
        
        .btn-secondary {
            background: var(--light);
            color: var(--text);
            border: 1px solid var(--border);
        }
        
        .btn-secondary:hover {
            background: var(--border);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .step-label {
                display: none;
            }
            
            .step-line {
                width: 50px;
            }
            
            .address-grid {
                grid-template-columns: 1fr;
            }
            
            .radio-group {
                flex-direction: column;
            }
            
            .radio-option {
                min-width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .checkout-container {
                margin: 20px auto;
            }
            
            .checkout-header h1 {
                font-size: 28px;
            }
            
            .checkout-form, .order-summary {
                padding: 20px;
            }
            
            .step {
                margin: 0 10px;
            }
            
            .step-line {
                width: 30px;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <p>Complete your purchase</p>
        </div>
        
        <!-- Display Messages -->
        <?php if ($success_message): ?>
            <div class="message success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Cart</div>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Checkout</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>
        
        <?php if ($has_out_of_stock): ?>
            <div class="stock-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Some items in your cart are out of stock or have insufficient quantity. Please update your cart before proceeding.</span>
            </div>
        <?php endif; ?>
        
        <div class="checkout-content">
            <!-- Checkout Form -->
            <form id="checkout-form" class="checkout-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="place_order" value="1">
                
                <!-- Shipping Address -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Shipping Address
                    </h2>
                    
                    <?php if (empty($addresses)): ?>
                        <div class="alert alert-danger">
                            <strong>No shipping address found!</strong>
                            <p>Please add a shipping address to continue.</p>
                            <button type="button" class="btn btn-primary" style="margin-top: 10px;" onclick="showAddressModal()">
                                <i class="fas fa-plus"></i> Add Address
                            </button>
                        </div>
                        <input type="hidden" name="address_id" value="" required>
                    <?php else: ?>
                        <div class="address-grid">
                            <?php foreach ($addresses as $address): ?>
                                <div class="address-card <?php echo ($address['is_default']) ? 'selected' : ''; ?>" 
                                     onclick="selectAddress(<?php echo $address['id']; ?>)">
                                    <div class="address-card-header">
                                        <div class="address-card-title">
                                            <?php echo htmlspecialchars($address['name']); ?>
                                            <?php if ($address['is_default']): ?>
                                                <span class="address-card-default">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <input type="radio" name="address_id" value="<?php echo $address['id']; ?>" 
                                               class="radio-input" 
                                               <?php echo ($address['is_default']) ? 'checked required' : ''; ?>>
                                    </div>
                                    <div class="address-card-body">
                                        <p><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                        <?php if (!empty($address['address_line2'])): ?>
                                            <p><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                        <?php endif; ?>
                                        <p>
                                            <?php echo htmlspecialchars($address['city']); ?>, 
                                            <?php echo htmlspecialchars($address['state']); ?> - 
                                            <?php echo htmlspecialchars($address['zip_code']); ?>
                                        </p>
                                        <p><?php echo htmlspecialchars($address['country']); ?></p>
                                        <p>Phone: <?php echo htmlspecialchars($address['phone']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="add-address-btn" onclick="showAddressModal()">
                                <i class="fas fa-plus add-address-icon"></i>
                                <span>Add New Address</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Method -->
                <div class="form-section" style="margin-top: 30px;">
                    <h2 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Payment Method
                    </h2>
                    
                    <div class="form-group">
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="cod" name="payment_method" value="cod" class="radio-input" required <?php echo (empty($_POST['payment_method']) || $_POST['payment_method'] == 'cod') ? 'checked' : ''; ?>>
                                <label for="cod" class="radio-label">
                                    <i class="fas fa-money-bill-wave radio-icon"></i>
                                    <div class="radio-text">Cash on Delivery</div>
                                </label>
                            </div>
                            
                            <div class="radio-option">
                                <input type="radio" id="card" name="payment_method" value="card" class="radio-input" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'card') ? 'checked' : ''; ?>>
                                <label for="card" class="radio-label">
                                    <i class="fas fa-credit-card radio-icon"></i>
                                    <div class="radio-text">Credit/Debit Card</div>
                                </label>
                            </div>
                            
                            <div class="radio-option">
                                <input type="radio" id="paypal" name="payment_method" value="paypal" class="radio-input" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'paypal') ? 'checked' : ''; ?>>
                                <label for="paypal" class="radio-label">
                                    <i class="fab fa-paypal radio-icon"></i>
                                    <div class="radio-text">PayPal</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Notes -->
                <div class="form-section" style="margin-top: 30px;">
                    <h2 class="section-title">
                        <i class="fas fa-sticky-note"></i>
                        Additional Information
                    </h2>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Order Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" 
                                  placeholder="Any special instructions for delivery..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <a href="cart.php" class="back-to-cart">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </form>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h2 class="section-title">
                    <i class="fas fa-receipt"></i>
                    Order Summary
                </h2>
                
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="order-item-image"
                                 onerror="this.src='https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                            <div class="order-item-details">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="order-item-meta">
                                    <span>Qty: <?php echo $item['quantity']; ?></span>
                                    <span>₹<?php echo number_format($item['price'], 2); ?> each</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal (<?php echo $total_items; ?> items)</span>
                    <span class="summary-value">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value">
                        <?php if($shipping > 0): ?>
                            ₹<?php echo number_format($shipping, 2); ?>
                        <?php else: ?>
                            FREE
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="summary-row summary-total">
                    <span class="summary-label">Total</span>
                    <span class="summary-value">₹<?php echo number_format($total, 2); ?></span>
                </div>
                
                <button type="submit" form="checkout-form" class="place-order-btn" <?php echo $has_out_of_stock ? 'disabled' : ''; ?>>
                    <i class="fas fa-lock"></i> Place Order
                </button>
                
                <a href="products.php" class="back-to-cart">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
    
    <!-- Address Modal -->
    <div id="address-modal" class="modal <?php echo !empty($form_data) ? 'show' : ''; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Address</h3>
                <button type="button" class="modal-close" onclick="hideAddressModal()">&times;</button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="add_address" value="1">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="address-name" class="form-label">Full Name *</label>
                        <input type="text" id="address-name" name="name" class="form-control" required 
                               value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address-phone" class="form-label">Phone Number *</label>
                        <input type="tel" id="address-phone" name="phone" class="form-control" required
                               value="<?php echo isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : ''; ?>"
                               pattern="[0-9]{10}" title="10 digit phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="address-line1" class="form-label">Address Line 1 *</label>
                        <input type="text" id="address-line1" name="address_line1" class="form-control" required
                               value="<?php echo isset($form_data['address_line1']) ? htmlspecialchars($form_data['address_line1']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address-line2" class="form-label">Address Line 2 (Optional)</label>
                        <input type="text" id="address-line2" name="address_line2" class="form-control"
                               value="<?php echo isset($form_data['address_line2']) ? htmlspecialchars($form_data['address_line2']) : ''; ?>">
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="address-city" class="form-label">City *</label>
                            <input type="text" id="address-city" name="city" class="form-control" required
                                   value="<?php echo isset($form_data['city']) ? htmlspecialchars($form_data['city']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address-state" class="form-label">State *</label>
                            <input type="text" id="address-state" name="state" class="form-control" required
                                   value="<?php echo isset($form_data['state']) ? htmlspecialchars($form_data['state']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="address-zip" class="form-label">ZIP Code *</label>
                            <input type="text" id="address-zip" name="zip_code" class="form-control" required
                                   value="<?php echo isset($form_data['zip_code']) ? htmlspecialchars($form_data['zip_code']) : ''; ?>"
                                   pattern="[0-9]{6}" title="6 digit ZIP code">
                        </div>
                        
                        <div class="form-group">
                            <label for="address-country" class="form-label">Country *</label>
                            <input type="text" id="address-country" name="country" class="form-control" required
                                   value="<?php echo isset($form_data['country']) ? htmlspecialchars($form_data['country']) : 'India'; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="address-default" name="is_default" value="1"
                                   <?php echo isset($form_data['is_default']) ? 'checked' : (empty($addresses) ? 'checked' : ''); ?>>
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideAddressModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Address Selection
        function selectAddress(addressId) {
            const addressCards = document.querySelectorAll('.address-card');
            addressCards.forEach(card => {
                card.classList.remove('selected');
            });
            
            const selectedCard = document.querySelector(`.address-card input[value="${addressId}"]`);
            if (selectedCard) {
                selectedCard.checked = true;
                selectedCard.closest('.address-card').classList.add('selected');
            }
        }
        
        // Address Modal
        function showAddressModal() {
            document.getElementById('address-modal').classList.add('show');
        }
        
        function hideAddressModal() {
            document.getElementById('address-modal').classList.remove('show');
            // Reset form if needed
            const form = document.querySelector('#address-modal form');
            if (form) form.reset();
        }
        
        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const addressSelected = document.querySelector('input[name="address_id"]:checked');
            const paymentSelected = document.querySelector('input[name="payment_method"]:checked');
            
            if (!addressSelected) {
                e.preventDefault();
                alert('Please select a shipping address');
                return false;
            }
            
            if (!paymentSelected) {
                e.preventDefault();
                alert('Please select a payment method');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('.place-order-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        });
        
        // Close modal on outside click
        document.getElementById('address-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddressModal();
            }
        });
        
        // Auto-show modal if there's an error in address form
        <?php if (!empty($form_data)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showAddressModal();
            });
        <?php endif; ?>
        
        // Initialize address selection
        document.addEventListener('DOMContentLoaded', function() {
            const defaultAddress = document.querySelector('.address-card.selected input[type="radio"]');
            if (defaultAddress) {
                defaultAddress.checked = true;
            }
            
            // Set default payment method if not set
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            let hasSelected = false;
            paymentMethods.forEach(method => {
                if (method.checked) hasSelected = true;
            });
            if (!hasSelected && paymentMethods.length > 0) {
                paymentMethods[0].checked = true;
            }
        });
    </script>
</body>
</html>