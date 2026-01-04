<?php
// products.php - Dynamic Products Page
session_start();
require_once 'config.php';

// Initialize variables
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Create database connection
$conn = getDBConnection();

// Fetch all categories for filter dropdown
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);

// Build the main query with filters
$baseQuery = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE 1=1";

$params = [];
$types = "";

// Apply search filter
if (!empty($search)) {
    $baseQuery .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Apply category filter
if ($category_id > 0) {
    $baseQuery .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

// Apply sorting
$orderBy = "";
switch ($sort) {
    case 'price_low':
        $orderBy = " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $orderBy = " ORDER BY p.price DESC";
        break;
    case 'rating':
        $orderBy = " ORDER BY p.rating DESC";
        break;
    case 'name':
        $orderBy = " ORDER BY p.name ASC";
        break;
    case 'discount':
        $orderBy = " ORDER BY ((p.original_price - p.price) / p.original_price * 100) DESC";
        break;
    default: // 'newest'
        $orderBy = " ORDER BY p.created_at DESC";
        break;
}

// Get total count for pagination (without ORDER BY for count)
$countQuery = "SELECT COUNT(*) as total FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               WHERE 1=1";
               
if (!empty($search)) {
    $countQuery .= " AND (p.name LIKE ? OR p.description LIKE ?)";
}

if ($category_id > 0) {
    $countQuery .= " AND p.category_id = ?";
}

// Prepare count query
if (!empty($params)) {
    $countStmt = $conn->prepare($countQuery);
    if ($countStmt) {
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        if ($countResult) {
            $totalData = $countResult->fetch_assoc();
            $totalProducts = $totalData['total'];
        } else {
            $totalProducts = 0;
        }
        $countStmt->close();
    } else {
        $totalProducts = 0;
        echo "Error preparing count query: " . $conn->error;
    }
} else {
    $countResult = $conn->query($countQuery);
    if ($countResult) {
        $totalData = $countResult->fetch_assoc();
        $totalProducts = $totalData['total'];
    } else {
        $totalProducts = 0;
        echo "Error in count query: " . $conn->error;
    }
}

// Build final query with sorting
$query = $baseQuery . $orderBy . " LIMIT ? OFFSET ?";

// Add limit and offset parameters
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Prepare and execute the main query
$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $productsResult = $stmt->get_result();
    
    if (!$productsResult) {
        echo "Error in main query: " . $stmt->error;
        $productsResult = false;
    }
} else {
    echo "Error preparing main query: " . $conn->error;
    $productsResult = false;
}

// Calculate total pages
$totalPages = ceil($totalProducts / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - ShopEasy</title>
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

        .mobile-menu-btn {
            display: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--dark);
        }

        /* Products Page Specific Styles */
        .products-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .products-hero h1 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .products-hero p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Filters and Controls */
        .products-controls {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        .control-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }

        .control-group select,
        .control-group input {
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: var(--transition);
        }

        .control-group select:focus,
        .control-group input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(78, 205, 196, 0.2);
        }

        .filter-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .filter-btn:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
        }

        .reset-btn {
            background-color: var(--light);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 12px 25px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .reset-btn:hover {
            background-color: var(--border);
        }

        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }

        .results-count {
            font-size: 16px;
            color: var(--text-light);
        }

        .results-count strong {
            color: var(--primary);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .product-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--primary);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }

        .product-badge.featured {
            background-color: var(--accent);
            color: var(--dark);
        }

        .product-badge.discount {
            background-color: var(--success);
        }

        .product-badge.out-of-stock {
            background-color: var(--text-light);
        }

        .product-img-container {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 20px;
        }

        .product-category {
            font-size: 12px;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .product-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--dark);
            line-height: 1.4;
            min-height: 50px;
        }

        .product-description {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 15px;
            line-height: 1.5;
            min-height: 42px;
        }

        .product-price {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .current-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .original-price {
            font-size: 16px;
            color: var(--text-light);
            text-decoration: line-through;
        }

        .discount-percent {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .rating-stock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .rating {
            color: var(--accent);
            font-size: 14px;
        }

        .stock-info {
            font-size: 12px;
            color: var(--text-light);
        }

        .stock-info.in-stock {
            color: var(--success);
        }

        .stock-info.low-stock {
            color: #ffa500;
        }

        .stock-info.out-of-stock {
            color: var(--primary);
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .product-actions .btn {
            flex: 1;
            padding: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-add-to-cart {
            background-color: var(--primary);
            color: white;
        }

        .btn-add-to-cart:hover {
            background-color: #ff5252;
        }

        .btn-view-details {
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-view-details:hover {
            background-color: var(--light);
            border-color: var(--text-light);
        }

        /* No Products Message */
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .no-products i {
            font-size: 60px;
            color: var(--border);
            margin-bottom: 20px;
            display: block;
        }

        .no-products h3 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .no-products p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 50px 0;
        }

        .pagination a,
        .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .pagination a {
            background-color: white;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .pagination a:hover {
            background-color: var(--light);
            border-color: var(--text-light);
        }

        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .disabled {
            background-color: var(--light);
            color: var(--text-light);
            cursor: not-allowed;
            border-color: var(--border);
        }

        .pagination .prev-next {
            width: auto;
            padding: 0 15px;
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
        @media (max-width: 1200px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

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
            
            .products-hero h1 {
                font-size: 32px;
            }
            
            .controls-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .results-info {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
            
            .product-title,
            .product-description {
                min-height: auto;
                height: auto;
            }
        }

        @media (max-width: 576px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .products-hero {
                padding: 40px 0;
            }
            
            .products-hero h1 {
                font-size: 28px;
            }
            
            .products-controls {
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
                        <li><a href="products.php" class="active">Products</a></li>
                        <li><a href="categories.php">Categories</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="products.php" method="GET" id="header-search">
                            <input type="text" name="search" placeholder="Search for products..." value="<?php echo htmlspecialchars($search); ?>">
                            <i class="fas fa-search" onclick="document.getElementById('header-search').submit()"></i>
                        </form>
                    </div>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-outline" onclick="window.location.href='login.php'">Login</button>
                        <button class="btn btn-primary" onclick="window.location.href='register.php'">Register</button>
                    <?php else: ?>
                        <span style="margin-right: 10px;">Hi, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span>
                    <?php endif; ?>
                    <div class="cart-icon">
                        <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
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
    </header>

    <!-- Products Hero Section -->
    <section class="products-hero">
        <div class="container">
            <h1>Discover Amazing Products</h1>
            <p>Browse our extensive collection of quality products at unbeatable prices</p>
        </div>
    </section>

    <!-- Products Controls -->
    <section class="products-controls">
        <div class="container">
            <form method="GET" action="products.php" id="products-filter-form">
                <div class="controls-grid">
                    <div class="control-group">
                        <label for="search"><i class="fas fa-search"></i> Search Products</label>
                        <input type="text" id="search" name="search" placeholder="Enter product name or description..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="control-group">
                        <label for="category"><i class="fas fa-filter"></i> Filter by Category</label>
                        <select id="category" name="category">
                            <option value="0">All Categories</option>
                            <?php if($categoriesResult && $categoriesResult->num_rows > 0): ?>
                                <?php while($category = $categoriesResult->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="control-group">
                        <label for="sort"><i class="fas fa-sort-amount-down"></i> Sort By</label>
                        <select id="sort" name="sort">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="discount" <?php echo $sort == 'discount' ? 'selected' : ''; ?>>Best Discount</option>
                        </select>
                    </div>
                    
                    <div class="control-group" style="display: flex; gap: 10px;">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button type="button" class="reset-btn" onclick="window.location.href='products.php'">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Products Results -->
    <div class="container">
        <div class="results-info">
            <div class="results-count">
                <?php 
                $displayCount = ($productsResult && $productsResult->num_rows > 0) ? $productsResult->num_rows : 0;
                ?>
                Showing <strong><?php echo min($limit, $displayCount); ?></strong> of <strong><?php echo $totalProducts; ?></strong> products
                <?php if(!empty($search)): ?>
                    for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <?php endif; ?>
                <?php if($category_id > 0): ?>
                    in <strong>
                        <?php 
                        if($categoriesResult && $categoriesResult->num_rows > 0) {
                            $categoriesResult->data_seek(0);
                            while($cat = $categoriesResult->fetch_assoc()) {
                                if($cat['id'] == $category_id) {
                                    echo htmlspecialchars($cat['name']);
                                    break;
                                }
                            }
                        }
                        ?>
                    </strong>
                <?php endif; ?>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if($productsResult && $productsResult->num_rows > 0): ?>
                <?php while($product = $productsResult->fetch_assoc()): ?>
                    <?php 
                    // Calculate discount percentage
                    $discount = 0;
                    if($product['original_price'] > 0 && $product['original_price'] > $product['price']) {
                        $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                    }
                    
                    // Determine stock status
                    $stock_status = '';
                    $stock_class = '';
                    if($product['stock_quantity'] > 50) {
                        $stock_status = 'In Stock';
                        $stock_class = 'in-stock';
                    } elseif($product['stock_quantity'] > 0) {
                        $stock_status = 'Low Stock';
                        $stock_class = 'low-stock';
                    } else {
                        $stock_status = 'Out of Stock';
                        $stock_class = 'out-of-stock';
                    }
                    ?>
                    
                    <div class="product-card">
                        <?php if($product['is_featured']): ?>
                            <span class="product-badge featured">Featured</span>
                        <?php endif; ?>
                        
                        <?php if($discount > 0): ?>
                            <span class="product-badge discount"><?php echo $discount; ?>% OFF</span>
                        <?php endif; ?>
                        
                        <?php if($product['stock_quantity'] == 0): ?>
                            <span class="product-badge out-of-stock">Out of Stock</span>
                        <?php endif; ?>
                        
                        <div class="product-img-container">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img" onerror="this.src='https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                        </div>
                        
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            
                            <div class="product-price">
                                <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                <?php if($product['original_price'] > 0 && $product['original_price'] > $product['price']): ?>
                                    <span class="original-price">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                    <span class="discount-percent">Save ₹<?php echo number_format($product['original_price'] - $product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rating-stock">
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
                                    
                                    <span style="margin-left: 5px; color: var(--text-light); font-size: 12px;">(<?php echo $rating; ?>)</span>
                                </div>
                                <div class="stock-info <?php echo $stock_class; ?>">
                                    <?php echo $stock_status; ?>
                                </div>
                            </div>
                            
                            <div class="product-actions">
                                <?php if($product['stock_quantity'] > 0): ?>
                                    <button class="btn btn-add-to-cart add-to-cart" data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-add-to-cart" disabled style="opacity: 0.6; cursor: not-allowed;">
                                        <i class="fas fa-ban"></i> Out of Stock
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-view-details view-details" onclick="window.location.href='product-details.php?id=<?php echo $product['id']; ?>'">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>No Products Found</h3>
                    <p>Try adjusting your search or filter to find what you're looking for.</p>
                    <button class="btn btn-primary" onclick="window.location.href='products.php'">
                        <i class="fas fa-redo"></i> Reset Filters
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?<?php 
                        $getParams = $_GET;
                        $getParams['page'] = $page - 1;
                        echo http_build_query($getParams); 
                    ?>" class="prev-next">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php else: ?>
                    <span class="prev-next disabled">Previous</span>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <a href="?<?php 
                            $getParams = $_GET;
                            $getParams['page'] = $i;
                            echo http_build_query($getParams); 
                        ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($page < $totalPages): ?>
                    <a href="?<?php 
                        $getParams = $_GET;
                        $getParams['page'] = $page + 1;
                        echo http_build_query($getParams); 
                    ?>" class="prev-next">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="prev-next disabled">Next</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
                    <h3 class="footer-heading">Categories</h3>
                    <ul>
                        <?php 
                        if($categoriesResult && $categoriesResult->num_rows > 0):
                            $categoriesResult->data_seek(0);
                            while($category = $categoriesResult->fetch_assoc()): ?>
                                <li><a href="products.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                            <?php endwhile;
                        endif; ?>
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
                <p>&copy; <?php echo date('Y'); ?> ShopEasy. All rights reserved. | Products: <?php echo $totalProducts; ?> | Page: <?php echo $page; ?> of <?php echo $totalPages; ?></p>
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
        
        // Add to Cart Functionality
        const cartCount = document.querySelector('.cart-count');
        let cartItems = <?php echo isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0; ?>;
        
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
                const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
                const productId = button.getAttribute('data-id');
                
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
                        const originalText = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check"></i> Added!';
                        button.style.backgroundColor = 'var(--success)';
                        
                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.style.backgroundColor = '';
                        }, 2000);
                    } else {
                        alert('Error adding to cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                });
            }
        });
        
        // Auto-submit filter form when select changes
        const sortSelect = document.getElementById('sort');
        const categorySelect = document.getElementById('category');
        const filterForm = document.getElementById('products-filter-form');
        
        if(sortSelect && filterForm) {
            sortSelect.addEventListener('change', function() {
                filterForm.submit();
            });
        }
        
        if(categorySelect && filterForm) {
            categorySelect.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    </script>
    <?php 
    // Close database connections
    if(isset($stmt) && $stmt) {
        $stmt->close();
    }
    if(isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>