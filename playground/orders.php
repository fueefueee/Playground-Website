<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle = 'My Orders';
$user = getCurrentUser();
$db = getDB();

$success  = $_GET['success'] ?? '';
$orderId  = intval($_GET['order'] ?? 0);

// Fetch user orders
$orders = $db->prepare("
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders->execute([$user['id']]);
$orders = $orders->fetchAll();

// Fetch items per order
$orderItems = [];
foreach ($orders as $order) {
    $stmt = $db->prepare("
        SELECT oi.*, p.name, p.image_url, p.category
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $orderItems[$order['id']] = $stmt->fetchAll();
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-content orders-page">
    <div class="container">
        <h1>My Orders</h1>

        <?php if ($success && $orderId): ?>
        <div class="alert alert-success" style="margin-bottom:2rem;">
            ✓ Order #<?= $orderId ?> placed successfully! Thank you for shopping with Playground.
        </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <div class="empty-cart" style="padding:4rem 2rem;">
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 1.25rem;display:block;color:var(--muted);">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
            </svg>
            <h2>No orders yet</h2>
            <p style="color:var(--muted);margin-bottom:1.5rem;">You haven't placed any orders. Start shopping!</p>
            <a href="<?= SITE_URL ?>/catalog.php" class="btn btn-primary">Browse Catalog</a>
        </div>
        <?php else: ?>

        <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="order-card-header">
                <div>
                    <div class="order-id">Order #<?= $order['id'] ?></div>
                    <div class="order-date"><?= date('F j, Y • g:i A', strtotime($order['created_at'])) ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                    <span style="font-weight:600;color:var(--white);">$<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
            <div class="order-card-body">
                <?php foreach ($orderItems[$order['id']] as $item): ?>
                <div class="order-item-row">
                    <div class="order-item-img">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>
                    <div class="order-item-info">
                        <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="order-item-meta"><?= htmlspecialchars($item['category']) ?> • Qty: <?= $item['quantity'] ?></div>
                    </div>
                    <div class="order-total">$<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
