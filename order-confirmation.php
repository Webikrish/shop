<?php
// order-confirmation.php - Order Confirmation Page
session_start();
require_once 'config.php';

if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header('Location: cart.php');
    exit();
}

// Clear the order success flag
unset($_SESSION['order_success']);

$order_number = $_SESSION['order_number'] ?? '';
unset($_SESSION['order_number']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - ShopEasy</title>
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
            text-align: center;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 80px auto;
            padding: 0 20px;
        }
        
        .confirmation-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 60px 40px;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 48px;
            color: white;
        }
        
        .confirmation-title {
            font-size: 36px;
            color: var(--dark);
            margin-bottom: 20px;
        }
        
        .confirmation-text {
            color: var(--text-light);
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .order-number {
            background: var(--light);
            padding: 15px 30px;
            border-radius: var(--radius);
            display: inline-block;
            margin-bottom: 30px;
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
            border: 2px dashed var(--border);
        }
        
        .confirmation-details {
            background: var(--light);
            padding: 30px;
            border-radius: var(--radius);
            margin: 40px 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            color: var(--text-light);
        }
        
        .detail-value {
            font-weight: 500;
            color: var(--dark);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        
        .btn {
            padding: 12px 30px;
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
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
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
        
        @media (max-width: 768px) {
            .confirmation-card {
                padding: 40px 20px;
            }
            
            .confirmation-title {
                font-size: 28px;
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
    
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="confirmation-title">Order Confirmed!</h1>
            
            <p class="confirmation-text">
                Thank you for your purchase. Your order has been successfully placed and is being processed.
            </p>
            
            <div class="order-number">
                Order #: <?php echo htmlspecialchars($order_number); ?>
            </div>
            
            <div class="confirmation-details">
                <h3 style="margin-bottom: 20px; color: var(--dark);">What's Next?</h3>
                <div class="detail-row">
                    <span class="detail-label">Order Processing</span>
                    <span class="detail-value">1-2 business days</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Shipping</span>
                    <span class="detail-value">3-5 business days</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Delivery</span>
                    <span class="detail-value">Standard delivery</span>
                </div>
            </div>
            
            <p style="color: var(--text-light); margin-bottom: 30px;">
                You will receive an email confirmation shortly with your order details and tracking information.
            </p>
            
            <div class="action-buttons">
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-clipboard-list"></i> View My Orders
                </a>
                <a href="products.php" class="btn btn-outline">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>