/* ============================================================
   PLAYGROUND — Main JavaScript
   ============================================================ */

// --- Cart State (localStorage) ---
const CART_KEY = 'playground_cart';

function getCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
    catch { return []; }
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
}

function addToCart(product) {
    const cart = getCart();
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        existing.qty = Math.min(existing.qty + product.qty, product.stock);
    } else {
        cart.push(product);
    }
    saveCart(cart);
    showToast(`"${product.name}" added to cart!`, 'success');
}

function removeFromCart(productId) {
    const cart = getCart().filter(i => i.id !== productId);
    saveCart(cart);
}

function updateCartQty(productId, qty) {
    const cart = getCart();
    const item = cart.find(i => i.id === productId);
    if (item) {
        item.qty = Math.max(1, qty);
        saveCart(cart);
    }
}

function clearCart() {
    localStorage.removeItem(CART_KEY);
    updateCartCount();
}

function getCartTotal() {
    return getCart().reduce((sum, i) => sum + (i.price * i.qty), 0);
}

function updateCartCount() {
    const count = getCart().reduce((sum, i) => sum + i.qty, 0);
    document.querySelectorAll('#cartCount, .cart-count').forEach(el => {
        el.textContent = count;
        el.classList.toggle('has-items', count > 0);
    });
}

// --- Toast Notifications ---
function showToast(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${icons[type] || icons.info}</span><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = '0.3s ease';
        setTimeout(() => toast.remove(), 350);
    }, 3000);
}

// --- Navbar ---
function initNavbar() {
    const navbar = document.getElementById('navbar');
    const toggle = document.getElementById('navToggle');
    const links  = document.getElementById('navLinks');

    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 30);
        });
    }
    if (toggle && links) {
        toggle.addEventListener('click', () => {
            links.classList.toggle('open');
        });
        document.addEventListener('click', e => {
            if (!toggle.contains(e.target) && !links.contains(e.target)) {
                links.classList.remove('open');
            }
        });
    }
}

// --- Product Modal ---
function openModal(product) {
    const overlay = document.getElementById('productModal');
    if (!overlay) return;

    overlay.querySelector('#modal-category').textContent  = product.category;
    overlay.querySelector('#modal-name').textContent      = product.name;
    overlay.querySelector('#modal-price').textContent     = 'RM' + parseFloat(product.price).toFixed(2);
    overlay.querySelector('#modal-desc').textContent      = product.description;
    overlay.querySelector('#modal-stock').textContent     = `${product.stock} in stock`;
    overlay.querySelector('#modal-img').src               = product.image_url;
    overlay.querySelector('#modal-img').alt               = product.name;
    overlay.querySelector('#modal-qty').value             = 1;
    overlay.querySelector('#modal-qty').max               = product.stock;
    overlay.querySelector('#modal-add-btn').dataset.product = JSON.stringify(product);
    overlay.querySelector('#modal-add-btn').dataset.stock   = product.stock;

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const overlay = document.getElementById('productModal');
    if (overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function initModal() {
    const overlay = document.getElementById('productModal');
    if (!overlay) return;

    overlay.querySelector('.modal-close').addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

    // Qty controls
    overlay.querySelector('#qty-minus').addEventListener('click', () => {
        const inp = overlay.querySelector('#modal-qty');
        inp.value = Math.max(1, parseInt(inp.value) - 1);
    });
    overlay.querySelector('#qty-plus').addEventListener('click', () => {
        const inp = overlay.querySelector('#modal-qty');
        const max = parseInt(inp.max) || 99;
        inp.value = Math.min(max, parseInt(inp.value) + 1);
    });

    // Add to cart
    overlay.querySelector('#modal-add-btn').addEventListener('click', function() {
        const product = JSON.parse(this.dataset.product);
        const qty = parseInt(overlay.querySelector('#modal-qty').value);
        addToCart({ ...product, qty });
        closeModal();
    });

    // Quick buy (go to cart)
    const quickBuy = overlay.querySelector('#modal-buy-btn');
    if (quickBuy) {
        quickBuy.addEventListener('click', function() {
            const product = JSON.parse(overlay.querySelector('#modal-add-btn').dataset.product);
            const qty = parseInt(overlay.querySelector('#modal-qty').value);
            addToCart({ ...product, qty });
            window.location.href = '/playground/cart.php';
        });
    }
}

// --- Catalog Filters ---
function initCatalogFilters() {
    const searchInput  = document.getElementById('catalogSearch');
    const sortSelect   = document.getElementById('catalogSort');
    const filterChecks = document.querySelectorAll('.filter-option input[type="checkbox"]');
    const countEl      = document.getElementById('catalogCount');

    function applyFilters() {
        const query      = searchInput ? searchInput.value.toLowerCase() : '';
        const sortVal    = sortSelect ? sortSelect.value : 'default';
        const activecats = [...filterChecks]
            .filter(c => c.checked)
            .map(c => c.value.toLowerCase());

        let cards = [...document.querySelectorAll('.product-card[data-id]')];

        // Filter
        cards.forEach(card => {
            const name  = card.dataset.name.toLowerCase();
            const cat   = card.dataset.category.toLowerCase();
            const match = (!query || name.includes(query) || cat.includes(query)) &&
                          (activeCards.length === 0 || activeCards.includes(cat));
            card.closest('.product-col').style.display = match ? '' : 'none';
        });

        // Sort
        const grid = document.getElementById('productsGrid');
        if (!grid) return;
        let visible = [...grid.querySelectorAll('.product-col')]
            .filter(c => c.style.display !== 'none');

        visible.sort((a, b) => {
            const priceA = parseFloat(a.querySelector('.product-card').dataset.price);
            const priceB = parseFloat(b.querySelector('.product-card').dataset.price);
            if (sortVal === 'price-asc') return priceA - priceB;
            if (sortVal === 'price-desc') return priceB - priceA;
            if (sortVal === 'name') return a.querySelector('.product-name').textContent.localeCompare(b.querySelector('.product-name').textContent);
            return 0;
        });
        visible.forEach(c => grid.appendChild(c));

        if (countEl) countEl.textContent = `${visible.length} products`;
    }

    // Use correct variable name
    function applyFiltersFixed() {
        const query      = searchInput ? searchInput.value.toLowerCase() : '';
        const sortVal    = sortSelect ? sortSelect.value : 'default';
        const activeCategories = [...filterChecks]
            .filter(c => c.checked)
            .map(c => c.value.toLowerCase());

        const grid = document.getElementById('productsGrid');
        if (!grid) return;
        let cols = [...grid.querySelectorAll('.product-col')];

        cols.forEach(col => {
            const card = col.querySelector('.product-card[data-id]');
            if (!card) return;
            const name = card.dataset.name.toLowerCase();
            const cat  = card.dataset.category.toLowerCase();
            const matchQuery = !query || name.includes(query) || cat.includes(query);
            const matchCat   = activeCategories.length === 0 || activeCategories.includes(cat);
            col.style.display = (matchQuery && matchCat) ? '' : 'none';
        });

        // Sort visible
        let visible = cols.filter(c => c.style.display !== 'none');
        visible.sort((a, b) => {
            const priceA = parseFloat(a.querySelector('.product-card').dataset.price);
            const priceB = parseFloat(b.querySelector('.product-card').dataset.price);
            if (sortVal === 'price-asc') return priceA - priceB;
            if (sortVal === 'price-desc') return priceB - priceA;
            if (sortVal === 'name') return (a.querySelector('.product-name').textContent).localeCompare(b.querySelector('.product-name').textContent);
            return 0;
        });
        visible.forEach(c => grid.appendChild(c));

        if (countEl) {
            const total = visible.length;
            countEl.textContent = `${total} product${total !== 1 ? 's' : ''}`;
        }
    }

    if (searchInput) searchInput.addEventListener('input', applyFiltersFixed);
    if (sortSelect) sortSelect.addEventListener('change', applyFiltersFixed);
    filterChecks.forEach(c => c.addEventListener('change', applyFiltersFixed));
}

// --- Cart Page ---
function initCartPage() {
    const cartContainer = document.getElementById('cartContainer');
    if (!cartContainer) return;
    renderCartPage();
}

function renderCartPage() {
    const cartContainer = document.getElementById('cartContainer');
    if (!cartContainer) return;

    const cart = getCart();

    if (cart.length === 0) {
        cartContainer.innerHTML = `
            <div class="empty-cart">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything yet.</p>
                <a href="/playground/catalog.php" class="btn btn-primary">Browse Catalog</a>
            </div>`;
        const summaryEl = document.getElementById('orderSummarySection');
        if (summaryEl) summaryEl.style.display = 'none';
        return;
    }

    const rows = cart.map(item => `
        <tr>
            <td>
                <div class="cart-item-info">
                    <div class="cart-item-img"><img src="${item.image_url}" alt="${item.name}"></div>
                    <div>
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-cat">${item.category}</div>
                    </div>
                </div>
            </td>
            <td>RM${parseFloat(item.price).toFixed(2)}</td>
            <td>
                <div class="qty-control">
                    <button class="qty-btn" onclick="changeCartQty(${item.id}, -1)">−</button>
                    <span class="qty-val">${item.qty}</span>
                    <button class="qty-btn" onclick="changeCartQty(${item.id}, 1)">+</button>
                </div>
            </td>
            <td>RM${(item.price * item.qty).toFixed(2)}</td>
            <td>
                <button class="cart-remove" onclick="removeCartItem(${item.id})" title="Remove">✕</button>
            </td>
        </tr>`).join('');

    cartContainer.innerHTML = `
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;

    const subtotal  = getCartTotal();
    const shipping  = subtotal >= 100 ? 0 : 9.99;
    const total     = subtotal + shipping;

    const summaryEl = document.getElementById('orderSummarySection');
    if (summaryEl) {
        summaryEl.style.display = '';
        document.getElementById('summarySubtotal').textContent = `RM${subtotal.toFixed(2)}`;
        document.getElementById('summaryShipping').textContent = shipping === 0 ? 'FREE' : `RM${shipping.toFixed(2)}`;
        document.getElementById('summaryTotal').textContent    = `RM${total.toFixed(2)}`;
    }
}

function changeCartQty(id, delta) {
    const cart = getCart();
    const item = cart.find(i => i.id === id);
    if (item) {
        item.qty = Math.max(1, item.qty + delta);
        saveCart(cart);
    }
    renderCartPage();
}

function removeCartItem(id) {
    removeFromCart(id);
    renderCartPage();
}

// --- Checkout Page ---
function initCheckoutPage() {
    const summaryEl = document.getElementById('checkoutItems');
    if (!summaryEl) return;

    const cart = getCart();
    if (cart.length === 0) {
        window.location.href = '/playground/cart.php';
        return;
    }

    summaryEl.innerHTML = cart.map(item => `
        <div class="checkout-item">
            <div class="checkout-item-img"><img src="${item.image_url}" alt="${item.name}"></div>
            <div class="checkout-item-info">
                <div class="checkout-item-name">${item.name}</div>
                <div class="checkout-item-qty">Qty: ${item.qty}</div>
            </div>
            <div class="checkout-item-price">RM${(item.price * item.qty).toFixed(2)}</div>
        </div>`).join('');

    const subtotal = getCartTotal();
    const shipping = subtotal >= 100 ? 0 : 9.99;
    const total    = subtotal + shipping;

    document.getElementById('coSubtotal').textContent = `RM${subtotal.toFixed(2)}`;
    document.getElementById('coShipping').textContent = shipping === 0 ? 'FREE' : `RM${shipping.toFixed(2)}`;
    document.getElementById('coTotal').textContent    = `RM${total.toFixed(2)}`;

    window._checkoutTotal = total.toFixed(2);
    window._checkoutCart  = cart;
}

// --- Admin Chart ---
function renderBarChart(containerId, labels, values, prefix = 'RM') {
    const container = document.getElementById(containerId);
    if (!container) return;
    const max = Math.max(...values, 1);
    container.innerHTML = `
        <div class="chart-bars">
            ${labels.map((label, i) => `
                <div class="chart-bar-wrap">
                    <span class="chart-val">${prefix}${values[i].toLocaleString()}</span>
                    <div class="chart-bar" style="height:${Math.round((values[i]/max)*200)}px" title="${label}: ${prefix}${values[i]}"></div>
                    <span class="chart-label">${label}</span>
                </div>`).join('')}
        </div>`;
}

// --- Admin Tab ---
function initAdminTabs() {
    const tabs = document.querySelectorAll('.tab-btn[data-period]');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const period = this.dataset.period;
            document.querySelectorAll('.report-section').forEach(s => {
                s.style.display = s.dataset.period === period ? '' : 'none';
            });
        });
    });
}

// --- Init ---
document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initModal();
    initCatalogFilters();
    initCartPage();
    initCheckoutPage();
    initAdminTabs();
    updateCartCount();
});
