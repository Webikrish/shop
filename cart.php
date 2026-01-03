<?php
// cart.php - Shopping Cart Page
require_once 'config.php';

// Check if user is logged in for database operations
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$conn = getDBConnection();

// Get cart items with product details
$cart_items = [];
$subtotal = 0;
$total_items = 0;

if ($user_id) {
    // Logged in user - get from database
    $cartSql = "SELECT c.*, p.name, p.price, p.original_price, p.image_url, p.stock_quantity, 
                       (p.price * c.quantity) as item_total 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
    $stmt = $conn->prepare($cartSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartResult = $stmt->get_result();
    
    while ($item = $cartResult->fetch_assoc()) {
        $cart_items[] = $item;
        $subtotal += $item['item_total'];
        $total_items += $item['quantity'];
    }
    $stmt->close();
} else {
    // Guest user - get from session
    if (isset($_SESSION['cart_items']) && !empty($_SESSION['cart_items'])) {
        $product_ids = array_keys($_SESSION['cart_items']);
        
        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            
            $cartSql = "SELECT p.* FROM products p WHERE p.id IN ($placeholders)";
            
            // Prepare statement
            $types = str_repeat('i', count($product_ids));
            $stmt = $conn->prepare($cartSql);
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $cartResult = $stmt->get_result();
            
            while ($item = $cartResult->fetch_assoc()) {
                $item['quantity'] = $_SESSION['cart_items'][$item['id']];
                $item['item_total'] = $item['price'] * $item['quantity'];
                $cart_items[] = $item;
                $subtotal += $item['item_total'];
                $total_items += $item['quantity'];
            }
            $stmt->close();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShopEasy</title>
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
        
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .cart-header {
            margin-bottom: 40px;
            text-align: center;
        }
        
        .cart-header h1 {
            font-size: 36px;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .cart-header p {
            color: var(--text-light);
            font-size: 18px;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        /* Cart Items */
        .cart-items {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
        }
        
        .cart-items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .cart-items-header h2 {
            font-size: 24px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .clear-cart-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }
        
        .clear-cart-btn:hover {
            color: #ff5252;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart i {
            font-size: 80px;
            color: var(--border);
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-cart h3 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .empty-cart p {
            color: var(--text-light);
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: auto 1fr auto auto auto;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--border);
            align-items: center;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: var(--radius);
        }
        
        .item-details {
            padding-right: 20px;
        }
        
        .item-details h3 {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .item-price {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .original-price {
            font-size: 14px;
            color: var(--text-light);
            text-decoration: line-through;
            margin-left: 10px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--light);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
        }
        
        .quantity-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-btn:disabled:hover {
            background: var(--light);
            color: var(--text-light);
            border-color: var(--border);
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .quantity-input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(78, 205, 196, 0.2);
        }
        
        .item-total {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            min-width: 100px;
            text-align: right;
        }
        
        .remove-item {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 20px;
            transition: color 0.3s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .remove-item:hover {
            color: var(--primary);
            background: rgba(255, 107, 107, 0.1);
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
        
        .order-summary h2 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .shipping-info {
            background: var(--light);
            padding: 15px;
            border-radius: var(--radius);
            margin: 20px 0;
            font-size: 14px;
            color: var(--text-light);
        }
        
        .shipping-info i {
            color: var(--success);
            margin-right: 5px;
        }
        
        .checkout-btn {
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
            text-decoration: none;
        }
        
        .checkout-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(255, 107, 107, 0.2);
        }
        
        .checkout-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .checkout-btn:disabled:hover {
            background: var(--primary);
            transform: none;
            box-shadow: none;
        }
        
        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .continue-shopping:hover {
            color: #ff5252;
            text-decoration: underline;
        }
        
        .cart-message {
            padding: 12px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }
        
        .cart-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        
        .cart-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
        
        .cart-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .cart-actions .btn {
            padding: 10px 20px;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #ff5252;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 1fr;
                gap: 15px;
                position: relative;
                padding: 15px;
                border: 1px solid var(--border);
                border-radius: var(--radius);
                margin-bottom: 15px;
            }
            
            .item-image {
                width: 100%;
                height: 150px;
            }
            
            .remove-item {
                position: absolute;
                top: 10px;
                right: 10px;
            }
            
            .quantity-control {
                justify-content: center;
            }
            
            .item-total {
                text-align: center;
            }
        }
        
        @media (max-width: 576px) {
            .cart-container {
                margin: 20px auto;
            }
            
            .cart-header h1 {
                font-size: 28px;
            }
            
            .cart-items, .order-summary {
                padding: 20px;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .cart-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>
        
        <!-- Success/Error Messages -->
        <div id="cart-message" class="cart-message"></div>
        
        <div class="cart-content">
            <!-- Cart Items Section -->
            <div class="cart-items">
                <div class="cart-items-header">
                    <h2><i class="fas fa-shopping-cart"></i> Your Cart (<span id="total-items"><?php echo $total_items; ?></span> items)</h2>
                    <?php if(!empty($cart_items)): ?>
                        <button type="button" class="clear-cart-btn" onclick="clearCart()">
                            <i class="fas fa-trash-alt"></i> Clear Cart
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if(empty($cart_items)): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any products to your cart yet. Start shopping to fill it up!</p>
                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> Browse Products
                            </a>
                            <a href="index.php" class="btn btn-outline">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($cart_items as $item): ?>
                        <?php 
                        $item_total = $item['price'] * $item['quantity'];
                        $max_quantity = min($item['stock_quantity'], 20); // Limit to stock or 20
                        ?>
                        <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image" onerror="this.src='https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                            
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div>
                                    <span class="item-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <?php if($item['original_price'] > 0 && $item['original_price'] > $item['price']): ?>
                                        <span class="original-price">₹<?php echo number_format($item['original_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <small style="color: <?php echo $item['stock_quantity'] > 0 ? 'var(--success)' : 'var(--primary)'; ?>;">
                                    <i class="fas fa-<?php echo $item['stock_quantity'] > 0 ? 'check-circle' : 'times-circle'; ?>"></i>
                                    <?php echo $item['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    <?php if($item['stock_quantity'] > 0): ?>
                                        (<?php echo $item['stock_quantity']; ?> available)
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn decrease" 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, -1)"
                                        <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                    -
                                </button>
                                <input type="number" 
                                       name="quantity" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $max_quantity; ?>" 
                                       class="quantity-input" 
                                       id="quantity-<?php echo $item['id']; ?>"
                                       onchange="updateQuantityDirect(<?php echo $item['id']; ?>)">
                                <button type="button" class="quantity-btn increase" 
                                        onclick="updateQuantity(<?php echo $item['id']; ?>, 1)"
                                        <?php echo $item['quantity'] >= $max_quantity ? 'disabled' : ''; ?>>
                                    +
                                </button>
                            </div>
                            
                            <div class="item-total" id="item-total-<?php echo $item['id']; ?>">
                                ₹<?php echo number_format($item_total, 2); ?>
                            </div>
                            
                            <button type="button" class="remove-item" onclick="removeItem(<?php echo $item['id']; ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="cart-actions" style="margin-top: 30px;">
                        <a href="products.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <button type="button" class="btn btn-primary" onclick="clearCart()">
                            <i class="fas fa-trash-alt"></i> Clear Cart
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Order Summary Section -->
            <div class="order-summary">
                <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal (<span id="summary-items"><?php echo $total_items; ?></span> items)</span>
                    <span class="summary-value" id="summary-subtotal">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value" id="summary-shipping">
                        <?php if($shipping > 0): ?>
                            ₹<?php echo number_format($shipping, 2); ?>
                        <?php else: ?>
                            FREE
                        <?php endif; ?>
                    </span>
                </div>
                
                <?php if($shipping > 0): ?>
                    <div class="shipping-info" id="shipping-info">
                        <i class="fas fa-info-circle"></i>
                        Add ₹<?php echo number_format(499 - $subtotal, 2); ?> more to get FREE shipping
                    </div>
                <?php endif; ?>
                
                <div class="summary-row summary-total">
                    <span class="summary-label">Total</span>
                    <span class="summary-value" id="summary-total">₹<?php echo number_format($total, 2); ?></span>
                </div>
                
                <?php if(!empty($cart_items)): ?>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="checkout-btn">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                    <?php else: ?>
                        <a href="login.php?redirect=checkout" class="checkout-btn">
                            <i class="fas fa-sign-in-alt"></i> Login to Checkout
                        </a>
                        <small style="display: block; text-align: center; margin-top: 10px; color: var(--text-light);">
                            Or <a href="register.php" style="color: var(--primary); text-decoration: none;">create an account</a>
                        </small>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="checkout-btn" disabled>
                        <i class="fas fa-shopping-cart"></i> Cart is Empty
                    </button>
                <?php endif; ?>
                
                <a href="index.php" class="continue-shopping">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Show message function
        function showMessage(message, type = 'success') {
            const messageDiv = document.getElementById('cart-message');
            messageDiv.textContent = message;
            messageDiv.className = 'cart-message ' + type;
            messageDiv.style.display = 'block';
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
        
        // Update quantity with buttons
        function updateQuantity(productId, change) {
            const input = document.getElementById('quantity-' + productId);
            let currentValue = parseInt(input.value);
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            currentValue += change;
            
            if (currentValue < min) currentValue = min;
            if (currentValue > max) currentValue = max;
            
            input.value = currentValue;
            
            // Enable/disable buttons
            const decreaseBtn = input.previousElementSibling;
            const increaseBtn = input.nextElementSibling;
            
            if (currentValue <= min) {
                decreaseBtn.disabled = true;
            } else {
                decreaseBtn.disabled = false;
            }
            
            if (currentValue >= max) {
                increaseBtn.disabled = true;
            } else {
                increaseBtn.disabled = false;
            }
            
            // Update cart via AJAX
            updateCartItem(productId, currentValue);
        }
        
        // Update quantity directly from input
        function updateQuantityDirect(productId) {
            const input = document.getElementById('quantity-' + productId);
            let currentValue = parseInt(input.value);
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            if (currentValue < min) currentValue = min;
            if (currentValue > max) currentValue = max;
            
            input.value = currentValue;
            
            // Update cart via AJAX
            updateCartItem(productId, currentValue);
        }
        
        // Update cart item via AJAX
        function updateCartItem(productId, quantity) {
            const input = document.getElementById('quantity-' + productId);
            if (!quantity) {
                quantity = input.value;
            }
            
            // Show loading state
            const itemElement = document.getElementById('cart-item-' + productId);
            itemElement.style.opacity = '0.7';
            
            // Send AJAX request to update cart
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            fetch('cart-ajax.php?action=update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                itemElement.style.opacity = '1';
                
                if (data.success) {
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Update item total
                    const priceElement = itemElement.querySelector('.item-price');
                    const priceText = priceElement.textContent.replace('₹', '').replace(/,/g, '');
                    const price = parseFloat(priceText);
                    const itemTotal = price * quantity;
                    
                    const itemTotalElement = document.getElementById('item-total-' + productId);
                    itemTotalElement.textContent = '₹' + itemTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    
                    // Update summary from server response
                    if (data.subtotal !== undefined && data.shipping !== undefined && data.total !== undefined) {
                        updateSummaryDisplay(data.subtotal, data.shipping, data.total, data.cart_count);
                    } else {
                        // Fallback: recalculate locally
                        recalculateSummary();
                    }
                    
                    showMessage('Cart updated successfully!');
                } else {
                    showMessage('Error: ' + data.message, 'error');
                    // Reset input to original value
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                itemElement.style.opacity = '1';
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
            });
        }
        
        // Remove item from cart
        function removeItem(productId) {
            if (!confirm('Are you sure you want to remove this item from cart?')) {
                return;
            }
            
            // Send AJAX request to remove item
            const formData = new FormData();
            formData.append('product_id', productId);
            
            fetch('cart-ajax.php?action=remove', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Remove item from DOM
                    const itemElement = document.getElementById('cart-item-' + productId);
                    itemElement.style.transition = 'all 0.3s';
                    itemElement.style.opacity = '0';
                    itemElement.style.transform = 'translateX(-20px)';
                    
                    setTimeout(() => {
                        itemElement.remove();
                        
                        // Update summary from server response
                        if (data.subtotal !== undefined && data.shipping !== undefined && data.total !== undefined) {
                            updateSummaryDisplay(data.subtotal, data.shipping, data.total, data.cart_count);
                        } else {
                            // Fallback: recalculate locally
                            recalculateSummary();
                        }
                        
                        // Check if cart is empty
                        const cartItemsContainer = document.querySelector('.cart-items');
                        const cartItems = cartItemsContainer.querySelectorAll('.cart-item');
                        
                        if (cartItems.length === 0) {
                            // Show empty cart message
                            location.reload();
                        }
                        
                        showMessage('Item removed from cart');
                    }, 300);
                } else {
                    showMessage('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
            });
        }
        
        // Clear entire cart
        function clearCart() {
            if (!confirm('Are you sure you want to clear your entire cart?')) {
                return;
            }
            
            // Send AJAX request to clear cart
            fetch('cart-ajax.php?action=clear', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = 0;
                    }
                    
                    // Clear cart items from DOM
                    const cartItems = document.querySelectorAll('.cart-item');
                    cartItems.forEach((item, index) => {
                        item.style.transition = 'all 0.3s';
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(-20px)';
                        
                        setTimeout(() => {
                            item.remove();
                        }, index * 100);
                    });
                    
                    // Update summary to zero
                    setTimeout(() => {
                        updateSummaryDisplay(0, 0, 0, 0);
                        showMessage('Cart cleared successfully');
                        // Reload to show empty cart state
                        setTimeout(() => location.reload(), 1000);
                    }, cartItems.length * 100);
                } else {
                    showMessage('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
            });
        }
        
        // Update order summary display
        function updateSummaryDisplay(subtotal, shipping, total, itemCount) {
            // Format numbers
            const formatCurrency = (amount) => {
                return '₹' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            };
            
            // Update display
            document.getElementById('summary-subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('summary-shipping').textContent = shipping > 0 ? formatCurrency(shipping) : 'FREE';
            document.getElementById('summary-total').textContent = formatCurrency(total);
            document.getElementById('total-items').textContent = itemCount;
            document.getElementById('summary-items').textContent = itemCount;
            
            // Update shipping info if needed
            const shippingInfo = document.getElementById('shipping-info');
            if (shippingInfo) {
                if (subtotal < 499 && subtotal > 0) {
                    const needed = 499 - parseFloat(subtotal);
                    shippingInfo.innerHTML = `<i class="fas fa-info-circle"></i> Add ₹${needed.toFixed(2)} more to get FREE shipping`;
                    shippingInfo.style.display = 'block';
                } else {
                    shippingInfo.style.display = 'none';
                }
            }
        }
        
        // Recalculate summary from DOM (fallback)
        function recalculateSummary() {
            let subtotal = 0;
            let totalItems = 0;
            const itemTotalElements = document.querySelectorAll('.item-total');
            
            itemTotalElements.forEach(el => {
                const text = el.textContent.replace('₹', '').replace(/,/g, '');
                subtotal += parseFloat(text);
            });
            
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                totalItems += parseInt(input.value);
            });
            
            const shipping = (subtotal > 0 && subtotal < 499) ? 40 : 0;
            const total = subtotal + shipping;
            
            updateSummaryDisplay(subtotal, shipping, total, totalItems);
        }
        
        // Initialize button states
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize quantity button states
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                const currentValue = parseInt(input.value);
                const min = parseInt(input.min);
                const max = parseInt(input.max);
                
                const decreaseBtn = input.previousElementSibling;
                const increaseBtn = input.nextElementSibling;
                
                if (currentValue <= min) {
                    decreaseBtn.disabled = true;
                }
                if (currentValue >= max) {
                    increaseBtn.disabled = true;
                }
            });
        });
    </script>
</body>
</html>
<?php
// Don't close connection as it might be closed already by statements
// Let PHP handle the connection cleanup
?>