<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$pageTitle = 'Manage Products';
$db = getDB();

$action  = $_GET['action'] ?? 'list';
$editId  = intval($_GET['id'] ?? 0);
$msg     = '';
$error   = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $desc      = trim($_POST['description'] ?? '');
    $price     = floatval($_POST['price'] ?? 0);
    $category  = trim($_POST['category'] ?? '');
    $imageUrl  = trim($_POST['image_url'] ?? '');
    $stock     = intval($_POST['stock'] ?? 0);
    $isActive  = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$price || !$category) {
        $error = 'Name, price, and category are required.';
    } else {
        if (isset($_POST['add_product'])) {
            $db->prepare("INSERT INTO products (name,description,price,category,image_url,stock,is_active) VALUES (?,?,?,?,?,?,?)")
               ->execute([$name,$desc,$price,$category,$imageUrl,$stock,$isActive]);
            $msg = 'Product added successfully.';
            $action = 'list';
        } elseif (isset($_POST['edit_product'])) {
            $id = intval($_POST['product_id']);
            $db->prepare("UPDATE products SET name=?,description=?,price=?,category=?,image_url=?,stock=?,is_active=? WHERE id=?")
               ->execute([$name,$desc,$price,$category,$imageUrl,$stock,$isActive,$id]);
            $msg = 'Product updated.';
            $action = 'list';
        }
    }
}

// Toggle active
if (isset($_GET['toggle'])) {
    $id  = intval($_GET['toggle']);
    $db->prepare("UPDATE products SET is_active = 1-is_active WHERE id=?")->execute([$id]);
    header('Location: ' . SITE_URL . '/admin/products.php?msg=toggled');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->prepare("UPDATE products SET is_active=0 WHERE id=?")->execute([$id]);
    header('Location: ' . SITE_URL . '/admin/products.php?msg=deleted');
    exit;
}

if (isset($_GET['msg'])) $msg = 'Product updated.';

// Fetch product for editing
$editProduct = null;
if ($action === 'edit' && $editId) {
    $editProduct = $db->prepare("SELECT * FROM products WHERE id=?")->execute([$editId]) ? null : null;
    $stmt = $db->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}

// Fetch all products
$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — Admin — Playground</title>
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
            <a href="<?= SITE_URL ?>/admin/products.php" class="active">Products</a>
            <a href="<?= SITE_URL ?>/admin/customers.php">Customers</a>
            <a href="<?= SITE_URL ?>/admin/reports.php">Reports</a>
            <a href="<?= SITE_URL ?>/index.php" style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">View Store</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Products</h1>
            <a href="?action=new" class="btn btn-primary btn-sm">+ Add Product</a>
        </div>

        <?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:1.5rem;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:1.5rem;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($action === 'new' || $action === 'edit'): ?>
        <!-- Product Form -->
        <div class="admin-card" style="margin-bottom:1.5rem;">
            <div class="admin-card-header">
                <h3><?= $action === 'edit' ? 'Edit Product' : 'Add New Product' ?></h3>
            </div>
            <div style="padding:1.5rem;">
                <form method="POST">
                    <?php if ($editProduct): ?>
                        <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
                    <?php endif; ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category">
                                <?php foreach (['T-Shirts','Hoodies','Bottoms','Outerwear','Sets','Accessories'] as $c): ?>
                                <option value="<?= $c ?>" <?= ($editProduct['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price (RM) *</label>
                            <input type="number" name="price" step="0.01" min="0" required value="<?= $editProduct['price'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" min="0" value="<?= $editProduct['stock'] ?? 0 ?>">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label>Image URL</label>
                            <input type="url" name="image_url" placeholder="https://..." value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label>Description</label>
                            <textarea name="description" rows="3" style="background:var(--charcoal);border:1px solid var(--border);border-radius:4px;padding:0.75rem;color:var(--off-white);width:100%;resize:vertical;outline:none;"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="checkbox" name="is_active" value="1" <?= ($editProduct['is_active'] ?? 1) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--accent);">
                                Active (visible in store)
                            </label>
                        </div>
                    </div>
                    <div style="display:flex;gap:1rem;margin-top:1rem;">
                        <button type="submit" name="<?= $action === 'edit' ? 'edit_product' : 'add_product' ?>" class="btn btn-primary">
                            <?= $action === 'edit' ? 'Update Product' : 'Add Product' ?>
                        </button>
                        <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Products Table -->
        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <?php if ($p['image_url']): ?>
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" style="width:42px;height:54px;object-fit:cover;border-radius:4px;">
                                <?php endif; ?>
                                <div style="color:var(--off-white);font-weight:500;"><?= htmlspecialchars($p['name']) ?></div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td style="color:var(--accent);">RM<?= number_format($p['price'], 2) ?></td>
                        <td>
                            <span style="color:<?= $p['stock'] <= 5 ? 'var(--danger)' : 'var(--silver)' ?>">
                                <?= $p['stock'] ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;padding:0.25rem 0.6rem;border-radius:20px;
                                background:<?= $p['is_active'] ? 'rgba(82,201,122,0.15)' : 'rgba(224,82,82,0.15)' ?>;
                                color:<?= $p['is_active'] ? 'var(--success)' : 'var(--danger)' ?>;">
                                <?= $p['is_active'] ? 'Active' : 'Hidden' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:0.5rem;">
                                <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-dark">Edit</a>
                                <a href="?toggle=<?= $p['id'] ?>" class="btn btn-sm btn-outline"><?= $p['is_active'] ? 'Hide' : 'Show' ?></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
