<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$pageTitle = 'Admin Dashboard';
$db = getDB();

// ---- Key Stats ----
$totalRevenue = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalOrders  = $db->query("SELECT COUNT(*) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalCustomers = $db->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalProducts  = $db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();

$revenueToday = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetchColumn();
$ordersToday  = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetchColumn();

// ---- Daily Sales (last 14 days) ----
$dailySales = $db->query("
    SELECT DATE(created_at) as day, COALESCE(SUM(total_amount),0) as revenue, COUNT(*) as orders
    FROM orders WHERE status!='cancelled' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(created_at) ORDER BY day ASC
")->fetchAll();

// ---- Monthly Sales (last 12 months) ----
$monthlySales = $db->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') as month, DATE_FORMAT(created_at,'%b %Y') as label,
           COALESCE(SUM(total_amount),0) as revenue, COUNT(*) as orders
    FROM orders WHERE status!='cancelled' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month ASC
")->fetchAll();

// ---- Yearly Sales ----
$yearlySales = $db->query("
    SELECT YEAR(created_at) as year, COALESCE(SUM(total_amount),0) as revenue, COUNT(*) as orders
    FROM orders WHERE status!='cancelled'
    GROUP BY YEAR(created_at) ORDER BY year ASC
")->fetchAll();

// ---- Top Products ----
$topProducts = $db->query("
    SELECT p.name, p.category, p.price, SUM(oi.quantity) as units_sold,
           SUM(oi.quantity * oi.unit_price) as revenue
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status != 'cancelled'
    GROUP BY oi.product_id ORDER BY units_sold DESC LIMIT 10
")->fetchAll();

// ---- Recent Orders ----
$recentOrders = $db->query("
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC LIMIT 15
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Playground</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <span>Admin Panel</span>
        </div>
        <nav class="admin-nav">
            <a href="<?= SITE_URL ?>/admin/dashboard.php" class="active">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="<?= SITE_URL ?>/admin/orders.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                Orders
            </a>
            <a href="<?= SITE_URL ?>/admin/products.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                Products
            </a>
            <a href="<?= SITE_URL ?>/admin/customers.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                Customers
            </a>
            <a href="<?= SITE_URL ?>/admin/reports.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Reports
            </a>
            <a href="<?= SITE_URL ?>/index.php" style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                View Store
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <div>
                <h1>Dashboard</h1>
                <div class="admin-date"><?= date('l, F j, Y') ?></div>
            </div>
            <div style="display:flex;gap:0.75rem;">
                <a href="<?= SITE_URL ?>/admin/reports.php" class="btn btn-outline btn-sm">Download Report</a>
                <a href="<?= SITE_URL ?>/admin/products.php?action=new" class="btn btn-primary btn-sm">+ Add Product</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-label">Total Revenue</div>
                <div class="stat-card-value">RM<?= number_format($totalRevenue, 2) ?></div>
                <div class="stat-card-sub">All time</div>
                <div class="stat-card-icon">RM</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Today's Revenue</div>
                <div class="stat-card-value">RM<?= number_format($revenueToday, 2) ?></div>
                <div class="stat-card-sub"><?= $ordersToday ?> orders today</div>
                <div class="stat-card-icon">↑</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Total Orders</div>
                <div class="stat-card-value"><?= number_format($totalOrders) ?></div>
                <div class="stat-card-sub">All time</div>
                <div class="stat-card-icon">◈</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Customers</div>
                <div class="stat-card-value"><?= number_format($totalCustomers) ?></div>
                <div class="stat-card-sub"><?= $totalProducts ?> active products</div>
                <div class="stat-card-icon">✦</div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Sales Overview</h3>
                <div class="tab-group">
                    <button class="tab-btn active" data-period="daily">Daily</button>
                    <button class="tab-btn" data-period="monthly">Monthly</button>
                    <button class="tab-btn" data-period="yearly">Yearly</button>
                </div>
            </div>

            <!-- Daily Chart -->
            <div class="report-section chart-container" data-period="daily">
                <div id="chart-daily"></div>
            </div>

            <!-- Monthly Chart -->
            <div class="report-section chart-container" data-period="monthly" style="display:none;">
                <div id="chart-monthly"></div>
            </div>

            <!-- Yearly Chart -->
            <div class="report-section chart-container" data-period="yearly" style="display:none;">
                <div id="chart-yearly"></div>
            </div>
        </div>

        <!-- Sales Table by Period -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
            <!-- Top Products -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Top Products</h3>
                    <span style="font-size:0.75rem;color:var(--muted);">By units sold</span>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Units</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--muted);">No sales data yet</td></tr>
                        <?php endif; ?>
                        <?php foreach ($topProducts as $i => $prod): ?>
                        <tr>
                            <td>
                                <div style="font-weight:500;color:var(--off-white);"><?= htmlspecialchars($prod['name']) ?></div>
                                <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($prod['category']) ?></div>
                            </td>
                            <td><?= $prod['units_sold'] ?></td>
                            <td style="color:var(--accent);">$<?= number_format($prod['revenue'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Monthly breakdown table -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Monthly Breakdown</h3>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr><th>Month</th><th>Orders</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthlySales)): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--muted);">No data yet</td></tr>
                        <?php endif; ?>
                        <?php foreach (array_reverse($monthlySales) as $row): ?>
                        <tr>
                            <td><?= $row['label'] ?></td>
                            <td><?= $row['orders'] ?></td>
                            <td style="color:var(--accent);">$<?= number_format($row['revenue'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Recent Orders</h3>
                <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:2rem;">No orders yet</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td style="font-weight:500;color:var(--off-white);">#<?= $order['id'] ?></td>
                        <td>
                            <div style="color:var(--off-white);"><?= htmlspecialchars($order['customer_name']) ?></div>
                            <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($order['customer_email']) ?></div>
                        </td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td><span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                        <td style="color:var(--accent);font-weight:500;">RM<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script>
// Pass PHP data to JS
const dailyData = <?= json_encode($dailySales) ?>;
const monthlyData = <?= json_encode($monthlySales) ?>;
const yearlyData = <?= json_encode($yearlySales) ?>;

document.addEventListener('DOMContentLoaded', () => {
    // Render daily chart
    renderBarChart('chart-daily',
        dailyData.map(r => new Date(r.day).toLocaleDateString('en',{month:'short',day:'numeric'})),
        dailyData.map(r => parseFloat(r.revenue))
    );

    // Render monthly chart
    renderBarChart('chart-monthly',
        monthlyData.map(r => r.label),
        monthlyData.map(r => parseFloat(r.revenue))
    );

    // Render yearly chart
    renderBarChart('chart-yearly',
        yearlyData.map(r => r.year),
        yearlyData.map(r => parseFloat(r.revenue))
    );

    // Tab switching
    document.querySelectorAll('.tab-btn[data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const period = this.dataset.period;
            document.querySelectorAll('.report-section').forEach(s => {
                s.style.display = s.dataset.period === period ? '' : 'none';
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
