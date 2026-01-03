<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 250px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-left: 10px;
        }

        .logo {
            width: 40px;
            height: 40px;
            background-color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .nav-links {
            padding: 20px 0;
        }

        .nav-links li {
            list-style: none;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
        }

        .nav-links i {
            width: 25px;
            margin-right: 10px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
        }

        /* Header */
        header {
            height: var(--header-height);
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-color);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        /* Dashboard Overview */
        .page-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--dark-color);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .sales .card-icon {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .orders .card-icon {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }

        .products .card-icon {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning-color);
        }

        .users .card-icon {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger-color);
        }

        .card-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-label {
            color: #666;
            font-size: 0.9rem;
        }

        .card-change {
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .positive {
            color: #2ecc71;
        }

        .negative {
            color: #e74c3c;
        }

        /* Charts and Tables */
        .dashboard-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-container, .recent-orders {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .chart-header, .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title, .table-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #f8f9fa;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Section Management */
        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-success {
            background-color: #2ecc71;
            color: white;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-warning {
            background-color: #f39c12;
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .dashboard-row {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .content {
                padding: 15px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            header {
                padding: 0 15px;
            }
        }

        /* Product Images */
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .search-box input {
            padding-left: 40px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">EC</div>
            <h2>E-Commerce Admin</h2>
        </div>
        <ul class="nav-links">
            <li><a href="#" class="active" data-section="dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#" data-section="products"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="#" data-section="categories"><i class="fas fa-list"></i> Categories</a></li>
            <li><a href="#" data-section="orders"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="#" data-section="users"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="#" data-section="offers"><i class="fas fa-tag"></i> Offers & Coupons</a></li>
            <li><a href="#" data-section="banners"><i class="fas fa-image"></i> Banners</a></li>
            <li><a href="#" data-section="payments"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="#" data-section="reviews"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="#" data-section="settings"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="#" data-section="profile"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="#" id="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="header-right">
                <div class="user-profile">
                    <div class="user-avatar">AJ</div>
                    <div>
                        <div class="user-name">Admin John</div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content">
            <!-- Dashboard Section -->
            <div class="section active" id="dashboard">
                <h1 class="page-title">Dashboard Overview</h1>
                
                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="card sales">
                        <div class="card-header">
                            <div>
                                <div class="card-value">$42,580</div>
                                <div class="card-label">Total Sales</div>
                                <div class="card-change positive"><i class="fas fa-arrow-up"></i> 12.5% from last month</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card orders">
                        <div class="card-header">
                            <div>
                                <div class="card-value">1,248</div>
                                <div class="card-label">Total Orders</div>
                                <div class="card-change positive"><i class="fas fa-arrow-up"></i> 8.2% from last month</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card products">
                        <div class="card-header">
                            <div>
                                <div class="card-value">356</div>
                                <div class="card-label">Total Products</div>
                                <div class="card-change positive"><i class="fas fa-arrow-up"></i> 5.7% from last month</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card users">
                        <div class="card-header">
                            <div>
                                <div class="card-value">4,892</div>
                                <div class="card-label">Total Users</div>
                                <div class="card-change positive"><i class="fas fa-arrow-up"></i> 3.4% from last month</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts and Recent Orders -->
                <div class="dashboard-row">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Sales Overview</h3>
                            <div>
                                <select id="chartPeriod">
                                    <option value="daily">Daily</option>
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                        <canvas id="salesChart"></canvas>
                    </div>
                    
                    <div class="recent-orders">
                        <div class="table-header">
                            <h3 class="table-title">Recent Orders</h3>
                            <a href="#" data-section="orders" class="btn btn-primary btn-sm">View All</a>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#ORD-7842</td>
                                    <td>John Smith</td>
                                    <td>$245.99</td>
                                    <td><span class="status status-delivered">Delivered</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-7841</td>
                                    <td>Sarah Johnson</td>
                                    <td>$129.50</td>
                                    <td><span class="status status-shipped">Shipped</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-7840</td>
                                    <td>Michael Brown</td>
                                    <td>$89.99</td>
                                    <td><span class="status status-processing">Processing</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-7839</td>
                                    <td>Emily Davis</td>
                                    <td>$320.00</td>
                                    <td><span class="status status-pending">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-7838</td>
                                    <td>Robert Wilson</td>
                                    <td>$65.50</td>
                                    <td><span class="status status-cancelled">Cancelled</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Products Section -->
            <div class="section" id="products">
                <h1 class="page-title">Product Management</h1>
                
                <div class="tabs">
                    <div class="tab active" data-tab="product-list">Product List</div>
                    <div class="tab" data-tab="add-product">Add New Product</div>
                </div>
                
                <div class="tab-content">
                    <!-- Product List Tab -->
                    <div class="tab-pane active" id="product-list">
                        <div class="search-filter">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="productSearch" placeholder="Search products...">
                            </div>
                            <select id="categoryFilter">
                                <option value="">All Categories</option>
                                <option value="electronics">Electronics</option>
                                <option value="fashion">Fashion</option>
                                <option value="home">Home & Kitchen</option>
                            </select>
                        </div>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <!-- Products will be loaded here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Add Product Tab -->
                    <div class="tab-pane" id="add-product">
                        <form id="addProductForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="productName">Product Name *</label>
                                    <input type="text" id="productName" required>
                                </div>
                                <div class="form-group">
                                    <label for="productCategory">Category *</label>
                                    <select id="productCategory" required>
                                        <option value="">Select Category</option>
                                        <option value="electronics">Electronics</option>
                                        <option value="fashion">Fashion</option>
                                        <option value="home">Home & Kitchen</option>
                                        <option value="beauty">Beauty & Health</option>
                                        <option value="sports">Sports</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="productPrice">Price ($) *</label>
                                    <input type="number" id="productPrice" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="productDiscount">Discount Price ($)</label>
                                    <input type="number" id="productDiscount" step="0.01">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="productStock">Stock Quantity *</label>
                                    <input type="number" id="productStock" required>
                                </div>
                                <div class="form-group">
                                    <label for="productImage">Product Image URL</label>
                                    <input type="text" id="productImage" placeholder="https://example.com/image.jpg">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="productDescription">Description</label>
                                <textarea id="productDescription" rows="4"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Other Sections (simplified for this example) -->
            <div class="section" id="categories">
                <h1 class="page-title">Category Management</h1>
                <p>Category management interface would go here with add, edit, delete functionality.</p>
            </div>
            
            <div class="section" id="orders">
                <h1 class="page-title">Order Management</h1>
                <p>Order management interface would go here with detailed order view and status updates.</p>
            </div>
            
            <div class="section" id="users">
                <h1 class="page-title">User Management</h1>
                <p>User management interface would go here with user details and block/unblock functionality.</p>
            </div>
            
            <div class="section" id="offers">
                <h1 class="page-title">Offers & Coupon Management</h1>
                <p>Offers and coupon management interface would go here.</p>
            </div>
            
            <div class="section" id="banners">
                <h1 class="page-title">Banner & Homepage Management</h1>
                <p>Banner management interface would go here.</p>
            </div>
            
            <div class="section" id="payments">
                <h1 class="page-title">Payment & Transaction Management</h1>
                <p>Payment and transaction management interface would go here.</p>
            </div>
            
            <div class="section" id="reviews">
                <h1 class="page-title">Reviews & Ratings Management</h1>
                <p>Reviews and ratings management interface would go here.</p>
            </div>
            
            <div class="section" id="settings">
                <h1 class="page-title">Settings</h1>
                <p>Store settings interface would go here.</p>
            </div>
            
            <div class="section" id="profile">
                <h1 class="page-title">Admin Profile</h1>
                <p>Admin profile management interface would go here.</p>
            </div>
        </div>
    </div>

    <!-- Modal for Edit Product -->
    <div class="modal" id="editProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form id="editProductForm">
                <div class="form-group">
                    <label for="editProductName">Product Name</label>
                    <input type="text" id="editProductName" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editProductPrice">Price ($)</label>
                        <input type="number" id="editProductPrice" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editProductStock">Stock Quantity</label>
                        <input type="number" id="editProductStock" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </div>
    </div>

    <script>
        // Sample product data
        const sampleProducts = [
            {
                id: 1,
                name: "Wireless Headphones",
                category: "electronics",
                price: 89.99,
                stock: 45,
                image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80"
            },
            {
                id: 2,
                name: "Running Shoes",
                category: "sports",
                price: 129.99,
                stock: 23,
                image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80"
            },
            {
                id: 3,
                name: "Coffee Maker",
                category: "home",
                price: 49.99,
                stock: 12,
                image: "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80"
            },
            {
                id: 4,
                name: "Smart Watch",
                category: "electronics",
                price: 199.99,
                stock: 34,
                image: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80"
            },
            {
                id: 5,
                name: "Backpack",
                category: "fashion",
                price: 39.99,
                stock: 67,
                image: "https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80"
            }
        ];

        // Initialize products
        let products = [...sampleProducts];

        // DOM Elements
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelectorAll('.nav-links a');
        const sections = document.querySelectorAll('.section');
        const tabs = document.querySelectorAll('.tab');
        const tabPanes = document.querySelectorAll('.tab-pane');
        const productTableBody = document.getElementById('productTableBody');
        const addProductForm = document.getElementById('addProductForm');
        const editProductModal = document.getElementById('editProductModal');
        const closeModal = document.querySelector('.close-modal');
        const editProductForm = document.getElementById('editProductForm');
        const productSearch = document.getElementById('productSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const logoutBtn = document.getElementById('logout');

        // Initialize Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Sales ($)',
                    data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 35000, 30000, 42000, 38000, 45000],
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Load products into table
        function loadProducts(filteredProducts = products) {
            productTableBody.innerHTML = '';
            
            filteredProducts.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><img src="${product.image}" alt="${product.name}" class="product-image"></td>
                    <td>${product.name}</td>
                    <td>${product.category.charAt(0).toUpperCase() + product.category.slice(1)}</td>
                    <td>$${product.price.toFixed(2)}</td>
                    <td>${product.stock}</td>
                    <td class="action-buttons">
                        <button class="btn btn-primary btn-sm edit-product" data-id="${product.id}">Edit</button>
                        <button class="btn btn-danger btn-sm delete-product" data-id="${product.id}">Delete</button>
                    </td>
                `;
                productTableBody.appendChild(row);
            });
            
            // Add event listeners to edit and delete buttons
            document.querySelectorAll('.edit-product').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = parseInt(this.getAttribute('data-id'));
                    openEditModal(productId);
                });
            });
            
            document.querySelectorAll('.delete-product').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = parseInt(this.getAttribute('data-id'));
                    if (confirm('Are you sure you want to delete this product?')) {
                        deleteProduct(productId);
                    }
                });
            });
        }

        // Open edit product modal
        function openEditModal(productId) {
            const product = products.find(p => p.id === productId);
            if (!product) return;
            
            document.getElementById('editProductName').value = product.name;
            document.getElementById('editProductPrice').value = product.price;
            document.getElementById('editProductStock').value = product.stock;
            
            // Store product ID in form for reference
            editProductForm.setAttribute('data-product-id', productId);
            
            editProductModal.style.display = 'flex';
        }

        // Delete product
        function deleteProduct(productId) {
            products = products.filter(p => p.id !== productId);
            loadProducts();
            
            // Update summary card
            document.querySelector('.products .card-value').textContent = products.length;
        }

        // Filter products
        function filterProducts() {
            const searchTerm = productSearch.value.toLowerCase();
            const category = categoryFilter.value;
            
            let filtered = products;
            
            if (searchTerm) {
                filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm));
            }
            
            if (category) {
                filtered = filtered.filter(p => p.category === category);
            }
            
            loadProducts(filtered);
        }

        // Event Listeners
        // Toggle sidebar on mobile
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Switch between sections
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Close sidebar on mobile after clicking a link
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                }
                
                // Remove active class from all links
                navLinks.forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                link.classList.add('active');
                
                // Hide all sections
                sections.forEach(section => section.classList.remove('active'));
                
                // Show selected section
                const sectionId = link.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });

        // Switch between tabs
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Hide all tab panes
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Show selected tab pane
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Add new product
        addProductForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const newProduct = {
                id: products.length > 0 ? Math.max(...products.map(p => p.id)) + 1 : 1,
                name: document.getElementById('productName').value,
                category: document.getElementById('productCategory').value,
                price: parseFloat(document.getElementById('productPrice').value),
                stock: parseInt(document.getElementById('productStock').value),
                image: document.getElementById('productImage').value || 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            };
            
            products.push(newProduct);
            loadProducts();
            
            // Reset form
            addProductForm.reset();
            
            // Switch to product list tab
            tabs[0].click();
            
            // Update summary card
            document.querySelector('.products .card-value').textContent = products.length;
            
            alert('Product added successfully!');
        });

        // Edit product
        editProductForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const productId = parseInt(editProductForm.getAttribute('data-product-id'));
            const productIndex = products.findIndex(p => p.id === productId);
            
            if (productIndex !== -1) {
                products[productIndex].name = document.getElementById('editProductName').value;
                products[productIndex].price = parseFloat(document.getElementById('editProductPrice').value);
                products[productIndex].stock = parseInt(document.getElementById('editProductStock').value);
                
                loadProducts();
                editProductModal.style.display = 'none';
                
                alert('Product updated successfully!');
            }
        });

        // Close modal
        closeModal.addEventListener('click', () => {
            editProductModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === editProductModal) {
                editProductModal.style.display = 'none';
            }
        });

        // Search and filter products
        productSearch.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);

        // Logout
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                alert('Logged out successfully!');
                // In a real app, you would redirect to login page
                // window.location.href = '/login';
            }
        });

        // Update chart based on period selection
        document.getElementById('chartPeriod').addEventListener('change', function() {
            const period = this.value;
            
            // In a real app, you would fetch new data based on the selected period
            // For demo, we'll just update the chart with sample data
            if (period === 'daily') {
                salesChart.data.labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                salesChart.data.datasets[0].data = [1200, 1900, 1500, 2500, 2200, 3000, 2800];
            } else if (period === 'monthly') {
                salesChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                salesChart.data.datasets[0].data = [12000, 19000, 15000, 25000, 22000, 30000, 28000, 35000, 30000, 42000, 38000, 45000];
            } else {
                salesChart.data.labels = ['2020', '2021', '2022', '2023', '2024'];
                salesChart.data.datasets[0].data = [85000, 125000, 185000, 240000, 320000];
            }
            
            salesChart.update();
        });

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', () => {
            // Load initial products
            loadProducts();
            
            // Set current date in header (optional)
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            // You could add a date display in the header if needed
        });
    </script>
</body>
</html>