<?php
/**
 * SIGR Admin Orders - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Order Management';
$currentPage = 'orders';
$orders = $orders ?? [];
$statusFilter = $statusFilter ?? null;

$statusConfig = [
    'pending' => ['label' => 'Pending', 'class' => 'warning', 'icon' => 'clock'],
    'confirmed' => ['label' => 'Confirmed', 'class' => 'info', 'icon' => 'check-circle'],
    'preparing' => ['label' => 'Preparing', 'class' => 'primary', 'icon' => 'fire'],
    'ready' => ['label' => 'Ready', 'class' => 'success', 'icon' => 'bell'],
    'served' => ['label' => 'Served', 'class' => 'secondary', 'icon' => 'check2-all'],
    'cancelled' => ['label' => 'Cancelled', 'class' => 'danger', 'icon' => 'x-circle'],
];

ob_start();
?>

<!-- Status Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="me-2 fw-bold">Filter by status:</span>
            <a href="<?= url('admin/orders') ?>" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="bi bi-grid me-1"></i>All
            </a>
            <?php foreach ($statusConfig as $key => $config): ?>
            <a href="<?= url('admin/orders?status=' . $key) ?>" 
               class="btn btn-sm <?= $statusFilter === $key ? 'btn-' . $config['class'] : 'btn-outline-' . $config['class'] ?>">
                <i class="bi bi-<?= $config['icon'] ?> me-1"></i><?= $config['label'] ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <i class="bi bi-receipt me-2 text-primary"></i>
            <?= $statusFilter ? $statusConfig[$statusFilter]['label'] . ' Orders' : 'All Orders' ?>
        </h4>
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="searchOrders" class="form-control border-start-0" placeholder="Search...">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order No.</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox display-4 d-block mb-3 text-muted"></i>
                            <p class="text-muted">No orders found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <?php $config = $statusConfig[$order['status'] ?? 'pending'] ?? $statusConfig['pending']; ?>
                    <tr>
                        <td>
                            <strong class="text-primary">#<?= e($order['order_number'] ?? $order['id']) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-pin-map me-1"></i>Table <?= e($order['table_number'] ?? '-') ?>
                            </span>
                        </td>
                        <td><?= $order['items_count'] ?? '-' ?> item(s)</td>
                        <td class="fw-bold"><?= Helpers::formatPrice($order['total_amount'] ?? 0) ?></td>
                        <td>
                            <span class="badge bg-<?= $config['class'] ?>">
                                <i class="bi bi-<?= $config['icon'] ?> me-1"></i><?= $config['label'] ?>
                            </span>
                        </td>
                        <td><?= Helpers::formatDate($order['created_at'] ?? 'now', 'm/d H:i') ?></td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="<?= url('admin/orders/' . $order['id']) ?>" class="dropdown-item">
                                            <i class="bi bi-eye me-2 text-primary"></i>View Details
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php if ($order['status'] === 'pending'): ?>
                                    <li>
                                        <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-check-circle me-2 text-info"></i>Confirm
                                            </button>
                                        </form>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                    <li>
                                        <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="preparing">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-fire me-2 text-primary"></i>Mark Preparing
                                            </button>
                                        </form>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'preparing'): ?>
                                    <li>
                                        <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="ready">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-bell me-2 text-success"></i>Mark Ready
                                            </button>
                                        </form>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'ready'): ?>
                                    <li>
                                        <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="served">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-check2-all me-2 text-secondary"></i>Mark Served
                                            </button>
                                        </form>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (!in_array($order['status'], ['served', 'cancelled'])): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-x-circle me-2"></i>Cancel
                                            </button>
                                        </form>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search
document.getElementById('searchOrders')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('#ordersTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});

// Auto-refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>

<?php
$content = ob_get_clean();

// Include layout
include VIEWS_PATH . '/layouts/admin.php';
?>
