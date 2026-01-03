<?php
// about.php - About Us Page
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - ShopEasy</title>
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

        /* About Hero Section */
        .about-hero {
            background: linear-gradient(rgba(45, 48, 71, 0.9), rgba(45, 48, 71, 0.9)), 
                        url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 60px;
        }

        .about-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .about-hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* About Content */
        .about-section {
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

        /* Our Story */
        .our-story {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
            margin-bottom: 80px;
        }

        @media (max-width: 992px) {
            .our-story {
                grid-template-columns: 1fr;
            }
        }

        .story-content h3 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 20px;
        }

        .story-content p {
            margin-bottom: 20px;
            color: var(--text-light);
        }

        .story-image {
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .story-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: var(--transition);
        }

        .story-image:hover img {
            transform: scale(1.05);
        }

        /* Mission Vision */
        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        .mission-card, .vision-card {
            background-color: white;
            padding: 40px 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .mission-card:hover, .vision-card:hover {
            transform: translateY(-10px);
        }

        .mission-card {
            border-top: 4px solid var(--primary);
        }

        .vision-card {
            border-top: 4px solid var(--secondary);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            background-color: rgba(255, 107, 107, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 32px;
            color: var(--primary);
        }

        .vision-card .card-icon {
            background-color: rgba(78, 205, 196, 0.1);
            color: var(--secondary);
        }

        .mission-card h3, .vision-card h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--dark);
        }

        /* Values */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        .value-card {
            background-color: white;
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .value-card:hover {
            transform: translateY(-5px);
        }

        .value-icon {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .value-card h4 {
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--dark);
        }

        /* Team */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        .team-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .team-card:hover {
            transform: translateY(-10px);
        }

        .team-img {
            height: 250px;
            overflow: hidden;
        }

        .team-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .team-card:hover .team-img img {
            transform: scale(1.1);
        }

        .team-info {
            padding: 25px;
            text-align: center;
        }

        .team-info h4 {
            font-size: 20px;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .team-info p {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .team-social a {
            width: 36px;
            height: 36px;
            background-color: var(--light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            text-decoration: none;
            transition: var(--transition);
        }

        .team-social a:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Stats */
        .stats-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 80px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-item p {
            font-size: 18px;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            background-color: var(--dark);
            color: white;
            padding: 80px 0;
            text-align: center;
            border-radius: var(--radius);
            margin-bottom: 80px;
        }

        .cta-section h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .cta-section p {
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        @media (max-width: 576px) {
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
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
            
            .about-hero h1 {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .about-hero {
                padding: 70px 0;
            }
            
            .section-title h2 {
                font-size: 28px;
            }
            
            .story-content h3 {
                font-size: 26px;
            }
            
            .stat-item h3 {
                font-size: 36px;
            }
            
            .cta-section h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 576px) {
            .about-hero {
                padding: 50px 0;
            }
            
            .about-hero h1 {
                font-size: 28px;
            }
            
            .about-hero p {
                font-size: 16px;
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
                        <li><a href="about.php" class="active">About</a></li>
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

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>Our Story</h1>
            <p>Building India's favorite shopping destination with passion, innovation, and customer-centric values</p>
        </div>
    </section>

    <!-- Our Story Section -->
    <div class="container">
        <section class="our-story">
            <div class="story-content">
                <h3>Welcome to ShopEasy</h3>
                <p>Founded in 2015, ShopEasy began as a small startup with a big vision: to make online shopping accessible, affordable, and enjoyable for everyone in India. What started as a modest e-commerce platform has grown into one of India's most trusted online shopping destinations.</p>
                <p>Our journey has been fueled by innovation, customer trust, and a relentless focus on providing value. Today, we serve millions of customers across the country, offering a wide range of products from electronics to fashion, home essentials to beauty products.</p>
                <p>At ShopEasy, we believe that shopping should be an experience, not just a transaction. That's why we've built a platform that combines great products with exceptional service, seamless technology, and genuine care for our customers.</p>
            </div>
            <div class="story-image">
                <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="ShopEasy Story">
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="about-section">
            <div class="section-title">
                <h2>Our Mission & Vision</h2>
                <p>Driven by purpose, guided by vision</p>
            </div>
            
            <div class="mission-vision">
                <div class="mission-card">
                    <div class="card-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>To democratize online shopping in India by providing high-quality products at unbeatable prices, delivered with exceptional customer service and technological innovation.</p>
                    <p>We aim to make quality products accessible to every Indian household, regardless of location or economic background.</p>
                </div>
                
                <div class="vision-card">
                    <div class="card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>To become India's most loved and trusted online shopping platform, known for our customer-centric approach, innovative solutions, and positive impact on communities.</p>
                    <p>We envision a future where shopping is seamless, sustainable, and brings joy to every customer's life.</p>
                </div>
            </div>
        </section>

        <!-- Our Values -->
        <section class="about-section">
            <div class="section-title">
                <h2>Our Core Values</h2>
                <p>The principles that guide everything we do</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Customer First</h4>
                    <p>Every decision we make starts with our customers' needs and satisfaction.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Trust & Integrity</h4>
                    <p>We build relationships based on transparency, honesty, and reliability.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Innovation</h4>
                    <p>We continuously evolve to provide better solutions and experiences.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4>Sustainability</h4>
                    <p>We're committed to environmentally responsible practices.</p>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <h3>2M+</h3>
                        <p>Happy Customers</p>
                    </div>
                    <div class="stat-item">
                        <h3>50K+</h3>
                        <p>Products Available</p>
                    </div>
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Cities Served</p>
                    </div>
                    <div class="stat-item">
                        <h3>98.7%</h3>
                        <p>Customer Satisfaction</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Team -->
        <section class="about-section">
            <div class="section-title">
                <h2>Meet Our Leadership</h2>
                <p>The passionate minds behind ShopEasy's success</p>
            </div>
            
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-img">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="CEO">
                    </div>
                    <div class="team-info">
                        <h4>Rajesh Sharma</h4>
                        <p>Founder & CEO</p>
                        <p>15+ years in e-commerce</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-card">
                    <div class="team-img">
                        <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="COO">
                    </div>
                    <div class="team-info">
                        <h4>Priya Patel</h4>
                        <p>Chief Operations Officer</p>
                        <p>Supply chain expert</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-card">
                    <div class="team-img">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="CTO">
                    </div>
                    <div class="team-info">
                        <h4>Amit Kumar</h4>
                        <p>Chief Technology Officer</p>
                        <p>Tech innovation leader</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <h2>Join Our Growing Community</h2>
                <p>Be part of the ShopEasy family and experience shopping reimagined. Whether you're a customer, partner, or potential team member, we'd love to connect with you.</p>
                <div class="cta-buttons">
                    <button class="btn btn-primary" onclick="window.location.href='products.php'" style="padding: 15px 30px; font-size: 16px;">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </button>
                    <button class="btn btn-outline" onclick="window.location.href='contact.php'" style="padding: 15px 30px; font-size: 16px; background-color: transparent; color: white; border-color: white;">
                        <i class="fas fa-envelope"></i> Get In Touch
                    </button>
                </div>
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
                    <h3 class="footer-heading">Shop Categories</h3>
                    <ul>
                        <li><a href="products.php?category=1">Electronics</a></li>
                        <li><a href="products.php?category=2">Fashion</a></li>
                        <li><a href="products.php?category=3">Home & Kitchen</a></li>
                        <li><a href="products.php?category=4">Beauty</a></li>
                        <li><a href="products.php?category=5">Sports</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> ShopEasy. All rights reserved. | Building India's shopping future since 2015</p>
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
        
        // Stats Counter Animation
        const statsSection = document.querySelector('.stats-section');
        const statNumbers = document.querySelectorAll('.stat-item h3');
        let animated = false;
        
        function animateStats() {
            if (animated) return;
            
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                const suffix = stat.textContent.replace(/[0-9]/g, '');
                let count = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    count += increment;
                    if (count >= target) {
                        count = target;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(count) + suffix;
                }, 20);
            });
            
            animated = true;
        }
        
        // Intersection Observer for stats animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateStats();
                }
            });
        }, { threshold: 0.5 });
        
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>