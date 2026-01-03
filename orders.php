<?php
// orders.php - Order History Page
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];
$order_items = [];

try {
    // Fetch user's orders
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count, 
               SUM(oi.quantity * oi.price) as total_amount
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If we have orders, fetch items for the first order by default
    if (!empty($orders)) {
        $first_order_id = $orders[0]['id'];
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image_url
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$first_order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Error fetching orders: " . $e->getMessage();
}

// Handle AJAX request for order details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image_url, p.description
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return JSON response for AJAX
    echo json_encode($order_items);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - ShopEasy</title>
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
            --info: #118ab2;
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
        
        .container {
            max-width: 1200px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
        }
        
        .orders-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .orders-sidebar {
            flex: 1;
            min-width: 300px;
        }
        
        .orders-main {
            flex: 2;
            min-width: 300px;
        }
        
        .orders-card, .order-details-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
        }
        
        .orders-card h3, .order-details-card h3 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .orders-card h3 i, .order-details-card h3 i {
            color: var(--primary);
        }
        
        .order-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .order-item {
            padding: 20px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .order-item:hover {
            border-color: var(--primary);
            transform: translateX(5px);
        }
        
        .order-item.active {
            border-color: var(--primary);
            background-color: rgba(255, 107, 107, 0.05);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .order-number {
            font-weight: 600;
            color: var(--primary);
            font-size: 18px;
        }
        
        .order-date {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .status-processing {
            background-color: rgba(255, 209, 102, 0.2);
            color: #b38b00;
        }
        
        .status-shipped {
            background-color: rgba(17, 138, 178, 0.2);
            color: var(--info);
        }
        
        .status-delivered {
            background-color: rgba(6, 214, 160, 0.2);
            color: var(--success);
        }
        
        .status-cancelled {
            background-color: rgba(239, 71, 111, 0.2);
            color: var(--danger);
        }
        
        .order-summary {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed var(--border);
        }
        
        .order-summary div {
            text-align: center;
        }
        
        .summary-label {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-weight: 600;
            color: var(--dark);
        }
        
        .order-details-card {
            margin-bottom: 20px;
        }
        
        .order-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .order-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .items-list {
            margin-bottom: 30px;
        }
        
        .item-row {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }
        
        .item-row:hover {
            background-color: var(--light);
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: var(--radius);
            overflow: hidden;
            margin-right: 20px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .item-image .placeholder {
            font-size: 24px;
            color: #ccc;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: var(--text-light);
        }
        
        .item-quantity {
            color: var(--text);
            font-weight: 500;
        }
        
        .item-total {
            font-weight: 700;
            color: var(--dark);
        }
        
        .shipping-info, .payment-info {
            margin-top: 30px;
            padding: 20px;
            background-color: var(--light);
            border-radius: var(--radius);
        }
        
        .shipping-info h4, .payment-info h4 {
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(255, 107, 107, 0.2);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #05c08f;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 80px;
            color: var(--border);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: var(--text-light);
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        @media (max-width: 768px) {
            .orders-container {
                flex-direction: column;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .item-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .item-image {
                width: 100%;
                height: 150px;
                margin-right: 0;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="container">
        <h1 class="page-title">My Orders</h1>
        
        <?php if (!empty($error)): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: var(--radius); margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <div class="orders-card empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                <div class="action-buttons" style="justify-content: center;">
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="orders-container">
                <div class="orders-sidebar">
                    <div class="orders-card">
                        <h3><i class="fas fa-history"></i> Order History</h3>
                        <div class="order-list">
                            <?php foreach ($orders as $index => $order): ?>
                                <div class="order-item <?php echo $index === 0 ? 'active' : ''; ?>" data-order-id="<?php echo $order['id']; ?>">
                                    <div class="order-header">
                                        <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                                        <div class="order-date"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                                    </div>
                                    <div>
                                        <span class="order-status status-<?php echo strtolower($order['status'] ?? 'processing'); ?>">
                                            <?php echo htmlspecialchars($order['status'] ?? 'Processing'); ?>
                                        </span>
                                    </div>
                                    <div class="order-summary">
                                        <div>
                                            <div class="summary-label">Items</div>
                                            <div class="summary-value"><?php echo $order['item_count']; ?></div>
                                        </div>
                                        <div>
                                            <div class="summary-label">Total</div>
                                            <div class="summary-value">$<?php echo number_format($order['total_amount'], 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="orders-main">
                    <div class="order-details-card">
                        <h3><i class="fas fa-clipboard-list"></i> Order Details</h3>
                        <div id="order-details-content">
                            <?php if (!empty($orders)): ?>
                                <?php
                                $first_order = $orders[0];
                                $status_class = 'status-' . strtolower($first_order['status'] ?? 'processing');
                                ?>
                                <div class="order-details-header">
                                    <div>
                                        <h4>Order #<?php echo htmlspecialchars($first_order['order_number']); ?></h4>
                                        <span class="order-status <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($first_order['status'] ?? 'Processing'); ?>
                                        </span>
                                    </div>
                                    <div class="order-total">
                                        $<?php echo number_format($first_order['total_amount'], 2); ?>
                                    </div>
                                </div>
                                
                                <div class="items-list">
                                    <h4 style="margin-bottom: 15px;">Order Items</h4>
                                    <?php if (!empty($order_items)): ?>
                                        <?php foreach ($order_items as $item): ?>
                                            <div class="item-row">
                                                <div class="item-image">
                                                    <?php if (!empty($item['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                    <?php else: ?>
                                                        <div class="placeholder">
                                                            <i class="fas fa-box"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="item-info">
                                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                    <div class="item-price">$<?php echo number_format($item['price'], 2); ?> each</div>
                                                </div>
                                                <div class="item-quantity">
                                                    Qty: <?php echo $item['quantity']; ?>
                                                </div>
                                                <div class="item-total">
                                                    $<?php echo number_format($item['quantity'] * $item['price'], 2); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="text-align: center; color: var(--text-light); padding: 20px;">
                                            No items found for this order.
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="shipping-info">
                                    <h4><i class="fas fa-truck"></i> Shipping Information</h4>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-label">Address</div>
                                            <div class="info-value"><?php echo htmlspecialchars($first_order['shipping_address'] ?? '123 Main St, Anytown, USA'); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Shipping Method</div>
                                            <div class="info-value"><?php echo htmlspecialchars($first_order['shipping_method'] ?? 'Standard Shipping'); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Estimated Delivery</div>
                                            <?php 
                                            $order_date = new DateTime($first_order['order_date']);
                                            $delivery_date = clone $order_date;
                                            $delivery_date->modify('+5 days');
                                            ?>
                                            <div class="info-value"><?php echo $delivery_date->format('M d, Y'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="payment-info">
                                    <h4><i class="fas fa-credit-card"></i> Payment Information</h4>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-label">Payment Method</div>
                                            <div class="info-value"><?php echo htmlspecialchars($first_order['payment_method'] ?? 'Credit Card'); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Payment Status</div>
                                            <div class="info-value">
                                                <span class="order-status status-delivered">Paid</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Transaction ID</div>
                                            <div class="info-value"><?php echo htmlspecialchars($first_order['transaction_id'] ?? 'TXN-' . $first_order['order_number']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <a href="#" class="btn btn-success">
                                        <i class="fas fa-print"></i> Print Invoice
                                    </a>
                                    <a href="#" class="btn btn-outline">
                                        <i class="fas fa-redo"></i> Reorder
                                    </a>
                                    <a href="products.php" class="btn btn-primary">
                                        <i class="fas fa-shopping-bag"></i> Shop Again
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderItems = document.querySelectorAll('.order-item');
            
            orderItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all orders
                    orderItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked order
                    this.classList.add('active');
                    
                    const orderId = this.getAttribute('data-order-id');
                    
                    // Fetch order details via AJAX
                    fetch('orders.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'order_id=' + orderId
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Update the order details section with the new data
                        updateOrderDetails(orderId, data);
                    })
                    .catch(error => {
                        console.error('Error fetching order details:', error);
                    });
                });
            });
            
            function updateOrderDetails(orderId, items) {
                // Find the order data from the clicked order item
                const orderItem = document.querySelector(`.order-item[data-order-id="${orderId}"]`);
                const orderNumber = orderItem.querySelector('.order-number').textContent;
                const orderDate = orderItem.querySelector('.order-date').textContent;
                const orderStatus = orderItem.querySelector('.order-status').textContent;
                const orderStatusClass = orderItem.querySelector('.order-status').className.split(' ').find(c => c.startsWith('status-'));
                const orderTotal = orderItem.querySelector('.summary-value:last-child').textContent;
                
                // Create the order details HTML
                let itemsHtml = '';
                let itemsTotal = 0;
                
                if (items.length > 0) {
                    items.forEach(item => {
                        const itemTotal = item.quantity * item.price;
                        itemsTotal += itemTotal;
                        
                        itemsHtml += `
                            <div class="item-row">
                                <div class="item-image">
                                    ${item.image_url ? 
                                        `<img src="${item.image_url}" alt="${item.product_name}">` : 
                                        `<div class="placeholder"><i class="fas fa-box"></i></div>`
                                    }
                                </div>
                                <div class="item-info">
                                    <div class="item-name">${item.product_name}</div>
                                    <div class="item-price">$${parseFloat(item.price).toFixed(2)} each</div>
                                </div>
                                <div class="item-quantity">
                                    Qty: ${item.quantity}
                                </div>
                                <div class="item-total">
                                    $${itemTotal.toFixed(2)}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    itemsHtml = '<p style="text-align: center; color: var(--text-light); padding: 20px;">No items found for this order.</p>';
                }
                
                // Generate a mock shipping address based on order number
                const shippingAddresses = [
                    '123 Main St, Anytown, USA 12345',
                    '456 Oak Ave, Springfield, USA 67890',
                    '789 Pine Rd, Lakeside, USA 11223',
                    '321 Elm St, Riverdale, USA 44556'
                ];
                const addressIndex = parseInt(orderId) % shippingAddresses.length;
                
                // Generate a mock transaction ID
                const transactionId = 'TXN-' + orderNumber.replace('Order #', '') + '-' + orderId;
                
                // Update the order details content
                document.getElementById('order-details-content').innerHTML = `
                    <div class="order-details-header">
                        <div>
                            <h4>${orderNumber}</h4>
                            <span class="order-status ${orderStatusClass}">${orderStatus}</span>
                        </div>
                        <div class="order-total">
                            ${orderTotal}
                        </div>
                    </div>
                    
                    <div class="items-list">
                        <h4 style="margin-bottom: 15px;">Order Items</h4>
                        ${itemsHtml}
                    </div>
                    
                    <div class="shipping-info">
                        <h4><i class="fas fa-truck"></i> Shipping Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value">${shippingAddresses[addressIndex]}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Shipping Method</div>
                                <div class="info-value">Standard Shipping</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Estimated Delivery</div>
                                <div class="info-value">
                                    ${new Date(new Date(orderDate).getTime() + 5 * 24 * 60 * 60 * 1000).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-info">
                        <h4><i class="fas fa-credit-card"></i> Payment Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Payment Method</div>
                                <div class="info-value">Credit Card</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Payment Status</div>
                                <div class="info-value">
                                    <span class="order-status status-delivered">Paid</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Transaction ID</div>
                                <div class="info-value">${transactionId}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="#" class="btn btn-success">
                            <i class="fas fa-print"></i> Print Invoice
                        </a>
                        <a href="#" class="btn btn-outline">
                            <i class="fas fa-redo"></i> Reorder
                        </a>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Shop Again
                        </a>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>