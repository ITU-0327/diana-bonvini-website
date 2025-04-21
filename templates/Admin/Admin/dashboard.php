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
 */
?>

<style>
    /* Dashboard Styles */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .stats-box {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .stats-card {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        height: 100%;
    }

    .stats-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        margin-right: 15px;
        color: white;
    }

    .green-bg {
        background-color: #4CAF50;
    }

    .orange-bg {
        background-color: #FF9800;
    }

    .red-bg {
        background-color: #F44336;
    }

    .stats-title {
        font-size: 14px;
        color: #777;
        margin-bottom: 5px;
    }

    .stats-number {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .stats-desc {
        font-size: 12px;
        color: #999;
    }

    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .chart-title {
        font-size: 18px;
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

    .legend-item {
        display: flex;
        align-items: center;
        margin-right: 20px;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 5px;
    }

    .chart-legend {
        display: flex;
        margin-top: 10px;
    }

    .reviews-table {
        width: 100%;
        border-collapse: collapse;
    }

    .reviews-table th {
        text-align: left;
        padding: 10px;
        border-bottom: 1px solid #e0e0e0;
        color: #777;
        font-weight: 500;
    }

    .reviews-table td {
        padding: 10px;
        border-bottom: 1px solid #e0e0e0;
    }

    .reviews-table tr:hover {
        background-color: #f9f9f9;
    }

    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        color: white;
    }

    .status-approved {
        background-color: #4CAF50;
    }

    .status-pending {
        background-color: #FF9800;
    }

    .rating-stars {
        color: #FFC107;
    }

    .circular-progress {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto;
    }

    .progress-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #f3f3f3;
        background: conic-gradient(#4e73df 0% 72%, #f3f3f3 72% 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .progress-circle::before {
        content: "";
        position: absolute;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: white;
    }

    .progress-value {
        position: absolute;
        font-size: 24px;
        font-weight: 600;
        color: #333;
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

    .product-image {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }

    .time-badge {
        color: #777;
        font-size: 12px;
    }
</style>

<div class="dashboard-header">
    <div>
        <h2>Ecommerce Dashboard</h2>
        <p>Here's what's going on at your business right now</p>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon green-bg">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div>
                <div class="stats-number"><?= h($ordersCount) ?> new orders</div>
                <div class="stats-desc">Awaiting processing</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon orange-bg">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div>
                <div class="stats-number"><?= h($pendingOrdersCount) ?> orders</div>
                <div class="stats-desc">On hold</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon red-bg">
                <i class="fas fa-paint-brush"></i>
            </div>
            <div>
                <div class="stats-number"><?= h($artworksCount) ?> artworks</div>
                <div class="stats-desc">Out of stock</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Left column with Sales Chart -->
    <div class="col-md-8">
        <!-- Total Sales Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <div class="chart-title">Total sales</div>
                <select class="date-selector">
                    <option>Mar 1 - 31, 2025</option>
                    <option>Apr 1 - 30, 2025</option>
                    <option>Last 30 days</option>
                </select>
            </div>
            <div style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="chart-container">
            <div class="chart-header">
                <div class="chart-title">Latest reviews</div>
                <div>
                    <input type="text" placeholder="Search..." class="form-control form-control-sm" style="width: 200px;">
                </div>
            </div>
            <table class="reviews-table">
                <thead>
                <tr>
                    <th width="5%"></th>
                    <th width="30%">PRODUCT</th>
                    <th width="15%">CUSTOMER</th>
                    <th width="10%">RATING</th>
                    <th width="25%">REVIEW</th>
                    <th width="10%">STATUS</th>
                    <th width="5%">TIME</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><input type="checkbox"></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40" class="product-image mr-2">
                            <span>Sunset over Mountain (Oil on Canvas)</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle mr-2">R</div>
                            Richard Dawkins
                        </div>
                    </td>
                    <td>
                        <div class="rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </td>
                    <td>This artwork is fantastic! I was looking for something to brighten my living room, and this is perfect.</td>
                    <td><span class="status-badge status-approved">APPROVED</span></td>
                    <td><span class="time-badge">Just now</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox"></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40" class="product-image mr-2">
                            <span>Proofreading Service - Business Proposal</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle mr-2">A</div>
                            Ashley Garrett
                        </div>
                    </td>
                    <td>
                        <div class="rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                    </td>
                    <td>The service was excellent. The feedback was delivered ahead of schedule and very thorough.</td>
                    <td><span class="status-badge status-approved">APPROVED</span></td>
                    <td><span class="time-badge">Just now</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox"></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40" class="product-image mr-2">
                            <span>Ocean Waves (Photography Print)</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle mr-2">W</div>
                            Woodrow Burton
                        </div>
                    </td>
                    <td>
                        <div class="rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </td>
                    <td>It's a beautiful piece. Once you've seen it in person, there's no going back. My first purchase from Diana and definitely not my last.</td>
                    <td><span class="status-badge status-pending">PENDING</span></td>
                    <td><span class="time-badge">Just now</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right column with stats -->
    <div class="col-md-4">
        <!-- Orders Stats -->
        <div class="stats-box mb-4">
            <div class="d-flex justify-content-between mb-2">
                <div>
                    <div style="font-size: 14px; color: #777;">Total orders</div>
                    <div style="font-size: 12px; color: #999;">Last 7 days</div>
                </div>
                <div style="font-size: 24px; font-weight: 600;">16,247</div>
            </div>
            <div style="height: 100px;">
                <canvas id="ordersChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #4e73df;"></div>
                    <div>Completed</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #e0e0e0;"></div>
                    <div>Pending payment</div>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <div>52%</div>
                <div>48%</div>
            </div>
        </div>

        <!-- New Customers Stats -->
        <div class="stats-box mb-4">
            <div class="d-flex justify-content-between mb-2">
                <div>
                    <div style="font-size: 14px; color: #777;">New customers</div>
                    <div style="font-size: 12px; color: #999;">Last 7 days</div>
                </div>
                <div style="font-size: 24px; font-weight: 600;">356</div>
            </div>
            <div style="height: 100px;">
                <canvas id="customersChart"></canvas>
            </div>
        </div>

        <!-- Top Coupons -->
        <div class="stats-box mb-4">
            <div>
                <div style="font-size: 14px; color: #777;">Top coupons</div>
                <div style="font-size: 12px; color: #999;">Last 7 days</div>
            </div>
            <div class="d-flex justify-content-center my-3">
                <div class="circular-progress">
                    <div class="progress-circle"></div>
                    <div class="progress-value">72%</div>
                </div>
            </div>
            <div class="chart-legend justify-content-center">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #4e73df;"></div>
                    <div>Percentage discount</div>
                </div>
                <div class="legend-item ml-4">
                    <div>72%</div>
                </div>
            </div>
            <div class="chart-legend justify-content-center">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #e0e0e0;"></div>
                    <div>Fixed card discount</div>
                </div>
                <div class="legend-item ml-4">
                    <div>18%</div>
                </div>
            </div>
            <div class="chart-legend justify-content-center">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #4e73df; opacity: 0.6;"></div>
                    <div>Fixed product discount</div>
                </div>
                <div class="legend-item ml-4">
                    <div>10%</div>
                </div>
            </div>
        </div>

        <!-- Paying vs Non-paying -->
        <div class="stats-box">
            <div>
                <div style="font-size: 14px; color: #777;">Paying vs non paying</div>
                <div style="font-size: 12px; color: #999;">Last 7 days</div>
            </div>
            <div class="d-flex justify-content-center my-3">
                <div style="width: 120px; height: 120px;">
                    <canvas id="payingChart"></canvas>
                </div>
            </div>
            <div class="chart-legend justify-content-center">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #4e73df;"></div>
                    <div>Paying customer</div>
                </div>
                <div class="legend-item ml-4">
                    <div>30%</div>
                </div>
            </div>
            <div class="chart-legend justify-content-center">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #e0e0e0;"></div>
                    <div>Non-paying customer</div>
                </div>
                <div class="legend-item ml-4">
                    <div>70%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sales Chart
        var salesCtx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['01 May', '05 May', '10 May', '15 May', '20 May', '25 May', '30 May'],
                datasets: [{
                    label: 'Artwork Sales',
                    borderColor: '#4e73df',
                    backgroundColor: 'transparent',
                    data: [3000, 3500, 3200, 4800, 4200, 5800, 4500],
                    borderWidth: 2,
                    tension: 0.4
                }, {
                    label: 'Writing Services',
                    borderColor: '#36b9cc',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    data: [2000, 2000, 2000, 3800, 2800, 3400, 3800],
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            borderDash: [3, 3],
                        },
                        beginAtZero: true
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Orders Bar Chart
        var ordersCtx = document.getElementById('ordersChart').getContext('2d');
        var ordersChart = new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Orders',
                    backgroundColor: '#4e73df',
                    data: [75, 55, 65, 45, 75, 65, 55],
                    barThickness: 8,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        display: false
                    },
                    x: {
                        display: false
                    }
                }
            }
        });

        // Customers Line Chart
        var customersCtx = document.getElementById('customersChart').getContext('2d');
        var customersChart = new Chart(customersCtx, {
            type: 'line',
            data: {
                labels: ['01 May', '02 May', '03 May', '04 May', '05 May', '06 May', '07 May'],
                datasets: [{
                    label: 'New Customers',
                    borderColor: '#4e73df',
                    backgroundColor: 'transparent',
                    data: [35, 40, 38, 50, 45, 60, 70],
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        display: false
                    },
                    x: {
                        display: false
                    }
                }
            }
        });

        // Paying vs Non-paying Chart
        var payingCtx = document.getElementById('payingChart').getContext('2d');
        var payingChart = new Chart(payingCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paying', 'Non-paying'],
                datasets: [{
                    data: [30, 70],
                    backgroundColor: ['#4e73df', '#e0e0e0'],
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
    });
</script>
