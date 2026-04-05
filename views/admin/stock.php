<?php
/**
 * SIGR Admin Stock Management - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Stock Management';
$currentPage = 'stock';
$products = $products ?? [];
$lowStockCount = 0;
$totalProducts = count($products);

foreach ($products as $product) {
    if (($product['stock_quantity'] ?? 0) <= ($product['stock_alert_threshold'] ?? 5)) {
        $lowStockCount++;
    }
}

ob_start();
?>

<!-- Header with stats -->
<div class="row mb-4">
    <div class="col-xl-4 col-sm-6">
        <div class="card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="bg-primary text-white p-3 rounded" style="font-size: 1.5rem;">
                            <i class="bi bi-box-seam"></i>
                        </span>
                    </div>
                    <div>
                        <p class="mb-1 text-muted">Total Products</p>
                        <h3 class="mb-0 text-primary"><?= $totalProducts ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-sm-6">
        <div class="card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="bg-danger text-white p-3 rounded" style="font-size: 1.5rem;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </span>
                    </div>
                    <div>
                        <p class="mb-1 text-muted">Low Stock</p>
                        <h3 class="mb-0 text-danger"><?= $lowStockCount ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-sm-12">
        <div class="card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">Actions</p>
                        <a href="<?= url('admin/menu/add') ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Product
                        </a>
                    </div>
                    <span class="bg-success text-white p-3 rounded" style="font-size: 1.5rem;">
                        <i class="bi bi-plus-lg"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <i class="bi bi-clipboard-data me-2 text-primary"></i>Stock Status
        </h4>
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="searchStock" class="form-control border-start-0" placeholder="Search...">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="stockTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Alert Threshold</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox display-4 d-block mb-3 text-muted"></i>
                            <p class="text-muted">No products in inventory</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <?php
                    $stock = $product['stock_quantity'] ?? 0;
                    $threshold = $product['stock_alert_threshold'] ?? 5;
                    $isLow = $stock <= $threshold;
                    $isOut = $stock <= 0;
                    ?>
                    <tr class="<?= $isLow ? 'table-warning' : '' ?> <?= $isOut ? 'table-danger' : '' ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= url($product['image_url']) ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?= e(!empty($product['name_en']) ? $product['name_en'] : ($product['name_fr'] ?? $product['name'] ?? 'Product')) ?></strong>
                                    <br><small class="text-muted"><?= Helpers::formatPrice($product['price'] ?? 0) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark"><?= e($product['category_name'] ?? 'Uncategorized') ?></span></td>
                        <td class="text-center">
                            <span class="fw-bold fs-5 <?= $isOut ? 'text-danger' : ($isLow ? 'text-warning' : 'text-success') ?>">
                                <?= $stock ?>
                            </span>
                        </td>
                        <td class="text-center"><?= $threshold ?></td>
                        <td class="text-center">
                            <?php if ($isOut): ?>
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Out of stock</span>
                            <?php elseif ($isLow): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Low stock</span>
                            <?php else: ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>OK</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#stockModal" 
                                    data-product-id="<?= $product['id'] ?>"
                                    data-product-name="<?= e(!empty($product['name_en']) ? $product['name_en'] : ($product['name_fr'] ?? $product['name'] ?? 'Product')) ?>"
                                    data-stock="<?= $stock ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= url('admin/stock/update') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" id="modalProductId">
                
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2 text-primary"></i>Edit Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Product: <strong id="modalProductName"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">New Stock</label>
                        <input type="number" name="stock_quantity" id="modalStock" class="form-control form-control-lg text-center" min="0" required>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" onclick="adjustStock(-10)">-10</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="adjustStock(-1)">-1</button>
                        <button type="button" class="btn btn-outline-primary" onclick="adjustStock(1)">+1</button>
                        <button type="button" class="btn btn-outline-primary" onclick="adjustStock(10)">+10</button>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Search
document.getElementById('searchStock')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('#stockTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});

// Modal
document.getElementById('stockModal')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modalProductId').value = btn.dataset.productId;
    document.getElementById('modalProductName').textContent = btn.dataset.productName;
    document.getElementById('modalStock').value = btn.dataset.stock;
});

function adjustStock(amount) {
    const input = document.getElementById('modalStock');
    const newValue = Math.max(0, parseInt(input.value || 0) + amount);
    input.value = newValue;
}
</script>

<?php
$content = ob_get_clean();

// Include layout
include VIEWS_PATH . '/layouts/admin.php';
?>
