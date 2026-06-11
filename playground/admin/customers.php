<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$pageTitle = 'Customers';
$db = getDB();

$customers = $db->query("
    SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total_spent
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id AND o.status != 'cancelled'
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY total_spent DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers — Admin — Playground</title>
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
            <a href="<?= SITE_URL ?>/admin/customers.php" class="active">Customers</a>
            <a href="<?= SITE_URL ?>/admin/reports.php">Reports</a>
            <a href="<?= SITE_URL ?>/index.php" style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">View Store</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Customers</h1>
            <span style="font-size:0.85rem;color:var(--muted);"><?= count($customers) ?> registered customers</span>
        </div>
        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr><th>Customer</th><th>Joined</th><th>Orders</th><th>Total Spent</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:2rem;">No customers yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:36px;height:36px;border-radius:50%;background:var(--accent);color:var(--black);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                                    <?= strtoupper(substr($c['name'],0,1)) ?>
                                </div>
                                <div>
                                    <div style="color:var(--off-white);font-weight:500;"><?= htmlspecialchars($c['name']) ?></div>
                                    <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($c['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                        <td><?= $c['order_count'] ?></td>
                        <td style="color:var(--accent);font-weight:500;">$<?= number_format($c['total_spent'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
