<?php
// terms.php - Terms & Conditions Page
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - ShopEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Use same CSS variables and base styles */
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            background-color: var(--light);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
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

        /* Terms Page Specific Styles */
        .terms-hero {
            background: linear-gradient(135deg, #2d3047 0%, #3a3e64 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 50px;
        }

        .terms-hero h1 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .terms-hero p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Terms Content */
        .terms-content {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 50px;
            margin-bottom: 80px;
        }

        .last-updated {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border);
        }

        .last-updated p {
            color: var(--text-light);
            font-size: 14px;
        }

        .last-updated .date {
            color: var(--primary);
            font-weight: 600;
        }

        /* Terms Navigation */
        .terms-nav {
            margin-bottom: 40px;
            background-color: var(--light);
            padding: 20px;
            border-radius: var(--radius);
        }

        .terms-nav h3 {
            margin-bottom: 15px;
            color: var(--dark);
        }

        .terms-nav ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .terms-nav li {
            margin-bottom: 10px;
        }

        .terms-nav a {
            color: var(--text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .terms-nav a:hover {
            background-color: var(--border);
            color: var(--primary);
        }

        .terms-nav i {
            color: var(--primary);
            font-size: 14px;
        }

        /* Terms Sections */
        .terms-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--border);
        }

        .terms-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .terms-section h2 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            display: inline-block;
        }

        .terms-section h3 {
            font-size: 20px;
            color: var(--dark);
            margin: 25px 0 15px;
        }

        .terms-section p {
            margin-bottom: 15px;
            color: var(--text-light);
        }

        .terms-section ul {
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .terms-section li {
            margin-bottom: 10px;
            color: var(--text-light);
        }

        .highlight-box {
            background-color: var(--light);
            padding: 20px;
            border-left: 4px solid var(--primary);
            margin: 20px 0;
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        .highlight-box p {
            margin-bottom: 0;
            color: var(--dark);
            font-weight: 500;
        }

        /* FAQ Accordion Styles */
        .faq-section {
            margin: 50px 0;
            padding: 30px;
            background-color: var(--light);
            border-radius: var(--radius);
        }

        .faq-section h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark);
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background-color: white;
            border-radius: var(--radius);
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .faq-question {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
            background-color: white;
        }

        .faq-question:hover {
            background-color: #f9f9f9;
        }

        .faq-question h3 {
            font-size: 18px;
            color: var(--dark);
            margin: 0;
            flex: 1;
        }

        .faq-icon {
            font-size: 20px;
            color: var(--primary);
            transition: var(--transition);
            margin-left: 15px;
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease, padding 0.3s ease;
        }

        .faq-answer p {
            padding: 20px 0;
            color: var(--text-light);
            border-top: 1px solid var(--border);
            margin: 0;
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

        /* Agreement */
        .agreement {
            background-color: var(--light);
            padding: 30px;
            border-radius: var(--radius);
            margin-top: 40px;
            text-align: center;
        }

        .agreement h3 {
            color: var(--dark);
            margin-bottom: 15px;
        }

        .agreement p {
            margin-bottom: 20px;
        }

        /* Contact Legal */
        .contact-legal {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--border);
        }

        .contact-legal h3 {
            color: var(--dark);
            margin-bottom: 15px;
        }

        /* Responsive */
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
        }

        @media (max-width: 768px) {
            .terms-hero h1 {
                font-size: 32px;
            }
            
            .terms-content {
                padding: 30px 20px;
            }
            
            .terms-nav ul {
                grid-template-columns: 1fr;
            }
            
            .terms-section h2 {
                font-size: 20px;
            }
            
            .terms-section h3 {
                font-size: 18px;
            }
            
            .faq-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms & Conditions</a>
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
                        <li><a href="contact.php">Contact</a></li>
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

    <!-- Terms Hero -->
    <section class="terms-hero">
        <div class="container">
            <h1>Terms & Conditions</h1>
            <p>Please read these terms carefully before using our website and services</p>
        </div>
    </section>

    <!-- Terms Content -->
    <div class="container">
        <div class="terms-content">
            <div class="last-updated">
                <p>Last updated: <span class="date"><?php echo date('F d, Y'); ?></span></p>
            </div>

            <!-- Quick Navigation -->
            <div class="terms-nav">
                <h3>Quick Navigation</h3>
                <ul>
                    <li><a href="#acceptance"><i class="fas fa-check-circle"></i> Acceptance of Terms</a></li>
                    <li><a href="#account"><i class="fas fa-user"></i> Account Registration</a></li>
                    <li><a href="#orders"><i class="fas fa-shopping-cart"></i> Orders & Payments</a></li>
                    <li><a href="#shipping"><i class="fas fa-truck"></i> Shipping & Delivery</a></li>
                    <li><a href="#returns"><i class="fas fa-exchange-alt"></i> Returns & Refunds</a></li>
                    <li><a href="#faq"><i class="fas fa-question-circle"></i> FAQ</a></li>
                </ul>
            </div>

            <!-- Terms Sections -->
            <section id="acceptance" class="terms-section">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using the ShopEasy website, mobile application, or services, you agree to be bound by these Terms & Conditions. If you do not agree with any part of these terms, you must not use our services.</p>
                
                <div class="highlight-box">
                    <p><i class="fas fa-exclamation-circle"></i> By using ShopEasy, you confirm that you are at least 18 years old or have parental consent to use our services.</p>
                </div>
            </section>

            <section id="account" class="terms-section">
                <h2>2. Account Registration</h2>
                <p>To access certain features of our services, you may need to create an account. You agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain the security of your password</li>
                    <li>Accept responsibility for all activities under your account</li>
                    <li>Notify us immediately of any unauthorized use</li>
                </ul>
                <p>We reserve the right to suspend or terminate accounts that violate these terms.</p>
            </section>

            <section id="orders" class="terms-section">
                <h2>3. Orders & Payments</h2>
                <h3>3.1 Order Placement</h3>
                <p>When you place an order, you are making an offer to purchase products. We reserve the right to accept or reject your order for any reason.</p>
                
                <h3>3.2 Pricing</h3>
                <p>All prices are in Indian Rupees (₹) and include applicable taxes. Prices are subject to change without notice.</p>
                
                <h3>3.3 Payment Methods</h3>
                <p>We accept various payment methods including credit/debit cards, UPI, net banking, and cash on delivery. Payment must be completed before order processing.</p>
                
                <div class="highlight-box">
                    <p><i class="fas fa-credit-card"></i> For cash on delivery orders, you must pay the exact amount to the delivery agent. We do not accept partial payments.</p>
                </div>
            </section>

            <section id="shipping" class="terms-section">
                <h2>4. Shipping & Delivery</h2>
                <p>We aim to process orders within 24 hours and deliver within the estimated timeframe provided during checkout.</p>
                
                <h3>4.1 Delivery Times</h3>
                <ul>
                    <li><strong>Standard Shipping:</strong> 5-7 business days</li>
                    <li><strong>Express Shipping:</strong> 2-3 business days</li>
                    <li><strong>Same-day Delivery:</strong> Available in select cities</li>
                </ul>
                
                <h3>4.2 Delivery Issues</h3>
                <p>If you are not available to receive delivery, our delivery partner will attempt delivery two more times. After three failed attempts, the order will be returned to us and a restocking fee may apply.</p>
            </section>

            <section id="returns" class="terms-section">
                <h2>5. Returns & Refunds</h2>
                <h3>5.1 Return Policy</h3>
                <p>Most products can be returned within 30 days of delivery if:</p>
                <ul>
                    <li>Product is unused and in original condition</li>
                    <li>Original packaging and tags are intact</li>
                    <li>Return request is initiated within return window</li>
                </ul>
                
                <h3>5.2 Non-Returnable Items</h3>
                <p>The following items cannot be returned:</p>
                <ul>
                    <li>Perishable goods</li>
                    <li>Intimate apparel</li>
                    <li>Personalized/customized products</li>
                    <li>Products without original packaging</li>
                </ul>
                
                <h3>5.3 Refund Process</h3>
                <p>Refunds are processed within 7-10 business days after we receive and inspect the returned item.</p>
            </section>

            <!-- FAQ Accordion Section -->
            <section id="faq" class="faq-section">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-container">
                    <div class="faq-item active">
                        <div class="faq-question">
                            <h3>What is your return policy?</h3>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>We offer a 30-day return policy for most products. Items must be unused, in original packaging with all tags attached. Some items like perishables and intimate apparel are non-returnable.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How can I track my order?</h3>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>You can track your order by logging into your account and visiting the "Order History" section. You'll receive tracking information via email and SMS once your order ships.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>What payment methods do you accept?</h3>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>We accept all major payment methods: Credit/Debit Cards (Visa, MasterCard, American Express), Net Banking, UPI, Wallet Payments (Paytm, PhonePe, Google Pay), and Cash on Delivery (available for orders up to ₹5,000).</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How long does shipping take?</h3>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Standard shipping: 5-7 business days<br>Express shipping: 2-3 business days<br>Same-day delivery: Available in select cities (order before 2 PM)<br>Free shipping on orders above ₹499</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Do you offer international shipping?</h3>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Currently, we only ship within India. However, we're working on expanding our services to international destinations. Please check back later for updates.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How do I reset my password?</h3>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Click on "Forgot Password" on the login page. Enter your registered email address and we'll send you a password reset link. If you don't receive the email within 5 minutes, check your spam folder or contact support.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Agreement -->
            <div class="agreement">
                <h3>Your Agreement</h3>
                <p>By using our website and services, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions.</p>
                <p>If you have any questions about these terms, please contact our legal department.</p>
            </div>

            <!-- Contact Legal -->
            <div class="contact-legal">
                <h3>Contact for Legal Inquiries</h3>
                <p><strong>Email:</strong> legal@shopeasy.com</p>
                <p><strong>Address:</strong> 123 Business Street, Mumbai, Maharashtra 400001</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
  

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
        
        // FAQ Accordion Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const icon = item.querySelector('.faq-icon');
                
                question.addEventListener('click', () => {
                    // Toggle active class on clicked item
                    const isActive = item.classList.contains('active');
                    
                    // Close all items
                    faqItems.forEach(otherItem => {
                        otherItem.classList.remove('active');
                    });
                    
                    // If it wasn't active, open it
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });
            
            // Open first FAQ item by default
            if (faqItems.length > 0) {
                faqItems[0].classList.add('active');
            }
        });
        
        // Smooth scrolling for terms navigation
        document.querySelectorAll('.terms-nav a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Close mobile menu if open
                    if (nav.classList.contains('active')) {
                        nav.classList.remove('active');
                        const icon = mobileMenuBtn.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                    
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Highlight current section in view
        const sections = document.querySelectorAll('.terms-section, .faq-section');
        const navLinks = document.querySelectorAll('.terms-nav a');
        
        window.addEventListener('scroll', () => {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (window.scrollY >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                    link.style.backgroundColor = 'var(--primary)';
                    link.style.color = 'white';
                } else {
                    link.style.backgroundColor = '';
                    link.style.color = '';
                }
            });
        });
        
        // Add print button
        const printButton = document.createElement('button');
        printButton.innerHTML = '<i class="fas fa-print"></i> Print Terms';
        printButton.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--radius);
            cursor: pointer;
            z-index: 1000;
            box-shadow: var(--shadow);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        `;
        
        printButton.addEventListener('click', () => {
            window.print();
        });
        
        document.body.appendChild(printButton);
        
        // Back to top button
        const backToTopButton = document.createElement('button');
        backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopButton.style.cssText = `
            position: fixed;
            bottom: 70px;
            right: 20px;
            background: var(--secondary);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1000;
            box-shadow: var(--shadow);
            font-size: 20px;
            display: none;
            align-items: center;
            justify-content: center;
        `;
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                backToTopButton.style.display = 'flex';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        document.body.appendChild(backToTopButton);
        
        // Add print styles
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                header, footer, .mobile-menu-btn, 
                .terms-nav, button[style*="fixed"] {
                    display: none !important;
                }
                
                .terms-content {
                    box-shadow: none !important;
                    padding: 0 !important;
                }
                
                .faq-section {
                    break-inside: avoid;
                }
                
                .faq-item .faq-answer {
                    max-height: none !important;
                    display: block !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>