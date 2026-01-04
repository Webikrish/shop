<?php
require_once '../config.php';
redirectIfNotAdmin();

$conn = getDBConnection();

// Handle delete product
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $product_id");
    header('Location: products.php');
    exit();
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $product_id = intval($_GET['toggle_status']);
    $conn->query("UPDATE products SET is_active = NOT is_active WHERE id = $product_id");
    header('Location: products.php');
    exit();
}

// Get all products with category names
$products = [];
$result = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Get categories for filter
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories WHERE is_active = 1");
while ($cat = $cat_result->fetch_assoc()) {
    $categories[] = $cat;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (same as before) -->
            <?php include "nav.php"?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">Manage Products</h1>
                    <div>
                        <a href="product_add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search products..." 
                                       value="<?php echo $_GET['search'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="stock" class="form-select">
                                    <option value="">All Stock</option>
                                    <option value="low" <?php echo (isset($_GET['stock']) && $_GET['stock'] == 'low') ? 'selected' : ''; ?>>Low Stock (< 10)</option>
                                    <option value="out" <?php echo (isset($_GET['stock']) && $_GET['stock'] == 'out') ? 'selected' : ''; ?>>Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $index => $product): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $product['image_url']; ?>" 
                                                     alt="<?php echo $product['name']; ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" 
                                                     class="me-3">
                                                <div>
                                                    <strong><?php echo $product['name']; ?></strong><br>
                                                    <small class="text-muted">SKU: <?php echo $product['sku']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $product['category_name'] ?? 'Uncategorized'; ?></td>
                                        <td>
                                            <strong>₹<?php echo number_format($product['price'], 2); ?></strong><br>
                                            <?php if ($product['original_price']): ?>
                                            <small class="text-danger"><s>₹<?php echo number_format($product['original_price'], 2); ?></s></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger'); ?>">
                                                <?php echo $product['stock_quantity']; ?> units
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($product['is_featured']): ?>
                                                <span class="badge bg-info">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?toggle_status=<?php echo $product['id']; ?>" 
                                                   class="btn btn-<?php echo $product['is_active'] ? 'warning' : 'success'; ?>"
                                                   onclick="return confirm('Toggle status?')">
                                                    <i class="fas fa-power-off"></i>
                                                </a>
                                                <a href="?delete=<?php echo $product['id']; ?>" 
                                                   class="btn btn-danger"
                                                   onclick="return confirm('Delete this product?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>