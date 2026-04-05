<?php
/**
 * SIGR Admin Product Form (Add/Edit) - FoodDesk Style
 */

use Core\Helpers;

// Variables
$isEdit = isset($product) && $product;
$pageTitle = $isEdit ? 'Edit Product' : 'Add Product';
$currentPage = 'menu';
$product = $product ?? [];
$categories = $categories ?? [];

ob_start();
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2 text-primary"></i>
                        <?= $pageTitle ?>
                    </h4>
                    <a href="<?= url('admin/menu') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form action="<?= url('admin/menu/' . ($isEdit ? 'update/' . $product['id'] : 'store')) ?>" 
                      method="POST" 
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row g-4">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-info-circle me-1"></i>Basic Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Product Name (EN) <span class="text-danger">*</span></label>
                            <input type="text" name="name_en" class="form-control form-control-lg" 
                                   value="<?= e($product['name_en'] ?? '') ?>" 
                                   placeholder="Ex: Braised Chicken" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nom du produit (FR)</label>
                            <input type="text" name="name_fr" class="form-control form-control-lg" 
                                   value="<?= e($product['name_fr'] ?? '') ?>" 
                                   placeholder="Ex: Poulet Braisé">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select form-select-lg">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                        <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name_en'] ?? $cat['name_fr'] ?? $cat['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Description (EN)</label>
                            <textarea name="description_en" class="form-control" rows="3" 
                                      placeholder="Describe your product in English..."><?= e($product['description_en'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Description (FR)</label>
                            <textarea name="description_fr" class="form-control" rows="3" 
                                      placeholder="Décrivez votre produit en français..."><?= e($product['description_fr'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Price & Stock -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-currency-dollar me-1"></i>Price & Stock
                            </h6>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="price" class="form-control" 
                                       value="<?= $product['price'] ?? '' ?>" 
                                       min="0" step="50" placeholder="0" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control form-control-lg" 
                                   value="<?= $product['stock_quantity'] ?? 100 ?>" min="0">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Stock Alert Threshold</label>
                            <input type="number" name="stock_alert_threshold" class="form-control form-control-lg" 
                                   value="<?= $product['stock_alert_threshold'] ?? 5 ?>" min="0">
                        </div>
                        
                        <!-- Image -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-image me-1"></i>Product Image
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Upload Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max 2 MB.</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Preview</label>
                            <div id="imagePreview" class="rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 150px; border: 2px dashed #dee2e6;">
                                <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= url($product['image_url']) ?>" class="img-fluid" style="max-height: 100%;">
                                <?php else: ?>
                                <span class="text-muted"><i class="bi bi-image me-1"></i>No image</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Options -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-toggles me-1"></i>Options
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_available" value="0">
                                <input type="checkbox" name="is_available" value="1" 
                                       class="form-check-input" id="isAvailable"
                                       <?= ($product['is_available'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isAvailable">
                                    <strong>Product Available</strong>
                                    <br><small class="text-muted">Show in client menu</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" 
                                       class="form-check-input" id="isFeatured"
                                       <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isFeatured">
                                    <strong>Featured Product</strong>
                                    <br><small class="text-muted">Highlight on home page</small>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Preparation -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-clock me-1"></i>Preparation
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Estimated Preparation Time</label>
                            <div class="input-group">
                                <input type="number" name="preparation_time" class="form-control" 
                                       value="<?= $product['preparation_time'] ?? 15 ?>" min="0">
                                <span class="input-group-text">minutes</span>
                            </div>
                        </div>
                        
                        <!-- Submit -->
                        <div class="col-12 mt-5">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="<?= url('admin/menu') ?>" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= $isEdit ? 'Save Changes' : 'Create Product' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Preview image
document.getElementById('imageInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = 
                '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 100%;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php
$content = ob_get_clean();

// Include layout
include VIEWS_PATH . '/layouts/admin.php';
?>
