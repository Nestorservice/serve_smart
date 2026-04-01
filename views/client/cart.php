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
            <h2 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">
                Votre Sélection
            </h2>
            <?php if (!empty($cartItems)): ?>
            <form action="<?= url('client/cart/clear') ?>" method="POST">
                <?= csrf_field() ?>
                <?php if ($tableNumber): ?>
                <input type="hidden" name="table" value="<?= e($tableNumber) ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-outline-secondary btn-sm" style="border-radius:0;">
                    Vider la sélection
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
            <p class="text-muted mb-4" style="font-family: 'Playfair Display', serif; font-style: italic;">Découvrez notre carte et ajoutez vos mets préférés</p>
            <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . $tableNumber : '')) ?>" class="btn px-4 py-2" style="background: transparent; color: var(--primary); border: 1px solid var(--primary); border-radius: 0; text-transform: uppercase; letter-spacing: 1px;">
                Explorer la Carte
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
                <div class="d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-light);">
                    <i class="bi bi-image" style="color: var(--text-light); font-size: 1.5rem;"></i>
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
                    <button type="submit" class="btn btn-sm" style="color: var(--text-light); transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-light)'">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Continuer les achats -->
        <div class="mt-4">
            <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . $tableNumber : '')) ?>" class="btn px-3 py-2" style="color: var(--text-light); text-decoration: none; border: 1px solid var(--border-light); font-size: 0.9rem; text-transform: uppercase;">
                <i class="bi bi-arrow-left me-2"></i>Retour à la carte
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Résumé de la commande -->
    <div class="col-lg-4">
        <div class="card border-0" style="background: var(--card-bg); border: 1px solid var(--border-light) !important; position: sticky; top: 100px;">
            <div class="card-body p-4">
                <h4 class="mb-4" style="font-family: 'Playfair Display', serif; font-style: italic; color: var(--primary);">
                    L'Addition
                </h4>
                
                <?php if ($tableNumber): ?>
                <div class="d-flex align-items-center mb-3 p-3 text-center justify-content-center" style="border-bottom: 1px solid var(--border-light);">
                    <div>
                        <small style="color: var(--text-light); text-transform: uppercase; letter-spacing: 2px;">Table</small><br>
                        <strong style="color: var(--primary); font-size: 1.2rem; font-family: 'Playfair Display', serif;"><?= e($tableNumber) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between mb-2">
                    <span style="color: var(--text-light);">Sous-total</span>
                    <span><?= Helpers::formatPrice($subtotal) ?></span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Service</span>
                    <span class="text-success">Gratuit</span>
                </div>
                
                <div class="d-flex justify-content-between mb-4 border-top pt-3 mt-3" style="border-color: var(--border-light) !important;">
                    <span class="fs-5" style="font-family: 'Playfair Display', serif;">Total</span>
                    <span class="fs-5" style="color: var(--primary); font-family: 'Playfair Display', serif;"><?= Helpers::formatPrice($subtotal) ?></span>
                </div>
                
                <?php if (!empty($cartItems)): ?>
                <form action="<?= url('client/order') ?>" method="POST">
                    <?= csrf_field() ?>
                    <?php if ($tableNumber): ?>
                    <input type="hidden" name="table_number" value="<?= e($tableNumber) ?>">
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label class="form-label" style="color: var(--text-light); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Requêtes Spéciales</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Allergies, préférences..." style="background: transparent; border: 1px solid var(--border-light); color: var(--text);"></textarea>
                    </div>
                    
                    <button type="submit" class="btn w-100 py-3" style="background: var(--primary); color: var(--dark); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                        Confirmer la Commande
                    </button>
                </form>
                <?php else: ?>
                <button class="btn w-100 py-3 disabled" style="background: rgba(255,255,255,0.05); color: var(--text-light); border: 1px solid var(--border-light); text-transform: uppercase; letter-spacing: 1px;">
                    Panier vide
                </button>
                <?php endif; ?>
            </div>
            
            <div class="text-center py-3" style="border-top: 1px solid var(--border-light);">
                <small style="color: var(--text-light); font-style: italic;">
                    Règlement auprès du Maître d'Hôtel
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
