<?php
// wishlist.php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Handle add to cart from wishlist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = sanitize($_POST['product_id']);
    
    // Check if product already in cart
    $checkCartSql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($checkCartSql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $cartResult = $stmt->get_result();
    
    if ($cartResult->num_rows > 0) {
        // Update quantity
        $updateSql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        // Add to cart
        $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }
    
    // Optionally remove from wishlist
    if (isset($_POST['remove_after_add'])) {
        $deleteSql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }
    
    header('Location: wishlist.php?success=added');
    exit();
}

// Handle remove from wishlist
if (isset($_GET['remove'])) {
    $product_id = sanitize($_GET['remove']);
    
    $deleteSql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    
    header('Location: wishlist.php?success=removed');
    exit();
}

// Handle move all to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['move_all_to_cart'])) {
    // Get all wishlist items
    $wishlistSql = "SELECT product_id FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($wishlistSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wishlistItems = $stmt->get_result();
    
    while ($item = $wishlistItems->fetch_assoc()) {
        $product_id = $item['product_id'];
        
        // Check if already in cart
        $checkCartSql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $checkStmt = $conn->prepare($checkCartSql);
        $checkStmt->bind_param("ii", $user_id, $product_id);
        $checkStmt->execute();
        $cartResult = $checkStmt->get_result();
        
        if ($cartResult->num_rows > 0) {
            // Update quantity
            $updateSql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $user_id, $product_id);
            $updateStmt->execute();
        } else {
            // Add to cart
            $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ii", $user_id, $product_id);
            $insertStmt->execute();
        }
    }
    
    // Clear wishlist
    $clearSql = "DELETE FROM wishlist WHERE user_id = ?";
    $clearStmt = $conn->prepare($clearSql);
    $clearStmt->bind_param("i", $user_id);
    $clearStmt->execute();
    
    header('Location: wishlist.php?success=moved_all');
    exit();
}

// Get wishlist items
$wishlistSql = "SELECT w.*, p.*, c.name as category_name 
                FROM wishlist w 
                JOIN products p ON w.product_id = p.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE w.user_id = ? 
                ORDER BY w.added_at DESC";
$stmt = $conn->prepare($wishlistSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlistItems = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - ShopEasy</title>
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
        }
        
        .wishlist-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .wishlist-header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-left h1 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .header-left p {
            color: var(--text-light);
            font-size: 18px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(255, 107, 107, 0.2);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        .wishlist-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .wishlist-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .wishlist-empty i {
            font-size: 80px;
            color: var(--border);
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .wishlist-empty h3 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .wishlist-empty p {
            color: var(--text-light);
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .wishlist-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s;
            position: relative;
        }
        
        .wishlist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .wishlist-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }
        
        .remove-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-light);
            transition: all 0.3s;
            z-index: 1;
            border: none;
        }
        
        .remove-btn:hover {
            background: var(--primary);
            color: white;
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid var(--border);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-category {
            color: var(--secondary);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .product-title {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 10px;
            line-height: 1.4;
            height: 45px;
            overflow: hidden;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .current-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .original-price {
            font-size: 16px;
            color: var(--text-light);
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: var(--success);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .stars {
            color: #ffd166;
        }
        
        .rating-count {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .product-actions .btn {
            flex: 1;
            padding: 10px;
            font-size: 14px;
            justify-content: center;
        }
        
        .stock-status {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .in-stock {
            color: var(--success);
        }
        
        .out-of-stock {
            color: var(--primary);
        }
        
        .message {
            padding: 12px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .wishlist-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .wishlist-content {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 576px) {
            .wishlist-content {
                grid-template-columns: 1fr;
            }
            
            .header-actions {
                flex-direction: column;
            }
            
            .header-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="wishlist-container">
        <div class="wishlist-header">
            <div class="header-left">
                <h1>My Wishlist</h1>
                <p>Save your favorite items for later</p>
            </div>
            
            <div class="header-actions">
                <a href="products.php" class="btn btn-outline">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <?php if($wishlistItems->num_rows > 0): ?>
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="move_all_to_cart" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> Move All to Cart
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <?php
            $messages = [
                'added' => 'Product added to cart successfully!',
                'removed' => 'Product removed from wishlist!',
                'moved_all' => 'All items moved to cart and wishlist cleared!'
            ];
            $message = $messages[$_GET['success']] ?? 'Action completed successfully!';
            ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($wishlistItems->num_rows > 0): ?>
            <div class="wishlist-content">
                <?php while($item = $wishlistItems->fetch_assoc()): ?>
                    <?php
                    $discount = 0;
                    if($item['original_price'] > 0) {
                        $discount = round((($item['original_price'] - $item['price']) / $item['original_price']) * 100);
                    }
                    ?>
                    <div class="wishlist-card">
                        <?php if($discount > 0): ?>
                            <div class="wishlist-badge"><?php echo $discount; ?>% OFF</div>
                        <?php endif; ?>
                        
                        <button class="remove-btn" onclick="window.location.href='?remove=<?php echo $item['product_id']; ?>'">
                            <i class="fas fa-times"></i>
                        </button>
                        
                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                        
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></div>
                            <h3 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            
                            <div class="product-price">
                                <span class="current-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                <?php if($item['original_price'] > 0): ?>
                                    <span class="original-price">₹<?php echo number_format($item['original_price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-rating">
                                <div class="stars">
                                    <?php 
                                    $rating = $item['rating'];
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    
                                    for($i = 0; $i < $fullStars; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    
                                    <?php if($hasHalfStar): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php endif; ?>
                                    
                                    <?php for($i = 0; $i < (5 - $fullStars - ($hasHalfStar ? 1 : 0)); $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">(<?php echo $rating; ?>)</span>
                            </div>
                            
                            <div class="stock-status <?php echo $item['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <i class="fas fa-<?php echo $item['stock_quantity'] > 0 ? 'check-circle' : 'times-circle'; ?>"></i>
                                <?php echo $item['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </div>
                            
                            <div class="product-actions">
                                <form method="POST" action="" style="flex: 1;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary" <?php echo $item['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                                <a href="product-details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="wishlist-empty">
                <i class="fas fa-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p>You haven't added any products to your wishlist yet. Start exploring our collection and save your favorite items for later!</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Browse Products
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Remove from wishlist confirmation
        const removeButtons = document.querySelectorAll('.remove-btn');
        removeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Remove this item from wishlist?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Add loading state to buttons
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                    
                    // Reset after 3 seconds in case of error
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 3000);
                }
            });
        });
    </script>
</body>
</html>