<?php
/**
 * SIGR Admin Categories Management - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Category Management';
$currentPage = 'categories';
$categories = $categories ?? [];

ob_start();
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="bi bi-tags me-2 text-primary"></i>Categories
                </h4>
                <span class="badge bg-primary"><?= count($categories) ?> categories</span>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-folder-x display-4 d-block mb-3 text-muted"></i>
                    <p class="text-muted">No categories created</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle me-1"></i>Create Category
                    </button>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($categories as $category): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="bi bi-<?= e($category['icon'] ?? 'tag') ?> text-white fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= e($category['name_en'] ?? $category['name_fr'] ?? $category['name'] ?? '') ?></h6>
                                <small class="text-muted">
                                    <?= $category['product_count'] ?? 0 ?> product(s)
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCategory<?= $category['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="<?= url('admin/categories/' . $category['id'] . '/delete') ?>" method="POST"
                                  onsubmit="return confirm('Delete this category?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Modal Edit -->
                    <div class="modal fade" id="editCategory<?= $category['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="<?= url('admin/categories/' . $category['id'] . '/update') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title">Edit Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" 
                                                   value="<?= e($category['name_en'] ?? $category['name_fr'] ?? $category['name'] ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Icon (Bootstrap Icons)</label>
                                            <input type="text" name="icon" class="form-control" 
                                                   value="<?= e($category['icon'] ?? 'tag') ?>" 
                                                   placeholder="ex: cup-hot, egg-fried, droplet">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Display Order</label>
                                            <input type="number" name="display_order" class="form-control" 
                                                   value="<?= $category['display_order'] ?? 0 ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>New Category
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= url('admin/categories/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Drinks" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" class="form-control" placeholder="ex: cup-hot">
                        <small class="text-muted">
                            <a href="https://icons.getbootstrap.com/" target="_blank">View available icons</a>
                        </small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-1"></i>Create Category
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Suggested Icons -->
        <div class="card mt-4">
            <div class="card-header border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightbulb me-2 text-warning"></i>Suggested Icons
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php 
                    $suggestedIcons = ['cup-hot', 'egg-fried', 'droplet', 'cake', 'basket', 'heart', 'star', 'fire', 'snow', 'sun'];
                    foreach ($suggestedIcons as $icon): 
                    ?>
                    <span class="badge bg-light text-dark" style="cursor: pointer;" 
                          onclick="document.querySelector('input[name=icon]').value = '<?= $icon ?>'">
                        <i class="bi bi-<?= $icon ?> me-1"></i><?= $icon ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include layout
include VIEWS_PATH . '/layouts/admin.php';
?>
