<?php
/**
 * SIGR Admin Menu Management - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Menu Management';
$currentPage = 'menu';
$products = $products ?? [];
$categories = $categories ?? [];

ob_start();
?>

<!-- Header with actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Menu Management</h4>
        <p class="text-muted mb-0"><?= count($products) ?> total product(s)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/categories') ?>" class="btn btn-outline-primary">
            <i class="bi bi-tags me-1"></i>Categories
        </a>
        <a href="<?= url('admin/menu/add') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Add Product
        </a>
    </div>
</div>

<!-- Filters and search -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchMenu" class="form-control" placeholder="Search product...">
                </div>
            </div>
            <div class="col-md-4">
                <select id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['name_en'] ?? $cat['name_fr'] ?? $cat['name'] ?? '') ?>"><?= e($cat['name_en'] ?? $cat['name_fr'] ?? $cat['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1">Available</option>
                    <option value="0">Unavailable</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Product Grid -->
<div class="row g-4" id="menuGrid">
    <?php if (empty($products)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-egg-fried display-1 text-muted mb-3"></i>
                <h4 class="text-muted">No products in menu</h4>
                <p class="text-muted mb-4">Start by adding your first dishes</p>
                <a href="<?= url('admin/menu/add') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Add Product
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($products as $product): ?>
    <div class="col-xl-3 col-lg-4 col-md-6 product-card-item" 
         data-name="<?= strtolower(e($product['name_en'] ?? $product['name_fr'] ?? $product['name'] ?? '')) ?>"
         data-category="<?= e($product['category_name'] ?? '') ?>"
         data-available="<?= $product['is_available'] ?? 1 ?>">
        <div class="card h-100 <?= empty($product['is_available']) ? 'opacity-50' : '' ?>">
            <div class="position-relative" style="overflow: hidden;">
                <?php if (!empty($product['image_url'])): ?>
                <img src="<?= url($product['image_url']) ?>" class="card-img-top" style="height: 180px; object-fit: cover;" alt="<?= e($product['name_en'] ?? $product['name_fr'] ?? $product['name'] ?? '') ?>">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                    <i class="bi bi-image display-4 text-muted"></i>
                </div>
                <?php endif; ?>
                
                <!-- Availability Badge -->
                <span class="position-absolute top-0 end-0 m-2 badge bg-<?= !empty($product['is_available']) ? 'success' : 'danger' ?>">
                    <?= !empty($product['is_available']) ? 'Available' : 'Unavailable' ?>
                </span>
                
                <!-- Category Badge -->
                <?php if (!empty($product['category_name'])): ?>
                <span class="position-absolute top-0 start-0 m-2 badge bg-primary">
                    <?= e($product['category_name']) ?>
                </span>
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                <h5 class="card-title fw-bold mb-2"><?= e($product['name_en'] ?? $product['name_fr'] ?? $product['name'] ?? '') ?></h5>
                <p class="card-text text-muted small mb-2" style="height: 40px; overflow: hidden;">
                    <?= e(substr($product['description_en'] ?? $product['description_fr'] ?? $product['description'] ?? 'No description', 0, 60)) ?>...
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fs-5 fw-bold text-primary">
                        <?= Helpers::formatPrice($product['price'] ?? 0) ?>
                    </span>
                    <small class="text-muted">
                        <i class="bi bi-box me-1"></i>Stock: <?= $product['stock_quantity'] ?? 0 ?>
                    </small>
                </div>
            </div>
            
            <div class="card-footer bg-transparent border-0 pt-0">
                <div class="d-flex gap-2">
                    <a href="<?= url('admin/menu/edit/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <form action="<?= url('admin/menu/toggle/' . $product['id']) ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= !empty($product['is_available']) ? 'warning' : 'success' ?>" 
                                title="<?= !empty($product['is_available']) ? 'Deactivate' : 'Activate' ?>">
                            <i class="bi bi-<?= !empty($product['is_available']) ? 'eye-slash' : 'eye' ?>"></i>
                        </button>
                    </form>
                    <form action="<?= url('admin/menu/delete/' . $product['id']) ?>" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this product?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Filter logic
function filterProducts() {
    const search = document.getElementById('searchMenu').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    document.querySelectorAll('.product-card-item').forEach(item => {
        const name = item.dataset.name;
        const cat = item.dataset.category;
        const available = item.dataset.available;
        
        let show = true;
        if (search && !name.includes(search)) show = false;
        if (category && cat !== category) show = false;
        if (status !== '' && available !== status) show = false;
        
        item.style.display = show ? 'block' : 'none';
    });
}

document.getElementById('searchMenu')?.addEventListener('input', filterProducts);
document.getElementById('categoryFilter')?.addEventListener('change', filterProducts);
document.getElementById('statusFilter')?.addEventListener('change', filterProducts);
</script>

<?php
$content = ob_get_clean();

// Include layout
include VIEWS_PATH . '/layouts/admin.php';
?>
