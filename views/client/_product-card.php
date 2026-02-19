<?php
use Core\Helpers;
use Core\Session;

$lang = Session::getInstance()->getLanguage();
?>
<div class="product-card" data-product-id="<?= $product['id'] ?>">
    <?php if (!empty($product['image_url'])): ?>
    <img src="<?= Helpers::e($product['image_url']) ?>" 
         alt="<?= Helpers::e($lang === 'en' ? $product['name_en'] : $product['name_fr']) ?>"
         loading="lazy">
    <?php else: ?>
    <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 150px;">
        <i class="bi bi-image text-white display-4"></i>
    </div>
    <?php endif; ?>
    
    <div class="card-body">
        <h5 class="card-title h6 mb-1 text-truncate">
            <?= Helpers::e($lang === 'en' ? $product['name_en'] : $product['name_fr']) ?>
        </h5>
        
        <?php if (!empty($product['description_fr']) || !empty($product['description_en'])): ?>
        <p class="small text-muted mb-2 text-truncate">
            <?= Helpers::e($lang === 'en' ? $product['description_en'] : $product['description_fr']) ?>
        </p>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center">
            <span class="price"><?= Helpers::formatPrice($product['price']) ?></span>
            
            <?php 
            $isAvailable = $product['is_available'] && (($product['stock_quantity'] ?? 999) > 0);
            if ($isAvailable): 
            ?>
            <button class="btn btn-primary btn-sm add-to-cart-btn" 
                    data-product-id="<?= $product['id'] ?>"
                    data-product-name="<?= Helpers::e($lang === 'en' ? $product['name_en'] : $product['name_fr']) ?>">
                <i class="bi bi-plus"></i>
            </button>
            <?php else: ?>
            <span class="badge bg-secondary small"><?= Helpers::__('client.unavailable') ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Gestionnaire d'ajout au panier pour cette carte
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    if (!btn.dataset.listenerAdded) {
        btn.dataset.listenerAdded = 'true';
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            try {
                const response = await fetch(`${SIGR.baseUrl}/api/cart`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: productId,
                        quantity: 1
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateCartUI(data.data.cart_count, data.data.cart_total);
                    showToast(__('client.added_to_cart'));
                } else {
                    showToast(data.message, 'danger');
                }
            } catch (error) {
                showToast(__('errors.generic'), 'danger');
            }
            
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-plus"></i>';
        });
    }
});
</script>
