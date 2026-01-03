<?php
// nav.php - Navigation header
require_once 'config.php';

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update cart count from database if user is logged in
if (isset($_SESSION['user_id'])) {
    $conn = getDBConnection();
    
    // Get cart count
    $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartData = $result->fetch_assoc();
        $_SESSION['cart_count'] = $cartData['cart_count'] ?? 0;
        
        $stmt->close();
    }
    $conn->close();
} else {
    // For guests, use session cart
    if (!isset($_SESSION['cart_count'])) {
        $_SESSION['cart_count'] = 0;
    }
    if (!isset($_SESSION['cart_items'])) {
        $_SESSION['cart_items'] = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopEasy - Online Shopping</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-top {
            background-color: var(--dark);
            color: white;
            padding: 8px 0;
            font-size: 14px;
            display: none; /* Hidden on mobile */
        }

        .header-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-top-links {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-top-links a {
            color: white;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .header-top-links a:hover {
            color: var(--secondary);
        }

        .header-top-links a i {
            font-size: 12px;
        }

        .header-main {
            padding: 15px 0;
        }

        .header-main .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: fit-content;
        }

        .logo i {
            color: var(--secondary);
            font-size: 22px;
        }

        .logo span {
            color: var(--dark);
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark);
            cursor: pointer;
            padding: 5px;
            z-index: 1002;
        }

        /* Navigation */
        nav {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 16px;
            transition: var(--transition);
            padding: 8px 0;
            display: block;
            white-space: nowrap;
        }

        nav ul li a:hover,
        nav ul li a.active {
            color: var(--primary);
        }

        nav ul li a.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary);
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: fit-content;
        }

        /* Search Box */
        .search-box {
            position: relative;
            width: 200px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: var(--transition);
            background-color: var(--light);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.1);
            background-color: white;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
        }

        .search-box i:hover {
            color: var(--primary);
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
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
            white-space: nowrap;
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

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: #ff5252;
            border-color: #ff5252;
        }

        /* User Greeting */
        .user-greeting {
            font-weight: 500;
            white-space: nowrap;
        }

        .user-greeting span {
            color: var(--primary);
        }

        /* Cart Icon */
        .cart-icon {
            position: relative;
            text-decoration: none;
            color: var(--dark);
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: var(--transition);
        }

        .cart-icon:hover {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--primary);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Mobile Navigation Overlay */
        .mobile-nav-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mobile-nav-overlay.active {
            opacity: 1;
        }

        /* Mobile Navigation Panel */
        .mobile-nav-panel {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100%;
            background-color: white;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: right 0.3s ease;
            overflow-y: auto;
            padding: 80px 20px 30px;
        }

        .mobile-nav-panel.active {
            right: 0;
        }

        .mobile-nav-panel ul {
            display: flex;
            flex-direction: column;
            gap: 0;
            list-style: none;
        }

        .mobile-nav-panel ul li {
            border-bottom: 1px solid var(--border);
        }

        .mobile-nav-panel ul li:last-child {
            border-bottom: none;
        }

        .mobile-nav-panel ul li a {
            display: block;
            padding: 15px;
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            transition: var(--transition);
            border-radius: var(--radius);
        }

        .mobile-nav-panel ul li a:hover,
        .mobile-nav-panel ul li a.active {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--primary);
            padding-left: 20px;
        }

        .mobile-user-info {
            padding: 20px 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .mobile-user-info .user-greeting {
            font-size: 18px;
            text-align: center;
        }

        .mobile-search-box {
            margin-bottom: 20px;
        }

        .mobile-search-box input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        .mobile-cart-icon {
            position: relative;
            display: inline-block;
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (min-width: 1200px) {
            .header-top {
                display: block;
            }
        }

        @media (max-width: 1199px) {
            .header-main .container {
                gap: 15px;
            }
            
            .search-box {
                width: 180px;
            }
            
            nav ul {
                gap: 20px;
            }
        }

        @media (max-width: 992px) {
            .mobile-menu-btn {
                display: block;
                order: 2;
            }
            
            .logo {
                order: 1;
                flex: 1;
            }
            
            nav {
                display: none;
            }
            
            .header-actions {
                order: 3;
                gap: 10px;
            }
            
            .search-box {
                display: none;
            }
            
            .btn {
                padding: 8px 15px;
                font-size: 13px;
            }
            
            .user-greeting {
                display: none;
            }
            
            .header-main .container {
                gap: 10px;
            }
            
            .cart-icon {
                width: 36px;
                height: 36px;
                font-size: 20px;
            }
        }

        @media (max-width: 768px) {
            .header-main {
                padding: 10px 0;
            }
            
            .logo {
                font-size: 20px;
            }
            
            .logo i {
                font-size: 20px;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            .btn i {
                font-size: 12px;
            }
            
            .cart-icon {
                width: 34px;
                height: 34px;
                font-size: 18px;
            }
            
            .cart-count {
                width: 18px;
                height: 18px;
                font-size: 10px;
                top: -3px;
                right: -3px;
            }
        }

        @media (max-width: 576px) {
            .header-actions .btn {
                padding: 8px;
                min-width: auto;
            }
            
            .btn span {
                display: none;
            }
            
            .btn i {
                margin: 0;
                font-size: 14px;
            }
            
            .logo {
                font-size: 18px;
            }
            
            .logo i {
                font-size: 18px;
            }
            
            .logo span {
                display: none;
            }
            
            .mobile-nav-panel {
                width: 250px;
            }
            
            .container {
                padding: 0 15px;
            }
        }

        @media (max-width: 400px) {
            .logo i {
                font-size: 16px;
            }
            
            .logo {
                font-size: 16px;
            }
            
            .btn {
                padding: 6px 8px;
            }
            
            .cart-icon {
                width: 32px;
                height: 32px;
                font-size: 16px;
            }
        }

        /* Mobile Header Top Alternative */
        .mobile-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 10px;
        }

        .mobile-contact-info {
            font-size: 12px;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <header>
        <!-- Desktop Header Top -->
        <div class="header-top">
            <div class="container">
                <p>Free shipping on orders above ₹499 | Easy returns</p>
                <div class="header-top-links">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="profile.php"><i class="fas fa-user"></i> My Account</a>
                        <a href="orders.php"><i class="fas fa-box"></i> My Orders</a>
                        <!-- <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a> -->
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                    <a href="track-order.php"><i class="fas fa-truck"></i> Track Order</a>
                    <a href="help-center.php"><i class="fas fa-question-circle"></i> Help Center</a>
                </div>
            </div>
        </div>
        
        <!-- Main Header -->
        <div class="header-main">
            <div class="container">
                <!-- Logo -->
                <a href="index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i>
                    Shop<span>Easy</span>
                </a>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Desktop Navigation -->
                <nav>
                    <ul>
                        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Products</a></li>
                        <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Categories</a></li>
                        <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
                        <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">Profile</a></li>
                            <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">Orders</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Desktop Search -->
                    <div class="search-box">
                        <form action="products.php" method="GET" id="header-search">
                            <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <i class="fas fa-search" onclick="document.getElementById('header-search').submit()"></i>
                        </form>
                    </div>
                    
                    <!-- User Actions -->
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-outline" onclick="window.location.href='login.php'">
                            <i class="fas fa-sign-in-alt"></i> <span>Login</span>
                        </button>
                        <button class="btn btn-primary" onclick="window.location.href='register.php'">
                            <i class="fas fa-user-plus"></i> <span>Register</span>
                        </button>
                    <?php else: ?>
                        <div class="user-greeting">
                            Hi, <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                        </div>
                        <a href="profile.php" class="btn btn-outline" style="text-decoration: none;">
                            <i class="fas fa-user"></i> <span>Profile</span>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Cart -->
                    
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    
    <!-- Mobile Navigation Panel -->
    <div class="mobile-nav-panel" id="mobileNavPanel">
        <!-- Mobile User Info -->
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="mobile-user-info">
                <div class="user-greeting">
                    Welcome, <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Mobile Search -->
        <div class="mobile-search-box">
            <form action="products.php" method="GET" id="mobile-header-search">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </form>
        </div>
        
        <!-- Mobile Cart -->
        <a href="cart.php" class="mobile-cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count"><?php echo $_SESSION['cart_count'] ?? 0; ?></span>
        </a>
        
        <!-- Mobile Navigation -->
        <ul>
            <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Products</a></li>
            <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Categories</a></li>
            <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
            <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">Profile</a></li>
                <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">My Orders</a></li>
                <li><a href="wishlist.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'active' : ''; ?>">Wishlist</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
            
            <!-- Additional Mobile Links -->
            <li><a href="track-order.php"><i class="fas fa-truck"></i> Track Order</a></li>
            <li><a href="help-center.php"><i class="fas fa-question-circle"></i> Help Center</a></li>
            <li><a href="#"><i class="fas fa-star"></i> Offers</a></li>
            <li><a href="#"><i class="fas fa-phone"></i> Contact Support</a></li>
        </ul>
        
        <!-- Mobile Contact Info -->
        <div class="mobile-contact-info">
            <p><i class="fas fa-phone"></i> +91 98765 43210</p>
            <p><i class="fas fa-envelope"></i> support@shopeasy.com</p>
        </div>
    </div>

    <script>
        // Mobile Navigation Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileNavOverlay = document.getElementById('mobileNavOverlay');
        const mobileNavPanel = document.getElementById('mobileNavPanel');
        const body = document.body;

        function toggleMobileMenu() {
            mobileNavOverlay.classList.toggle('active');
            mobileNavPanel.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
            
            // Toggle body scroll
            body.style.overflow = body.style.overflow === 'hidden' ? '' : 'hidden';
            
            // Change menu icon
            const icon = mobileMenuBtn.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

        // Event Listeners
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        }

        if (mobileNavOverlay) {
            mobileNavOverlay.addEventListener('click', toggleMobileMenu);
        }

        // Close mobile menu when clicking on a link
        const mobileNavLinks = mobileNavPanel.querySelectorAll('a');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', () => {
                toggleMobileMenu();
            });
        });

        // Close mobile menu on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNavPanel.classList.contains('active')) {
                toggleMobileMenu();
            }
        });

        // Handle search form submission for mobile
        const mobileSearchForm = document.getElementById('mobile-header-search');
        const mobileSearchInput = mobileSearchForm.querySelector('input');
        
        mobileSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                mobileSearchForm.submit();
                toggleMobileMenu();
            }
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > 992 && mobileNavPanel.classList.contains('active')) {
                    toggleMobileMenu();
                }
            }, 250);
        });

        // Update cart count dynamically (example function)
        function updateCartCount(count) {
            const cartCounts = document.querySelectorAll('.cart-count');
            cartCounts.forEach(cartCount => {
                cartCount.textContent = count;
            });
        }

        // Example: Update cart count when adding items (you'll call this from your AJAX)
        // updateCartCount(5);
    </script>
</body>
</html>