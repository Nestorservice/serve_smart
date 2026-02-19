<?php
/**
 * SIGR Kitchen Display - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Cuisine';
$orders = $orders ?? [];

$statusConfig = [
    'pending' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
    'confirmed' => ['label' => 'Confirmée', 'class' => 'info', 'icon' => 'check-circle'],
    'preparing' => ['label' => 'En préparation', 'class' => 'primary', 'icon' => 'fire'],
    'ready' => ['label' => 'Prête', 'class' => 'success', 'icon' => 'bell'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= e($pageTitle) ?> - SIGR</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #1a1a2e;
            min-height: 100vh;
        }
        
        .kitchen-header {
            background: var(--gradient);
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .kitchen-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .clock {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .order-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }
        
        .order-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            animation: slideIn 0.5s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .order-card.urgent {
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 10px 40px rgba(255, 193, 7, 0.5); }
            50% { box-shadow: 0 10px 60px rgba(255, 193, 7, 0.8); }
        }
        
        .order-header {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-header.pending { background: #ffc107; color: #000; }
        .order-header.preparing { background: var(--gradient); color: white; }
        .order-header.ready { background: #28a745; color: white; }
        
        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .order-table {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .order-body {
            padding: 1.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-quantity {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            margin-right: 1rem;
        }
        
        .item-name {
            font-weight: 600;
            flex-grow: 1;
        }
        
        .order-notes {
            background: #fff3cd;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .order-footer {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-kitchen {
            flex: 1;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-kitchen:hover {
            transform: scale(1.02);
        }
        
        .btn-prepare {
            background: var(--gradient);
            color: white;
        }
        
        .btn-ready {
            background: #28a745;
            color: white;
        }
        
        .timer {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .no-orders {
            text-align: center;
            padding: 4rem;
            color: rgba(255,255,255,0.5);
        }
        
        .no-orders i {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        /* Status filter buttons */
        .filter-bar {
            display: flex;
            gap: 0.5rem;
            padding: 0.5rem;
            background: rgba(255,255,255,0.1);
            margin: 1rem;
            border-radius: 15px;
            justify-content: center;
        }
        
        .filter-btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: transparent;
            color: white;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: white;
            color: var(--primary);
        }
        
        .filter-btn .count {
            background: rgba(255,255,255,0.3);
            padding: 0.1rem 0.5rem;
            border-radius: 20px;
            margin-left: 0.5rem;
            font-size: 0.8rem;
        }
        
        .filter-btn.active .count {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="kitchen-header">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-fire fs-2"></i>
            <h1>Écran Cuisine</h1>
        </div>
        <div class="clock" id="clock">--:--:--</div>
        <div class="d-flex gap-2">
            <a href="<?= url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                <i class="bi bi-speedometer2 me-1"></i>Admin
            </a>
            <button onclick="toggleFullscreen()" class="btn btn-light btn-sm">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
        </div>
    </header>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <button class="filter-btn active" data-status="all">
            Toutes <span class="count" id="count-all">0</span>
        </button>
        <button class="filter-btn" data-status="pending">
            <i class="bi bi-clock me-1"></i>En attente <span class="count" id="count-pending">0</span>
        </button>
        <button class="filter-btn" data-status="preparing">
            <i class="bi bi-fire me-1"></i>En préparation <span class="count" id="count-preparing">0</span>
        </button>
        <button class="filter-btn" data-status="ready">
            <i class="bi bi-bell me-1"></i>Prêtes <span class="count" id="count-ready">0</span>
        </button>
    </div>
    
    <!-- Orders Grid -->
    <div class="order-grid" id="ordersGrid">
        <?php if (empty($orders)): ?>
        <div class="no-orders" style="grid-column: 1 / -1;">
            <i class="bi bi-check-circle d-block"></i>
            <h3>Aucune commande en attente</h3>
            <p>Les nouvelles commandes apparaîtront ici</p>
        </div>
        <?php else: ?>
        <?php foreach ($orders as $order): ?>
        <?php 
        $status = $order['status'] ?? 'pending';
        $config = $statusConfig[$status] ?? $statusConfig['pending'];
        $createdAt = strtotime($order['created_at'] ?? 'now');
        $waitTime = time() - $createdAt;
        $isUrgent = $status === 'pending' && $waitTime > 600; // 10 minutes
        ?>
        <div class="order-card <?= $isUrgent ? 'urgent' : '' ?>" data-status="<?= $status ?>" data-order-id="<?= $order['id'] ?>">
            <div class="order-header <?= $status ?>">
                <div>
                    <span class="order-number">#<?= e($order['order_number'] ?? $order['id']) ?></span>
                    <div class="timer">
                        <i class="bi bi-clock me-1"></i>
                        <span class="wait-time" data-created="<?= $createdAt ?>"><?= floor($waitTime / 60) ?> min</span>
                    </div>
                </div>
                <span class="order-table">Table <?= e($order['table_number'] ?? '-') ?></span>
            </div>
            
            <div class="order-body">
                <?php $items = $order['items'] ?? []; ?>
                <?php foreach ($items as $item): ?>
                <div class="order-item">
                    <div class="item-quantity"><?= $item['quantity'] ?? 1 ?></div>
                    <span class="item-name"><?= e($item['product_name'] ?? $item['name'] ?? 'Produit') ?></span>
                </div>
                <?php endforeach; ?>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="order-notes">
                    <i class="bi bi-chat-dots me-1"></i> <?= e($order['notes']) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="order-footer">
                <?php if ($status === 'pending'): ?>
                <form action="<?= url('kitchen/status/' . $order['id']) ?>" method="POST" class="d-flex flex-grow-1 gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="preparing">
                    <button type="submit" class="btn-kitchen btn-prepare">
                        <i class="bi bi-fire me-1"></i>Commencer
                    </button>
                </form>
                <?php elseif ($status === 'preparing'): ?>
                <form action="<?= url('kitchen/status/' . $order['id']) ?>" method="POST" class="d-flex flex-grow-1 gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="ready">
                    <button type="submit" class="btn-kitchen btn-ready">
                        <i class="bi bi-check-circle me-1"></i>Prête !
                    </button>
                </form>
                <?php elseif ($status === 'ready'): ?>
                <div class="text-center flex-grow-1 text-success fw-bold">
                    <i class="bi bi-check-circle me-1"></i>En attente du service
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Horloge
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = 
                now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateClock, 1000);
        updateClock();
        
        // Mise à jour des timers
        function updateTimers() {
            document.querySelectorAll('.wait-time').forEach(el => {
                const created = parseInt(el.dataset.created);
                const wait = Math.floor((Date.now() / 1000) - created);
                el.textContent = Math.floor(wait / 60) + ' min';
            });
        }
        setInterval(updateTimers, 60000);
        
        // Compteurs
        function updateCounts() {
            const all = document.querySelectorAll('.order-card').length;
            const pending = document.querySelectorAll('.order-card[data-status="pending"]').length;
            const preparing = document.querySelectorAll('.order-card[data-status="preparing"]').length;
            const ready = document.querySelectorAll('.order-card[data-status="ready"]').length;
            
            document.getElementById('count-all').textContent = all;
            document.getElementById('count-pending').textContent = pending;
            document.getElementById('count-preparing').textContent = preparing;
            document.getElementById('count-ready').textContent = ready;
        }
        updateCounts();
        
        // Filtrage
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const status = this.dataset.status;
                document.querySelectorAll('.order-card').forEach(card => {
                    if (status === 'all' || card.dataset.status === status) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
        
        // Plein écran
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }
        
        // Rafraîchissement automatique
        setTimeout(() => location.reload(), 30000);
        
        // Son de notification (nouvelle commande)
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleATU8G...');
    </script>
</body>
</html>
