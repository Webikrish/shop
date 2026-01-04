<?php
session_start();
require_once 'config/db.php';

// Fetch user's orders (assuming user_id is stored in session)
$user_id = $_SESSION['user_id'] ?? 1; // Replace with actual session variable

$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - ShopEasy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'nav.php' ?>
    
    <div class="container py-4">
        <h2 class="mb-4">My Orders</h2>
        
        <?php if (count($orders) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        $status = $order['order_status'];
                                        echo $status == 'processing' ? 'warning' : 
                                             ($status == 'shipped' ? 'info' : 
                                             ($status == 'delivered' ? 'success' : 
                                             ($status == 'cancelled' ? 'danger' : 'secondary'))); 
                                    ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order.php?order_number=<?php echo urlencode($order['order_number']); ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h4>No orders found</h4>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<!-- <?php $conn->close(); ?> -->