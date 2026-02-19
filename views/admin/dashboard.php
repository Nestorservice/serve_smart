<?php
/**
 * SIGR Admin Dashboard - FoodDesk Style with Charts
 */

use Core\Helpers;

// Variables depuis le contrôleur
$todaySales = $todaySales ?? 0;
$totalOrders = $totalOrders ?? 0;
$pendingOrders = $pendingOrders ?? 0;
$lowStockCount = $lowStockCount ?? 0;
$recentOrders = $recentOrders ?? [];
$popularProducts = $popularProducts ?? [];
$weeklySales = $weeklySales ?? [];
$orderStats = $orderStats ?? ['pending' => 0, 'preparing' => 0, 'ready' => 0, 'served' => 0];

// Configuration du layout
$pageTitle = 'Tableau de bord';
$currentPage = 'dashboard';
ob_start();
?>

<div class="row">
    <!-- Statistiques Cards avec icônes stylisées -->
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-1 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">Ventes du jour</span>
                    <h2 class="text-white font-w600"><?= Helpers::formatPrice($todaySales) ?></h2>
                </div>
                <div class="text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 16 16" opacity="0.5">
                        <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.59v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-2 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">Commandes totales</span>
                    <h2 class="text-white font-w600"><?= $totalOrders ?></h2>
                </div>
                <div class="text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 16 16" opacity="0.5">
                        <path d="M3 14.5a.5.5 0 0 1-.5-.5V2a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5H3zm7.5-10.5v1h2V4h-2zm0 2v1h2V6h-2zm0 2v1h2V8h-2zm-6-4v5h5V4h-5zm0 6v1h5v-1h-5zm0 2v1h5v-1h-5zm0 2v1h2v-1h-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-3 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">En attente</span>
                    <h2 class="text-white font-w600"><?= $pendingOrders ?></h2>
                </div>
                <div class="text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 16 16" opacity="0.5">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-4 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">Stock bas</span>
                    <h2 class="text-white font-w600"><?= $lowStockCount ?></h2>
                </div>
                <div class="text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 16 16" opacity="0.5">
                        <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                        <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Graphique des ventes hebdomadaires -->
    <div class="col-xl-8 col-lg-12">
        <div class="card">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>Ventes de la semaine
                </h4>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Cette semaine
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Graphique circulaire des statuts de commandes -->
    <div class="col-xl-4 col-lg-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Statuts des commandes
                </h4>
            </div>
            <div class="card-body">
                <canvas id="orderStatusChart" height="200"></canvas>
                <div class="row mt-4">
                    <div class="col-6">
                        <span class="badge bg-warning me-2">&nbsp;</span> En attente: <?= $orderStats['pending'] ?>
                    </div>
                    <div class="col-6">
                        <span class="badge bg-primary me-2">&nbsp;</span> Préparation: <?= $orderStats['preparing'] ?>
                    </div>
                    <div class="col-6 mt-2">
                        <span class="badge bg-success me-2">&nbsp;</span> Prêtes: <?= $orderStats['ready'] ?>
                    </div>
                    <div class="col-6 mt-2">
                        <span class="badge bg-secondary me-2">&nbsp;</span> Servies: <?= $orderStats['served'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Commandes récentes -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="bi bi-receipt me-2 text-primary"></i>Commandes récentes
                </h4>
                <a href="<?= url('admin/orders') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-eye me-1"></i>Voir tout
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-responsive-md table-hover">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Table</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    Aucune commande récente
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong class="text-primary">#<?= e($order['order_number'] ?? $order['id']) ?></strong></td>
                                <td><span class="badge bg-light text-dark">Table <?= e($order['table_number'] ?? '-') ?></span></td>
                                <td class="font-w600"><?= Helpers::formatPrice($order['total_amount'] ?? 0) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($order['status'] ?? 'pending') {
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'preparing' => 'primary',
                                        'ready' => 'success',
                                        'served' => 'secondary',
                                        'cancelled' => 'danger',
                                        default => 'dark'
                                    };
                                    $statusLabel = match($order['status'] ?? 'pending') {
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'preparing' => 'En préparation',
                                        'ready' => 'Prête',
                                        'served' => 'Servie',
                                        'cancelled' => 'Annulée',
                                        default => $order['status']
                                    };
                                    ?>
                                    <span class="badge badge-<?= $statusClass ?> badge-lg"><?= $statusLabel ?></span>
                                </td>
                                <td><?= Helpers::formatDate($order['created_at'] ?? 'now', 'd/m H:i') ?></td>
                                <td>
                                    <a href="<?= url('admin/orders/' . $order['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions rapides & Produits populaires -->
    <div class="col-xl-4">
        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-lightning me-2 text-primary"></i>Actions rapides
                </h4>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('admin/menu/add') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                    </a>
                    <a href="<?= url('admin/tables') ?>" class="btn btn-success">
                        <i class="bi bi-qr-code me-2"></i>Gérer les tables
                    </a>
                    <a href="<?= url('admin/stock') ?>" class="btn btn-warning text-white">
                        <i class="bi bi-box-seam me-2"></i>Voir les stocks
                    </a>
                    <a href="<?= url('kitchen') ?>" target="_blank" class="btn btn-info text-white">
                        <i class="bi bi-display me-2"></i>Écran Cuisine
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Produits populaires -->
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-star me-2 text-primary"></i>Top Produits
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($popularProducts)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-basket display-4 d-block mb-2"></i>
                    Aucune donnée
                </p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($popularProducts, 0, 5) as $index => $product): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary rounded-circle me-3"><?= $index + 1 ?></span>
                            <span><?= e($product['name_fr'] ?? $product['name'] ?? 'Produit') ?></span>
                        </div>
                        <span class="badge bg-success rounded-pill"><?= $product['order_count'] ?? 0 ?> vendus</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Préparer les données JSON pour les charts
$weeklySalesJson = json_encode(array_values($weeklySales ?: [0, 0, 0, 0, 0, 0, 0]));
$orderStatsJson = json_encode([
    ($orderStats['pending'] ?? 0),
    ($orderStats['preparing'] ?? 0),
    ($orderStats['ready'] ?? 0),
    ($orderStats['served'] ?? 0)
]);

// Scripts pour les charts
$scripts = <<<SCRIPTS
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Données pour les graphiques
const weeklySales = {$weeklySalesJson};
const orderStats = {$orderStatsJson};

// Graphique des ventes
const salesCtx = document.getElementById('salesChart');
if (salesCtx) {
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Ventes (FCFA)',
                data: weeklySales,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                borderRadius: 10,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

// Graphique des statuts
const statusCtx = document.getElementById('orderStatusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'En préparation', 'Prêtes', 'Servies'],
            datasets: [{
                data: orderStats,
                backgroundColor: [
                    '#ffc107',
                    '#667eea',
                    '#28a745',
                    '#6c757d'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });
}
</script>
SCRIPTS;

// Inclure le layout
include VIEWS_PATH . '/layouts/admin.php';
?>

