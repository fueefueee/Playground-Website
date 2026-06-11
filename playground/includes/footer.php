
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="logo-play">PLAY</span><span class="logo-ground">GROUND</span>
            <p>Clothing for those who refuse to blend in.<br>Born and made with intention.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Shop</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/catalog.php">All Products</a></li>
                    <li><a href="<?= SITE_URL ?>/catalog.php?category=T-Shirts">T-Shirts</a></li>
                    <li><a href="<?= SITE_URL ?>/catalog.php?category=Hoodies">Hoodies</a></li>
                    <li><a href="<?= SITE_URL ?>/catalog.php?category=Bottoms">Bottoms</a></li>
                    <li><a href="<?= SITE_URL ?>/catalog.php?category=Outerwear">Outerwear</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/about.php#story">Our Story</a></li>
                    <li><a href="mailto:hello@playground.com">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <ul>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?= SITE_URL ?>/orders.php">My Orders</a></li>
                        <li><a href="<?= SITE_URL ?>/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
                        <li><a href="<?= SITE_URL ?>/signup.php">Sign Up</a></li>
                    <?php endif; ?>
                    <li><a href="<?= SITE_URL ?>/cart.php">Cart</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Playground. All rights reserved.</p>
        <p>Payments secured by PayPal</p>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<?= isset($extraScripts) ? $extraScripts : '' ?>
</body>
</html>
