<?php
// profile.php - User Profile Page
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Handle profile update
$update_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Get form data
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $city = sanitize($_POST['city']);
        $state = sanitize($_POST['state']);
        $zip_code = sanitize($_POST['zip_code']);
        $country = sanitize($_POST['country']);
        
        // Update user information
        $update_sql = "UPDATE users SET 
                      first_name = ?, 
                      last_name = ?, 
                      phone = ?, 
                      address = ?, 
                      city = ?, 
                      state = ?, 
                      zip_code = ?, 
                      country = ?, 
                      updated_at = CURRENT_TIMESTAMP 
                      WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        if ($stmt) {
            $stmt->bind_param("ssssssssi", 
                $first_name, $last_name, $phone, $address, 
                $city, $state, $zip_code, $country, $user_id
            );
            
            if ($stmt->execute()) {
                $update_message = "Profile updated successfully!";
                // Update session variables if needed
                $_SESSION['username'] = $_SESSION['username']; // Keep username
            } else {
                $error_message = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Database error. Please try again.";
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error_message = "Password must be at least 6 characters long.";
        } else {
            // Get current password hash
            $sql = "SELECT password_hash FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if ($user && password_verify($current_password, $user['password_hash'])) {
                // Update password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET password_hash = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $new_password_hash, $user_id);
                
                if ($stmt->execute()) {
                    $update_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password.";
                }
                $stmt->close();
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
    }
}

// Fetch user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Fetch user's orders
$orders_sql = "SELECT o.*, 
               COUNT(oi.id) as item_count,
               SUM(oi.total_price) as order_total
               FROM orders o
               LEFT JOIN order_items oi ON o.id = oi.order_id
               WHERE o.user_id = ?
               GROUP BY o.id
               ORDER BY o.created_at DESC
               LIMIT 10";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_stmt->close();

// Fetch user's wishlist count
$wishlist_sql = "SELECT COUNT(*) as wishlist_count FROM wishlist WHERE user_id = ?";
$wishlist_stmt = $conn->prepare($wishlist_sql);
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result();
$wishlist_data = $wishlist_result->fetch_assoc();
$wishlist_stmt->close();

// Fetch user's cart count
$cart_sql = "SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_data = $cart_result->fetch_assoc();
$cart_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ShopEasy</title>
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
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            --radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            line-height: 1.6;
            background-color: var(--light);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }

        .profile-header-content {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: var(--primary);
            box-shadow: var(--shadow);
        }

        .profile-info h1 {
            font-size: 32px;
            margin-bottom: 5px;
        }

        .profile-info p {
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .member-since {
            font-size: 14px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            border-radius: var(--radius);
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.orders {
            background-color: var(--primary);
        }

        .stat-icon.wishlist {
            background-color: var(--secondary);
        }

        .stat-icon.cart {
            background-color: var(--warning);
        }

        .stat-content h3 {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-content p {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Profile Content */
        .profile-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (max-width: 992px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }

        /* Profile Sections */
        .profile-section {
            background-color: white;
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: var(--primary);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
            transition: var(--transition);
            background-color: var(--light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.1);
            background-color: white;
        }

        .form-control:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        /* Buttons */
        .btn {
            padding: 12px 25px;
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #ff5252;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-block {
            width: 100%;
        }

        /* Messages */
        .message {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .message.error {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            padding: 15px;
            background-color: var(--light);
            color: var(--dark);
            font-weight: 600;
            border-bottom: 2px solid var(--border);
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
        }

        .orders-table tr:hover {
            background-color: rgba(247, 249, 252, 0.5);
        }

        .order-number {
            font-weight: 500;
            color: var(--primary);
        }

        .order-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: rgba(255, 209, 102, 0.1);
            color: #e6b800;
        }

        .status-confirmed {
            background-color: rgba(78, 205, 196, 0.1);
            color: var(--secondary);
        }

        .status-processing {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .status-shipped {
            background-color: rgba(147, 51, 234, 0.1);
            color: #9333ea;
        }

        .status-delivered {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success);
        }

        .status-cancelled {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--primary);
        }

        /* Quick Links */
        .quick-links {
            background-color: white;
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
        }

        .quick-links ul {
            list-style: none;
        }

        .quick-links li {
            margin-bottom: 10px;
        }

        .quick-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            text-decoration: none;
            color: var(--text);
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .quick-links a:hover {
            background-color: var(--light);
            color: var(--primary);
            padding-left: 20px;
        }

        .quick-links a i {
            width: 20px;
            text-align: center;
            color: var(--primary);
        }

        /* No Orders Message */
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .no-orders i {
            font-size: 48px;
            color: var(--border);
            margin-bottom: 20px;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-header-content {
                flex-direction: column;
                text-align: center;
            }

            .profile-info h1 {
                font-size: 24px;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .orders-table {
                display: block;
                overflow-x: auto;
            }

            .section-title {
                font-size: 20px;
            }

            .profile-section {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .stat-content h3 {
                font-size: 24px;
            }
        }

        /* Profile Picture Upload */
        .profile-picture-upload {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: var(--shadow);
            margin-bottom: 15px;
        }

        .upload-btn {
            display: inline-block;
            background-color: var(--light);
            color: var(--text);
            padding: 8px 20px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .upload-btn:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Account Settings */
        .account-settings {
            margin-top: 40px;
        }

        .danger-zone {
            background-color: rgba(255, 107, 107, 0.05);
            border: 1px solid rgba(255, 107, 107, 0.2);
            border-radius: var(--radius);
            padding: 20px;
            margin-top: 30px;
        }

        .danger-zone h4 {
            color: var(--primary);
            margin-bottom: 15px;
        }

        /* Order Details Button */
        .btn-sm {
            padding: 8px 15px;
            font-size: 13px;
        }

        /* Last Login Info */
        .last-login {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
        }

        /* Loading State */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include "nav.php"; ?>

    <!-- Profile Header -->
    <section class="profile-header">
        <div class="container">
            <div class="profile-header-content">
                <div class="profile-avatar">
                    <?php 
                    $initials = '';
                    if (!empty($user['first_name']) && !empty($user['last_name'])) {
                        $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                    } else {
                        $initials = strtoupper(substr($user['username'], 0, 2));
                    }
                    echo $initials;
                    ?>
                </div>
                <div class="profile-info">
                    <h1>
                        <?php 
                        if (!empty($user['first_name']) && !empty($user['last_name'])) {
                            echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                        } else {
                            echo htmlspecialchars($user['username']);
                        }
                        ?>
                    </h1>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="member-since">
                        <i class="far fa-calendar-alt"></i> 
                        Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                    </span>
                    <?php if ($user['last_login']): ?>
                        <div class="last-login">
                            <i class="far fa-clock"></i> 
                            Last login: <?php echo date('M d, Y H:i', strtotime($user['last_login'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Cards -->
    <div class="container">
        <div class="stats-cards">
            <div class="stat-card" onclick="window.location.href='orders.php'">
                <div class="stat-icon orders">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $orders_result->num_rows; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <!-- <div class="stat-card" onclick="window.location.href='wishlist.php'">
                <div class="stat-icon wishlist">
                    <i class="fas fa-heart"></i>
                </div> -->
                <!-- <div class="stat-content">
                    <h3><?php echo $wishlist_data['wishlist_count'] ?? 0; ?></h3>
                    <p>Wishlist Items</p>
                </div> -->
            </div>
            <div class="stat-card" onclick="window.location.href='cart.php'">
                <div class="stat-icon cart">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $cart_data['cart_count'] ?? 0; ?></h3>
                    <p>Cart Items</p>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($update_message)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $update_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <!-- Left Column: Profile Information and Orders -->
            <div class="left-column">
                <!-- Personal Information -->
                <div class="profile-section">
                    <h2 class="section-title">Personal Information</h2>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" 
                                       placeholder="Enter your first name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" 
                                       placeholder="Enter your last name">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       placeholder="Enter your phone number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" 
                                      placeholder="Enter your complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" 
                                       placeholder="Enter your city">
                            </div>
                            <div class="form-group">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" 
                                       placeholder="Enter your state">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Zip Code</label>
                                <input type="text" name="zip_code" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" 
                                       placeholder="Enter zip code">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <select name="country" class="form-control">
                                    <option value="India" <?php echo ($user['country'] ?? 'India') == 'India' ? 'selected' : ''; ?>>India</option>
                                    <option value="USA" <?php echo ($user['country'] ?? '') == 'USA' ? 'selected' : ''; ?>>United States</option>
                                    <option value="UK" <?php echo ($user['country'] ?? '') == 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="Canada" <?php echo ($user['country'] ?? '') == 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="Australia" <?php echo ($user['country'] ?? '') == 'Australia' ? 'selected' : ''; ?>>Australia</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <!-- Recent Orders -->
                <div class="profile-section">
                    <h2 class="section-title">Recent Orders</h2>
                    <?php if ($orders_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <span class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td>₹<?php echo number_format($order['order_total'] ?? $order['total_amount'], 2); ?></td>
                                            <!-- <td>
                                                <button class="btn btn-outline btn-sm" 
                                                        onclick="">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td> -->
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="btn btn-primary" onclick="window.location.href='orders.php'">
                                <i class="fas fa-list"></i> View All Orders
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="no-orders">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>No Orders Yet</h3>
                            <p>You haven't placed any orders yet. Start shopping now!</p>
                            <button class="btn btn-primary" onclick="window.location.href='products.php'" style="margin-top: 15px;">
                                <i class="fas fa-shopping-cart"></i> Start Shopping
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Quick Links and Password Change -->
            <div class="right-column">
                <!-- Password Change -->
                

                <!-- Quick Links -->
                <div class="quick-links">
                    <h2 class="section-title">Quick Links</h2>
                    <ul>
                        <li>
                            <a href="orders.php">
                                <i class="fas fa-shopping-bag"></i>
                                <span>My Orders</span>
                            </a>
                        </li>
                        <li>
                            <a href="wishlist.php">
                                <i class="fas fa-heart"></i>
                                <span>Wishlist</span>
                            </a>
                        </li>
                        <li>
                            <a href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Shopping Cart</span>
                            </a>
                        </li>
                       
                        <li>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Account Status -->
                <div class="profile-section account-settings">
                    <h2 class="section-title">Account Status</h2>
                    <div class="form-group">
                        <label class="form-label">Account Created</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?>" 
                               disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Updated</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('F j, Y, g:i a', strtotime($user['updated_at'])); ?>" 
                               disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <input type="text" class="form-control" 
                               value="<?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>" 
                               disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Profile form validation
            const profileForm = document.querySelector('form[action=""]');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    const phoneInput = this.querySelector('input[name="phone"]');
                    const zipInput = this.querySelector('input[name="zip_code"]');
                    
                    // Phone validation (basic)
                    if (phoneInput.value && !/^[0-9+\-\s]+$/.test(phoneInput.value)) {
                        e.preventDefault();
                        alert('Please enter a valid phone number.');
                        phoneInput.focus();
                        return;
                    }
                    
                    // Zip code validation (basic)
                    if (zipInput.value && !/^[0-9A-Z\s\-]+$/.test(zipInput.value)) {
                        e.preventDefault();
                        alert('Please enter a valid zip code.');
                        zipInput.focus();
                        return;
                    }
                });
            }
            
            // Password form validation
            const passwordForm = document.querySelector('form[action=""]:last-of-type');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const newPassword = this.querySelector('input[name="new_password"]');
                    const confirmPassword = this.querySelector('input[name="confirm_password"]');
                    
                    if (newPassword.value.length < 6) {
                        e.preventDefault();
                        alert('Password must be at least 6 characters long.');
                        newPassword.focus();
                        return;
                    }
                    
                    if (newPassword.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert('Passwords do not match.');
                        confirmPassword.focus();
                        return;
                    }
                });
            }
            
            // Auto-dismiss messages after 5 seconds
            setTimeout(function() {
                const messages = document.querySelectorAll('.message');
                messages.forEach(message => {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s';
                    setTimeout(() => message.remove(), 500);
                });
            }, 5000);
            
            // Add loading state to buttons on click
            const buttons = document.querySelectorAll('button[type="submit"]');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.classList.contains('loading')) {
                        this.classList.add('loading');
                        const originalText = this.innerHTML;
                        this.innerHTML = '<span class="loading"></span> Processing...';
                        
                        // Revert after 3 seconds (in case form submission fails)
                        setTimeout(() => {
                            this.classList.remove('loading');
                            this.innerHTML = originalText;
                        }, 3000);
                    }
                });
            });
        });
        
        // Profile picture upload simulation
        function uploadProfilePicture() {
            alert('Profile picture upload feature coming soon!');
        }
        
        // Logout confirmation
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>