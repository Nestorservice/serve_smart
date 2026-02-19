<?php
/**
 * SIGR Admin Product Form (Add/Edit) - FoodDesk Style
 */

use Core\Helpers;

// Variables
$isEdit = isset($product) && $product;
$pageTitle = $isEdit ? 'Modifier le produit' : 'Ajouter un produit';
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
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form action="<?= url('admin/menu/' . ($isEdit ? 'update/' . $product['id'] : 'store')) ?>" 
                      method="POST" 
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row g-4">
                        <!-- Informations de base -->
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-info-circle me-1"></i>Informations de base
                            </h6>
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" name="name_fr" class="form-control form-control-lg" 
                                   value="<?= e($product['name_fr'] ?? '') ?>" 
                                   placeholder="Ex: Poulet Braisé" required>
                            <input type="hidden" name="name_en" value="<?= e($product['name_en'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Catégorie</label>
                            <select name="category_id" class="form-select form-select-lg">
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                        <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name_fr'] ?? $cat['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description_fr" class="form-control" rows="3" 
                                      placeholder="Décrivez votre produit..."><?= e($product['description_fr'] ?? '') ?></textarea>
                            <input type="hidden" name="description_en" value="<?= e($product['description_en'] ?? '') ?>">
                        </div>
                        
                        <!-- Prix et Stock -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-currency-dollar me-1"></i>Prix et Stock
                            </h6>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Prix <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="price" class="form-control" 
                                       value="<?= $product['price'] ?? '' ?>" 
                                       min="0" step="50" placeholder="0" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Quantité en stock</label>
                            <input type="number" name="stock_quantity" class="form-control form-control-lg" 
                                   value="<?= $product['stock_quantity'] ?? 100 ?>" min="0">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Seuil d'alerte stock</label>
                            <input type="number" name="stock_alert_threshold" class="form-control form-control-lg" 
                                   value="<?= $product['stock_alert_threshold'] ?? 5 ?>" min="0">
                        </div>
                        
                        <!-- Image -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-image me-1"></i>Image du produit
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Télécharger une image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                            <small class="text-muted">Formats acceptés : JPG, PNG, GIF. Max 2 Mo.</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Aperçu</label>
                            <div id="imagePreview" class="rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 150px; border: 2px dashed #dee2e6;">
                                <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= url($product['image_url']) ?>" class="img-fluid" style="max-height: 100%;">
                                <?php else: ?>
                                <span class="text-muted"><i class="bi bi-image me-1"></i>Pas d'image</span>
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
                                    <strong>Produit disponible</strong>
                                    <br><small class="text-muted">Afficher dans le menu client</small>
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
                                    <strong>Produit vedette</strong>
                                    <br><small class="text-muted">Mettre en avant sur la page d'accueil</small>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Préparation -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-clock me-1"></i>Préparation
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Temps de préparation estimé</label>
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
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le produit' ?>
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

// Inclure le layout
include VIEWS_PATH . '/layouts/admin.php';
?>
