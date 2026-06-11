<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$pageTitle = 'Manage Orders';
$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId   = intval($_POST['order_id']);
    $newStatus = $_POST['status'];
    $allowed   = ['pending','paid','shipped','completed','cancelled'];
    if (in_array($newStatus, $allowed)) {
        $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$newStatus, $orderId]);
        header('Location: ' . SITE_URL . '/admin/orders.php?updated=1');
        exit;
    }
}

$filter = $_GET['status'] ?? 'all';
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
               COUNT(oi.id) as item_count
        FROM orders o
        JOIN users u ON u.id = o.user_id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE 1=1";
$params = [];
if ($filter !== 'all') { $sql .= " AND o.status=?"; $params[] = $filter; }
$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$orders = $db->prepare($sql);
$orders->execute($params);
$orders = $orders->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — Admin — Playground</title>
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
            <a href="<?= SITE_URL ?>/admin/orders.php" class="active">Orders</a>
            <a href="<?= SITE_URL ?>/admin/products.php">Products</a>
            <a href="<?= SITE_URL ?>/admin/customers.php">Customers</a>
            <a href="<?= SITE_URL ?>/admin/reports.php">Reports</a>
            <a href="<?= SITE_URL ?>/index.php" style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">View Store</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Orders</h1>
        </div>

        <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem;">Order status updated successfully.</div>
        <?php endif; ?>

        <!-- Filter tabs -->
        <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap;">
            <?php foreach (['all','pending','paid','shipped','completed','cancelled'] as $s): ?>
            <a href="?status=<?= $s ?>" class="btn btn-sm <?= $filter===$s ? 'btn-primary' : 'btn-dark' ?>"><?= ucfirst($s) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th><th>Customer</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--muted);">No orders found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td style="color:var(--white);font-weight:500;">#<?= $order['id'] ?></td>
                        <td>
                            <div style="color:var(--off-white);"><?= htmlspecialchars($order['customer_name']) ?></div>
                            <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($order['customer_email']) ?></div>
                        </td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td><?= $order['item_count'] ?></td>
                        <td style="color:var(--accent);font-weight:500;">$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                        <td>
                            <form method="POST" style="display:flex;gap:0.5rem;align-items:center;">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status" style="background:var(--charcoal);border:1px solid var(--border);border-radius:4px;padding:0.35rem 0.6rem;color:var(--off-white);font-size:0.78rem;outline:none;">
                                    <?php foreach (['pending','paid','shipped','completed','cancelled'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
