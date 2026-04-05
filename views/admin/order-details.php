<?php
/**
 * SIGR Admin Order Detail - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Order Details';
$currentPage = 'orders';
$order = $order ?? [];
$items = $items ?? [];

$statusConfig = [
    'pending' => ['label' => 'Pending', 'class' => 'warning', 'icon' => 'clock'],
    'confirmed' => ['label' => 'Confirmed', 'class' => 'info', 'icon' => 'check-circle'],
    'preparing' => ['label' => 'Preparing', 'class' => 'primary', 'icon' => 'fire'],
    'ready' => ['label' => 'Ready', 'class' => 'success', 'icon' => 'bell'],
    'served' => ['label' => 'Served', 'class' => 'secondary', 'icon' => 'check2-all'],
    'cancelled' => ['label' => 'Cancelled', 'class' => 'danger', 'icon' => 'x-circle'],
];

$currentStatus = $order['status'] ?? 'pending';
$config = $statusConfig[$currentStatus] ?? $statusConfig['pending'];

ob_start();
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Details -->
        <div class="card mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">
                        <i class="bi bi-receipt me-2 text-primary"></i>
                        Order #<?= e($order['order_number'] ?? $order['id']) ?>
                    </h4>
                </div>
                <span class="badge bg-<?= $config['class'] ?> badge-lg">
                    <i class="bi bi-<?= $config['icon'] ?> me-1"></i><?= $config['label'] ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Table</p>
                        <h5><i class="bi bi-pin-map me-1"></i>Table <?= e($order['table_number'] ?? '-') ?></h5>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Order Date</p>
                        <h5><?= Helpers::formatDate($order['created_at'] ?? 'now', 'm/d/Y H:i') ?></h5>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Total Amount</p>
                        <h5 class="text-success"><?= Helpers::formatPrice($order['total_amount'] ?? 0) ?></h5>
                    </div>
                </div>
                
                <!-- Items -->
                <h6 class="mb-3"><i class="bi bi-basket me-1"></i>Ordered Items</h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e(!empty($item['name_en']) ? $item['name_en'] : ($item['name_fr'] ?? $item['product_name'] ?? $item['name'] ?? 'Product')) ?></strong>
                                    <?php if (!empty($item['special_instructions'] ?? $item['notes'] ?? '')): ?>
                                    <br><small class="text-muted"><?= e($item['special_instructions'] ?? $item['notes']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= $item['quantity'] ?? 1 ?></span>
                                </td>
                                <td class="text-end"><?= Helpers::formatPrice($item['unit_price'] ?? 0) ?></td>
                                <td class="text-end fw-bold"><?= Helpers::formatPrice(($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end text-success fs-5"><?= Helpers::formatPrice($order['total_amount'] ?? 0) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-chat-dots me-2"></i>
                    <strong>Notes:</strong> <?= e($order['notes']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2 text-primary"></i>Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($currentStatus === 'pending'): ?>
                    <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-check-circle me-1"></i>Confirm
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if (in_array($currentStatus, ['pending', 'confirmed'])): ?>
                    <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-fire me-1"></i>Mark Preparing
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if ($currentStatus === 'preparing'): ?>
                    <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-bell me-1"></i>Mark Ready
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if ($currentStatus === 'ready'): ?>
                    <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="served">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-check2-all me-1"></i>Mark Served
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if (!in_array($currentStatus, ['served', 'cancelled'])): ?>
                    <hr>
                    <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST"
                          onsubmit="return confirm('Cancel this order?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i>Cancel Order
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Print -->
        <div class="card">
            <div class="card-body">
                <button onclick="window.print()" class="btn btn-outline-primary w-100">
                    <i class="bi bi-printer me-1"></i>Print Receipt
                </button>
            </div>
        </div>
        
        <a href="<?= url('admin/orders') ?>" class="btn btn-link w-100 mt-3">
            <i class="bi bi-arrow-left me-1"></i>Back to Orders List
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include layout
include VIEWS_PATH . '/layouts/admin.php';
?>
