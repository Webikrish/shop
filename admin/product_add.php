<?php
require_once '../config.php';
redirectIfNotAdmin();

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : NULL;
    $category_id = intval($_POST['category_id']);
    $brand = sanitize($_POST['brand'] ?? '');
    $sku = sanitize($_POST['sku']);
    $stock = intval($_POST['stock_quantity']);
    $image_url = sanitize($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 1;
    
    $sql = "INSERT INTO products (name, slug, description, price, original_price, category_id, 
            brand, sku, stock_quantity, image_url, is_featured, is_active) 
            VALUES ('$name', '$slug', '$description', $price, " . 
            ($original_price ? "'$original_price'" : "NULL") . ", $category_id, 
            '$brand', '$sku', $stock, '$image_url', $is_featured, $is_active)";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Product added successfully';
        header('Location: products.php');
        exit();
    } else {
        $error = 'Error adding product: ' . $conn->error;
    }
}

// Get categories
$categories = [];
$result = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <!-- ... sidebar ... -->
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">Add New Product</h1>
                    <a href="products.php" class="btn btn-secondary">Back to Products</a>
                </div>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Product Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="4"></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Price (₹) *</label>
                                            <input type="number" name="price" class="form-control" step="0.01" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Original Price (₹)</label>
                                            <input type="number" name="original_price" class="form-control" step="0.01">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Category *</label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Brand</label>
                                            <input type="text" name="brand" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">SKU *</label>
                                        <input type="text" name="sku" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Stock Quantity *</label>
                                        <input type="number" name="stock_quantity" class="form-control" value="0" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Image URL</label>
                                        <input type="url" name="image_url" class="form-control" 
                                               placeholder="https://example.com/image.jpg">
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured">
                                        <label class="form-check-label" for="is_featured">Featured Product</label>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Add Product</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>