<?php
session_start();

// Database connection configuration
$host = 'localhost';
$username = 'root';  // Change if you have a different username
$password = '';      // Change if you have a password
$database = 'shopeasy'; // Change to your database name

// Initialize variables
$order = null;
$order_items = [];
$error = '';
$success = '';
$order_number = '';
$email = '';

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fix: Check if 'email' key exists before accessing it
    $order_number = isset($_POST['order_number']) ? trim($_POST['order_number']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($order_number)) {
        $error = "Please enter your order number.";
    } elseif (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        try {
            // Query to get order details with user email verification
            $query = "
                SELECT o.*, u.email as user_email, u.name as user_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.order_number = ? AND u.email = ?
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $order_number, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $order = $result->fetch_assoc();
                
                // Get order items
                $items_query = "
                    SELECT oi.*, p.image, p.slug 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ";
                
                $items_stmt = $conn->prepare($items_query);
                $items_stmt->bind_param("i", $order['id']);
                $items_stmt->execute();
                $order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                $success = "Order found!";
            } else {
                $error = "No order found with the provided order number and email.";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error = "An error occurred while processing your request. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - ShopEasy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .track-order-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .card-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .card-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 40px;
        }

        .track-form {
            max-width: 500px;
            margin: 0 auto 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-track {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 18px 40px;
            font-size: 1.1rem;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-track:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efffef;
            color: #2a8;
            border: 1px solid #cfc;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 40px;
            display: <?php echo $order ? 'block' : 'none'; ?>;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e5e9;
        }

        .order-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
        }

        .order-status {
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-processing {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-shipped {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-delivered {
            background: #e8f5e9;
            color: #388e3c;
        }

        .status-cancelled {
            background: #ffebee;
            color: #d32f2f;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .info-box h3 {
            font-size: 1rem;
            color: #666;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box p {
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
            line-height: 1.6;
        }

        .order-items {
            margin-top: 40px;
        }

        .order-items h2 {
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: #333;
        }

        .items-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .items-table th {
            background: #f8f9fa;
            padding: 20px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e1e5e9;
        }

        .items-table td {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            vertical-align: middle;
        }

        .item-product {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .item-name {
            font-weight: 500;
            color: #333;
        }

        .total-amount {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-top: 30px;
            text-align: right;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .grand-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            padding-top: 15px;
            border-top: 2px solid #e1e5e9;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card-header {
                padding: 30px 20px;
            }
            
            .card-body {
                padding: 30px 20px;
            }
            
            .order-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .items-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="track-order-card">
            <div class="card-header">
                <h1>Track Your Order</h1>
                <p>Enter your order number and email to view order status and details</p>
            </div>
            
            <div class="card-body">
                <form method="POST" class="track-form">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="order_number">Order Number</label>
                        <input type="text" 
                               id="order_number" 
                               name="order_number" 
                               class="form-control" 
                               placeholder="e.g., ORD202601037923BD" 
                               value="<?php echo htmlspecialchars($order_number); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Enter the email used for ordering" 
                               value="<?php echo htmlspecialchars($email); ?>" 
                               required>
                    </div>
                    
                    <button type="submit" class="btn-track">Track Order</button>
                </form>
                
                <?php if ($order): ?>
                <div class="order-details">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div style="color: #666; margin-top: 5px;">
                                Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        <div class="order-status status-<?php echo htmlspecialchars($order['order_status']); ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-info-grid">
                        <div class="info-box">
                            <h3>Customer Information</h3>
                            <p>
                                <strong><?php echo htmlspecialchars($order['user_name']); ?></strong><br>
                                <?php echo htmlspecialchars($order['user_email']); ?>
                            </p>
                        </div>
                        
                        <div class="info-box">
                            <h3>Shipping Address</h3>
                            <?php 
                            $shipping_address = json_decode($order['shipping_address'], true);
                            if ($shipping_address): 
                            ?>
                                <p>
                                    <?php echo htmlspecialchars($shipping_address['name']); ?><br>
                                    <?php echo htmlspecialchars($shipping_address['address_line1']); ?><br>
                                    <?php if (!empty($shipping_address['address_line2'])): ?>
                                        <?php echo htmlspecialchars($shipping_address['address_line2']); ?><br>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($shipping_address['city']) . ', ' . 
                                               htmlspecialchars($shipping_address['state']) . ' ' . 
                                               htmlspecialchars($shipping_address['zip_code']); ?><br>
                                    <?php echo htmlspecialchars($shipping_address['country']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="info-box">
                            <h3>Payment Information</h3>
                            <p>
                                <strong>Method:</strong> <?php echo strtoupper($order['payment_method']); ?><br>
                                <strong>Status:</strong> 
                                <span style="color: <?php echo $order['payment_status'] == 'paid' ? '#388e3c' : '#f57c00'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h2>Order Items</h2>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="item-product">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                     class="item-image">
                                            <?php endif; ?>
                                            <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        </div>
                                    </td>
                                    <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>₹<?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="total-amount">
                            <div class="amount-row">
                                <span>Subtotal:</span>
                                <span>₹<?php echo number_format($order['total_amount'] - $order['shipping_amount'], 2); ?></span>
                            </div>
                            <div class="amount-row">
                                <span>Shipping:</span>
                                <span>₹<?php echo number_format($order['shipping_amount'], 2); ?></span>
                            </div>
                            <div class="amount-row grand-total">
                                <span>Grand Total:</span>
                                <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>