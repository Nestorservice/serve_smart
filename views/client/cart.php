<?php
/**
 * SIGR Client Cart - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Mon Panier';
$currentPage = 'cart';
$cartItems = $cartItems ?? [];
$tableNumber = $tableNumber ?? null;
$cart = \Core\Session::getInstance()->get('cart', []);
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
}

ob_start();
?>

<div class="row">
    <!-- Liste des articles -->
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-cart3 me-2" style="color: var(--primary)"></i>Mon Panier
            </h2>
            <?php if (!empty($cartItems)): ?>
            <form action="<?= url('client/cart/clear') ?>" method="POST">
                <?= csrf_field() ?>
                <?php if ($tableNumber): ?>
                <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i> Vider le panier
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (empty($cartItems)): ?>
        <!-- Panier Vide -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-cart-x display-1" style="color: #ccc;"></i>
            </div>
            <h3 class="text-muted mb-3">Votre panier est vide</h3>
            <p class="text-muted mb-4">Découvrez notre menu et ajoutez vos plats préférés</p>
            <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . $tableNumber : '')) ?>" class="btn btn-primary btn-lg" style="background: var(--gradient); border: none; border-radius: 50px; padding: 1rem 2rem;">
                <i class="bi bi-grid me-2"></i>Voir le menu
            </a>
        </div>
        <?php else: ?>
        <!-- Liste des articles -->
        <div class="cart-items">
            <?php foreach ($cartItems as $index => $item): ?>
            <div class="cart-item d-flex align-items-center">
                <?php if (!empty($item['image_url'])): ?>
                <img src="<?= url($item['image_url']) ?>" alt="<?= e($item['name']) ?>">
                <?php else: ?>
                <div class="d-flex align-items-center justify-content-center bg-light" style="width: 80px; height: 80px; border-radius: 12px;">
                    <i class="bi bi-image text-muted fs-3"></i>
                </div>
                <?php endif; ?>
                
                <div class="flex-grow-1 ms-3">
                    <h6 class="fw-bold mb-1"><?= e($item['name']) ?></h6>
                    <span class="text-muted small"><?= Helpers::formatPrice($item['price']) ?></span>
                </div>
                
                <div class="quantity-control me-4">
                    <form action="<?= url('client/cart/update') ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <input type="hidden" name="action" value="decrease">
                        <?php if ($tableNumber): ?>
                        <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                        <?php endif; ?>
                        <button type="submit" class="quantity-btn">−</button>
                    </form>
                    <span class="fw-bold px-2"><?= $item['quantity'] ?? 1 ?></span>
                    <form action="<?= url('client/cart/update') ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <input type="hidden" name="action" value="increase">
                        <?php if ($tableNumber): ?>
                        <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                        <?php endif; ?>
                        <button type="submit" class="quantity-btn">+</button>
                    </form>
                </div>
                
                <div class="text-end me-3">
                    <span class="fw-bold" style="color: var(--primary)">
                        <?= Helpers::formatPrice(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?>
                    </span>
                </div>
                
                <form action="<?= url('client/cart/remove') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="index" value="<?= $index ?>">
                    <?php if ($tableNumber): ?>
                    <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 36px; height: 36px;">
                        <i class="bi bi-x"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Continuer les achats -->
        <div class="mt-4">
            <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . $tableNumber : '')) ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Continuer les achats
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Résumé de la commande -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-lg" style="border-radius: 20px; position: sticky; top: 100px;">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-4">
                    <i class="bi bi-receipt me-2" style="color: var(--primary)"></i>Résumé
                </h4>
                
                <?php if ($tableNumber): ?>
                <div class="d-flex align-items-center mb-3 p-3 rounded" style="background: rgba(102, 126, 234, 0.1);">
                    <i class="bi bi-pin-map fs-4 me-2" style="color: var(--primary)"></i>
                    <div>
                        <small class="text-muted d-block">Table</small>
                        <strong><?= e($tableNumber) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Sous-total</span>
                    <span><?= Helpers::formatPrice($subtotal) ?></span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Service</span>
                    <span class="text-success">Gratuit</span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-5" style="color: var(--primary)"><?= Helpers::formatPrice($subtotal) ?></span>
                </div>
                
                <?php if (!empty($cartItems)): ?>
                <form action="<?= url('client/order') ?>" method="POST">
                    <?= csrf_field() ?>
                    <?php if ($tableNumber): ?>
                    <input type="hidden" name="table_number" value="<?= e($tableNumber) ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-chat-dots me-1"></i> Notes (optionnel)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Allergies, préférences..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-lg w-100" style="background: var(--gradient); border: none; color: white; border-radius: 50px; padding: 1rem;">
                        <i class="bi bi-check-circle me-2"></i>Passer la commande
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-lg w-100 btn-secondary" disabled style="border-radius: 50px; padding: 1rem;">
                    <i class="bi bi-cart me-2"></i>Panier vide
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Badge sécurité -->
            <div class="text-center py-3 border-top">
                <small class="text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Paiement sur place
                </small>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Inclure le layout
include VIEWS_PATH . '/layouts/client.php';
?>
