<?php
/**
 * SIGR Order Tracking - Premium Dark POS Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Order Tracking';
$currentPage = 'tracking';
$order = $order ?? null;
$orderItems = $orderItems ?? [];
$tableNumber = $order['table_number'] ?? null;

$statuses = [
    'pending' => ['label' => 'Pending', 'icon' => 'clock', 'color' => '#ffc107', 'desc' => 'Order received'],
    'confirmed' => ['label' => 'Confirmed', 'icon' => 'check-circle', 'color' => '#17a2b8', 'desc' => 'Sent to kitchen'],
    'preparing' => ['label' => 'Preparing', 'icon' => 'fire', 'color' => '#ff6b35', 'desc' => 'Chef is cooking'],
    'ready' => ['label' => 'Ready', 'icon' => 'bell', 'color' => '#28a745', 'desc' => 'Waiting for server'],
    'served' => ['label' => 'Served', 'icon' => 'check2-all', 'color' => '#adb5bd', 'desc' => 'Enjoy your meal!'],
    'cancelled' => ['label' => 'Cancelled', 'icon' => 'x-circle', 'color' => '#dc3545', 'desc' => 'Order was cancelled'],
];

$currentStatus = $order['status'] ?? 'pending';
$statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'served'];
$currentIndex = array_search($currentStatus, $statusOrder);

ob_start();
?>

<style>
/* Premium Tracking Styles */
.tracking-container {
    max-width: 800px;
    margin: 0 auto;
    color: #e0e0e0;
}
.tracking-header {
    background: rgba(255, 107, 53, 0.05);
    border: 1px solid rgba(255, 107, 53, 0.2);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.tracking-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, transparent, #ff6b35, transparent);
}
.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 90px; height: 90px;
    border-radius: 50%;
    background: rgba(26, 28, 35, 0.8);
    box-shadow: 0 0 30px rgba(0,0,0,0.5), inset 0 0 20px rgba(255, 107, 53, 0.2);
    border: 1px solid rgba(255, 107, 53, 0.3);
    margin-bottom: 1rem;
    position: relative;
}
.status-badge i {
    font-size: 2.5rem;
}
.status-glow {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 100%; height: 100%;
    border-radius: 50%;
    animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
    z-index: 0;
}
@keyframes ping {
    75%, 100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 3rem;
    margin-top: 2rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 17px;
    top: 0; bottom: 0;
    width: 2px;
    background: rgba(255,255,255,0.1);
}
.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -3rem;
    top: 5px;
    width: 14px; height: 14px;
    border-radius: 50%;
    background: #2a2d3e;
    border: 2px solid rgba(255,255,255,0.2);
    margin-left: 11px;
    z-index: 2;
    transition: all 0.3s ease;
}
.timeline-item.completed::before {
    background: #ff6b35;
    border-color: #ff6b35;
    box-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
}
.timeline-item.active::before {
    background: #ffc107;
    border-color: #ffc107;
    box-shadow: 0 0 15px rgba(255, 193, 7, 0.8);
    animation: activePulse 1.5s infinite;
}
@keyframes activePulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}
.timeline-card {
    background: rgba(42, 45, 62, 0.4);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 1.2rem;
    transition: all 0.3s ease;
}
.timeline-item.active .timeline-card {
    background: rgba(42, 45, 62, 0.8);
    border-color: rgba(255, 193, 7, 0.3);
}
.timeline-item.completed .timeline-card {
    opacity: 0.7;
}

/* Order Details */
.receipt-card {
    background: #1e2029;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px dashed rgba(255,255,255,0.1);
}
.receipt-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.receipt-item:last-child {
    border-bottom: none;
}
.qty-badge {
    background: rgba(255, 107, 53, 0.1);
    color: #ff6b35;
    width: 35px; height: 35px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    font-weight: bold;
}
</style>

<?php if (!$order): ?>
<div class="text-center py-5">
    <i class="bi bi-search display-1 text-muted mb-4"></i>
    <h2 class="mb-3">Order Not Found</h2>
    <p class="text-muted mb-4">Please check your order number and try again.</p>
    <a href="<?= url('/') ?>" class="btn px-4 py-2" style="background: transparent; border: 1px solid #ff6b35; color: #ff6b35; text-transform: uppercase;">
        <i class="bi bi-house me-2"></i>Back to Home
    </a>
</div>
<?php else: ?>

<div class="tracking-container pb-5">
    
    <!-- Status Header -->
    <div class="tracking-header mb-5 shadow-lg">
        <?php $statusInfo = $statuses[$currentStatus] ?? $statuses['pending']; ?>
        <div class="status-badge">
            <div class="status-glow" style="background: <?= $statusInfo['color'] ?>; opacity: 0.2;"></div>
            <i class="bi bi-<?= $statusInfo['icon'] ?>" style="color: <?= $statusInfo['color'] ?>; z-index: 1;"></i>
        </div>
        <h2 class="fw-bold mb-2" style="color: <?= $statusInfo['color'] ?>"><?= strtoupper($statusInfo['label']) ?></h2>
        <p class="text-muted mb-3"><?= $statusInfo['desc'] ?></p>
        
        <div class="d-flex justify-content-center gap-3 text-sm" style="color: #a0a0a0;">
            <span><i class="bi bi-receipt me-1"></i> #<?= e($order['order_number'] ?? $order['id']) ?></span>
            <span><i class="bi bi-clock me-1"></i> <?= Helpers::formatDate($order['created_at'] ?? 'now', 'H:i') ?></span>
            <?php if ($tableNumber): ?>
            <span><i class="bi bi-geo-alt me-1"></i> Table <?= e($tableNumber) ?></span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row g-4">
        
        <!-- Timeline Column -->
        <div class="col-md-6">
            <h5 class="fw-bold mb-0 ps-3" style="color: #ff6b35;">Live Tracking</h5>
            <div class="timeline">
                <?php foreach ($statusOrder as $index => $statusKey): ?>
                <?php 
                $stepStatus = $statuses[$statusKey];
                $isCompleted = $currentIndex !== false && $index < $currentIndex;
                $isActive = $statusKey === $currentStatus && $currentStatus !== 'served';
                $isFuture = $currentIndex === false || $index > $currentIndex;
                ?>
                <div class="timeline-item <?= $isCompleted ? 'completed' : '' ?> <?= $isActive ? 'active' : '' ?>">
                    <div class="timeline-card d-flex align-items-center">
                        <div class="me-3 fs-3" style="color: <?= $isFuture ? '#444' : $stepStatus['color'] ?>">
                            <i class="bi bi-<?= $stepStatus['icon'] ?>"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold <?= $isFuture ? 'text-muted' : 'text-white' ?>"><?= $stepStatus['label'] ?></h6>
                            <small class="text-muted">
                                <?php if ($isCompleted): ?> Done
                                <?php elseif ($isActive): ?> In Progress...
                                <?php else: ?> Upcoming
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Order Details Column -->
        <div class="col-md-6">
            <h5 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                <span style="color: #ff6b35;">Order Summary</span>
                <?php if (!empty($order['payment_status']) && $order['payment_status'] === 'paid'): ?>
                <span class="badge bg-success" style="font-size: 0.75rem;">PAID</span>
                <?php else: ?>
                <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">UNPAID</span>
                <?php endif; ?>
            </h5>
            
            <div class="receipt-card">
                <?php foreach ($orderItems as $item): ?>
                <div class="receipt-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="qty-badge"><?= $item['quantity'] ?></div>
                        <div>
                            <div class="fw-bold text-white"><?= e(($lang === 'en' && !empty($item['name_en'])) ? $item['name_en'] : ($item['name_fr'] ?? 'Item')) ?></div>
                            <small class="text-muted"><?= Helpers::formatPrice($item['unit_price'] ?? 0) ?> each</small>
                        </div>
                    </div>
                    <div class="fw-bold" style="color: #ff6b35;">
                        <?= Helpers::formatPrice($item['total_price'] ?? 0) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="mt-4 pt-4" style="border-top: 1px dashed rgba(255,255,255,0.2);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="text-white"><?= Helpers::formatPrice($order['subtotal'] ?? 0) ?></span>
                    </div>
                    <?php if (($order['tax_amount'] ?? 0) > 0): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">VAT (<?= $order['tax_rate'] ?? 0 ?>%)</span>
                        <span class="text-white"><?= Helpers::formatPrice($order['tax_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary">
                        <span class="fs-5 fw-bold text-white">TOTAL</span>
                        <span class="fs-4 fw-bold" style="color: #ff6b35;">
                            <?= Helpers::formatPrice($order['total_amount'] ?? 0) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-4 d-grid gap-2">
                <?php if (empty($order['payment_status']) || $order['payment_status'] !== 'paid'): ?>
                <a href="<?= url('client/payment/' . $order['id']) ?>" class="btn btn-lg w-100 fw-bold border-0" style="background: #ff6b35; color: white;">
                    <i class="bi bi-wallet2 me-2"></i> Pay Now
                </a>
                <?php else: ?>
                <a href="<?= url('client/ticket/' . $order['id']) ?>" class="btn btn-lg w-100 fw-bold border-0" style="background: var(--success); color: white;">
                    <i class="bi bi-receipt me-2"></i> View Receipt
                </a>
                <?php endif; ?>
                <a href="<?= url('client/menu' . ($tableNumber ? '?table=' . $tableNumber : '')) ?>" class="btn btn-lg w-100 fw-bold" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                    <i class="bi bi-plus-circle me-2"></i> New Order
                </a>
            </div>
            
        </div>
        
    </div>
</div>

<!-- Auto-refresh for pending/preparing orders -->
<?php if (in_array($currentStatus, ['pending', 'confirmed', 'preparing'])): ?>
<script>
    setTimeout(function() { location.reload(); }, 30000);
</script>
<?php endif; ?>

<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/client.php';
?>
