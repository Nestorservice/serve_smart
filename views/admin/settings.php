<?php
/**
 * SIGR Admin Settings - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Paramètres';
$currentPage = 'settings';
$settings = $settings ?? [];

// Groupes de paramètres par défaut
$settingGroups = [
    'general' => [
        'title' => 'Général',
        'icon' => 'gear',
        'settings' => [
            'restaurant_name' => ['label' => 'Nom du restaurant', 'type' => 'text', 'default' => 'SIGR Restaurant'],
            'restaurant_email' => ['label' => 'Email', 'type' => 'email', 'default' => ''],
            'restaurant_phone' => ['label' => 'Téléphone', 'type' => 'tel', 'default' => ''],
            'restaurant_address' => ['label' => 'Adresse', 'type' => 'textarea', 'default' => ''],
        ]
    ],
    'orders' => [
        'title' => 'Commandes',
        'icon' => 'receipt',
        'settings' => [
            'order_prefix' => ['label' => 'Préfixe commandes', 'type' => 'text', 'default' => 'CMD'],
            'auto_confirm_orders' => ['label' => 'Confirmer auto. les commandes', 'type' => 'checkbox', 'default' => false],
            'kitchen_display_enabled' => ['label' => 'Écran cuisine activé', 'type' => 'checkbox', 'default' => true],
        ]
    ],
    'display' => [
        'title' => 'Affichage',
        'icon' => 'palette',
        'settings' => [
            'currency' => ['label' => 'Devise', 'type' => 'text', 'default' => 'FCFA'],
            'language' => ['label' => 'Langue par défaut', 'type' => 'select', 'default' => 'fr', 'options' => ['fr' => 'Français', 'en' => 'English']],
            'theme_color' => ['label' => 'Couleur principale', 'type' => 'color', 'default' => '#667eea'],
        ]
    ],
    'stock' => [
        'title' => 'Stock',
        'icon' => 'box-seam',
        'settings' => [
            'low_stock_threshold' => ['label' => 'Seuil stock bas (par défaut)', 'type' => 'number', 'default' => 5],
            'stock_alerts_enabled' => ['label' => 'Alertes stock activées', 'type' => 'checkbox', 'default' => true],
        ]
    ],
];

ob_start();
?>

<form action="<?= url('admin/settings/save') ?>" method="POST">
    <?= csrf_field() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Paramètres du restaurant</h4>
            <p class="text-muted mb-0">Configurez votre application SIGR</p>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i>Enregistrer
        </button>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <!-- Navigation des groupes -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <?php $first = true; ?>
                        <?php foreach ($settingGroups as $groupId => $group): ?>
                        <button class="nav-link text-start <?= $first ? 'active' : '' ?>" 
                                data-bs-toggle="pill" 
                                data-bs-target="#settings-<?= $groupId ?>" 
                                type="button">
                            <i class="bi bi-<?= $group['icon'] ?> me-2"></i><?= $group['title'] ?>
                        </button>
                        <?php $first = false; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="tab-content">
                <?php $first = true; ?>
                <?php foreach ($settingGroups as $groupId => $group): ?>
                <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="settings-<?= $groupId ?>">
                    <div class="card">
                        <div class="card-header border-0">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-<?= $group['icon'] ?> me-2 text-primary"></i>
                                <?= $group['title'] ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($group['settings'] as $key => $setting): ?>
                            <?php $value = $settings[$key] ?? $setting['default']; ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold"><?= $setting['label'] ?></label>
                                
                                <?php if ($setting['type'] === 'text' || $setting['type'] === 'email' || $setting['type'] === 'tel' || $setting['type'] === 'number'): ?>
                                <input type="<?= $setting['type'] ?>" 
                                       name="<?= $key ?>" 
                                       value="<?= e($value) ?>" 
                                       class="form-control">
                                       
                                <?php elseif ($setting['type'] === 'textarea'): ?>
                                <textarea name="<?= $key ?>" class="form-control" rows="3"><?= e($value) ?></textarea>
                                
                                <?php elseif ($setting['type'] === 'checkbox'): ?>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="<?= $key ?>" value="0">
                                    <input type="checkbox" 
                                           name="<?= $key ?>" 
                                           value="1" 
                                           class="form-check-input" 
                                           <?= $value ? 'checked' : '' ?>>
                                    <label class="form-check-label">Activé</label>
                                </div>
                                
                                <?php elseif ($setting['type'] === 'select'): ?>
                                <select name="<?= $key ?>" class="form-select">
                                    <?php foreach ($setting['options'] ?? [] as $optValue => $optLabel): ?>
                                    <option value="<?= $optValue ?>" <?= $value == $optValue ? 'selected' : '' ?>>
                                        <?= e($optLabel) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <?php elseif ($setting['type'] === 'color'): ?>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" 
                                           name="<?= $key ?>" 
                                           value="<?= e($value) ?>" 
                                           class="form-control form-control-color">
                                    <input type="text" 
                                           value="<?= e($value) ?>" 
                                           class="form-control" 
                                           style="max-width: 120px;" 
                                           readonly>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php $first = false; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Bouton de sauvegarde en bas -->
    <div class="d-flex justify-content-end mt-4 pt-4 border-top">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
        </button>
    </div>
</form>

<!-- Section Informations système -->
<div class="card mt-4">
    <div class="card-header border-0">
        <h5 class="card-title mb-0">
            <i class="bi bi-info-circle me-2 text-primary"></i>Informations système
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-1 text-muted">Version SIGR</p>
                <strong>1.0.0</strong>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted">Version PHP</p>
                <strong><?= phpversion() ?></strong>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted">Dernière mise à jour</p>
                <strong><?= date('d/m/Y H:i') ?></strong>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Inclure le layout
include VIEWS_PATH . '/layouts/admin.php';
?>
