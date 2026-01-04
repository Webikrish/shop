<?php
session_start();
require_once 'config/db.php';

// Check if order number is provided
if (!isset($_GET['order_number']) || empty($_GET['order_number'])) {
    header('Location: orders.php');
    exit();
}

$order_number = $_GET['order_number'];

// Fetch order details
$stmt = $conn->prepare("
    SELECT o.* 
    FROM orders o
    WHERE o.order_number = ?
");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $order_number);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: orders.php?error=Order not found');
    exit();
}

// Get order ID
$order_id = $order['id'];

// Fetch order items from order_items table
// First try with products join, if that fails, try without join
$sql = "
    SELECT oi.* 
    FROM order_items oi
    WHERE oi.order_id = ?
    ORDER BY oi.id
";

$stmt_items = $conn->prepare($sql);
if (!$stmt_items) {
    // If prepare fails, try a simpler query
    $sql_simple = "SELECT * FROM order_items WHERE order_id = ? ORDER BY id";
    $stmt_items = $conn->prepare($sql_simple);
    
    if (!$stmt_items) {
        die("Prepare failed for order items: " . $conn->error);
    }
}

$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$order_items = [];
$subtotal = 0;

while ($item = $result_items->fetch_assoc()) {
    $order_items[] = $item;
    $subtotal += $item['total_price'];
}

// Decode JSON addresses
$shipping_address = json_decode($order['shipping_address'], true);
$billing_address = json_decode($order['billing_address'], true);

// Check if decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    // If JSON decode fails, use a default array
    $shipping_address = [
        'name' => 'Customer Name',
        'phone' => 'N/A',
        'address_line1' => 'Address not available',
        'address_line2' => '',
        'city' => 'City',
        'state' => 'State',
        'country' => 'Country',
        'zip_code' => 'ZIP'
    ];
    $billing_address = $shipping_address;
}

// Define status badges
$status_badges = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'cancelled' => 'danger'
];

$payment_badges = [
    'pending' => 'warning',
    'paid' => 'success',
    'failed' => 'danger',
    'refunded' => 'secondary'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars($order_number); ?> - ShopEasy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .order-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
        }
        .order-card .card-header {
            background-color: #fff;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .total-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 1.5rem;
            border-left: 4px solid #007bff;
        }
        .address-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 1.5rem;
            height: 100%;
            border-left: 4px solid #28a745;
        }
        .billing-box {
            border-left: 4px solid #6f42c1;
        }
        .breadcrumb {
            background-color: transparent;
            padding: 0;
        }
        .action-buttons {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        @media print {
            .no-print {
                display: none;
            }
            .order-header {
                background: none !important;
                color: black !important;
                padding: 1rem 0 !important;
            }
            .order-card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
            .btn {
                display: none !important;
            }
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php 
    // Simple navbar if includes/navbar.php doesn't exist
    if (!@include('includes/navbar.php')): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">ShopEasy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Display success/error messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4 no-print">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Order #<?php echo htmlspecialchars($order_number); ?></li>
            </ol>
        </nav>

        <!-- Order Header -->
        <div class="order-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 mb-2">
                            <i class="fas fa-receipt me-2"></i>
                            Order #<?php echo htmlspecialchars($order_number); ?>
                        </h1>
                        <p class="mb-0">
                            <i class="far fa-calendar-alt me-1"></i>
                            Placed on <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                        </p>
                        <?php if (!empty($shipping_address['name'])): ?>
                            <p class="mb-0 mt-1">
                                <i class="fas fa-user me-1"></i>
                                Customer: <?php echo htmlspecialchars($shipping_address['name']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($order['shipping_amount'] > 0): ?>
                            <p class="mb-0 mt-1">
                                <i class="fas fa-shipping-fast me-1"></i>
                                Shipping: ₹<?php echo number_format($order['shipping_amount'], 2); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-flex flex-column flex-md-row justify-content-md-end align-items-md-center gap-2">
                            <span class="status-badge bg-<?php echo $status_badges[$order['order_status']] ?? 'secondary'; ?>">
                                <i class="fas fa-truck me-1"></i>
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                            <span class="status-badge bg-<?php echo $payment_badges[$order['payment_status']] ?? 'secondary'; ?>">
                                <i class="fas fa-credit-card me-1"></i>
                                Payment: <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        <p class="mt-2 mb-0">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons mb-4 no-print">
            <div class="d-flex flex-wrap gap-2">
                <a href="orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Orders
                </a>
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print me-1"></i> Print Invoice
                </button>
                <?php if ($order['order_status'] == 'processing'): ?>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="fas fa-times me-1"></i> Cancel Order
                    </button>
                <?php endif; ?>
                <a href="contact.php?order=<?php echo urlencode($order_number); ?>" class="btn btn-outline-info">
                    <i class="fas fa-question-circle me-1"></i> Need Help?
                </a>
            </div>
        </div>

        <!-- Order Details -->
        <div class="row">
            <!-- Order Items -->
            <div class="col-lg-8">
                <div class="card order-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-shopping-cart me-2"></i>
                            Order Items
                        </span>
                        <span class="badge bg-primary"><?php echo count($order_items); ?> item(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($order_items) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                    <?php if (!empty($item['sku'])): ?>
                                                        <small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end align-middle">₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-secondary rounded-pill px-3"><?php echo $item['quantity']; ?></span>
                                                </td>
                                                <td class="text-end align-middle fw-bold">₹<?php echo number_format($item['total_price'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No items found in this order.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Notes -->
                <?php if (!empty($order['notes'])): ?>
                    <div class="card order-card">
                        <div class="card-header">
                            <i class="fas fa-sticky-note me-2"></i>Order Notes
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Order Timeline -->
                <div class="card order-card">
                    <div class="card-header">
                        <i class="fas fa-history me-2"></i>Order Status Timeline
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item <?php echo $order['order_status'] == 'processing' || $order['order_status'] == 'shipped' || $order['order_status'] == 'delivered' ? 'completed' : ''; ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Order Placed</h6>
                                    <p class="text-muted mb-0"><?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $order['order_status'] == 'shipped' || $order['order_status'] == 'delivered' ? 'completed' : ($order['order_status'] == 'processing' ? 'active' : ''); ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Processing</h6>
                                    <p class="text-muted mb-0">Order is being processed</p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $order['order_status'] == 'delivered' ? 'completed' : ($order['order_status'] == 'shipped' ? 'active' : ''); ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Shipped</h6>
                                    <p class="text-muted mb-0">Order has been shipped</p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $order['order_status'] == 'delivered' ? 'active' : ''; ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Delivered</h6>
                                    <p class="text-muted mb-0">Order delivered to customer</p>
                                </div>
                            </div>
                        </div>
                        <style>
                            .timeline {
                                position: relative;
                                padding-left: 30px;
                            }
                            .timeline-item {
                                position: relative;
                                margin-bottom: 30px;
                            }
                            .timeline-item.completed .timeline-icon {
                                background-color: #28a745;
                                color: white;
                            }
                            .timeline-item.active .timeline-icon {
                                background-color: #007bff;
                                color: white;
                                animation: pulse 2s infinite;
                            }
                            .timeline-item:not(.completed):not(.active) .timeline-icon {
                                background-color: #e9ecef;
                                color: #6c757d;
                            }
                            .timeline-icon {
                                position: absolute;
                                left: -45px;
                                top: 0;
                                width: 30px;
                                height: 30px;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                z-index: 2;
                            }
                            .timeline-content {
                                padding-left: 10px;
                            }
                            @keyframes pulse {
                                0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
                                70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
                                100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
                            }
                        </style>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card order-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Order Summary
                    </div>
                    <div class="card-body">
                        <div class="total-section">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-bold">₹<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Shipping:</span>
                                <span class="fw-bold">₹<?php echo number_format($order['shipping_amount'], 2); ?></span>
                            </div>
                            <?php if ($order['shipping_amount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Shipping Method:</span>
                                    <span class="fw-bold">Standard</span>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0">Total Amount:</span>
                                <span class="h4 text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <?php if ($order['payment_status'] == 'paid'): ?>
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Paid on <?php echo date('M d, Y'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card order-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-truck me-2"></i>Shipping Address
                    </div>
                    <div class="card-body">
                        <div class="address-box">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-user me-2"></i>
                                <?php echo htmlspecialchars($shipping_address['name']); ?>
                            </h6>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($shipping_address['address_line1']); ?>
                            </p>
                            <?php if (!empty($shipping_address['address_line2'])): ?>
                                <p class="mb-2 ps-4">
                                    <?php echo htmlspecialchars($shipping_address['address_line2']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="mb-2">
                                <i class="fas fa-city me-2"></i>
                                <?php echo htmlspecialchars($shipping_address['city']); ?>, 
                                <?php echo htmlspecialchars($shipping_address['state']); ?> - 
                                <?php echo htmlspecialchars($shipping_address['zip_code']); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-globe me-2"></i>
                                <?php echo htmlspecialchars($shipping_address['country']); ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($shipping_address['phone']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="card order-card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice me-2"></i>Billing Address
                    </div>
                    <div class="card-body">
                        <div class="address-box billing-box">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-user me-2"></i>
                                <?php echo htmlspecialchars($billing_address['name']); ?>
                            </h6>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($billing_address['address_line1']); ?>
                            </p>
                            <?php if (!empty($billing_address['address_line2'])): ?>
                                <p class="mb-2 ps-4">
                                    <?php echo htmlspecialchars($billing_address['address_line2']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="mb-2">
                                <i class="fas fa-city me-2"></i>
                                <?php echo htmlspecialchars($billing_address['city']); ?>, 
                                <?php echo htmlspecialchars($billing_address['state']); ?> - 
                                <?php echo htmlspecialchars($billing_address['zip_code']); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-globe me-2"></i>
                                <?php echo htmlspecialchars($billing_address['country']); ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($billing_address['phone']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/cancel_order.php" method="POST">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Are you sure you want to cancel order #<?php echo htmlspecialchars($order_number); ?>?
                        </div>
                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">Reason for cancellation:</label>
                            <select class="form-select" id="cancel_reason" name="reason" required>
                                <option value="">Select a reason</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="found_cheaper">Found cheaper elsewhere</option>
                                <option value="delivery_time">Delivery time too long</option>
                                <option value="ordered_by_mistake">Ordered by mistake</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cancel_note" class="form-label">Additional notes (optional):</label>
                            <textarea class="form-control" id="cancel_note" name="note" rows="3" placeholder="Please provide additional details..."></textarea>
                        </div>
                        <input type="hidden" name="order_number" value="<?php echo htmlspecialchars($order_number); ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i> Cancel Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5 no-print">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>ShopEasy</h5>
                    <p>Your one-stop shop for all your needs.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">© <?php echo date('Y'); ?> ShopEasy. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Print functionality
        function printInvoice() {
            window.print();
        }

        // Modal handling
        document.addEventListener('DOMContentLoaded', function() {
            var cancelModal = document.getElementById('cancelModal');
            if (cancelModal) {
                cancelModal.addEventListener('shown.bs.modal', function () {
                    document.getElementById('cancel_reason').focus();
                });
            }
        });
    </script>
</body>
</html>
<?php 
// Close statements and connection
if (isset($stmt)) {
    $stmt->close();
}
if (isset($stmt_items)) {
    $stmt_items->close();
}
if (isset($conn)) {
    $conn->close();
}