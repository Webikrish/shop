<?php
require_once '../config.php';
redirectIfNotAdmin();

$conn = getDBConnection();

// Handle operations
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if category has products
    $check = $conn->query("SELECT COUNT(*) as count FROM products WHERE category_id = $id");
    if ($check->fetch_assoc()['count'] > 0) {
        $_SESSION['error'] = 'Cannot delete category with existing products';
    } else {
        // Delete category image if exists
        $result = $conn->query("SELECT image_url FROM categories WHERE id = $id");
        $category = $result->fetch_assoc();
        if (!empty($category['image_url']) && file_exists('../' . $category['image_url'])) {
            unlink('../' . $category['image_url']);
        }
        
        $conn->query("DELETE FROM categories WHERE id = $id");
        $_SESSION['success'] = 'Category deleted';
    }
    header('Location: categories.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = sanitize($_POST['name']);
        $slug = strtolower(str_replace(' ', '-', $name));
        $description = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon_class'] ?? 'fas fa-box');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $image_url = sanitize($_POST['image_url'] ?? '');
        
        $sql = "INSERT INTO categories (name, slug, description, icon_class, image_url, is_active) 
                VALUES ('$name', '$slug', '$description', '$icon', '$image_url', $is_active)";
        $conn->query($sql);
        $_SESSION['success'] = 'Category added';
        
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon_class'] ?? 'fas fa-box');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $image_url = sanitize($_POST['image_url'] ?? '');
        
        // Get current image
        $result = $conn->query("SELECT image_url FROM categories WHERE id = $id");
        $current_category = $result->fetch_assoc();
        
        // If delete image checkbox is checked, clear the image URL
        if (isset($_POST['delete_image']) && !empty($current_category['image_url'])) {
            // If it's a local file, delete it
            if (strpos($current_category['image_url'], 'http') !== 0 && file_exists('../' . $current_category['image_url'])) {
                unlink('../' . $current_category['image_url']);
            }
            $image_url = '';
        } elseif (empty($image_url) && !empty($current_category['image_url'])) {
            // Keep current image if new one is empty
            $image_url = $current_category['image_url'];
        }
        
        $sql = "UPDATE categories SET 
                name = '$name',
                description = '$description',
                icon_class = '$icon',
                image_url = '$image_url',
                is_active = $is_active
                WHERE id = $id";
        $conn->query($sql);
        $_SESSION['success'] = 'Category updated';
    }
    
    header('Location: categories.php');
    exit();
}

// Get all categories with product counts in a single query
$categories = [];
$result = $conn->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
");
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
    <title>Manage Categories - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        .url-preview {
            max-width: 100%;
            max-height: 150px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include "nav.php"?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Manage Categories</h1>

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Add New Category</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label>Category Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label>Image URL</label>
                                        <input type="text" name="image_url" id="imageUrlInput" 
                                               class="form-control" 
                                               placeholder="https://example.com/image.jpg">
                                        <small class="text-muted">Enter full image URL (e.g., https://example.com/image.jpg)</small>
                                        <div id="imagePreview" class="image-preview">
                                            <img src="" class="url-preview img-thumbnail" alt="Preview">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Icon Class (Font Awesome)</label>
                                        <input type="text" name="icon_class" class="form-control" 
                                               value="fas fa-box" placeholder="fas fa-tag">
                                        <small class="text-muted">Optional: Use Font Awesome icon class</small>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="addActive" checked>
                                        <label class="form-check-label" for="addActive">Active</label>
                                    </div>
                                    <button type="submit" name="add" class="btn btn-primary">Add Category</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>All Categories</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Image</th>
                                                <th>Category</th>
                                                <th>Products</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $index => $cat): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <?php if (!empty($cat['image_url'])): ?>
                                                        <?php if (strpos($cat['image_url'], 'http') === 0): ?>
                                                            <img src="<?php echo $cat['image_url']; ?>" 
                                                                 alt="<?php echo $cat['name']; ?>" 
                                                                 class="category-image"
                                                                 onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block';">
                                                        <?php else: ?>
                                                            <img src="../<?php echo $cat['image_url']; ?>" 
                                                                 alt="<?php echo $cat['name']; ?>" 
                                                                 class="category-image"
                                                                 onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block';">
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (empty($cat['image_url'])): ?>
                                                        <i class="<?php echo $cat['icon_class']; ?> fa-2x"></i>
                                                    <?php else: ?>
                                                        <i class="<?php echo $cat['icon_class']; ?> fa-2x" style="display:none;"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo $cat['name']; ?></strong><br>
                                                    <small class="text-muted"><?php echo substr($cat['description'], 0, 50); ?>...</small>
                                                </td>
                                                <td><span class="badge bg-info"><?php echo $cat['product_count']; ?> products</span></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $cat['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-info" data-bs-toggle="modal" 
                                                                data-bs-target="#editModal<?php echo $cat['id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="?delete=<?php echo $cat['id']; ?>" 
                                                           class="btn btn-danger"
                                                           onclick="return confirm('Delete this category?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $cat['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Category</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label>Category Name *</label>
                                                                    <input type="text" name="name" class="form-control" 
                                                                           value="<?php echo $cat['name']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label>Description</label>
                                                                    <textarea name="description" class="form-control" rows="3"><?php echo $cat['description']; ?></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label>Image URL</label>
                                                                    <?php if (!empty($cat['image_url'])): ?>
                                                                        <div class="mb-2">
                                                                            <?php if (strpos($cat['image_url'], 'http') === 0): ?>
                                                                                <img src="<?php echo $cat['image_url']; ?>" 
                                                                                     alt="Current Image" 
                                                                                     class="img-thumbnail url-preview"
                                                                                     style="max-height: 100px;"
                                                                                     onerror="this.style.display='none';">
                                                                            <?php else: ?>
                                                                                <img src="../<?php echo $cat['image_url']; ?>" 
                                                                                     alt="Current Image" 
                                                                                     class="img-thumbnail url-preview"
                                                                                     style="max-height: 100px;"
                                                                                     onerror="this.style.display='none';">
                                                                            <?php endif; ?>
                                                                            <div class="form-check mt-2">
                                                                                <input type="checkbox" name="delete_image" class="form-check-input" id="deleteImage<?php echo $cat['id']; ?>">
                                                                                <label class="form-check-label text-danger" for="deleteImage<?php echo $cat['id']; ?>">Remove current image</label>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <input type="text" name="image_url" 
                                                                           class="form-control" 
                                                                           value="<?php echo $cat['image_url']; ?>"
                                                                           placeholder="https://example.com/image.jpg">
                                                                    <small class="text-muted">Enter full image URL or leave empty for no image</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label>Icon Class</label>
                                                                    <input type="text" name="icon_class" class="form-control" 
                                                                           value="<?php echo $cat['icon_class']; ?>">
                                                                </div>
                                                                <div class="mb-3 form-check">
                                                                    <input type="checkbox" name="is_active" class="form-check-input" 
                                                                           id="active<?php echo $cat['id']; ?>" <?php echo $cat['is_active'] ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="active<?php echo $cat['id']; ?>">Active</label>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="update" class="btn btn-primary">Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image URL preview for add form
        document.getElementById('imageUrlInput').addEventListener('input', function(e) {
            const url = e.target.value;
            const preview = document.getElementById('imagePreview');
            const previewImg = preview.querySelector('img');
            
            if (url && isValidUrl(url)) {
                preview.style.display = 'block';
                previewImg.src = url;
            } else {
                preview.style.display = 'none';
            }
        });
        
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Handle image error for edit modal previews
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.url-preview').forEach(img => {
                img.onerror = function() {
                    this.style.display = 'none';
                };
            });
        });
    </script>
</body>
</html>