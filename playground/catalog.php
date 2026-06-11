<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Catalog';

$db = getDB();

// Fetch all categories
$categories = $db->query("SELECT DISTINCT category FROM products WHERE is_active=1 ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Fetch all products
$products = $db->query("SELECT * FROM products WHERE is_active=1 ORDER BY created_at DESC")->fetchAll();

$selectedCat = $_GET['category'] ?? '';
?>
<?php include 'includes/header.php'; ?>

<div class="page-content">
    <!-- Catalog Hero -->
    <div class="catalog-hero">
        <div class="container">
            <div class="section-eyebrow">Playground Collection</div>
            <h1>All Products</h1>
            <p>Every drop, every style — browse the full collection.</p>
        </div>
    </div>

    <div class="container">
        <div class="catalog-layout">
            <!-- Sidebar Filters -->
            <aside class="catalog-sidebar">
                <div class="filter-card">
                    <h4>Filters</h4>
                    <div class="filter-group">
                        <h5>Category</h5>
                        <?php foreach ($categories as $cat): ?>
                        <label class="filter-option">
                            <input type="checkbox" class="cat-filter" value="<?= htmlspecialchars($cat) ?>"
                                <?= ($selectedCat === $cat) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h5>Price Range</h5>
                        <label class="filter-option">
                            <input type="checkbox" class="price-filter" value="0-100"> Under RM100
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="price-filter" value="100-300"> RM100 – RM300
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="price-filter" value="300-500"> RM300 – RM500
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="price-filter" value="500-99999"> Over RM500
                        </label>
                    </div>

                    <button class="btn btn-outline btn-full btn-sm" onclick="clearAllFilters()">Clear Filters</button>
                </div>
            </aside>

            <!-- Products -->
            <div class="catalog-main">
                <div class="catalog-top">
                    <div class="catalog-search">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="catalogSearch" placeholder="Search products...">
                    </div>
                    <span class="catalog-count" id="catalogCount"><?= count($products) ?> products</span>
                    <div class="catalog-sort">
                        <select id="catalogSort">
                            <option value="default">Sort: Featured</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                            <option value="name">Name: A–Z</option>
                        </select>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $p): ?>
                    <div class="product-col">
                        <div class="product-card"
                             data-id="<?= $p['id'] ?>"
                             data-name="<?= htmlspecialchars($p['name']) ?>"
                             data-category="<?= htmlspecialchars($p['category']) ?>"
                             data-price="<?= $p['price'] ?>">
                            <div class="product-card-img">
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                                <?php if ($p['stock'] <= 5 && $p['stock'] > 0): ?>
                                    <span class="product-badge" style="background:var(--danger)">Low Stock</span>
                                <?php elseif ($p['stock'] === 0): ?>
                                    <span class="product-badge" style="background:var(--muted)">Sold Out</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-body">
                                <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
                                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="product-price">RM<?= number_format($p['price'], 2) ?></div>
                                <div class="product-actions">
                                    <button class="btn btn-outline" onclick='openModal(<?= json_encode($p) ?>)'>View</button>
                                    <?php if ($p['stock'] > 0): ?>
                                    <button class="btn btn-primary" onclick='addToCart({id:<?= $p["id"] ?>,name:<?= json_encode($p["name"]) ?>,price:<?= $p["price"] ?>,image_url:<?= json_encode($p["image_url"]) ?>,category:<?= json_encode($p["category"]) ?>,stock:<?= $p["stock"] ?>,qty:1})'>Add</button>
                                    <?php else: ?>
                                    <button class="btn btn-dark" disabled>Sold Out</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- No results message -->
                <div id="noResults" style="display:none; text-align:center; padding:3rem; color:var(--muted);">
                    <p style="font-size:1rem;">No products match your filters.</p>
                    <button class="btn btn-outline" style="margin-top:1rem;" onclick="clearAllFilters()">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal-overlay" id="productModal">
    <div class="modal">
        <button class="modal-close" aria-label="Close">✕</button>
        <div class="modal-inner">
            <div class="modal-img">
                <img id="modal-img" src="" alt="">
            </div>
            <div class="modal-details">
                <div class="modal-category" id="modal-category"></div>
                <h2 class="modal-name" id="modal-name"></h2>
                <div class="modal-price" id="modal-price"></div>
                <p class="modal-desc" id="modal-desc"></p>
                <div class="modal-stock" id="modal-stock"></div>
                <div class="qty-control">
                    <button class="qty-btn" id="qty-minus">−</button>
                    <input class="qty-val" id="modal-qty" type="number" value="1" min="1">
                    <button class="qty-btn" id="qty-plus">+</button>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-primary btn-full" id="modal-add-btn">Add to Cart</button>
                    <button class="btn btn-outline btn-full" id="modal-buy-btn">Buy Now</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ── Single unified filter function ──
// Replaces all previous conflicting filter code

const searchInput  = document.getElementById('catalogSearch');
const sortSelect   = document.getElementById('catalogSort');
const countEl      = document.getElementById('catalogCount');
const noResults    = document.getElementById('noResults');
const grid         = document.getElementById('productsGrid');
const totalCount   = <?= count($products) ?>;

function applyFilters() {
    const query        = searchInput.value.trim().toLowerCase();
    const activeCats   = [...document.querySelectorAll('.cat-filter:checked')].map(c => c.value.toLowerCase());
    const activePrices = [...document.querySelectorAll('.price-filter:checked')].map(c => c.value);
    const sortVal      = sortSelect.value;

    const cols = [...grid.querySelectorAll('.product-col')];

    cols.forEach(col => {
        const card  = col.querySelector('.product-card[data-id]');
        if (!card) return;

        const name     = (card.dataset.name || '').toLowerCase();
        const category = (card.dataset.category || '').toLowerCase();
        const price    = parseFloat(card.dataset.price) || 0;

        // Search match
        const matchSearch = !query || name.includes(query) || category.includes(query);

        // Category match
        const matchCat = activeCats.length === 0 || activeCats.includes(category);

        // Price match — check each checked range
        let matchPrice = activePrices.length === 0; // if none checked, show all
        if (!matchPrice) {
            matchPrice = activePrices.some(range => {
                const parts = range.split('-');
                const min   = parseFloat(parts[0]);
                const max   = parseFloat(parts[1]);
                return price >= min && price <= max;
            });
        }

        col.style.display = (matchSearch && matchCat && matchPrice) ? '' : 'none';
    });

    // Sort visible columns
    const visible = cols.filter(c => c.style.display !== 'none');

    visible.sort((a, b) => {
        const cardA  = a.querySelector('.product-card');
        const cardB  = b.querySelector('.product-card');
        const priceA = parseFloat(cardA.dataset.price) || 0;
        const priceB = parseFloat(cardB.dataset.price) || 0;
        const nameA  = cardA.querySelector('.product-name').textContent;
        const nameB  = cardB.querySelector('.product-name').textContent;

        if (sortVal === 'price-asc')  return priceA - priceB;
        if (sortVal === 'price-desc') return priceB - priceA;
        if (sortVal === 'name')       return nameA.localeCompare(nameB);
        return 0;
    });

    // Re-append in sorted order
    visible.forEach(col => grid.appendChild(col));

    // Update count
    const count = visible.length;
    if (countEl) countEl.textContent = `${count} product${count !== 1 ? 's' : ''}`;

    // Show/hide no results message
    if (noResults) noResults.style.display = count === 0 ? 'block' : 'none';
}

function clearAllFilters() {
    document.querySelectorAll('.cat-filter, .price-filter').forEach(c => c.checked = false);
    searchInput.value = '';
    sortSelect.value  = 'default';
    // Show all
    document.querySelectorAll('.product-col').forEach(c => c.style.display = '');
    if (countEl)   countEl.textContent   = `${totalCount} products`;
    if (noResults) noResults.style.display = 'none';
}

// Attach listeners
searchInput.addEventListener('input', applyFilters);
sortSelect.addEventListener('change', applyFilters);
document.querySelectorAll('.cat-filter, .price-filter').forEach(cb => {
    cb.addEventListener('change', applyFilters);
});

// Pre-select category from URL ?category=
document.addEventListener('DOMContentLoaded', () => {
    const param = new URLSearchParams(window.location.search).get('category');
    if (param) {
        const cb = document.querySelector(`.cat-filter[value="${param}"]`);
        if (cb) {
            cb.checked = true;
            applyFilters();
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>