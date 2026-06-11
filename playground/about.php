<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'About Us';
?>
<?php include 'includes/header.php'; ?>

<div class="page-content">

    <!-- Hero -->
    <section class="about-hero" id="story">
        <div class="container">
            <div class="section-eyebrow">Who We Are</div>
            <h1 style="max-width:640px;">We Make Clothes<br>For <em style="font-style:italic;color:var(--accent);">Real People.</em></h1>
            <p style="color:var(--silver);font-size:1.1rem;max-width:520px;margin-top:1.25rem;line-height:1.8;">
                Playground is more than a clothing brand — it's a mindset. Founded on the belief that what you wear should feel like an extension of who you are.
            </p>
        </div>
    </section>

    <!-- Story Section -->
    <section class="about-story">
        <div class="container">
            <div class="about-grid">
                <div class="about-img-wrap">
                    <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=700&q=80" alt="Playground origins">
                </div>
                <div class="about-text">
                    <div class="eyebrow">Our Story</div>
                    <h2>Started From<br>the Ground Up</h2>
                    <p>
                        Playground was born in 2018 out of a small studio apartment with nothing but a sewing machine, a vision, and an unwillingness to settle for ordinary. Our founder, tired of clothing that looked the same on everyone, set out to create pieces that felt personal — garments that spoke before you did.
                    </p>
                    <p>
                        What started as a handful of hand-printed tees sold to friends quickly grew into something much bigger. Word spread, the community grew, and Playground found its people — individuals who valued quality, authenticity, and self-expression above all else.
                    </p>
                    <p>
                        Today we design every piece with that same original intention: clothing that's made to be lived in, worn loud, and remembered.
                    </p>
                </div>
            </div>

            <div class="about-grid reverse" style="margin-top:5rem;">
                <div class="about-img-wrap">
                    <img src="https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=700&q=80" alt="Our craft">
                </div>
                <div class="about-text">
                    <div class="eyebrow">Our Craft</div>
                    <h2>Quality Is<br>Non-Negotiable</h2>
                    <p>
                        Every Playground piece is made with premium materials sourced from ethical suppliers. We work with a network of skilled artisans and manufacturers who share our commitment to craftsmanship and fair labor practices.
                    </p>
                    <p>
                        From the weight of the fabric to the finishing of every seam, we obsess over the details that make the difference between a piece you throw on and one you reach for every time.
                    </p>
                    <a href="/playground/catalog.php" class="btn btn-primary" style="margin-top:1.25rem;">Shop the Collection</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="values-section">
        <div class="container">
            <div style="text-align:center;margin-bottom:3rem;">
                <div class="section-eyebrow">What Drives Us</div>
                <h2 class="section-title">Our Values</h2>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">✦</div>
                    <h4>Authenticity</h4>
                    <p>We never chase trends. Every collection is a genuine expression of what we believe in — timeless, honest, and made to last.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">◈</div>
                    <h4>Quality First</h4>
                    <p>Premium fabrics, expert construction, and obsessive attention to detail — because good enough has never been good enough for us.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">◎</div>
                    <h4>Community</h4>
                    <p>Our customers aren't just buyers — they're the Playground family. We build with them, listen to them, and grow with them.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">⬡</div>
                    <h4>Sustainability</h4>
                    <p>We're committed to reducing our footprint — from packaging to production. Fashion shouldn't cost the planet.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section style="padding:5rem 0;background:var(--off-black);border-top:1px solid var(--border);text-align:center;">
        <div class="container">
            <h2 class="section-title" style="margin-bottom:1rem;">Ready to Find Your Fit?</h2>
            <p style="color:var(--silver);font-size:1rem;margin-bottom:2rem;">Browse the full Playground collection and find pieces that speak your language.</p>
            <a href="/playground/catalog.php" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </section>

</div>

<?php include 'includes/footer.php'; ?>
