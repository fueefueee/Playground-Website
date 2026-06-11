<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Cart';
?>
<?php include 'includes/header.php'; ?>

<div class="page-content cart-page">
    <div class="container">
        <h1 style="margin-bottom:0.5rem;">Your Cart</h1>
        <p style="color:var(--muted);font-size:0.88rem;margin-bottom:2rem;">Review your items before checkout.</p>

        <div class="cart-layout">
            <!-- Cart items rendered by JS -->
            <div id="cartContainer">
                <div style="text-align:center;padding:3rem;color:var(--muted);">Loading cart...</div>
            </div>

            <!-- Order Summary -->
            <div id="orderSummarySection">
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">RM0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span id="summaryShipping">RM20.00</span>
                    </div>
                    <div class="summary-row" style="font-size:0.78rem;color:var(--muted);">
                        <span>Free shipping over RM500</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="summaryTotal">RM0.00</span>
                    </div>

                    <?php if (isLoggedIn()): ?>
                        <a href="<?= SITE_URL ?>/checkout.php" class="btn btn-primary btn-full" style="margin-top:1.25rem;">
                            Proceed to Checkout
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode(SITE_URL . '/checkout.php') ?>"
                           class="btn btn-primary btn-full" style="margin-top:1.25rem;">
                            Login to Checkout
                        </a>
                        <p style="font-size:0.78rem;color:var(--muted);text-align:center;margin-top:0.75rem;">
                            You need an account to place an order.
                        </p>
                    <?php endif; ?>

                    <a href="<?= SITE_URL ?>/catalog.php" class="btn btn-outline btn-full" style="margin-top:0.75rem;">
                        Continue Shopping
                    </a>

                    <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);text-align:center;">
                        <p style="font-size:0.75rem;color:var(--muted);">Secured by PayPal</p>
                        <div style="display:flex;justify-content:center;gap:0.5rem;margin-top:0.5rem;font-size:0.7rem;color:var(--muted);">
                            <span>🔒 SSL Encrypted</span>
                            <span>•</span>
                            <span>PayPal Buyer Protection</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
