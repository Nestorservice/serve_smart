<?php
/**
 * SIGR Client Menu - Premium Redesign
 */

use Core\Helpers;

// Variables
$pageTitle = 'Notre Menu';
$currentPage = 'menu';
$categories = $categories ?? [];
$products = $products ?? [];
$featured = $featured ?? [];
$selectedCategory = $selectedCategory ?? null;
$tableNumber = $tableNumber ?? null;
$cart = $cart ?? [];
$readOnly = $readOnly ?? false;
$cartCount = count($cart);

// Default food images by category slug/icon
$defaultImages = [
    'burger' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
    'pizza' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&h=300&fit=crop',
    'salad' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
    'dessert' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=400&h=300&fit=crop',
    'drink' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=300&fit=crop',
    'default' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&h=300&fit=crop',
];

// Category icons mapping
$categoryIcons = [];
foreach ($categories as $cat) {
    $categoryIcons[$cat['id']] = $cat['icon'] ?? 'tag';
}

function getProductImage($product, $defaultImages, $categoryIcons) {
    if (!empty($product['image_url'])) {
        return url($product['image_url']);
    }
    $catId = $product['category_id'] ?? 0;
    $icon = $categoryIcons[$catId] ?? 'tag';
    // Map icons to images
    $iconMap = [
        'egg-fried' => 'burger', 'cup-hot' => 'drink', 'tropical-storm' => 'drink',
        'ice-cream' => 'dessert', 'cake' => 'dessert', 'fire' => 'burger',
        'heart' => 'salad', 'leaf' => 'salad', 'cup-straw' => 'drink',
    ];
    $key = $iconMap[$icon] ?? 'default';
    return $defaultImages[$key] ?? $defaultImages['default'];
}

ob_start();
?>

<!-- Hero Banner -->
<div class="menu-hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-badge">
            <i class="bi bi-stars"></i> Saveurs d'Afrique
        </div>
        <h1>Découvrez nos <span class="hero-highlight">Saveurs</span></h1>
        <p>Des plats authentiques préparés avec passion et amour</p>
        
        <!-- Search -->
        <div class="hero-search">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Rechercher un plat, une boisson...">
        </div>
    </div>
</div>

<!-- Category Navigation -->
<div class="categories-nav" id="categoriesNav">
    <div class="categories-scroll">
        <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . e($tableNumber) : '')) ?>" 
           class="cat-chip <?= !$selectedCategory ? 'active' : '' ?>">
            <i class="bi bi-grid-fill"></i>
            <span>Tout</span>
            <span class="cat-count"><?= count($products) ?></span>
        </a>
        <?php foreach ($categories as $category): ?>
        <a href="<?= url('client/menu?category=' . $category['id'] . ($tableNumber ? '&table=' . e($tableNumber) : '')) ?>" 
           class="cat-chip <?= $selectedCategory == $category['id'] ? 'active' : '' ?>">
            <i class="bi bi-<?= e($category['icon'] ?? 'tag') ?>"></i>
            <span><?= e($category['name_fr'] ?? $category['name'] ?? '') ?></span>
            <span class="cat-count"><?= $category['product_count'] ?? 0 ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!empty($featured) && !$selectedCategory): ?>
<!-- Featured Section -->
<section class="featured-section">
    <div class="section-header">
        <h2><i class="bi bi-fire text-danger"></i> Populaires</h2>
        <span class="section-line"></span>
    </div>
    <div class="featured-scroll">
        <?php foreach (array_slice($featured, 0, 4) as $product): ?>
        <div class="featured-card">
            <div class="featured-img">
                <img src="<?= getProductImage($product, $defaultImages, $categoryIcons) ?>" alt="<?= e($product['name_fr'] ?? '') ?>">
                <div class="featured-badge">⭐ Populaire</div>
            </div>
            <div class="featured-info">
                <h4><?= e($product['name_fr'] ?? $product['name'] ?? '') ?></h4>
                <span class="price-tag"><?= Helpers::formatPrice($product['price']) ?></span>
            </div>
            <?php if (!$readOnly): ?>
            <form action="<?= url('client/cart/add') ?>" method="POST" class="add-to-cart-form">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <?php if ($tableNumber): ?>
                <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                <?php endif; ?>
                <button type="submit" class="featured-add-btn">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Products Grid -->
<section class="products-section">
    <div class="section-header">
        <h2>
            <i class="bi bi-grid-3x3-gap"></i> 
            <?= $selectedCategory ? e($categories[array_search($selectedCategory, array_column($categories, 'id'))]['name_fr'] ?? 'Catégorie') : 'Tout le menu' ?>
        </h2>
        <span class="product-count"><?= count($products) ?> plat<?= count($products) > 1 ? 's' : '' ?></span>
    </div>
    
    <div class="products-grid" id="productsGrid">
        <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="empty-icon">🍽️</div>
            <h3>Menu bientôt disponible</h3>
            <p>Nous préparons de délicieuses surprises pour vous</p>
        </div>
        <?php else: ?>
        <?php foreach ($products as $product): ?>
        <div class="product-card-wrap" data-name="<?= strtolower(e($product['name_fr'] ?? $product['name'] ?? '')) ?>" data-desc="<?= strtolower(e($product['description_fr'] ?? '')) ?>">
            <div class="product-card2">
                <div class="product-img-wrap">
                    <img src="<?= getProductImage($product, $defaultImages, $categoryIcons) ?>" 
                         alt="<?= e($product['name_fr'] ?? '') ?>" loading="lazy">
                    <?php if (!empty($product['is_featured'])): ?>
                    <span class="prod-badge badge-hot">🔥 Populaire</span>
                    <?php endif; ?>
                    <?php if (!empty($product['preparation_time'])): ?>
                    <span class="prod-badge badge-time">
                        <i class="bi bi-clock"></i> <?= $product['preparation_time'] ?> min
                    </span>
                    <?php endif; ?>
                </div>
                <div class="product-details">
                    <div class="product-meta">
                        <h5 class="product-name"><?= e($product['name_fr'] ?? $product['name'] ?? '') ?></h5>
                        <p class="product-desc"><?= e(mb_substr($product['description_fr'] ?? $product['description'] ?? 'Plat fait maison avec amour', 0, 70)) ?></p>
                    </div>
                    <div class="product-bottom">
                        <span class="product-price2"><?= Helpers::formatPrice($product['price']) ?></span>
                        <?php if (!isset($product['is_available']) || $product['is_available']): ?>
                            <?php if (!$readOnly): ?>
                            <form action="<?= url('client/cart/add') ?>" method="POST" class="add-to-cart-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <?php if ($tableNumber): ?>
                                <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                                <?php endif; ?>
                                <button type="submit" class="add-btn">
                                    <i class="bi bi-cart-plus"></i> Ajouter
                                </button>
                            </form>
                            <?php else: ?>
                            <a href="<?= url('/') ?>" class="scan-btn">
                                <i class="bi bi-qr-code"></i> Scanner
                            </a>
                            <?php endif; ?>
                        <?php else: ?>
                        <span class="unavailable-badge">Indisponible</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Floating Cart Button (Mobile) -->
<?php if ($cartCount > 0 && !$readOnly): ?>
<a href="<?= url('client/cart') ?>" class="floating-cart" id="floatingCart">
    <div class="floating-cart-inner">
        <div class="fc-left">
            <i class="bi bi-cart3"></i>
            <span class="fc-badge"><?= $cartCount ?></span>
        </div>
        <span class="fc-text">Voir le panier</span>
        <i class="bi bi-arrow-right"></i>
    </div>
</a>
<?php endif; ?>

<script>
// Search
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.product-card-wrap').forEach(item => {
        const name = item.dataset.name || '';
        const desc = item.dataset.desc || '';
        item.style.display = (name.includes(q) || desc.includes(q)) ? '' : 'none';
    });
    // Show/hide empty state
    const visible = document.querySelectorAll('.product-card-wrap[style=""], .product-card-wrap:not([style])').length;
    const grid = document.getElementById('productsGrid');
    const existing = grid.querySelector('.search-empty');
    if (visible === 0 && q.length > 0) {
        if (!existing) {
            grid.insertAdjacentHTML('beforeend', '<div class="search-empty empty-state"><div class="empty-icon">🔍</div><h3>Aucun résultat</h3><p>Essayez un autre terme</p></div>');
        }
    } else if (existing) {
        existing.remove();
    }
});

// Add to cart animation
document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const btn = this.querySelector('button, .featured-add-btn');
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Ajouté !';
            btn.classList.add('added');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('added');
            }, 1200);
        }
    });
});

// Sticky categories shadow
const catNav = document.getElementById('categoriesNav');
if (catNav) {
    window.addEventListener('scroll', () => {
        catNav.classList.toggle('scrolled', window.scrollY > 200);
    });
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/client.php';
?>
