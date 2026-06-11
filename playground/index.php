<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Home';

// Fetch featured products
$db = getDB();
$featured = $db->query("SELECT * FROM products WHERE is_active=1 ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid-lines"></div>
    <div class="hero-content">
        <div class="hero-text">
            <div class="hero-eyebrow">New Season Drop</div>
            <h1 class="hero-title">
                Play <br>Your <em>GAME.</em>
            </h1>
            <p class="hero-subtitle">
                Playground is an independent custom sublimation shirt built on intention. Every piece is a statement, every fit a mood. Designed for those who refuse to dress like the crowd.
            </p>
            <div class="hero-cta">
                <a href="/playground/catalog.php" class="btn btn-primary btn-lg">Shop the Collection</a>
                <a href="/playground/about.php" class="btn btn-outline btn-lg">Our Story</a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-num">200+</div>
                    <div class="stat-label">Unique Pieces</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">10K+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">100%</div>
                    <div class="stat-label">Premium Quality</div>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-img-card">
                <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=500&q=80" alt="Playground lookbook">
            </div>
            <div class="hero-img-card">
                <img src="https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=500&q=80" alt="Playground style">
            </div>
        </div>
    </div>
</section>

<!-- MARQUEE -->
<div class="marquee-section">
    <div class="marquee-track">
        <span>Free Shipping on Orders Over RM 500</span>
        <span class="accent">✦</span>
        <span>New Arrivals Every Week</span>
        <span class="accent">✦</span>
        <span>Premium Quality Fabrics</span>
        <span class="accent">✦</span>
        <span>Secure PayPal Checkout</span>
        <span class="accent">✦</span>
        <span>Free Shipping on Orders Over RM 500</span>
        <span class="accent">✦</span>
        <span>New Arrivals Every Week</span>
        <span class="accent">✦</span>
        <span>Premium Quality Fabrics</span>
        <span class="accent">✦</span>
        <span>Secure PayPal Checkout</span>
        <span class="accent">✦</span>
    </div>
</div>

<!-- CATEGORIES -->
<section class="categories-section">
    <div class="container">
        <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2rem;">
            <div>
                <div class="section-eyebrow">Browse By</div>
                <h2 class="section-title">Shop Categories</h2>
            </div>
            <a href="/playground/catalog.php" class="btn btn-outline">View All</a>
        </div>
        <div class="categories-grid">
            <?php
            $cats = [
                ['name'=>'T-Shirts',  'sub'=>'Graphic & Essential', 'img'=>'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80'],
                ['name'=>'Hoodies',   'sub'=>'Fleece & Zip-Up',     'img'=>'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=400&q=80'],
                ['name'=>'Bottoms',   'sub'=>'Pants & Shorts',      'img'=>'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=400&q=80'],
                ['name'=>'Outerwear', 'sub'=>'Jackets & Vests',     'img'=>'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400&q=80'],
                ['name'=>'Sets',      'sub'=>'Matching Looks',      'img'=>'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400&q=80'],
            ];
            foreach ($cats as $cat): ?>
            <a href="/playground/catalog.php?category=<?= urlencode($cat['name']) ?>" class="category-card">
                <img src="<?= $cat['img'] ?>" alt="<?= $cat['name'] ?>">
                <div class="category-label">
                    <h3><?= $cat['name'] ?></h3>
                    <p><?= $cat['sub'] ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section">
    <div class="container">
        <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between;">
            <div>
                <div class="section-eyebrow">Fresh Drops</div>
                <h2 class="section-title">New Arrivals</h2>
            </div>
            <a href="/playground/catalog.php" class="btn btn-outline">View All</a>
        </div>
        <div class="products-grid" style="margin-top:2rem;">
            <?php foreach ($featured as $p): ?>
            <div class="product-card"
                 data-id="<?= $p['id'] ?>"
                 data-name="<?= htmlspecialchars($p['name']) ?>"
                 data-category="<?= htmlspecialchars($p['category']) ?>"
                 data-price="<?= $p['price'] ?>">
                <div class="product-card-img">
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <span class="product-badge">New</span>
                </div>
                <div class="product-card-body">
                    <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
                    <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-price">RM<?= number_format($p['price'], 2) ?></div>
                    <div class="product-actions">
                        <button class="btn btn-outline" onclick='openModal(<?= json_encode($p) ?>)'>Quick View</button>
                        <button class="btn btn-primary" onclick='addToCart({id:<?= $p["id"] ?>,name:<?= json_encode($p["name"]) ?>,price:<?= $p["price"] ?>,image_url:<?= json_encode($p["image_url"]) ?>,category:<?= json_encode($p["category"]) ?>,stock:<?= $p["stock"] ?>,qty:1})'>Add to Cart</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PROMO BANNER -->
<section style="background:var(--off-black);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:5rem 0;">
    <div class="container" style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
        <div>
            <div class="section-eyebrow">Limited Time</div>
            <h2 class="section-title" style="margin-bottom:1rem;">Free Shipping<br>Over <span class="gold">RM 500</span></h2>
            <p style="color:var(--silver);line-height:1.8;margin-bottom:2rem;">Stock up on your favourites and we'll cover the shipping. No code needed — the discount applies automatically at checkout.</p>
            <a href="/playground/catalog.php" class="btn btn-primary">Shop Now</a>
        </div>
        <div style="position:relative;">
            <div style="border-radius:12px;overflow:hidden;aspect-ratio:4/3;">
                <img src="https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=700&q=80" alt="Free shipping promo" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div style="position:absolute;bottom:1.5rem;left:1.5rem;background:var(--accent);color:var(--black);padding:0.6rem 1.2rem;border-radius:4px;font-weight:700;font-size:0.85rem;letter-spacing:0.05em;">FREE SHIPPING</div>
        </div>
    </div>
</section>

<!-- PRODUCT MODAL -->
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

<?php include 'includes/footer.php'; ?>
