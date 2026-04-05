<?php
/**
 * SIGR Client Menu - Dribbble POS Redesign
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

// Calculate Cart Totals
$cartSubtotal = 0;
foreach ($cart as $item) {
    if (isset($item['product']['price']) && isset($item['quantity'])) {
        $cartSubtotal += $item['product']['price'] * $item['quantity'];
    }
}
$taxRate = Helpers::getSetting('tax_rate', 0);
$taxAmount = $cartSubtotal * ($taxRate / 100);
$cartTotal = $cartSubtotal + $taxAmount;

// Default food images by category slug/icon
$defaultImages = [
    'burger' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
    'pizza' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&h=300&fit=crop',
    'salad' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
    'dessert' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=400&h=300&fit=crop',
    'drink' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=300&fit=crop',
    'default' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&h=300&fit=crop',
];

$categoryIcons = [];
foreach ($categories as $cat) {
    $categoryIcons[$cat['id']] = $cat['icon'] ?? 'tag';
}

function getProductImage($product, $defaultImages, $categoryIcons) {
    if (!empty($product['image_url'])) { return url($product['image_url']); }
    $catId = $product['category_id'] ?? 0;
    $icon = $categoryIcons[$catId] ?? 'tag';
    $iconMap = [ 'egg-fried' => 'burger', 'cup-hot' => 'drink', 'tropical-storm' => 'drink', 'ice-cream' => 'dessert', 'cake' => 'dessert', 'fire' => 'burger', 'heart' => 'salad', 'leaf' => 'salad', 'cup-straw' => 'drink' ];
    $key = $iconMap[$icon] ?? 'default';
    return $defaultImages[$key] ?? $defaultImages['default'];
}

ob_start();
?>
<style>
    /* Content overrides */
    .cat-selector {
        display: flex;
        gap: 15px;
        overflow-x: auto;
        padding-bottom: 10px;
        margin-bottom: 20px;
        scrollbar-width: none; /* Firefox */
    }
    .cat-selector::-webkit-scrollbar { display: none; }
    
    .cat-item {
        background: var(--bg-color); /* darker than card */
        border: 1px solid var(--border-color);
        padding: 8px 16px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-muted);
        text-decoration: none;
        white-space: nowrap;
        transition: 0.2s;
    }
    .cat-item img {
        width: 24px;
        height: 24px;
        border-radius: 40%;
        object-fit: cover;
    }
    .cat-item.active, .cat-item:hover {
        background: var(--bg-color);
        border-color: var(--primary-orange);
        color: var(--text-main);
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }
    
    .prd-card {
        background: var(--bg-color);
        border-radius: var(--radius-lg);
        padding: 10px;
        transition: 0.3s;
        border: 1px solid var(--border-color);
        position: relative;
    }
    .prd-card:hover {
        transform: translateY(-4px);
        border-color: rgba(255,159,28,0.5);
    }
    
    .prd-img {
        width: 100%;
        height: 160px;
        border-radius: var(--radius-md);
        object-fit: cover;
        margin-bottom: 15px;
        background: #fff; /* gives a white backdrop to PNGs like dribbble */
    }
    
    .prd-info { padding: 0 5px; }
    .prd-title {
        font-weight: 600;
        font-size: 1.05rem;
        margin-bottom: 4px;
    }
    .prd-desc {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 15px;
        height: 36px; /* truncate 2 lines */
        overflow: hidden;
    }
    
    .prd-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .prd-price {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-main);
    }
    .prd-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    
    /* Cart Sidebar */
    .cart-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cart-header h4 { margin: 0; font-size: 1.1rem; font-weight: 600; }
    
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }
    .cart-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--bg-color);
        padding: 10px;
        border-radius: var(--radius-md);
        margin-bottom: 12px;
    }
    .cart-item img {
        width: 50px; height: 50px;
        border-radius: var(--radius-sm);
        object-fit: cover;
    }
    .cart-item-info { flex: 1; }
    .cart-item-title { font-size: 0.9rem; font-weight: 600; margin-bottom: 2px; }
    .cart-item-price { font-size: 0.9rem; color: var(--primary-orange); font-weight: 600;}
    
    .qty-control {
        display: flex;
        align-items: center;
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 50px;
        padding: 2px 5px;
    }
    .qty-btn {
        background: none; border: none; color: var(--text-main);
        width: 20px; height: 20px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; cursor: pointer;
    }
    .qty-btn:hover { color: var(--primary-orange); }
    .qty-val { font-size: 0.85rem; width: 20px; text-align: center; }
    
    .cart-footer {
        padding: 20px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-color);
    }
    .cart-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
        color: var(--text-muted);
    }
    .cart-row.total {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed var(--border-color);
        color: var(--text-main);
        font-size: 1.1rem;
        font-weight: 700;
    }
    .checkout-btn {
        background: var(--primary-orange);
        color: var(--bg-color);
        width: 100%;
        border: none;
        padding: 12px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.2s;
    }
    .checkout-btn:hover { background: var(--primary-hover); transform: translateY(-2px);}
    
    .add-to-cart-form button {
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        border-radius: 50px;
        padding: 6px 16px;
        font-size: 0.8rem;
        transition: 0.3s;
    }
    .add-to-cart-form button:hover {
        background: var(--primary-orange);
        color: var(--bg-color);
        border-color: var(--primary-orange);
    }
</style>

<div class="app-layout">
    
    <!-- LEFT: MENU -->
    <div class="main-content">
        <h4 class="mb-4">Product Category</h4>
        <div class="cat-selector">
            <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . e($tableNumber) : '')) ?>" 
               class="cat-item <?= !$selectedCategory ? 'active' : '' ?>">
                All (<?= count($products) ?>)
            </a>
            <?php foreach ($categories as $category): ?>
            <a href="<?= url('client/menu?category=' . $category['id'] . ($tableNumber ? '&table=' . e($tableNumber) : '')) ?>" 
               class="cat-item <?= $selectedCategory == $category['id'] ? 'active' : '' ?>">
                <i class="bi bi-<?= e($category['icon'] ?? 'tag') ?>"></i>
                <?= e(!empty($category['name_en']) ? $category['name_en'] : ($category['name_fr'] ?? '')) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <?php 
                $catName = 'Our Menu';
                if ($selectedCategory) {
                    $cIndex = array_search($selectedCategory, array_column($categories, 'id'));
                    if ($cIndex !== false) {
                        $catName = !empty($categories[$cIndex]['name_en']) ? $categories[$cIndex]['name_en'] : ($categories[$cIndex]['name_fr'] ?? 'Category');
                    }
                }
            ?>
            <h5 class="mb-0"><?= e($catName) ?></h5>
            <small class="text-muted">Showing <?= count($products) ?> Results</small>
        </div>

        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="prd-card">
                <img src="<?= getProductImage($product, $defaultImages, $categoryIcons) ?>" alt="" class="prd-img">
                <div class="prd-info">
                    <div class="prd-title"><?= e(!empty($product['name_en']) ? $product['name_en'] : ($product['name_fr'] ?? '')) ?></div>
                    <div class="prd-desc"><?= e(!empty($product['description_en']) ? $product['description_en'] : ($product['description_fr'] ?? 'A delicious choice.')) ?></div>
                    <div class="prd-bottom mt-2">
                        <span class="prd-price"><?= Helpers::formatPrice($product['price']) ?></span>
                        
                        <?php if (!$readOnly): ?>
                        <div class="add-to-cart-container d-flex align-items-center gap-2">
                            <div class="qty-selector d-flex align-items-center bg-dark rounded-pill px-2 border" style="border-color: var(--border-color) !important;">
                                <button type="button" class="btn btn-sm text-white p-1 border-0 card-qty-minus"><i class="bi bi-dash"></i></button>
                                <input type="number" class="card-qty-input bg-transparent border-0 text-white text-center fw-bold" value="1" min="1" style="width: 30px; font-size: 0.85rem;">
                                <button type="button" class="btn btn-sm text-white p-1 border-0 card-qty-plus"><i class="bi bi-plus"></i></button>
                            </div>
                            <form action="<?= url('client/cart/add') ?>" method="POST" class="add-to-cart-form m-0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" class="form-qty-input" value="1">
                                <?php if ($tableNumber): ?>
                                <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                                <?php endif; ?>
                                <button type="submit">Add</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <span class="prd-meta text-warning"><i class="bi bi-qr-code"></i> Scan</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT: CART SIDEBAR -->
    <div class="sidebar-cart">
        <div class="cart-header">
            <h4>Current Orders</h4>
            <a href="<?= url('client/cart/clear') ?>" class="text-muted"><i class="bi bi-trash"></i></a>
        </div>
        
        <div class="cart-items">
            <?php if (empty($cart)): ?>
                <div class="text-center text-muted" style="margin-top: 100px;">
                    <i class="bi bi-cart-x" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p class="mt-3">No items selected</p>
                </div>
            <?php else: ?>
                <?php foreach ($cart as $id => $item): ?>
                <div class="cart-item">
                    <img src="<?= getProductImage($item['product'], $defaultImages, $categoryIcons) ?>" alt="">
                    <div class="cart-item-info">
                        <div class="cart-item-title"><?= e(!empty($item['product']['name_en']) ? $item['product']['name_en'] : ($item['product']['name_fr'] ?? '')) ?></div>
                        <div class="cart-item-price"><?= Helpers::formatPrice($item['product']['price']) ?></div>
                    </div>
                    <form action="<?= url('client/cart/update') ?>" method="POST" class="qty-control m-0 p-0 d-flex bg-transparent border-0 qty-update-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="key" value="<?= $item['key'] ?>">
                        <input type="hidden" name="quantity" class="qty-input" value="<?= $item['quantity'] ?>">
                        <button type="button" class="qty-btn btn-minus"><i class="bi bi-dash"></i></button>
                        <div class="qty-val fw-bold text-white"><?= $item['quantity'] ?></div>
                        <button type="button" class="qty-btn btn-plus"><i class="bi bi-plus"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper to format currency
            const formatPrice = (price) => {
                return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XAF' }).format(price).replace('XAF', 'FCFA');
            };

            // 1. Menu Card Quantity Controls
            document.querySelectorAll('.add-to-cart-container').forEach(container => {
                const minusBtn = container.querySelector('.card-qty-minus');
                const plusBtn = container.querySelector('.card-qty-plus');
                const qtyInput = container.querySelector('.card-qty-input');
                const formQtyInput = container.querySelector('.form-qty-input');
                
                minusBtn.addEventListener('click', () => {
                    let val = parseInt(qtyInput.value);
                    if (val > 1) { val--; qtyInput.value = val; formQtyInput.value = val; }
                });
                
                plusBtn.addEventListener('click', () => {
                    let val = parseInt(qtyInput.value);
                    val++; qtyInput.value = val; formQtyInput.value = val;
                });

                qtyInput.addEventListener('change', () => {
                    let val = parseInt(qtyInput.value);
                    if (isNaN(val) || val < 1) val = 1;
                    qtyInput.value = val; formQtyInput.value = val;
                });
            });

            // 2. AJAX Add to Cart
            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const btn = form.querySelector('button');
                    const originalText = btn.innerHTML;
                    
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const result = await response.json();
                        
                        if (result.success) {
                            // Instant feedback
                            btn.innerHTML = '<i class="bi bi-check"></i>';
                            setTimeout(() => { btn.innerHTML = originalText; btn.disabled = false; }, 1000);
                            
                            // Refresh sidebar
                            location.reload(); // Simple refresh for now to rebuild enriched cart items, or we could fetch JSON
                        } else {
                            alert(result.message || 'Error adding to cart');
                            btn.innerHTML = originalText; btn.disabled = false;
                        }
                    } catch (err) {
                        console.error(err);
                        btn.innerHTML = originalText; btn.disabled = false;
                    }
                });
            });

            // 3. AJAX Update Cart (Sidebar)
            document.querySelectorAll('.qty-update-form').forEach(form => {
                const minusBtn = form.querySelector('.btn-minus');
                const plusBtn = form.querySelector('.btn-plus');
                const qtyInput = form.querySelector('.qty-input');
                
                const updateQty = async (newVal) => {
                    const formData = new FormData(form);
                    formData.set('quantity', newVal);
                    
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const result = await response.json();
                        if (result.success) {
                            location.reload(); 
                        }
                    } catch (err) { console.error(err); }
                };

                minusBtn.addEventListener('click', () => {
                    const val = parseInt(qtyInput.value);
                    if (val >= 1) updateQty(val - 1);
                });
                
                plusBtn.addEventListener('click', () => {
                    updateQty(parseInt(qtyInput.value) + 1);
                });
            });
        });
        </script>

        <div class="cart-footer">
            <div class="cart-row">
                <span>Subtotal</span>
                <span><?= Helpers::formatPrice($cartSubtotal) ?></span>
            </div>
            <div class="cart-row">
                <span>Tax Rate (<?= $taxRate ?>%)</span>
                <span><?= Helpers::formatPrice($taxAmount) ?></span>
            </div>
            <div class="cart-row total">
                <span>Total</span>
                <span><?= Helpers::formatPrice($cartTotal) ?></span>
            </div>

            <?php if (!$readOnly && !empty($cart)): ?>
            <form action="<?= url('client/order') ?>" method="POST" class="mt-3">
                <?= csrf_field() ?>
                <button type="submit" class="checkout-btn">Continue to payment <i class="bi bi-arrow-right ms-2"></i></button>
            </form>
            <?php elseif (empty($cart)): ?>
            <button class="checkout-btn mt-3" style="opacity: 0.5; background: var(--border-color); color: var(--text-muted);" disabled>Continue to payment <i class="bi bi-arrow-right ms-2"></i></button>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/client.php';
?>
