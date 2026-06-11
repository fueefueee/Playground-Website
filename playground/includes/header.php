<?php
require_once __DIR__ . '/config.php';
startSession();
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' . SITE_NAME : SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body class="page-<?= $currentPage ?>">

<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
            <span class="logo-play">PLAY</span><span class="logo-ground">GROUND</span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= SITE_URL ?>/catalog.php" class="<?= $currentPage === 'catalog' ? 'active' : '' ?>">Catalog</a></li>
            <li><a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a></li>
            <?php if ($currentUser): ?>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <li><a href="<?= SITE_URL ?>/admin/dashboard.php" class="nav-admin">Admin</a></li>
                <?php endif; ?>
                <li class="nav-user-menu">
                    <a href="#" class="nav-user-trigger">
                        <span class="user-avatar"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></span>
                        <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>
                    </a>
                    <div class="user-dropdown">
                        <a href="<?= SITE_URL ?>/orders.php">My Orders</a>
                        <a href="<?= SITE_URL ?>/logout.php">Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/login.php" class="nav-btn <?= $currentPage === 'login' ? 'active' : '' ?>">Login</a></li>
            <?php endif; ?>
            <li class="nav-cart-item">
                <a href="<?= SITE_URL ?>/cart.php" class="nav-cart">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
