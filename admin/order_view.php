<?php
require_once '../config.php';
redirectIfNotAdmin();

$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit();
}

$conn = getDBConnection();

// Get order details
$result = $conn->query("
    SELECT o.*, u.username, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id
");

if (!$result || $result->num_rows === 0) {
    header('Location: orders.php');
    exit();
}

$order = $result->fetch_assoc();

// Get order items
$items = [];
$result = $conn->query("
    SELECT oi.*, p.image_url 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = $order_id
");

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

// Parse addresses
$shipping_address = json_decode($order['shipping_address'], true);
$billing_address = json_decode($order['billing_address'], true);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Panel</title>
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
                    <div>
                        <h1 class="h2">Order Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                                <li class="breadcrumb-item active"><?php echo $order['order_number']; ?></li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="orders.php" class="btn btn-secondary">Back</a>
                        <a href="invoice.php?id=<?php echo $order_id; ?>" class="btn btn-primary">
                            <i class="fas fa-print"></i> Print Invoice
                        </a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Order Information</h6>
                                        <p class="mb-1"><strong>Order #:</strong> <?php echo $order['order_number']; ?></p>
                                        <p class="mb-1"><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></p>
                                        <p class="mb-1"><strong>Status:</strong> 
                                            <span class="badge bg-<?php echo $order['order_status'] == 'processing' ? 'warning' : 
                                                ($order['order_status'] == 'delivered' ? 'success' : 
                                                ($order['order_status'] == 'cancelled' ? 'danger' : 'info')); ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Customer Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> <?php echo $order['username']; ?></p>
                                        <p class="mb-1"><strong>Email:</strong> <?php echo $order['email']; ?></p>
                                        <p class="mb-1"><strong>Phone:</strong> <?php echo $order['phone'] ?? 'N/A'; ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Payment Information</h6>
                                        <p class="mb-1"><strong>Method:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
                                        <p class="mb-1"><strong>Status:</strong> 
                                            <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </p>
                                        <p class="mb-1"><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Order Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item['image_url']): ?>
                                                        <img src="<?php echo $item['image_url']; ?>" 
                                                             alt="Product" style="width: 50px; height: 50px; object-fit: cover;" 
                                                             class="me-3">
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?php echo $item['product_name']; ?></strong><br>
                                                            <small>SKU: <?php echo $item['product_id']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><strong>₹<?php echo number_format($item['total_price'], 2); ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                                <td><strong>₹<?php echo number_format($order['total_amount'] - $order['shipping_amount'], 2); ?></strong></td>
                                            </tr>
                                            <?php if ($order['shipping_amount'] > 0): ?>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                                <td><strong>₹<?php echo number_format($order['shipping_amount'], 2); ?></strong></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td><strong class="text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Shipping Address</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong><?php echo $shipping_address['name']; ?></strong></p>
                                <p class="mb-1"><?php echo $shipping_address['phone']; ?></p>
                                <p class="mb-1"><?php echo $shipping_address['address_line1']; ?></p>
                                <?php if (!empty($shipping_address['address_line2'])): ?>
                                <p class="mb-1"><?php echo $shipping_address['address_line2']; ?></p>
                                <?php endif; ?>
                                <p class="mb-1"><?php echo $shipping_address['city']; ?>, <?php echo $shipping_address['state']; ?></p>
                                <p class="mb-1"><?php echo $shipping_address['country']; ?> - <?php echo $shipping_address['zip_code']; ?></p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5>Billing Address</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong><?php echo $billing_address['name']; ?></strong></p>
                                <p class="mb-1"><?php echo $billing_address['phone']; ?></p>
                                <p class="mb-1"><?php echo $billing_address['address_line1']; ?></p>
                                <?php if (!empty($billing_address['address_line2'])): ?>
                                <p class="mb-1"><?php echo $billing_address['address_line2']; ?></p>
                                <?php endif; ?>
                                <p class="mb-1"><?php echo $billing_address['city']; ?>, <?php echo $billing_address['state']; ?></p>
                                <p class="mb-1"><?php echo $billing_address['country']; ?> - <?php echo $billing_address['zip_code']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <?php if (!empty($order['notes'])): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Order Notes</h5>
                            </div>
                            <div class="card-body">
                                <p><?php echo $order['notes']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>