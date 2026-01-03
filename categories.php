<?php
// categories.php - Categories Page
session_start();
require_once 'config.php';

// Create database connection
$conn = getDBConnection();

// Fetch all categories with product counts
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name";
$categoriesResult = $conn->query($query);

// Fetch featured products for each category
$featuredQuery = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_featured = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT 8";
$featuredResult = $conn->query($featuredQuery);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - ShopEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            background-color: var(--light);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Categories Hero */
        .categories-hero {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .categories-hero h1 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .categories-hero p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .category-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-decoration: none;
            color: var(--text);
            display: block;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .category-image {
            height: 200px;
            overflow: hidden;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .category-card:hover .category-image img {
            transform: scale(1.05);
        }

        .category-content {
            padding: 25px;
            text-align: center;
        }

        .category-icon {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .category-content h3 {
            font-size: 22px;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .category-description {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .product-count {
            background-color: var(--light);
            color: var(--primary);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }

        /* Featured Products */
        .featured-products {
            padding: 60px 0;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 32px;
            color: var(--dark);
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 15px;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .product-card-small {
            background-color: var(--light);
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
            transition: var(--transition);
        }

        .product-card-small:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .product-card-small img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: var(--radius);
            margin-bottom: 15px;
        }

        .product-card-small h4 {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .product-card-small .price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
        }

        .view-all {
            text-align: center;
            margin-top: 40px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }
            
            .categories-hero h1 {
                font-size: 32px;
            }
            
            .section-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <!-- Simple Header -->
    <header style="background-color: white; box-shadow: var(--shadow); padding: 15px 0; margin-bottom: 20px;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" style="font-size: 24px; font-weight: 700; color: var(--primary); text-decoration: none;">
                <i class="fas fa-shopping-bag"></i> Shop<span style="color: var(--dark);">Easy</span>
            </a>
            <nav>
                <a href="index.php" style="color: var(--text); text-decoration: none; margin-left: 20px;">Home</a>
                <a href="products.php" style="color: var(--text); text-decoration: none; margin-left: 20px;">Products</a>
                <a href="categories.php" style="color: var(--primary); text-decoration: none; margin-left: 20px; font-weight: 600;">Categories</a>
            </nav>
        </div>
    </header>

    <div class="categories-hero">
        <div class="container">
            <h1>Shop by Categories</h1>
            <p>Browse our wide range of product categories and find exactly what you need</p>
        </div>
    </div>

    <div class="container">
        <!-- All Categories -->
        <div class="categories-grid">
            <?php if($categoriesResult && $categoriesResult->num_rows > 0): ?>
                <?php while($category = $categoriesResult->fetch_assoc()): ?>
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                        <div class="category-image">
                            <?php 
                            // Use category-specific images (these would be stored in database in real application)
                            $categoryImages = [
                                1 => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                                2 => 'https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                                3 => 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                                4 => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                                5 => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                                6 => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
                            ];
                            $image = isset($categoryImages[$category['id']]) ? $categoryImages[$category['id']] : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                            ?>
                            <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                        </div>
                        <div class="category-content">
                            <div class="category-icon">
                                <i class="<?php echo $category['icon_class']; ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                            <span class="product-count"><?php echo $category['product_count']; ?> Products</span>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-light);">
                    No categories found.
                </p>
            <?php endif; ?>
        </div>

        <!-- Featured Products -->
        <?php if($featuredResult && $featuredResult->num_rows > 0): ?>
            <div class="featured-products">
                <h2 class="section-title">Featured Products</h2>
                <div class="products-grid">
                    <?php while($product = $featuredResult->fetch_assoc()): ?>
                        <div class="product-card-small">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <div class="price">₹<?php echo number_format($product['price'], 2); ?></div>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="view-all">
                    <a href="products.php" class="btn" style="padding: 12px 30px;">
                        <i class="fas fa-store"></i> View All Products
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>