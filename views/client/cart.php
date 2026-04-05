<?php
/**
 * SIGR Client Cart - Mobile Fallback (Dribbble dark POS theme)
 */

use Core\Helpers;

$pageTitle = 'Your Cart';
$currentPage = 'cart';
$cart = $cartItems ?? ($cart ?? []);
$tableNumber = $tableNumber ?? null;

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

ob_start();
?>
<div class="row m-0 p-0 justify-content-center">
    <div class="col-12 col-md-8 col-lg-6 mt-4">
        <h3 class="mb-4">Current Orders</h3>
        
        <div class="card bg-transparent border-0">
            <div class="card-body p-0">
            <?php if (empty($cartItems)): ?>
                <div class="text-center text-muted" style="margin-top: 100px;">
                    <i class="bi bi-cart-x" style="font-size: 4rem; opacity: 0.5;"></i>
                    <p class="mt-3">No items selected</p>
                    <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . e($tableNumber) : '')) ?>" class="btn btn-outline-warning mt-3">Back to menu</a>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                <div class="d-flex align-items-center mb-3 p-3 rounded" style="background: var(--card-bg); border: 1px solid var(--border-color);">
                    <div style="flex:1">
                        <h6 class="mb-1 text-white"><?= e(!empty($item['product']['name_en']) ? $item['product']['name_en'] : ($item['product']['name_fr'] ?? '')) ?></h6>
                        <div class="text-orange fw-bold"><?= Helpers::formatPrice($item['product']['price']) ?></div>
                    </div>
                    
                    <form action="<?= url('client/cart/update') ?>" method="POST" class="d-flex align-items-center m-0 qty-update-form" style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 50px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="key" value="<?= $item['key'] ?>">
                        <input type="hidden" name="quantity" class="qty-input" value="<?= $item['quantity'] ?>">
                        <button type="button" class="btn border-0 py-1 px-2 text-white btn-minus"><i class="bi bi-dash"></i></button>
                        <div class="fw-bold text-white px-2"><?= $item['quantity'] ?></div>
                        <button type="button" class="btn border-0 py-1 px-2 text-white btn-plus"><i class="bi bi-plus"></i></button>
                    </form>
                    
                    <form action="<?= url('client/cart/remove') ?>" method="POST" class="m-0 ms-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="key" value="<?= $item['key'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
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
                
                <div class="mt-4 p-4 rounded" style="background: var(--card-bg); border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Subtotal</span>
                        <span><?= Helpers::formatPrice($cartSubtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Tax Rate (<?= $taxRate ?>%)</span>
                        <span><?= Helpers::formatPrice($taxAmount) ?></span>
                    </div>
                    <hr style="border-color: var(--border-color);">
                    <div class="d-flex justify-content-between fs-5 fw-bold text-white mb-4">
                        <span>Total</span>
                        <span><?= Helpers::formatPrice($cartTotal) ?></span>
                    </div>
                    
                    <form action="<?= url('client/order') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn w-100 py-3 fw-bold" style="background: var(--primary-orange); color: var(--bg-color);">Continue to payment <i class="bi bi-arrow-right ms-2"></i></button>
                    </form>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
// Inclure le layour client normal puisqu'il contient de quoi avoir une belle UI complete
include VIEWS_PATH . '/layouts/client.php';
?>
