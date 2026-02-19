<?php
/**
 * SIGR Admin Statistics - FoodDesk Style with Charts
 */

use Core\Helpers;

// Variables
$pageTitle = 'Statistiques';
$currentPage = 'stats';
$stats = $stats ?? [
    'today_sales' => 0,
    'week_sales' => 0,
    'month_sales' => 0,
    'total_orders' => 0,
    'avg_order_value' => 0,
    'top_products' => [],
    'sales_by_day' => [],
    'orders_by_status' => [],
    'hourly_orders' => [],
];

ob_start();
?>

<!-- Cards de statistiques -->
<div class="row mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-1 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">Ventes du jour</span>
                    <h2 class="text-white font-w600"><?= Helpers::formatPrice($stats['today_sales'] ?? 0) ?></h2>
                </div>
                <i class="bi bi-calendar-day text-white opacity-50" style="font-size: 3rem;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-2 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">Ventes de la semaine</span>
                    <h2 class="text-white font-w600"><?= Helpers::formatPrice($stats['week_sales'] ?? 0) ?></h2>
                </div>
                <i class="bi bi-calendar-week text-white opacity-50" style="font-size: 3rem;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-3 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">Ventes du mois</span>
                    <h2 class="text-white font-w600"><?= Helpers::formatPrice($stats['month_sales'] ?? 0) ?></h2>
                </div>
                <i class="bi bi-calendar-month text-white opacity-50" style="font-size: 3rem;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card gradient-4 card-bx">
            <div class="card-body d-flex align-items-center">
                <div class="me-auto text-white">
                    <span class="fs-16 font-w600">Panier moyen</span>
                    <h2 class="text-white font-w600"><?= Helpers::formatPrice($stats['avg_order_value'] ?? 0) ?></h2>
                </div>
                <i class="bi bi-cart-check text-white opacity-50" style="font-size: 3rem;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Graphique des ventes -->
    <div class="col-xl-8 col-lg-12">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2 text-primary"></i>Évolution des ventes
                </h4>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Distribution des commandes -->
    <div class="col-xl-4 col-lg-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Statut des commandes
                </h4>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Heures de pointe -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-clock me-2 text-primary"></i>Heures de pointe
                </h4>
            </div>
            <div class="card-body">
                <canvas id="hourlyChart" height="150"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Produits -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-trophy me-2 text-primary"></i>Top 10 Produits
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($stats['top_products'])): ?>
                <p class="text-muted text-center py-4">Aucune donnée disponible</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th class="text-center">Vendus</th>
                                <th class="text-end">Revenus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($stats['top_products'], 0, 10) as $index => $product): ?>
                            <tr>
                                <td>
                                    <?php if ($index < 3): ?>
                                    <span class="badge bg-<?= ['warning', 'secondary', 'danger'][$index] ?>">
                                        <?= $index + 1 ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted"><?= $index + 1 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= e($product['name_fr'] ?? $product['name'] ?? 'Produit') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= $product['quantity'] ?? 0 ?></span>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    <?= Helpers::formatPrice($product['revenue'] ?? 0) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Résumé rapide -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-0">
                    <i class="bi bi-clipboard-data me-2 text-primary"></i>Résumé Rapide
                </h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded" style="background: rgba(102, 126, 234, 0.1);">
                            <h3 class="text-primary mb-1"><?= $stats['total_orders'] ?? 0 ?></h3>
                            <small class="text-muted">Total Commandes</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded" style="background: rgba(40, 167, 69, 0.1);">
                            <h3 class="text-success mb-1"><?= $stats['completed_orders'] ?? 0 ?></h3>
                            <small class="text-muted">Commandes Servies</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded" style="background: rgba(255, 193, 7, 0.1);">
                            <h3 class="text-warning mb-1"><?= $stats['pending_orders'] ?? 0 ?></h3>
                            <small class="text-muted">En Attente</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded" style="background: rgba(220, 53, 69, 0.1);">
                            <h3 class="text-danger mb-1"><?= $stats['cancelled_orders'] ?? 0 ?></h3>
                            <small class="text-muted">Annulées</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Data pour les charts - préparer AVANT le heredoc
$salesData = $stats['sales_by_day'] ?? [];
$statusData = $stats['orders_by_status'] ?? ['pending' => 0, 'preparing' => 0, 'ready' => 0, 'served' => 0];
$hourlyData = $stats['hourly_orders'] ?? array_fill(0, 24, 0);

$salesLabels = json_encode(array_keys($salesData));
$salesValues = json_encode(array_values($salesData));
$hourlyJson = json_encode(array_values($hourlyData));

$scripts = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Couleurs
const primaryColor = 'rgba(102, 126, 234, 0.8)';
const gradientColors = ['#667eea', '#764ba2'];

// Graphique des ventes
const salesCtx = document.getElementById('salesChart');
if (salesCtx) {
    const salesLabels = {$salesLabels};
    const salesValues = {$salesValues};
    
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesLabels.length ? salesLabels : ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Ventes (FCFA)',
                data: salesValues.length ? salesValues : [0, 0, 0, 0, 0, 0, 0],
                borderColor: primaryColor,
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// Graphique des statuts
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'En préparation', 'Prêtes', 'Servies'],
            datasets: [{
                data: [{$statusData['pending']}, {$statusData['preparing']}, {$statusData['ready']}, {$statusData['served']}],
                backgroundColor: ['#ffc107', '#667eea', '#28a745', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '65%'
        }
    });
}

// Graphique horaire
const hourlyCtx = document.getElementById('hourlyChart');
if (hourlyCtx) {
    const hourlyData = {$hourlyJson};
    
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: [...Array(24).keys()].map(h => h + 'h'),
            datasets: [{
                label: 'Commandes',
                data: hourlyData,
                backgroundColor: primaryColor,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
</script>
HTML;

// Inclure le layout
include VIEWS_PATH . '/layouts/admin.php';
?>
