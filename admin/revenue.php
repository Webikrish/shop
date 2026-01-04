<?php
require_once '../config.php';
redirectIfNotAdmin();

$conn = getDBConnection();

// Get date range
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-t');

// Get revenue summary
$summary = [];
$result = $conn->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as average_order_value,
        SUM(CASE WHEN order_status = 'cancelled' THEN total_amount ELSE 0 END) as cancelled_amount
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'
");

if ($result) {
    $summary = $result->fetch_assoc();
}

// Get daily revenue for chart
$daily_revenue = [];
$result = $conn->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE order_status != 'cancelled' 
    AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'
    GROUP BY DATE(created_at)
    ORDER BY date
");

while ($row = $result->fetch_assoc()) {
    $daily_revenue[] = $row;
}

// Get top products
$top_products = [];
$result = $conn->query("
    SELECT 
        p.name,
        p.sku,
        COUNT(oi.id) as units_sold,
        SUM(oi.total_price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.order_status != 'cancelled'
    AND DATE(o.created_at) BETWEEN '$date_from' AND '$date_to'
    GROUP BY p.id
    ORDER BY revenue DESC
    LIMIT 10
");

while ($row = $result->fetch_assoc()) {
    $top_products[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Report - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <!-- ... sidebar ... -->
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Revenue Report</h1>

                <!-- Date Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                                <button type="button" onclick="printReport()" class="btn btn-secondary">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="mt-4">
                                    <span class="badge bg-success fs-6">
                                        Total Revenue: ₹<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle">Total Orders</h6>
                                <h2 class="card-title"><?php echo $summary['total_orders'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle">Total Revenue</h6>
                                <h2 class="card-title">₹<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle">Avg Order Value</h6>
                                <h2 class="card-title">₹<?php echo number_format($summary['average_order_value'] ?? 0, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle">Cancelled Amount</h6>
                                <h2 class="card-title">₹<?php echo number_format($summary['cancelled_amount'] ?? 0, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5>Daily Revenue Trend</h5>
                                <canvas id="dailyChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>Revenue Distribution</h5>
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Top Selling Products</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th>Units Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_products as $index => $product): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo $product['name']; ?></td>
                                                <td><?php echo $product['sku']; ?></td>
                                                <td><?php echo $product['units_sold']; ?></td>
                                                <td><strong>₹<?php echo number_format($product['revenue'], 2); ?></strong></td>
                                            </tr>
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

    <script>
        function printReport() {
            window.print();
        }

        // Daily Revenue Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($daily_revenue, 'date')); ?>,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: <?php echo json_encode(array_column($daily_revenue, 'revenue')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Processing', 'Cancelled'],
                datasets: [{
                    data: [
                        <?php echo $summary['total_revenue'] - ($summary['cancelled_amount'] ?? 0); ?>,
                        0, // You would need to calculate processing revenue
                        <?php echo $summary['cancelled_amount'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 205, 86, 0.5)',
                        'rgba(255, 99, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>