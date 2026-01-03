<?php
// contact.php - Contact Us Page
session_start();
require_once 'config.php';

// Handle contact form submission
$success_message = '';
$error_message = '';
$name = $email = $subject = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (empty($errors)) {
        // Save to database (if you want to store contact messages)
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success_message = 'Thank you! Your message has been sent successfully. We will get back to you soon.';
            // Clear form
            $name = $email = $subject = $message = '';
        } else {
            $error_message = 'Sorry, there was an error sending your message. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
        
        // Alternatively, you can send an email
        // mail('support@shopeasy.com', $subject, $message, "From: $email");
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - ShopEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --accent: #ffd166;
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
            background-color: var(--light);
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
        }

        .header-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-top-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }

        .header-main {
            padding: 15px 0;
        }

        .header-main .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 8px;
            color: var(--secondary);
        }

        .logo span {
            color: var(--dark);
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 30px;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        nav ul li a:hover {
            color: var(--primary);
        }

        nav ul li a.active {
            color: var(--primary);
        }

        nav ul li a.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
        }

        .search-box {
            position: relative;
            margin-right: 20px;
        }

        .search-box input {
            padding: 10px 15px;
            padding-right: 40px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            width: 250px;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(78, 205, 196, 0.2);
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Poppins', sans-serif;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            margin-right: 10px;
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #ff5252;
        }

        .cart-icon {
            position: relative;
            margin-left: 20px;
            font-size: 22px;
            color: var(--dark);
            cursor: pointer;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wishlist-icon {
            position: relative;
            margin-left: 15px;
            font-size: 22px;
            color: var(--dark);
            cursor: pointer;
        }

        .wishlist-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--secondary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-menu-btn {
            display: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--dark);
        }

        /* Contact Hero Section */
        .contact-hero {
            background: linear-gradient(rgba(255, 107, 107, 0.9), rgba(78, 205, 196, 0.9)), 
                        url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 60px;
        }

        .contact-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .contact-hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Contact Content */
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 80px;
        }

        @media (max-width: 992px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }

        /* Contact Info */
        .contact-info-section {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
        }

        .contact-info-section h2 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .contact-info-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary);
        }

        .contact-details {
            display: flex;
            flex-direction: column;
            gap: 25px;
            margin-bottom: 40px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(255, 107, 107, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 20px;
            color: var(--primary);
        }

        .contact-item:nth-child(2) .contact-icon {
            background-color: rgba(78, 205, 196, 0.1);
            color: var(--secondary);
        }

        .contact-item:nth-child(3) .contact-icon {
            background-color: rgba(255, 209, 102, 0.1);
            color: var(--accent);
        }

        .contact-text h4 {
            font-size: 18px;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .contact-text p {
            color: var(--text-light);
        }

        .contact-text a {
            color: var(--text);
            text-decoration: none;
            transition: var(--transition);
        }

        .contact-text a:hover {
            color: var(--primary);
        }

        /* Business Hours */
        .business-hours {
            margin-top: 40px;
        }

        .business-hours h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .hours-list {
            list-style: none;
        }

        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .hours-list li:last-child {
            border-bottom: none;
        }

        .day {
            font-weight: 500;
            color: var(--dark);
        }

        .time {
            color: var(--text-light);
        }

        /* Contact Form */
        .contact-form-section {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
        }

        .contact-form-section h2 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .contact-form-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--secondary);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(78, 205, 196, 0.2);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 25px;
        }

        .alert-success {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success);
            border: 1px solid rgba(6, 214, 160, 0.3);
        }

        .alert-error {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--primary);
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        /* FAQ Section */
        .faq-section {
            margin-bottom: 80px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 36px;
            color: var(--dark);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
        }

        .section-title p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 15px;
            overflow: hidden;
        }

        .faq-question {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .faq-question:hover {
            background-color: var(--light);
        }

        .faq-question h4 {
            font-size: 18px;
            color: var(--dark);
            margin: 0;
        }

        .faq-icon {
            font-size: 20px;
            color: var(--primary);
            transition: var(--transition);
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-answer p {
            padding: 20px 0;
            color: var(--text-light);
            border-top: 1px solid var(--border);
        }

        .faq-item.active .faq-question {
            background-color: var(--light);
        }

        .faq-item.active .faq-icon {
            transform: rotate(45deg);
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
        }

        /* Map Section */
        .map-section {
            margin-bottom: 80px;
        }

        .map-container {
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            height: 400px;
        }

        /* Footer */
        footer {
            background-color: #1a1c2e;
            color: white;
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            display: block;
        }

        .footer-about p {
            margin-bottom: 20px;
            color: #aaa;
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #2a2c3e;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-icons a:hover {
            background-color: var(--primary);
        }

        .footer-heading {
            font-size: 20px;
            margin-bottom: 25px;
            color: white;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--primary);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links ul li {
            margin-bottom: 12px;
        }

        .footer-links ul li a {
            color: #aaa;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links ul li a:hover {
            color: var(--primary);
            padding-left: 5px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #aaa;
        }

        .contact-item i {
            color: var(--secondary);
            margin-top: 5px;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #2a2c3e;
            color: #aaa;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .mobile-menu-btn {
                display: block;
            }
            
            nav {
                position: fixed;
                top: 0;
                left: -100%;
                width: 80%;
                height: 100vh;
                background-color: white;
                box-shadow: var(--shadow);
                transition: var(--transition);
                z-index: 1001;
                padding: 80px 30px 30px;
            }
            
            nav.active {
                left: 0;
            }
            
            nav ul {
                flex-direction: column;
            }
            
            nav ul li {
                margin: 0 0 20px 0;
            }
            
            .header-actions {
                flex: 1;
                justify-content: flex-end;
            }
            
            .search-box input {
                width: 150px;
            }
            
            .contact-hero h1 {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .contact-hero {
                padding: 70px 0;
            }
            
            .contact-hero h1 {
                font-size: 32px;
            }
            
            .contact-info-section,
            .contact-form-section {
                padding: 30px;
            }
            
            .section-title h2 {
                font-size: 28px;
            }
            
            .contact-info-section h2,
            .contact-form-section h2 {
                font-size: 26px;
            }
        }

        @media (max-width: 576px) {
            .contact-hero {
                padding: 50px 0;
            }
            
            .contact-hero h1 {
                font-size: 28px;
            }
            
            .contact-hero p {
                font-size: 16px;
            }
            
            .contact-info-section,
            .contact-form-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="header-top">
            <div class="container">
                <p>Free shipping on orders above ₹499 | Easy returns</p>
                <div class="header-top-links">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="profile.php">My Account</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                    <a href="#">Track Order</a>
                    <a href="#">Help Center</a>
                </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <a href="index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i>
                    Shop<span>Easy</span>
                </a>
                
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
                
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="categories.php">Categories</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php" class="active">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="products.php" method="GET" id="header-search">
                            <input type="text" name="search" placeholder="Search for products...">
                            <i class="fas fa-search" onclick="document.getElementById('header-search').submit()"></i>
                        </form>
                    </div>
                    
                    <div class="header-icons">
                        <?php if(!isset($_SESSION['user_id'])): ?>
                            <button class="btn btn-outline" onclick="window.location.href='login.php'">Login</button>
                            <button class="btn btn-primary" onclick="window.location.href='register.php'">Register</button>
                        <?php else: ?>
                            <span style="margin-right: 10px;">Hi, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span>
                        <?php endif; ?>
                        
                        <div class="wishlist-icon" onclick="window.location.href='wishlist.php'">
                            <i class="fas fa-heart"></i>
                            <span class="wishlist-count">
                                <?php 
                                if(isset($_SESSION['wishlist_count'])) {
                                    echo htmlspecialchars($_SESSION['wishlist_count']);
                                } else {
                                    echo '0';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="cart-icon" onclick="window.location.href='cart.php'">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">
                                <?php 
                                if(isset($_SESSION['cart_count'])) {
                                    echo htmlspecialchars($_SESSION['cart_count']);
                                } else {
                                    echo '0';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contact Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1>Get In Touch</h1>
            <p>We're here to help! Contact us for any queries, feedback, or support</p>
        </div>
    </section>

    <!-- Contact Content -->
    <div class="container">
        <div class="contact-container">
            <!-- Contact Info -->
            <div class="contact-info-section">
                <h2>Contact Information</h2>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Our Office</h4>
                            <p>
                                123 Business Street,<br>
                                Mumbai, Maharashtra 400001<br>
                                India
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Phone Numbers</h4>
                            <p>
                                <a href="tel:+919876543210">+91 98765 43210</a><br>
                                <a href="tel:+912212345678">+91 22 1234 5678</a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email Address</h4>
                            <p>
                                <a href="mailto:support@shopeasy.com">support@shopeasy.com</a><br>
                                <a href="mailto:sales@shopeasy.com">sales@shopeasy.com</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="business-hours">
                    <h3>Business Hours</h3>
                    <ul class="hours-list">
                        <li>
                            <span class="day">Monday - Friday</span>
                            <span class="time">9:00 AM - 8:00 PM</span>
                        </li>
                        <li>
                            <span class="day">Saturday</span>
                            <span class="time">10:00 AM - 6:00 PM</span>
                        </li>
                        <li>
                            <span class="day">Sunday</span>
                            <span class="time">11:00 AM - 5:00 PM</span>
                        </li>
                    </ul>
                </div>
                
                <div style="margin-top: 40px;">
                    <h3>Follow Us</h3>
                    <div class="social-icons" style="margin-top: 15px;">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form-section">
                <h2>Send Us a Message</h2>
                
                <?php if($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="contact.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message *</label>
                        <textarea id="message" name="message" required><?php echo htmlspecialchars($message); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department">
                            <option value="">Select Department</option>
                            <option value="customer_service">Customer Service</option>
                            <option value="sales">Sales</option>
                            <option value="technical">Technical Support</option>
                            <option value="billing">Billing</option>
                            <option value="partnership">Partnership</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="padding: 15px 30px; font-size: 16px; width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item active">
                    <div class="faq-question">
                        <h4>What are your shipping options and delivery times?</h4>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>We offer standard shipping (5-7 business days), express shipping (2-3 business days), and same-day delivery in select cities. Shipping is free on orders above ₹499. You can track your order in real-time through your account dashboard.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>What is your return and refund policy?</h4>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>We offer a 30-day return policy for most products. Items must be unused, in original packaging with tags attached. Refunds are processed within 7-10 business days after we receive the returned item. Some products like perishables and personalized items are non-returnable.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>How can I track my order?</h4>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>You can track your order by logging into your account and visiting the "Order History" section. We also send regular email and SMS updates with tracking links. For any issues, contact our customer support team.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Do you offer international shipping?</h4>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Currently, we only ship within India. However, we're working on expanding our services to international destinations. Stay tuned for updates!</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>How can I contact customer support?</h4>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>You can contact us through phone (+91 98765 43210), email (support@shopeasy.com), or the contact form on this page. Our customer support team is available Monday to Friday, 9 AM to 8 PM, and on weekends from 10 AM to 6 PM.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Map Section -->
        <section class="map-section">
            <div class="section-title">
                <h2>Find Our Office</h2>
                <p>Visit us at our headquarters in Mumbai</p>
            </div>
            
            <div class="map-container">
                <!-- Embed Google Map -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3771.7552468202027!2d72.82766931577772!3d19.055829458577245!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7c96a34dc4401%3A0x3ffc07e83942b13f!2sMumbai%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1679996821233!5m2!1sen!2sin" 
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <a href="index.php" class="footer-logo">ShopEasy</a>
                    <p>India's favorite online shopping destination for quality products at affordable prices. We deliver happiness to your doorstep.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms & Conditions</a></li>
                        <li><a href="faq.php">FAQs</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3 class="footer-heading">Customer Support</h3>
                    <ul>
                        <li><a href="track-order.php">Track Your Order</a></li>
                        <li><a href="returns.php">Returns & Refunds</a></li>
                        <li><a href="shipping.php">Shipping Policy</a></li>
                        <li><a href="size-guide.php">Size Guide</a></li>
                        <li><a href="help-center.php">Help Center</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3 class="footer-heading">Contact Info</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <p>123 Business Street, Mumbai, Maharashtra 400001</p>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <p>+91 98765 43210</p>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <p>support@shopeasy.com</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> ShopEasy. All rights reserved. | Need help? Call us at +91 98765 43210</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const nav = document.querySelector('nav');
        
        if(mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                nav.classList.toggle('active');
                const icon = mobileMenuBtn.querySelector('i');
                if(icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (mobileMenuBtn && nav && !nav.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                nav.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // FAQ Toggle
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            const icon = item.querySelector('.faq-icon');
            
            question.addEventListener('click', () => {
                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        });
        
        // Form validation
        const contactForm = document.querySelector('form');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const subject = document.getElementById('subject').value.trim();
                const message = document.getElementById('message').value.trim();
                
                let valid = true;
                
                // Clear previous error messages
                document.querySelectorAll('.error-message').forEach(el => el.remove());
                
                if (!name) {
                    showError('name', 'Name is required');
                    valid = false;
                }
                
                if (!email) {
                    showError('email', 'Email is required');
                    valid = false;
                } else if (!isValidEmail(email)) {
                    showError('email', 'Please enter a valid email address');
                    valid = false;
                }
                
                if (!subject) {
                    showError('subject', 'Subject is required');
                    valid = false;
                }
                
                if (!message) {
                    showError('message', 'Message is required');
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                }
            });
        }
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const error = document.createElement('div');
            error.className = 'error-message';
            error.style.color = 'var(--primary)';
            error.style.fontSize = '14px';
            error.style.marginTop = '5px';
            error.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            field.parentNode.appendChild(error);
        }
        
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Auto-hide success message after 5 seconds
        const successMessage = document.querySelector('.alert-success');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>