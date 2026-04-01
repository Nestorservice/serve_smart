<?php
/**
 * SIGR Order Tracking - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Suivi de commande';
$currentPage = 'tracking';
$order = $order ?? null;
$orderItems = $orderItems ?? [];
$tableNumber = $order['table_number'] ?? null;
$cart = [];

$statuses = [
    'pending' => ['label' => 'En attente', 'icon' => 'clock', 'color' => 'warning'],
    'confirmed' => ['label' => 'Confirmée', 'icon' => 'check-circle', 'color' => 'info'],
    'preparing' => ['label' => 'En préparation', 'icon' => 'fire', 'color' => 'primary'],
    'ready' => ['label' => 'Prête !', 'icon' => 'bell', 'color' => 'success'],
    'served' => ['label' => 'Servie', 'icon' => 'check2-all', 'color' => 'secondary'],
    'cancelled' => ['label' => 'Annulée', 'icon' => 'x-circle', 'color' => 'danger'],
];

$currentStatus = $order['status'] ?? 'pending';
$statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'served'];
$currentIndex = array_search($currentStatus, $statusOrder);

ob_start();
?>

<?php if (!$order): ?>
<!-- Pas de commande -->
<div class="text-center py-5">
    <i class="bi bi-search display-1 text-muted mb-4"></i>
    <h2 class="mb-3">Commande non trouvée</h2>
    <p class="text-muted mb-4">Vérifiez le numéro de commande et réessayez</p>
    <a href="<?= url('/') ?>" class="btn px-4 py-2" style="background: transparent; border: 1px solid var(--primary); color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">
        <i class="bi bi-house me-2"></i>Retour à l'accueil
    </a>
</div>
<?php else: ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        
        <!-- Header Commande -->
        <div class="card border-0 mb-4" style="border-radius: var(--radius); overflow: hidden; background: var(--card-bg); border: 1px solid var(--border-light) !important;">
            <div class="card-body p-0">
                <div class="p-4 text-center" style="border-bottom: 1px solid var(--border-light);">
                    <h4 class="mb-2" style="font-family: 'Playfair Display', serif; color: var(--primary);">Commande #<?= e($order['order_number'] ?? $order['id']) ?></h4>
                    <p class="mb-0" style="color: var(--text-light); font-style: italic;">
                        <i class="bi bi-clock me-1"></i>
                        <?= Helpers::formatDate($order['created_at'] ?? 'now', 'd/m/Y à H:i') ?>
                    </p>
                </div>
                
                <!-- Statut actuel -->
                <div class="p-4 text-center">
                    <?php $status = $statuses[$currentStatus] ?? $statuses['pending']; ?>
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                         style="width: 80px; height: 80px; background: rgba(<?= $status['color'] == 'warning' ? '255, 193, 7' : ($status['color'] == 'success' ? '40, 167, 69' : '102, 126, 234') ?>, 0.15);">
                        <i class="bi bi-<?= $status['icon'] ?> display-5 text-<?= $status['color'] ?>"></i>
                    </div>
                    <h3 class="text-<?= $status['color'] ?> fw-bold mb-1"><?= $status['label'] ?></h3>
                    <?php if ($currentStatus === 'preparing'): ?>
                    <p class="text-muted mb-0">Notre chef prépare votre commande avec soin</p>
                    <?php elseif ($currentStatus === 'ready'): ?>
                    <p class="text-success mb-0 fw-bold">
                        <i class="bi bi-bell-fill me-1"></i>
                        Votre commande est prête ! Un serveur vous l'apporte.
                    </p>
                    <?php elseif ($currentStatus === 'pending'): ?>
                    <p class="text-muted mb-0">Votre commande a bien été reçue</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Timeline de progression -->
        <div class="card border-0 mb-4" style="background: var(--card-bg); border: 1px solid var(--border-light) !important; border-radius: var(--radius);">
            <div class="card-body p-4">
                <h5 class="mb-4" style="font-family: 'Playfair Display', serif; color: var(--primary); font-style: italic;">
                    <i class="bi bi-signpost-2 me-2"></i>
                    Suivi de l'expérience
                </h5>
                
                <div class="tracking-timeline">
                    <?php foreach ($statusOrder as $index => $statusKey): ?>
                    <?php 
                    $stepStatus = $statuses[$statusKey];
                    $isCompleted = $currentIndex !== false && $index < $currentIndex;
                    $isActive = $statusKey === $currentStatus && $currentStatus !== 'served';
                    $isFuture = $currentIndex === false || $index > $currentIndex;
                    ?>
                    <div class="tracking-step <?= $isCompleted ? 'completed' : '' ?> <?= $isActive ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1 <?= $isFuture ? 'text-muted' : '' ?>"><?= $stepStatus['label'] ?></h6>
                                <small class="text-muted">
                                    <?php if ($isCompleted): ?>
                                    <i class="bi bi-check-circle text-success me-1"></i> Terminé
                                    <?php elseif ($isActive): ?>
                                    <i class="bi bi-arrow-right text-primary me-1"></i> En cours...
                                    <?php else: ?>
                                    <i class="bi bi-circle text-muted me-1"></i> À venir
                                    <?php endif; ?>
                                </small>
                            </div>
                            <i class="bi bi-<?= $stepStatus['icon'] ?> fs-4 <?= $isFuture ? 'text-muted' : 'text-' . $stepStatus['color'] ?>"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Détails de la commande -->
        <div class="card border-0" style="background: var(--card-bg); border: 1px solid var(--border-light) !important; border-radius: var(--radius);">
            <div class="card-body p-4">
                <h5 class="mb-4" style="font-family: 'Playfair Display', serif; color: var(--primary); font-style: italic;">
                    <i class="bi bi-basket me-2"></i>
                    Détails du repas
                </h5>
                
                <?php if ($tableNumber): ?>
                <div class="d-flex align-items-center mb-4 p-3 justify-content-center text-center" style="border-bottom: 1px solid var(--border-light);">
                    <div>
                        <small style="color: var(--text-light); text-transform: uppercase; letter-spacing: 2px;">Table</small><br>
                        <strong style="color: var(--primary); font-size: 1.2rem; font-family: 'Playfair Display', serif;"><?= e($tableNumber) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php foreach ($orderItems as $item): ?>
                <div class="d-flex align-items-center py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-center me-3" 
                         style="width: 40px; height: 40px; border: 1px solid var(--primary); border-radius: 50%;">
                        <span style="color: var(--primary); font-family: 'Playfair Display', serif;"><?= $item['quantity'] ?></span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0" style="font-family: 'Playfair Display', serif; color: var(--text);"><?= e($item['product_name'] ?? 'Produit') ?></h6>
                        <small style="color: var(--text-light);"><?= Helpers::formatPrice($item['unit_price'] ?? 0) ?> / unité</small>
                    </div>
                    <strong style="color: var(--primary)"><?= Helpers::formatPrice($item['subtotal'] ?? 0) ?></strong>
                </div>
                <?php endforeach; ?>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="mt-4 p-3 rounded" style="background: #fff9e6; border-left: 4px solid #ffc107;">
                    <small class="text-muted d-block mb-1"><i class="bi bi-chat-dots me-1"></i> Notes :</small>
                    <span><?= e($order['notes']) ?></span>
                </div>
                <?php endif; ?>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5 fw-bold">Total</span>
                    <span class="fs-4 fw-bold" style="color: var(--primary)">
                        <?= Helpers::formatPrice($order['total_amount'] ?? 0) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="text-center mt-4">
            <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . $tableNumber : '')) ?>" class="btn px-4 py-2" style="background: transparent; border: 1px solid var(--primary); color: var(--primary); letter-spacing: 1px; text-transform: uppercase;">
                Nouvelle commande
            </a>
        </div>
        
    </div>
</div>

<!-- Auto-refresh pour les commandes en cours -->
<?php if (in_array($currentStatus, ['pending', 'confirmed', 'preparing'])): ?>
<script>
    // Rafraîchir la page toutes les 30 secondes pour les commandes en cours
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
<?php endif; ?>

<?php endif; ?>

<?php
$content = ob_get_clean();

// Inclure le layout
include VIEWS_PATH . '/layouts/client.php';
?>
