<?php
require_once '../config.php';
redirectIfNotAdmin();

$conn = getDBConnection();

// Handle actions
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM reviews WHERE id = $id");
    $_SESSION['success'] = 'Review deleted';
    header('Location: reviews.php');
    exit();
}

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE reviews SET approved = 1 WHERE id = $id");
    $_SESSION['success'] = 'Review approved';
    header('Location: reviews.php');
    exit();
}

// Get all reviews with user and product info
$reviews = [];
$result = $conn->query("
    SELECT r.*, u.username, p.name as product_name, p.image_url 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN products p ON r.product_id = p.id 
    ORDER BY r.created_at DESC
");

while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Panel</title>
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
                <h1 class="h2 mb-4">Customer Reviews</h1>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $index => $review): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($review['image_url']): ?>
                                                <img src="<?php echo $review['image_url']; ?>" 
                                                     alt="Product" style="width: 40px; height: 40px; object-fit: cover;" 
                                                     class="me-2">
                                                <?php endif; ?>
                                                <span><?php echo $review['product_name']; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo $review['username']; ?></td>
                                        <td>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                                <?php endfor; ?>
                                                <span class="text-muted ms-1">(<?php echo $review['rating']; ?>)</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="review-text">
                                                <?php echo substr($review['comment'], 0, 100); ?>
                                                <?php if (strlen($review['comment']) > 100): ?>...<?php endif; ?>
                                            </div>
                                            <?php if (strlen($review['comment']) > 100): ?>
                                            <button class="btn btn-sm btn-link" 
                                                    onclick="toggleReview(<?php echo $review['id']; ?>)">
                                                Read more
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?approve=<?php echo $review['id']; ?>" 
                                                   class="btn btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="?delete=<?php echo $review['id']; ?>" 
                                                   class="btn btn-danger"
                                                   onclick="return confirm('Delete this review?')">
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

    <script>
        function toggleReview(id) {
            const reviewText = document.querySelector(`tr:nth-child(${id}) .review-text`);
            if (reviewText.textContent.includes('...')) {
                // Load full review
                // In a real app, you would fetch the full review via AJAX
                alert('Full review functionality would be implemented here');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>