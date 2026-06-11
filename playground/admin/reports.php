<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$pageTitle = 'Reports';
$db = getDB();

$period    = $_GET['period'] ?? 'monthly';
$dateFrom  = $_GET['from'] ?? date('Y-m-01');
$dateTo    = $_GET['to']   ?? date('Y-m-d');

// Build report data based on period
switch ($period) {
    case 'daily':
        $groupFormat = '%Y-%m-%d';
        $labelFormat = 'M j, Y';
        $dateFrom    = date('Y-m-d', strtotime('-30 days'));
        break;
    case 'yearly':
        $groupFormat = '%Y';
        $labelFormat = 'Y';
        break;
    default: // monthly
        $groupFormat = '%Y-%m';
        $labelFormat = 'M Y';
}

$salesReport = $db->prepare("
    SELECT DATE_FORMAT(o.created_at, '$groupFormat') as period,
           COUNT(DISTINCT o.id) as orders,
           COUNT(oi.id) as items_sold,
           COALESCE(SUM(oi.quantity),0) as units_sold,
           COALESCE(SUM(o.total_amount),0) as revenue
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.status != 'cancelled'
      AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(o.created_at, '$groupFormat')
    ORDER BY period ASC
");
$salesReport->execute([$dateFrom, $dateTo]);
$reportRows = $salesReport->fetchAll();

$totalRevenue = array_sum(array_column($reportRows, 'revenue'));
$totalOrders  = array_sum(array_column($reportRows, 'orders'));
$totalUnits   = array_sum(array_column($reportRows, 'units_sold'));
$avgOrder     = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="playground_report_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Period', 'Orders', 'Units Sold', 'Revenue']);
    foreach ($reportRows as $row) {
        fputcsv($out, [$row['period'], $row['orders'], $row['units_sold'], 'RM' . number_format($row['revenue'], 2)]);
    }
    fputcsv($out, ['TOTAL', $totalOrders, $totalUnits, 'RM' . number_format($totalRevenue, 2)]);
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — Admin — Playground</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header"><span>Admin Panel</span></div>
        <nav class="admin-nav">
            <a href="<?= SITE_URL ?>/admin/dashboard.php">Dashboard</a>
            <a href="<?= SITE_URL ?>/admin/orders.php">Orders</a>
            <a href="<?= SITE_URL ?>/admin/products.php">Products</a>
            <a href="<?= SITE_URL ?>/admin/customers.php">Customers</a>
            <a href="<?= SITE_URL ?>/admin/reports.php" class="active">Reports</a>
            <a href="<?= SITE_URL ?>/index.php" style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">View Store</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Sales Reports</h1>
        </div>

        <!-- Report Controls -->
        <div class="admin-card" style="margin-bottom:1.5rem;">
            <div style="padding:1.25rem 1.5rem;">
                <form method="GET" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div class="tab-group">
                        <?php foreach (['daily'=>'Daily','monthly'=>'Monthly','yearly'=>'Yearly'] as $k=>$v): ?>
                        <button type="submit" name="period" value="<?= $k ?>"
                                class="tab-btn <?= $period===$k?'active':'' ?>"><?= $v ?></button>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($period !== 'daily' && $period !== 'yearly'): ?>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <label style="font-size:0.78rem;color:var(--muted);">From</label>
                        <input type="date" name="from" value="<?= $dateFrom ?>" style="background:var(--charcoal);border:1px solid var(--border);border-radius:4px;padding:0.45rem 0.75rem;color:var(--off-white);font-size:0.82rem;outline:none;">
                        <label style="font-size:0.78rem;color:var(--muted);">To</label>
                        <input type="date" name="to" value="<?= $dateTo ?>" style="background:var(--charcoal);border:1px solid var(--border);border-radius:4px;padding:0.45rem 0.75rem;color:var(--off-white);font-size:0.82rem;outline:none;">
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    <a href="?period=<?= $period ?>&from=<?= $dateFrom ?>&to=<?= $dateTo ?>&export=1"
                       class="btn btn-outline btn-sm">Export CSV</a>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid" style="margin-bottom:1.5rem;">
            <div class="stat-card">
                <div class="stat-card-label">Total Revenue</div>
                <div class="stat-card-value">RM<?= number_format($totalRevenue, 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Total Orders</div>
                <div class="stat-card-value"><?= $totalOrders ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Units Sold</div>
                <div class="stat-card-value"><?= $totalUnits ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Avg Order Value</div>
                <div class="stat-card-value">RM<?= number_format($avgOrder, 2) ?></div>
            </div>
        </div>

        <!-- Chart -->
        <?php if (!empty($reportRows)): ?>
        <div class="admin-card" style="margin-bottom:1.5rem;">
            <div class="admin-card-header"><h3>Revenue Chart</h3></div>
            <div class="chart-container"><div id="report-chart"></div></div>
        </div>
        <?php endif; ?>

        <!-- Data Table -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><?= ucfirst($period) ?> Sales Breakdown</h3>
                <span style="font-size:0.75rem;color:var(--muted);"><?= count($reportRows) ?> periods</span>
            </div>
            <table class="admin-table">
                <thead>
                    <tr><th>Period</th><th>Orders</th><th>Units Sold</th><th>Revenue</th><th>Avg Order</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($reportRows)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:2rem;">No data for the selected period.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($reportRows as $row): ?>
                    <tr>
                        <td style="color:var(--off-white);font-weight:500;"><?= $row['period'] ?></td>
                        <td><?= $row['orders'] ?></td>
                        <td><?= $row['units_sold'] ?></td>
                        <td style="color:var(--accent);font-weight:500;">RM<?= number_format($row['revenue'], 2) ?></td>
                        <td style="color:var(--silver);">RM<?= $row['orders'] > 0 ? number_format($row['revenue'] / $row['orders'], 2) : '0.00' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!empty($reportRows)): ?>
                    <tr style="background:rgba(200,169,110,0.06);border-top:2px solid var(--accent);">
                        <td style="font-weight:700;color:var(--white);">TOTAL</td>
                        <td style="font-weight:700;color:var(--white);"><?= $totalOrders ?></td>
                        <td style="font-weight:700;color:var(--white);"><?= $totalUnits ?></td>
                        <td style="font-weight:700;color:var(--accent);">RM<?= number_format($totalRevenue, 2) ?></td>
                        <td style="color:var(--silver);">RM<?= number_format($avgOrder, 2) ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
const reportData = <?= json_encode($reportRows) ?>;
document.addEventListener('DOMContentLoaded', () => {
    if (reportData.length) {
        renderBarChart('report-chart',
            reportData.map(r => r.period),
            reportData.map(r => parseFloat(r.revenue))
        );
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
