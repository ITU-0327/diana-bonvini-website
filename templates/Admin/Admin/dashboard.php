<?php
/**
 * Admin Dashboard View
 *
 * @var \App\View\AppView $this
 * @var int $artworksCount
 * @var int $ordersCount
 * @var int $pendingOrdersCount
 * @var int $writingRequestsCount
 * @var int $usersCount
 * @var int $lowStockCount
 * @var int $pendingApprovalCount
 * @var int $upcomingBookingsCount
 * @var int $pendingQuotesCount
 * @var int $completedServicesCount
 * @var float $totalRevenueToday
 * @var float $totalRevenueWeek
 * @var float $totalRevenueMonth
 */
?>

<style>
    /* Dashboard Styles */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .dashboard-header h2 {
        margin: 0;
        color: #333;
        font-weight: 600;
    }

    .dashboard-header p {
        color: #777;
        margin: 5px 0 0 0;
    }

    .period-selector {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 8px 15px;
        font-size: 14px;
        color: #333;
    }

    .stats-card {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .stats-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 10px;
        margin-right: 15px;
        color: white;
        font-size: 18px;
    }

    .turquoise-bg {
        background-color: #2A9D8F;
    }

    .purple-bg {
        background-color: #8E44AD;
    }

    .orange-bg {
        background-color: #FF9800;
    }

    .red-bg {
        background-color: #F44336;
    }

    .blue-bg {
        background-color: #3498DB;
    }

    .green-bg {
        background-color: #4CAF50;
    }

    .pink-bg {
        background-color: #E91E63;
    }

    .gold-bg {
        background-color: #F39C12;
    }

    .stats-content {
        flex-grow: 1;
    }

    .stats-title {
        font-size: 14px;
        color: #777;
        margin-bottom: 5px;
    }

    .stats-number {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }

    .stats-desc {
        font-size: 12px;
        color: #999;
    }

    .stats-change {
        display: flex;
        align-items: center;
        font-size: 12px;
        font-weight: 500;
    }

    .change-up {
        color: #4CAF50;
    }

    .change-down {
        color: #F44336;
    }

    .stats-cta {
        font-size: 12px;
        color: #2A9D8F;
        text-decoration: none;
        display: flex;
        align-items: center;
        margin-top: 5px;
    }

    .stats-cta i {
        margin-left: 3px;
        font-size: 10px;
    }

    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .chart-title {
        font-size: 16px;
        color: #333;
        font-weight: 500;
    }

    .date-selector {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 5px 10px;
        font-size: 14px;
        color: #333;
    }

    .table-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        text-align: left;
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
        color: #777;
        font-weight: 500;
        font-size: 13px;
    }

    .data-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }

    .data-table tr:hover {
        background-color: #f9f9f9;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        color: white;
    }

    .status-completed {
        background-color: #4CAF50;
    }

    .status-pending {
        background-color: #FF9800;
    }

    .status-processing {
        background-color: #3498DB;
    }

    .status-low {
        background-color: #F44336;
    }

    .avatar-circle {
        width: 30px;
        height: 30px;
        background-color: #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        font-weight: 600;
    }

    .action-button {
        background-color: #f1f3f5;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        color: #555;
        font-size: 12px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .action-button:hover {
        background-color: #e0e0e0;
    }

    .action-button.primary {
        background-color: #2A9D8F;
        color: white;
    }

    .action-button.primary:hover {
        background-color: #218a7e;
    }

    .tab-container {
        margin-bottom: 20px;
    }

    .tab-nav {
        display: flex;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 15px;
    }

    .tab-item {
        padding: 10px 20px;
        cursor: pointer;
        font-size: 14px;
        color: #777;
        border-bottom: 2px solid transparent;
        transition: color 0.2s, border-color 0.2s;
    }

    .tab-item.active {
        color: #2A9D8F;
        border-bottom: 2px solid #2A9D8F;
    }

    .widget-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .widget {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        width: calc(33.333% - 14px);
    }

    .widget-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .widget-title {
        font-size: 16px;
        color: #333;
        font-weight: 500;
    }

    .widget-action {
        color: #2A9D8F;
        font-size: 12px;
        cursor: pointer;
    }

    .custom-tooltip {
        position: relative;
        display: inline-block;
    }

    .custom-tooltip .tooltip-text {
        visibility: hidden;
        width: 200px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -100px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 12px;
    }

    .custom-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    /* Responsive fixes */
    @media (max-width: 992px) {
        .widget {
            width: calc(50% - 10px);
        }
    }

    @media (max-width: 768px) {
        .widget {
            width: 100%;
        }
    }
</style>

<div class="dashboard-header">
    <div>
        <h2>Dashboard</h2>
        <p>Welcome back, Diana. Here's what's happening with your business.</p>
    </div>
    <select class="period-selector">
        <option>Today</option>
        <option>Yesterday</option>
        <option>Last 7 days</option>
        <option selected>Last 30 days</option>
        <option>This month</option>
        <option>Last month</option>
    </select>
</div>

<!-- Sales Overview Section -->
<h4 class="mb-3">Sales Overview</h4>
<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stats-card">
            <div class="stats-icon turquoise-bg">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stats-content">
                <div class="stats-title">Total Revenue</div>
                <div class="stats-number">$<?= number_format($totalRevenueMonth, 2) ?></div>
                <div class="stats-change change-up">
                    <i class="fas fa-arrow-up mr-1"></i> 12.5% from last month
                </div>
                <a href="#" class="stats-cta">View details <i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stats-card">
            <div class="stats-icon purple-bg">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stats-content">
                <div class="stats-title">Completed Orders</div>
                <div class="stats-number"><?= h($ordersCount - $pendingOrdersCount) ?></div>
                <div class="stats-change change-up">
                    <i class="fas fa-arrow-up mr-1"></i> 8.3% from last month
                </div>
                <a href="#" class="stats-cta">View orders <i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stats-card">
            <div class="stats-icon orange-bg">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-content">
                <div class="stats-title">Pending Orders</div>
                <div class="stats-number"><?= h($pendingOrdersCount) ?></div>
                <div class="stats-desc">Awaiting processing or payment</div>
                <a href="#" class="stats-cta">Process now <i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Sales Chart -->
        <div class="chart-container mb-4">
            <div class="chart-header">
                <div class="chart-title">Revenue Breakdown</div>
                <select class="date-selector">
                    <option>Today</option>
                    <option>Yesterday</option>
                    <option>Last 7 days</option>
                    <option selected>Last 30 days</option>
                    <option>This month</option>
                    <option>Last month</option>
                </select>
            </div>
            <div style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="d-flex justify-content-around mt-3">
                <div class="text-center">
                    <div style="font-size: 14px; color: #777;">Artwork Sales</div>
                    <div style="font-size: 18px; font-weight: 600; color: #2A9D8F;">$12,540</div>
                </div>
                <div class="text-center">
                    <div style="font-size: 14px; color: #777;">Writing Services</div>
                    <div style="font-size: 18px; font-weight: 600; color: #8E44AD;">$8,350</div>
                </div>
                <div class="text-center">
                    <div style="font-size: 14px; color: #777;">Proofreading</div>
                    <div style="font-size: 18px; font-weight: 600; color: #F39C12;">$5,210</div>
                </div>
            </div>
        </div>

        <!-- Tabs Container for Tables -->
        <div class="tab-container">
            <div class="tab-nav">
                <div class="tab-item active" data-tab="pending-orders">Pending Orders</div>
                <div class="tab-item" data-tab="low-stock">Low Stock Alert</div>
                <div class="tab-item" data-tab="upcoming-bookings">Upcoming Bookings</div>
            </div>

            <!-- Pending Orders Table -->
            <div class="tab-content" id="pending-orders-content">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>#ORD-2541</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">J</div>
                                    <div>James Wilson</div>
                                </div>
                            </td>
                            <td>Ocean Waves (Photography)</td>
                            <td>$350.00</td>
                            <td><span class="status-badge status-pending">Pending Payment</span></td>
                            <td>Apr 20, 2025</td>
                            <td>
                                <button class="action-button primary">Process</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-2540</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">S</div>
                                    <div>Sarah Johnson</div>
                                </div>
                            </td>
                            <td>Abstract Dreams (Oil Painting)</td>
                            <td>$520.00</td>
                            <td><span class="status-badge status-processing">Processing</span></td>
                            <td>Apr 19, 2025</td>
                            <td>
                                <button class="action-button primary">Complete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-2539</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">M</div>
                                    <div>Michael Chang</div>
                                </div>
                            </td>
                            <td>Urban Life (Photography)</td>
                            <td>$250.00</td>
                            <td><span class="status-badge status-pending">Pending Payment</span></td>
                            <td>Apr 18, 2025</td>
                            <td>
                                <button class="action-button primary">Process</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-2538</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">E</div>
                                    <div>Elena Martinez</div>
                                </div>
                            </td>
                            <td>Sunset Colors (Oil Painting)</td>
                            <td>$450.00</td>
                            <td><span class="status-badge status-processing">Processing</span></td>
                            <td>Apr 17, 2025</td>
                            <td>
                                <button class="action-button primary">Complete</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <a href="#" class="stats-cta">View all pending orders <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert Table -->
            <div class="tab-content" id="low-stock-content" style="display: none;">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>#ART-154</td>
                            <td>Sunset over Mountain (Print)</td>
                            <td>Photography</td>
                            <td>2</td>
                            <td><span class="status-badge status-low">Low Stock</span></td>
                            <td>$150.00</td>
                            <td>
                                <button class="action-button primary">Restock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ART-147</td>
                            <td>Abstract Pattern (Digital Print)</td>
                            <td>Digital Art</td>
                            <td>1</td>
                            <td><span class="status-badge status-low">Low Stock</span></td>
                            <td>$120.00</td>
                            <td>
                                <button class="action-button primary">Restock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ART-162</td>
                            <td>Ocean Waves (Photography)</td>
                            <td>Photography</td>
                            <td>3</td>
                            <td><span class="status-badge status-low">Low Stock</span></td>
                            <td>$175.00</td>
                            <td>
                                <button class="action-button primary">Restock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ART-138</td>
                            <td>City Skyline (Oil Canvas)</td>
                            <td>Painting</td>
                            <td>1</td>
                            <td><span class="status-badge status-low">Low Stock</span></td>
                            <td>$450.00</td>
                            <td>
                                <button class="action-button primary">Restock</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <a href="#" class="stats-cta">Manage inventory <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Bookings Table -->
            <div class="tab-content" id="upcoming-bookings-content" style="display: none;">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Client</th>
                            <th>Service Type</th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>#BKG-125</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">R</div>
                                    <div>Robert Chen</div>
                                </div>
                            </td>
                            <td>GAMSAT Preparation</td>
                            <td>Apr 23, 2025 - 10:00 AM</td>
                            <td>60 min</td>
                            <td><span class="status-badge status-pending">Confirmed</span></td>
                            <td>
                                <button class="action-button primary">Join Meeting</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#BKG-124</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">L</div>
                                    <div>Leila Patel</div>
                                </div>
                            </td>
                            <td>Manuscript Review</td>
                            <td>Apr 24, 2025 - 2:00 PM</td>
                            <td>90 min</td>
                            <td><span class="status-badge status-pending">Confirmed</span></td>
                            <td>
                                <button class="action-button primary">Reschedule</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#BKG-123</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">D</div>
                                    <div>Daniel Smith</div>
                                </div>
                            </td>
                            <td>Proofreading</td>
                            <td>Apr 25, 2025 - 11:30 AM</td>
                            <td>45 min</td>
                            <td><span class="status-badge status-pending">Confirmed</span></td>
                            <td>
                                <button class="action-button primary">Reschedule</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#BKG-122</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2">J</div>
                                    <div>Jessica Wong</div>
                                </div>
                            </td>
                            <td>Writing Consultation</td>
                            <td>Apr 26, 2025 - 3:00 PM</td>
                            <td>60 min</td>
                            <td><span class="status-badge status-pending">Confirmed</span></td>
                            <td>
                                <button class="action-button primary">Reschedule</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <a href="#" class="stats-cta">View full calendar <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Inventory Status Widget -->
        <div class="chart-container mb-4">
            <div class="chart-header">
                <div class="chart-title">Inventory Status</div>
                <div class="custom-tooltip">
                    <i class="fas fa-info-circle"></i>
                    <span class="tooltip-text">Overview of your current art inventory status</span>
                </div>
            </div>
            <div style="height: 200px;">
                <canvas id="inventoryChart"></canvas>
            </div>
            <div class="d-flex justify-content-around mt-3">
                <div class="text-center">
                    <div style="font-size: 14px; color: #777;">In Stock</div>
                    <div style="font-size: 18px; font-weight: 600; color: #2A9D8F;"><?= h($artworksCount - $lowStockCount - $pendingApprovalCount) ?></div>
                </div>
                <div class="text-center">
                    <div style="font-size: 14px; color: #777;">Low Stock</div>
                    <div style="font-size: 18px; font-weight: 600; color: #F39C12;"><?= h($lowStockCount) ?></div>
                </div>
                <div class="text-center">
                    <div style="font-size: 14px; color: #777;">Pending</div>
                    <div style="font-size: 18px; font-weight: 600; color: #8E44AD;"><?= h($pendingApprovalCount) ?></div>
                </div>
            </div>
        </div>

        <!-- Service & Bookings Widget -->
        <div class="chart-container mb-4">
            <div class="chart-header">
                <div class="chart-title">Service Bookings</div>
                <div class="custom-tooltip">
                    <i class="fas fa-info-circle"></i>
                    <span class="tooltip-text">Overview of your writing and proofreading services</span>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div style="font-size: 14px; color: #777;">Upcoming Bookings</div>
                    <div style="font-size: 20px; font-weight: 600;"><?= h($upcomingBookingsCount) ?></div>
                </div>
                <div>
                    <div style="font-size: 14px; color: #777;">Pending Quotes</div>
                    <div style="font-size: 20px; font-weight: 600;"><?= h($pendingQuotesCount) ?></div>
                </div>
                <div>
                    <div style="font-size: 14px; color: #777;">Completed</div>
                    <div style="font-size: 20px; font-weight: 600;"><?= h($completedServicesCount) ?></div>
                </div>
            </div>
            <div style="height: 170px;">
                <canvas id="servicesChart"></canvas>
            </div>
        </div>

        <!-- Pending Quotes Widget -->
        <div class="chart-container">
            <div class="chart-header">
                <div class="chart-title">Pending Quotes</div>
                <a href="#" class="stats-cta">View all <i class="fas fa-chevron-right"></i></a>
            </div>
            <table class="data-table">
                <tbody>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle mr-2">A</div>
                            <div>
                                <div>Alex Thompson</div>
                                <div style="font-size: 12px; color: #777;">Business Proposal</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div>5,000 words</div>
                            <div style="font-size: 12px; color: #777;">Proofreading</div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 14px; font-weight: 600;">$250.00</div>
                    </td>
                    <td>
                        <button class="action-button primary">Quote</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle mr-2">M</div>
                            <div>
                                <div>Maria Gonzalez</div>
                                <div style="font-size: 12px; color: #777;">GAMSAT Essay</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div>2,500 words</div>
                            <div style="font-size: 12px; color: #777;">Editing</div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 14px; font-weight: 600;">$175.00</div>
                    </td>
                    <td>
                        <button class="action-button primary">Quote</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle mr-2">T</div>
                            <div>
                                <div>Thomas Lee</div>
                                <div style="font-size: 12px; color: #777;">Research Paper</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div>4,200 words</div>
                            <div style="font-size: 12px; color: #777;">Proofreading</div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 14px; font-weight: 600;">$210.00</div>
                    </td>
                    <td>
                        <button class="action-button primary">Quote</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript for Charts and Interactions -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab Functionality
        const tabItems = document.querySelectorAll('.tab-item');
        const tabContents = document.querySelectorAll('.tab-content');

        tabItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all tabs
                tabItems.forEach(tab => tab.classList.remove('active'));

                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab contents
                tabContents.forEach(content => content.style.display = 'none');

                // Show the corresponding tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-content').style.display = 'block';
            });
        });

        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['1 Apr', '5 Apr', '10 Apr', '15 Apr', '20 Apr', '25 Apr', '30 Apr'],
                datasets: [
                    {
                        label: 'Artwork Sales',
                        borderColor: '#2A9D8F',
                        backgroundColor: 'rgba(42, 157, 143, 0.1)',
                        data: [3000, 3500, 3200, 4800, 4200, 5800, 4500],
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Writing Services',
                        borderColor: '#8E44AD',
                        backgroundColor: 'rgba(142, 68, 173, 0.1)',
                        data: [2000, 2200, 2500, 3200, 2800, 3400, 3800],
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Proofreading',
                        borderColor: '#F39C12',
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        data: [1000, 1200, 1500, 1700, 1600, 1800, 2100],
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [3, 3],
                        },
                        ticks: {
                            callback: function(value) {
                                return ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    intersect: false
                }
            }
        });

        // Inventory Chart
        const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
        const inventoryChart = new Chart(inventoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Pending Approval'],
                datasets: [{
                    data: [65, 15, 20],
                    backgroundColor: [
                        '#2A9D8F', // Turquoise
                        '#F39C12', // Gold
                        '#8E44AD'  // Purple
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Services Chart
        const servicesCtx = document.getElementById('servicesChart').getContext('2d');
        const servicesChart = new Chart(servicesCtx, {
            type: 'bar',
            data: {
                labels: ['Writing', 'Proofreading', 'GAMSAT', 'Editing'],
                datasets: [{
                    label: 'Completed',
                    backgroundColor: '#2A9D8F',
                    data: [12, 15, 8, 6],
                    barThickness: 10,
                    borderRadius: 4
                }, {
                    label: 'Upcoming',
                    backgroundColor: '#8E44AD',
                    data: [5, 7, 10, 4],
                    barThickness: 10,
                    borderRadius: 4
                }, {
                    label: 'Pending',
                    backgroundColor: '#F39C12',
                    data: [3, 2, 5, 1],
                    barThickness: 10,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 10,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [3, 3],
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
