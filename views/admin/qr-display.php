<?php
/**
 * SIGR Admin QR Display - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'QR Code - Table ' . ($table['table_number'] ?? '');
$currentPage = 'tables';
$table = $table ?? [];
$qrUrl = $qrUrl ?? '';
$qrImage = $qrImage ?? '';

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0 text-center">
                <h4 class="card-title mb-0">
                    <i class="bi bi-qr-code me-2 text-primary"></i>
                    QR Code - Table <?= e($table['table_number'] ?? '') ?>
                </h4>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <?php if ($qrImage): ?>
                    <img src="<?= e($qrImage) ?>" alt="QR Code Table <?= e($table['table_number'] ?? '') ?>" 
                         class="img-fluid rounded shadow" style="max-width: 300px;">
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <p class="text-muted mb-1">Menu URL:</p>
                    <code class="d-block p-2 bg-light rounded"><?= e($qrUrl) ?></code>
                </div>
                
                <div class="row text-start mb-4">
                    <div class="col-6">
                        <p class="text-muted mb-1">Table Number</p>
                        <h5><?= e($table['table_number'] ?? '-') ?></h5>
                    </div>
                    <div class="col-6">
                        <p class="text-muted mb-1">Capacity</p>
                        <h5><?= $table['capacity'] ?? '-' ?> seats</h5>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer me-2"></i>Print QR Code
                    </button>
                    <a href="<?= url('admin/tables') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to tables
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/admin.php';
?>
