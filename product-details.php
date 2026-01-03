<?php
// product-details.php - Product Details Page
session_start();
require_once 'config.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Create database connection
$conn = getDBConnection();

// Fetch product details
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Product not found
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();

// Fetch related products (same category, excluding current product)
$relatedQuery = "SELECT * FROM products 
                 WHERE category_id = ? AND id != ? AND stock_quantity > 0 
                 ORDER BY rating DESC, created_at DESC 
                 LIMIT 4";
$relatedStmt = $conn->prepare($relatedQuery);
$relatedStmt->bind_param("ii", $product['category_id'], $product_id);
$relatedStmt->execute();
$relatedResult = $relatedStmt->get_result();

// Close connection
$stmt->close();
$relatedStmt->close();
$conn->close();

// Calculate discount
$discount = 0;
if ($product['original_price'] > 0 && $product['original_price'] > $product['price']) {
    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}

// Determine stock status
$stock_status = '';
$stock_class = '';
if ($product['stock_quantity'] > 50) {
    $stock_status = 'In Stock';
    $stock_class = 'in-stock';
} elseif ($product['stock_quantity'] > 0) {
    $stock_status = 'Only ' . $product['stock_quantity'] . ' left in stock';
    $stock_class = 'low-stock';
} else {
    $stock_status = 'Out of Stock';
    $stock_class = 'out-of-stock';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ShopEasy</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Product Details Layout */
        .product-details {
            padding: 40px 0;
        }

        .breadcrumb {
            margin-bottom: 30px;
            font-size: 14px;
            color: var(--text-light);
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .product-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 60px;
        }

        @media (max-width: 992px) {
            .product-details-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        /* Product Images */
        .product-images {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-images {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: var(--radius);
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: var(--transition);
        }

        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--primary);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Product Info */
        .product-info {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
        }

        .product-category {
            font-size: 14px;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .product-title {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stars {
            color: var(--accent);
        }

        .rating-value {
            font-weight: 600;
            color: var(--text);
        }

        .review-count {
            color: var(--text-light);
            font-size: 14px;
        }

        .product-price-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border);
        }

        .current-price {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .original-price {
            font-size: 20px;
            color: var(--text-light);
            text-decoration: line-through;
            margin-right: 15px;
        }

        .discount-percent {
            background-color: var(--success);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
        }

        .product-stock {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border);
        }

        .stock-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: var(--radius);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stock-status.in-stock {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success);
        }

        .stock-status.low-stock {
            background-color: rgba(255, 166, 0, 0.1);
            color: #ffa500;
        }

        .stock-status.out-of-stock {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--primary);
        }

        .product-description-full {
            margin-bottom: 30px;
        }

        .product-description-full h3 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .product-description-full p {
            color: var(--text-light);
            line-height: 1.8;
        }

        .product-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 25px;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            flex: 2;
        }

        .btn-primary:hover {
            background-color: #ff5252;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
        }

        .btn-secondary {
            background-color: var(--light);
            color: var(--text);
            border: 1px solid var(--border);
            flex: 1;
        }

        .btn-secondary:hover {
            background-color: var(--border);
        }

        .btn-disabled {
            background-color: var(--text-light);
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
            flex: 2;
        }

        /* Related Products */
        .related-products {
            margin-top: 60px;
            padding-top: 60px;
            border-top: 1px solid var(--border);
        }

        .section-title {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 30px;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .related-product {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .related-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .related-product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .related-product-info {
            padding: 20px;
        }

        .related-product-title {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 10px;
            line-height: 1.4;
            height: 44px;
            overflow: hidden;
        }

        .related-product-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-title {
                font-size: 24px;
            }
            
            .current-price {
                font-size: 28px;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .main-image {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Simple Header for Product Details -->
    <header style="background-color: white; box-shadow: var(--shadow); padding: 15px 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" style="font-size: 24px; font-weight: 700; color: var(--primary); text-decoration: none;">
                <i class="fas fa-shopping-bag"></i> Shop<span style="color: var(--dark);">Easy</span>
            </a>
            <a href="products.php" style="color: var(--text); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </header>

    <div class="container">
        <div class="product-details">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt; 
                <a href="products.php">Products</a> &gt; 
                <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> &gt; 
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>

            <div class="product-details-grid">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <img id="mainImage" src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="thumbnail-images">
                        <div class="thumbnail active" onclick="changeImage('<?php echo $product['image_url']; ?>')">
                            <img src="<?php echo $product['image_url']; ?>" alt="Main Image">
                        </div>
                        <!-- Additional thumbnails (could be fetched from database in real application) -->
                        <div class="thumbnail" onclick="changeImage('https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80')">
                            <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Alternative View">
                        </div>
                        <div class="thumbnail" onclick="changeImage('https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80')">
                            <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Detail View">
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-rating">
                        <div class="stars">
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
                        </div>
                        <span class="rating-value"><?php echo $rating; ?></span>
                        <span class="review-count">(Based on customer reviews)</span>
                    </div>
                    
                    <div class="product-price-section">
                        <div class="current-price">₹<?php echo number_format($product['price'], 2); ?></div>
                        <?php if($product['original_price'] > 0 && $product['original_price'] > $product['price']): ?>
                            <div>
                                <span class="original-price">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                <span class="discount-percent"><?php echo $discount; ?>% OFF</span>
                            </div>
                            <p style="color: var(--success); font-weight: 500; margin-top: 5px;">
                                Save ₹<?php echo number_format($product['original_price'] - $product['price'], 2); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-stock">
                        <div class="stock-status <?php echo $stock_class; ?>">
                            <?php echo $stock_status; ?>
                        </div>
                        <?php if($product['stock_quantity'] > 0): ?>
                            <p>Free shipping on orders above ₹499</p>
                            <p>10-day easy return policy</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-description-full">
                        <h3>Product Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        <p style="margin-top: 15px;">This product comes with a 1-year manufacturer warranty and 24/7 customer support.</p>
                    </div>
                    
                    <div class="product-actions">
                        <?php if($product['stock_quantity'] > 0): ?>
                            <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <button class="btn btn-secondary buy-now" data-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-bolt"></i> Buy Now
                            </button>
                        <?php else: ?>
                            <button class="btn btn-disabled" disabled>
                                <i class="fas fa-ban"></i> Out of Stock
                            </button>
                            <button class="btn btn-secondary notify-me">
                                <i class="fas fa-bell"></i> Notify When Available
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if($relatedResult->num_rows > 0): ?>
                <div class="related-products">
                    <h2 class="section-title">Related Products</h2>
                    <div class="related-grid">
                        <?php while($related = $relatedResult->fetch_assoc()): ?>
                            <a href="product-details.php?id=<?php echo $related['id']; ?>" class="related-product">
                                <img src="<?php echo $related['image_url']; ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                <div class="related-product-info">
                                    <h3 class="related-product-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                                    <div class="related-product-price">₹<?php echo number_format($related['price'], 2); ?></div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Change main image when thumbnail is clicked
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.closest('.thumbnail').classList.add('active');
        }

        // Add to Cart Functionality
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) {
                const productId = e.target.getAttribute('data-id');
                
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
                        // Show success message
                        const btn = e.target;
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
                        btn.style.backgroundColor = 'var(--success)';
                        
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.style.backgroundColor = '';
                        }, 2000);
                    } else {
                        alert('Error adding to cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Buy Now functionality
            if (e.target.classList.contains('buy-now')) {
                const productId = e.target.getAttribute('data-id');
                // Add to cart and redirect to checkout
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
                        window.location.href = 'checkout.php';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    </script>
</body>
</html>