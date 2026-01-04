<?php
require_once '../config.php';
redirectIfNotAdmin();

$conn = getDBConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = sanitize($_POST['status']);
    
    $conn->query("UPDATE orders SET order_status = '$status' WHERE id = $order_id");
    $_SESSION['success'] = 'Order status updated';
    header('Location: orders.php');
    exit();
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT o.*, u.username, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE 1=1";

if ($status_filter) {
    $query .= " AND o.order_status = '$status_filter'";
}

if ($date_from) {
    $query .= " AND DATE(o.created_at) >= '$date_from'";
}

if ($date_to) {
    $query .= " AND DATE(o.created_at) <= '$date_to'";
}

if ($search) {
    $query .= " AND (o.order_number LIKE '%$search%' OR u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$query .= " ORDER BY o.created_at DESC";

$orders = [];
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Get status counts for summary
$status_counts = [];
$result = $conn->query("
    SELECT order_status, COUNT(*) as count 
    FROM orders 
    GROUP BY order_status
");
while ($row = $result->fetch_assoc()) {
    $status_counts[$row['order_status']] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include "nav.php"?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Manage Orders</h1>

                <!-- Status Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap">
                                    <a href="orders.php" class="btn m-1 <?php echo !$status_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        All <span class="badge bg-secondary"><?php echo array_sum($status_counts); ?></span>
                                    </a>
                                    <?php 
                                    $statuses = ['processing', 'shipped', 'delivered', 'cancelled'];
                                    $status_colors = [
                                        'processing' => 'warning',
                                        'shipped' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    foreach ($statuses as $status): 
                                        $count = $status_counts[$status] ?? 0;
                                    ?>
                                    <a href="orders.php?status=<?php echo $status; ?>" 
                                       class="btn m-1 <?php echo $status_filter == $status ? 'btn-' . $status_colors[$status] : 'btn-outline-' . $status_colors[$status]; ?>">
                                        <?php echo ucfirst($status); ?> 
                                        <span class="badge bg-secondary"><?php echo $count; ?></span>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search orders..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo $status_filter == $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($status); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="orders.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                                        <td>
                                            <div>
                                                <strong><?php echo $order['username']; ?></strong><br>
                                                <small><?php echo $order['email']; ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                                        <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($order['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status" class="form-select form-select-sm" 
                                                        onchange="this.form.submit()">
                                                    <?php foreach ($statuses as $status): ?>
                                                    <option value="<?php echo $status; ?>" 
                                                        <?php echo $order['order_status'] == $status ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst($status); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="order_view.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="invoice.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-secondary" title="Invoice">
                                                    <i class="fas fa-file-invoice"></i>
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