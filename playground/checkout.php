<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle = 'Checkout';
$user = getCurrentUser();
$extraHead = '<script src="https://www.paypal.com/sdk/js?client-id=' . PAYPAL_CLIENT_ID . '&currency=MYR"></script>';
?>
<?php include 'includes/header.php'; ?>

<div class="page-content checkout-page">
    <div class="container">
        <h1 style="margin-bottom:0.5rem;">Checkout</h1>
        <p style="color:var(--muted);font-size:0.88rem;margin-bottom:2.5rem;">Complete your order securely with PayPal.</p>

        <div class="checkout-layout">
            <!-- Left: Shipping + Payment -->
            <div>
                <!-- Shipping Info -->
                <div class="checkout-section">
                    <h3>Shipping Information</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" id="ship-name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Full name">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="ship-email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label>Street Address</label>
                            <input type="text" id="ship-address" placeholder="123 Main Street">
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" id="ship-city" placeholder="City">
                        </div>
                        <div class="form-group">
                            <label>Postal Code</label>
                            <input type="text" id="ship-zip" placeholder="ZIP / Postal Code">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label>Country</label>
                            <select id="ship-country">
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="GB">United Kingdom</option>
                                <option value="AU">Australia</option>
                                <option value="SG">Singapore</option>
                                <option value="PH">Philippines</option>
                                <option value="MY">Malaysia</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="checkout-section">
                    <h3>Payment</h3>
                    <p style="font-size:0.85rem;color:var(--silver);margin-bottom:1.25rem;">
                        Your order will be processed securely through PayPal. You can pay with your PayPal account or any major credit/debit card.
                    </p>
                    <div id="paypal-button-container"></div>
                    <div id="payment-message" style="display:none;" class="alert alert-error" style="margin-top:1rem;"></div>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div>
                <div class="checkout-order-summary">
                    <h3 style="font-size:0.88rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--silver);margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border);">
                        Order Summary
                    </h3>
                    <div id="checkoutItems"></div>
                    <div style="border-top:1px solid var(--border);margin-top:1rem;padding-top:1rem;">
                        <div class="summary-row"><span>Subtotal</span><span id="coSubtotal">RM0.00</span></div>
                        <div class="summary-row"><span>Shipping</span><span id="coShipping">RM0.00</span></div>
                        <div class="summary-row total"><span>Total</span><span id="coTotal">RM0.00</span></div>
                    </div>
                    <div style="margin-top:1.25rem;padding:0.85rem;background:rgba(82,201,122,0.08);border:1px solid rgba(82,201,122,0.2);border-radius:4px;">
                        <p style="font-size:0.76rem;color:var(--success);text-align:center;">
                            🔒 Your payment is secured by PayPal's Buyer Protection
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initCheckoutPage();

    if (typeof paypal === 'undefined') {
        document.getElementById('paypal-button-container').innerHTML =
            '<div class="alert alert-info">PayPal is not configured yet. Add your Client ID in includes/config.php</div>';
        return;
    }

    paypal.Buttons({
        style: {
            layout: 'vertical',
            color:  'gold',
            shape:  'rect',
            label:  'paypal'
        },

        createOrder: function(data, actions) {
            const total = window._checkoutTotal || '0.00';
            console.log(total);
            return actions.order.create({
                purchase_units: [{
                    description: 'Playground Clothing Order',
                    amount: {
                        currency_code: 'MYR',
                        value: total,
                        breakdown: {
                            item_total: { currency_code: 'MYR', value: total }
                        }
                    }
                }]
            });
        },

        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Send order to backend
                const cart = JSON.parse(localStorage.getItem('playground_cart') || '[]');
                const shipping = {
                    name:    document.getElementById('ship-name').value,
                    email:   document.getElementById('ship-email').value,
                    address: document.getElementById('ship-address').value,
                    city:    document.getElementById('ship-city').value,
                    zip:     document.getElementById('ship-zip').value,
                    country: document.getElementById('ship-country').value,
                };

                fetch('/playground/api/create_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        paypal_order_id: data.orderID,
                        paypal_payer_id: details.payer.payer_id,
                        total: window._checkoutTotal,
                        cart: cart,
                        shipping: shipping
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        localStorage.removeItem('playground_cart');
                        window.location.href = '/playground/orders.php?success=1&order=' + res.order_id;
                    } else {
                        showPaymentError(res.error || 'Order processing failed.');
                    }
                })
                .catch(() => showPaymentError('Network error. Please contact support.'));
            });
        },

        onError: function(err) {
            showPaymentError('Payment failed. Please try again or use a different payment method.');
            console.error('PayPal error:', err);
        },

        onCancel: function() {
            showToast('Payment cancelled.', 'info');
        }
    }).render('#paypal-button-container');

    function showPaymentError(msg) {
        const el = document.getElementById('payment-message');
        el.textContent = msg;
        el.style.display = 'block';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
