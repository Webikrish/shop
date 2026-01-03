<?php
// index.php - Main home page
require_once 'config.php';

// Create database connection
$conn = getDBConnection();

// Handle newsletter subscription
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subscribe_email'])) {
    $email = sanitize($_POST['subscribe_email']);
    
    // Check if email already exists
    $checkSql = "SELECT id FROM subscribers WHERE email = ?";
    $stmt = $conn->prepare($checkSql);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Insert new subscriber
            $insertSql = "INSERT INTO subscribers (email) VALUES (?)";
            $stmt2 = $conn->prepare($insertSql);
            if ($stmt2) {
                $stmt2->bind_param("s", $email);
                
                if ($stmt2->execute()) {
                    $subscribeMessage = "Thank you for subscribing!";
                } else {
                    $subscribeMessage = "Error subscribing. Please try again.";
                }
                $stmt2->close();
            } else {
                $subscribeMessage = "Database error. Please try again.";
            }
        } else {
            $subscribeMessage = "You are already subscribed!";
        }
        $stmt->close();
    } else {
        $subscribeMessage = "Database connection error.";
    }
}

// Fetch categories from database
$categoriesQuery = "SELECT * FROM categories ORDER BY id";
$categoriesResult = $conn->query($categoriesQuery);

// Fetch featured products from database
$productsQuery = "SELECT p.*, c.name as category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_featured = 1 AND p.is_active = 1
                  ORDER BY p.created_at DESC 
                  LIMIT 6";
$productsResult = $conn->query($productsQuery);

// Fetch testimonials from database - check if table exists
$testimonialsQuery = "SHOW TABLES LIKE 'testimonials'";
$tableExists = $conn->query($testimonialsQuery);
$testimonialsResult = false;
$testimonialsData = [];

if ($tableExists && $tableExists->num_rows > 0) {
    $testimonialsQuery = "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3";
    $testimonialsResult = $conn->query($testimonialsQuery);
    if ($testimonialsResult) {
        while($row = $testimonialsResult->fetch_assoc()) {
            $testimonialsData[] = $row;
        }
        $testimonialsResult->data_seek(0); // Reset pointer
    }
}

// Store categories for footer use
$categoriesForFooter = [];
if ($categoriesResult && $categoriesResult->num_rows > 0) {
    while($row = $categoriesResult->fetch_assoc()) {
        $categoriesForFooter[] = $row;
    }
    $categoriesResult->data_seek(0); // Reset again for main display
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopEasy - Modern E-Commerce</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
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
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .hero-btns {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: white;
            color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .btn-outline:hover {
            background-color: white;
            color: var(--primary);
            transform: translateY(-2px);
        }
        
        /* Section Common */
        .section-title {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
            color: var(--dark);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: var(--primary);
            border-radius: 2px;
        }
        
        /* Categories Section */
        .categories {
            padding: 80px 0;
            background-color: var(--light);
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .category-card {
            background-color: white;
            padding: 30px;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--shadow);
            cursor: pointer;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .category-icon {
            font-size: 50px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .category-card h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        /* Featured Products */
        .featured-products {
            padding: 80px 0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .product-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--dark);
            min-height: 54px;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .current-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .original-price {
            font-size: 18px;
            color: var(--text-light);
            text-decoration: line-through;
        }
        
        .rating {
            color: var(--accent);
            margin-bottom: 15px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .product-actions .btn {
            flex: 1;
            padding: 10px;
            font-size: 14px;
        }
        
        /* Best Deals */
        .best-deals {
            padding: 80px 0;
            background-color: var(--light);
        }
        
        .deals-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .deal-card {
            background-color: white;
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
        }
        
        .deal-card:hover {
            transform: translateY(-5px);
        }
        
        .deal-icon {
            font-size: 40px;
            color: var(--primary);
        }
        
        .deal-content h3 {
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        /* Why Choose Us */
        .why-us {
            padding: 80px 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 50px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        /* Testimonials */
        .testimonials {
            padding: 80px 0;
            background-color: var(--light);
        }
        
        .testimonial-slider {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        
        .testimonial-slide {
            display: none;
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .testimonial-slide.active {
            display: block;
        }
        
        .testimonial-text {
            font-size: 18px;
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        
        .testimonial-rating {
            color: var(--accent);
            margin-bottom: 10px;
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--dark);
        }
        
        .slider-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--border);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .dot.active {
            background-color: var(--primary);
            transform: scale(1.2);
        }
        
        /* Newsletter */
        .newsletter {
            padding: 80px 0;
            text-align: center;
            background: linear-gradient(135deg, var(--dark) 0%, #3a3d5f 100%);
            color: white;
        }
        
        .newsletter h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        
        .newsletter p {
            max-width: 600px;
            margin: 0 auto 30px;
            font-size: 18px;
            opacity: 0.9;
        }
        
        .newsletter-form {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
        }
        
        .newsletter-form input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
        }
        
        .newsletter-form .btn {
            white-space: nowrap;
        }
        
        .message {
            padding: 10px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            max-width: 500px;
            margin: 0 auto 20px;
        }
        
        .message.success {
            background-color: rgba(6, 214, 160, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
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
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
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
            transition: all 0.3s;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .hero-btns {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-btns .btn {
                width: 200px;
            }
            
            .section-title {
                font-size: 30px;
            }
            
            .categories-grid,
            .products-grid,
            .deals-container,
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .newsletter-form {
                flex-direction: column;
            }
            
            .newsletter-form input,
            .newsletter-form .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .hero {
                padding: 60px 0;
            }
            
            .hero h1 {
                font-size: 28px;
            }
            
            .section-title {
                font-size: 24px;
            }
            
            .testimonial-slide {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include "nav.php"; ?>

    <!-- Hero Banner -->
    <section class="hero">
        <div class="container">
            <h1>Shop the Best Products at Best Prices</h1>
            <p>New Arrivals – Up to 50% Off. Discover amazing deals on electronics, fashion, home appliances and more!</p>
            <div class="hero-btns">
                <button class="btn btn-primary" onclick="window.location.href='products.php'">Shop Now</button>
                <button class="btn btn-outline" onclick="window.location.href='categories.php'">View Categories</button>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Shop By Category</h2>
            <div class="categories-grid">
                <?php if($categoriesResult && $categoriesResult->num_rows > 0): ?>
                    <?php while($category = $categoriesResult->fetch_assoc()): ?>
                        <?php 
                        // Set default icon based on category name
                        $icon_class = 'fas fa-box';
                        switch(strtolower($category['name'])) {
                            case 'electronics':
                                $icon_class = 'fas fa-laptop';
                                break;
                            case 'fashion':
                                $icon_class = 'fas fa-tshirt';
                                break;
                            case 'home & kitchen':
                                $icon_class = 'fas fa-home';
                                break;
                            case 'books':
                                $icon_class = 'fas fa-book';
                                break;
                            case 'beauty':
                                $icon_class = 'fas fa-spa';
                                break;
                            case 'sports':
                                $icon_class = 'fas fa-futbol';
                                break;
                        }
                        ?>
                        <div class="category-card" onclick="window.location.href='products.php?category=<?php echo $category['id']; ?>'">
                            <div class="category-icon">
                                <i class="<?php echo htmlspecialchars($icon_class); ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($category['description'] ?? 'Browse products', 0, 100)); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-categories">
                        <p>No categories found. Please add categories in the database.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-grid">
                <?php if($productsResult && $productsResult->num_rows > 0): ?>
                    <?php while($product = $productsResult->fetch_assoc()): ?>
                        <?php 
                        $discount = 0;
                        if($product['original_price'] > 0) {
                            $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                        }
                        ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-img"
                                 onerror="this.src='https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price">
                                    <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if($product['original_price'] > 0): ?>
                                        <span class="original-price">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                    <?php endif; ?>
                                    <?php if($discount > 0): ?>
                                        <span style="margin-left: auto; color: var(--success); font-weight: 500;"><?php echo $discount; ?>% OFF</span>
                                    <?php endif; ?>
                                </div>
                                <div class="rating">
                                    <?php 
                                    $rating = $product['rating'];
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                    
                                    for($i = 0; $i < $fullStars; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    
                                    <?php if($hasHalfStar): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php endif; ?>
                                    
                                    <?php for($i = 0; $i < $emptyStars; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                    
                                    <span style="margin-left: 5px; color: var(--text-light); font-size: 14px;">(<?php echo $rating; ?>)</span>
                                </div>
                                <div class="product-actions">
                                    <!-- <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button> -->
                                    <button class="btn btn-primary add-to-cart" onclick="window.location.href='product-details.php?id=<?php echo $product['id']; ?>'">View Details</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No featured products found. Please add products in the database.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Best Deals Section -->
    <section class="best-deals">
        <div class="container">
            <h2 class="section-title">Best Deals & Offers</h2>
            <div class="deals-container">
                <div class="deal-card">
                    <div class="deal-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="deal-content">
                        <h3>Today's Deals</h3>
                        <p>Flash sale with up to 70% off. Limited time offer!</p>
                    </div>
                </div>
                <div class="deal-card">
                    <div class="deal-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="deal-content">
                        <h3>Best Sellers</h3>
                        <p>Top rated products loved by customers</p>
                    </div>
                </div>
                <div class="deal-card">
                    <div class="deal-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="deal-content">
                        <h3>Limited Time Offers</h3>
                        <p>Special discounts ending soon</p>
                    </div>
                </div>
                <div class="deal-card">
                    <div class="deal-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="deal-content">
                        <h3>Festival Sale</h3>
                        <p>Celebrate with amazing discounts</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us">
        <div class="container">
            <h2 class="section-title">Why Choose Us</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Same day & next day delivery options available across India</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure Payment</h3>
                    <p>100% secure payment with SSL encryption & multiple options</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3>Easy Returns</h3>
                    <p>10-day easy return policy with pickup from your home</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round the clock customer support via chat, phone & email</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">Customer Reviews</h2>
            <div class="testimonial-slider">
                <?php if(!empty($testimonialsData)): ?>
                    <?php $slideIndex = 0; ?>
                    <?php foreach($testimonialsData as $testimonial): ?>
                        <div class="testimonial-slide <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
                            <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['review_text']); ?>"</p>
                            <div class="testimonial-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $testimonial['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <p class="testimonial-author"><?php echo htmlspecialchars($testimonial['customer_name']); ?></p>
                        </div>
                        <?php $slideIndex++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="testimonial-slide active">
                        <p class="testimonial-text">"Excellent service! Fast delivery and great quality products. Highly recommended!"</p>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-author">Rajesh Kumar</p>
                    </div>
                <?php endif; ?>
                
                <?php if(count($testimonialsData) > 1): ?>
                    <div class="slider-dots">
                        <?php for($i = 0; $i < count($testimonialsData); $i++): ?>
                            <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <h2>Get Updates & Special Offers</h2>
            <p>Subscribe to our newsletter and be the first to know about new arrivals, exclusive deals, and special promotions.</p>
            <?php if(isset($subscribeMessage)): ?>
                <div class="message success"><?php echo $subscribeMessage; ?></div>
            <?php endif; ?>
            <form class="newsletter-form" method="POST" action="">
                <input type="email" name="subscribe_email" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    </section>

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
                        <!-- <li><a href="privacy.php">Privacy Policy</a></li> -->
                        <li><a href="terms.php">Terms & Conditions</a></li>
                        <li><a href="help-center.php">FAQs</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3 class="footer-heading">Categories</h3>
                    <ul>
                        <?php if(!empty($categoriesForFooter)): ?>
                            <?php foreach($categoriesForFooter as $category): ?>
                                <li><a href="products.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                <p>&copy; <?php echo date('Y'); ?> ShopEasy. All rights reserved. | Designed for Modern E-Commerce</p>
                <!-- <p style="margin-top: 10px; font-size: 12px;">Total Products: 
                    <?php 
                    $totalProductsQuery = "SELECT COUNT(*) as total FROM products";
                    $totalResult = $conn->query($totalProductsQuery);
                    if ($totalResult) {
                        $total = $totalResult->fetch_assoc();
                        echo $total['total'] ?? 0;
                    } else {
                        echo "0";
                    }
                    ?> | Total Categories: 
                    <?php 
                    $totalCatQuery = "SELECT COUNT(*) as total FROM categories";
                    $catResult = $conn->query($totalCatQuery);
                    if ($catResult) {
                        $catTotal = $catResult->fetch_assoc();
                        echo $catTotal['total'] ?? 0;
                    } else {
                        echo "0";
                    }
                    ?>
                </p> -->
            </div>
        </div>
    </footer>

    <script>
        // Testimonial Slider
        const slides = document.querySelectorAll('.testimonial-slide');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;
        
        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            currentSlide = (n + slides.length) % slides.length;
            
            if (slides[currentSlide]) {
                slides[currentSlide].classList.add('active');
            }
            if (dots[currentSlide]) {
                dots[currentSlide].classList.add('active');
            }
        }
        
        if (dots.length > 0) {
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    showSlide(index);
                });
            });
        }
        
        // Auto slide every 5 seconds
        if (slides.length > 1) {
            setInterval(() => {
                showSlide(currentSlide + 1);
            }, 5000);
        }
        
        // Add to Cart Functionality
        const cartCount = document.querySelector('.cart-count');
        let cartItems = <?php echo isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0; ?>;
        
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) {
                const productId = e.target.getAttribute('data-id');
                
                // Send AJAX request to add to cart
                fetch('add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartItems = data.cart_count;
                        if(cartCount) cartCount.textContent = cartItems;
                        
                        // Show a quick confirmation
                        const btn = e.target;
                        const originalText = btn.textContent;
                        btn.textContent = 'Added!';
                        btn.style.backgroundColor = 'var(--success)';
                        
                        setTimeout(() => {
                            btn.textContent = originalText;
                            btn.style.backgroundColor = '';
                        }, 1000);
                    } else {
                        alert('Error adding to cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
        
        // Category Card Hover Effects
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                const icon = card.querySelector('.category-icon i');
                if (icon) {
                    icon.style.transform = 'scale(1.2)';
                    icon.style.transition = 'transform 0.3s ease';
                }
            });
            
            card.addEventListener('mouseleave', () => {
                const icon = card.querySelector('.category-icon i');
                if (icon) {
                    icon.style.transform = 'scale(1)';
                }
            });
        });
    </script>
    <?php 
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>