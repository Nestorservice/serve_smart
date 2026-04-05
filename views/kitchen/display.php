<?php
/**
 * SIGR Kitchen Display - Premium POS Dark Kanban Style
 */

use Core\Helpers;

$pageTitle = 'Kitchen';
$orders = $orders ?? [];

$statusConfig = [
    'pending' => ['label' => 'Pending', 'class' => 'warning', 'icon' => 'clock', 'badge' => '#ff9f1c'],
    'confirmed' => ['label' => 'Confirmed', 'class' => 'info', 'icon' => 'check-circle', 'badge' => '#3498db'],
    'preparing' => ['label' => 'Preparing', 'class' => 'primary', 'icon' => 'fire', 'badge' => '#e74c3c'],
    'ready' => ['label' => 'Ready', 'class' => 'success', 'icon' => 'bell', 'badge' => '#2ed573'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= e($pageTitle) ?> - Kitchen</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-base: #13151a;
            --bg-card: #20232a;
            --bg-card-hover: #262932;
            --border-color: #303641;
            --text-main: #ffffff;
            --text-muted: #8b92a5;
            --radius-xl: 16px;
            --radius-md: 12px;
            --font-main: 'Inter', sans-serif;
        }
        
        body {
            font-family: var(--font-main);
            background: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
        }
        
        /* HEADER */
        .kitchen-titlebar {
            background: rgba(32, 35, 42, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .kitchen-brand h3 { margin: 0; font-weight: 700; letter-spacing: -0.5px; }
        .kitchen-brand i { color: #ff9f1c; margin-right: 10px; }
        
        .header-stats { display: flex; gap: 20px; }
        .stat-pill {
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            padding: 5px 15px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .clock {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ff9f1c;
            font-variant-numeric: tabular-nums;
        }

        /* GRID */
        .order-kanban {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .ticket-card {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            border: 2px solid transparent;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: 0.2s;
        }
        
        .ticket-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.3); }

        /* TICKET HEADER */
        .ticket-header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed var(--border-color);
        }
        .th-left { display: flex; align-items: center; gap: 10px; }
        .th-number {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .th-table {
            background: rgba(255,255,255,0.1);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #d1d5db;
        }
        .th-time {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .time-badge {
            background: #ff4757;
            color: white;
            padding: 2px rcpx;
            border-radius: 4px;
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(255, 71, 87, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 71, 87, 0); }
        }

        /* TICKET ITEMS */
        .ticket-body {
            padding: 15px 20px;
            flex: 1;
        }
        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .item-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .item-row:last-child { border-bottom: none; }
        .item-qty {
            font-weight: 800;
            font-size: 1.1rem;
            width: 35px;
            color: #ff9f1c;
        }
        .item-name {
            font-weight: 600;
            font-size: 1.05rem;
        }
        .item-note {
            display: block;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
            background: rgba(255, 159, 28, 0.1);
            padding: 5px 8px;
            border-left: 2px solid #ff9f1c;
            border-radius: 4px;
        }

        /* TICKET FOOTER / ACTIONS */
        .ticket-footer {
            padding: 15px 20px;
            background: rgba(0,0,0,0.2);
            border-top: 1px solid var(--border-color);
        }
        
        .action-btn {
            width: 100%;
            border: none;
            padding: 12px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1rem;
            transition: 0.2s;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .action-btn i { font-size: 1.2rem; margin-right: 5px; }
        
        .btn-confirm { background: #3498db; color: white; }
        .btn-confirm:hover { background: #2980b9; }
        
        .btn-prepare { background: #e74c3c; color: white; }
        .btn-prepare:hover { background: #c0392b; }
        
        .btn-ready { background: #2ed573; color: #111; }
        .btn-ready:hover { background: #27ae60; }

        /* BORDERS FOR STATUS */
        .border-pending { border-color: rgba(255, 159, 28, 0.4) !important; }
        .border-confirmed { border-color: rgba(52, 152, 219, 0.4) !important; }
        .border-preparing { border-color: rgba(231, 76, 60, 0.5) !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-base); }
        ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #4a5568; }
    </style>
</head>
<body>

    <header class="kitchen-titlebar">
        <div class="kitchen-brand">
            <h3><i class="bi bi-fire"></i> KITCHEN BOARD</h3>
        </div>
        
        <div class="header-stats d-none d-md-flex">
            <div class="stat-pill"><span class="text-warning"><?= count(array_filter($orders, fn($o) => in_array($o['status'], ['pending', 'confirmed']))) ?></span> New</div>
            <div class="stat-pill"><span class="text-danger"><?= count(array_filter($orders, fn($o) => $o['status'] === 'preparing')) ?></span> Active</div>
            <div class="stat-pill"><span class="text-success"><?= count(array_filter($orders, fn($o) => $o['status'] === 'ready')) ?></span> Ready</div>
        </div>
        
        <div class="clock" id="clock">00:00:00</div>
    </header>

    <div class="order-kanban">
        <?php if (empty($orders)): ?>
            <div class="col-12 text-center" style="margin-top: 15vh;">
                <i class="bi bi-cup-hot" style="font-size: 5rem; color: var(--border-color);"></i>
                <h2 class="mt-4" style="color: var(--text-muted);">No active orders</h2>
                <p style="color: #555;">The kitchen is quiet for now.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <?php 
                $status = $statusConfig[$order['status']] ?? $statusConfig['pending'];
                
                // Calculate time elapsed since creation
                $createdAt = strtotime($order['created_at']);
                $now = time();
                $diffMinutes = floor(($now - $createdAt) / 60);
                
                // Urgent class if > 20 min in preparation/confirmed
                $isUrgent = ($diffMinutes > 20 && in_array($order['status'], ['confirmed', 'preparing']));
            ?>
            <div class="ticket-card border-<?= $order['status'] ?>">
                
                <div class="ticket-header" style="background: <?= $status['badge'] ?>15">
                    <div class="th-left">
                        <div class="th-number">#<?= e($order['order_number'] ?? $order['id']) ?></div>
                        <div class="th-table">T. <?= e($order['table_number'] ?? '?') ?></div>
                    </div>
                    <div class="th-time <?= $isUrgent ? 'text-danger' : 'text-muted' ?>">
                        <i class="bi bi-clock-history"></i>
                        <?= $diffMinutes ?>m
                        <?php if ($isUrgent): ?>
                            <span class="spinner-grow spinner-grow-sm text-danger ms-1" role="status"></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ticket-body">
                    <ul class="item-list">
                        <?php foreach ($order['items'] ?? [] as $item): ?>
                        <li class="item-row">
                            <div class="item-qty"><?= $item['quantity'] ?>x</div>
                            <div style="flex:1">
                                <div class="item-name"><?= e(!empty($item['name_en']) ? $item['name_en'] : ($item['name_fr'] ?? 'Item')) ?></div>
                                <?php if (!empty($item['special_instructions'])): ?>
                                    <span class="item-note"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> <?= e($item['special_instructions']) ?></span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if (!empty($order['notes'])): ?>
                    <div class="mt-3 p-2 rounded" style="background: rgba(255,255,255,0.05); border-left: 3px solid #3498db; font-size: 0.9rem;">
                        <i class="bi bi-chat-left-text text-info me-1"></i> <?= e($order['notes']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="ticket-footer">
                    <form action="<?= url('kitchen/status/' . $order['id']) ?>" method="POST" class="status-form">
                        <?= csrf_field() ?>
                        
                        <?php if ($order['status'] === 'pending'): ?>
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="action-btn btn-confirm"><i class="bi bi-check2-circle"></i> Confirm</button>
                        
                        <?php elseif ($order['status'] === 'confirmed'): ?>
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit" class="action-btn btn-prepare"><i class="bi bi-fire"></i> Prepare</button>
                        
                        <?php elseif ($order['status'] === 'preparing'): ?>
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" class="action-btn btn-ready"><i class="bi bi-bell-fill"></i> Ready!</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- AUDIO FOR NEW ORDERS -->
    <audio id="orderAlert" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // HORLOGE
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // AJAX SUBMIT (Pas de refresh total)
        document.querySelectorAll('.status-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
                btn.style.opacity = '0.7';
                btn.disabled = true;
                
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => {
                    if(res.ok) window.location.reload();
                });
            });
        });

        // AUTO-REFRESH SANS CLIGNOTEMENT
        let currentOrderCount = <?= count($orders) ?>;
        setInterval(() => {
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newBoard = doc.querySelector('.order-kanban');
                    const newCount = doc.querySelectorAll('.ticket-card').length;
                    
                    if (newCount > currentOrderCount) {
                        // Play alert sound for new order
                        document.getElementById('orderAlert').play().catch(() => {});
                    }
                    
                    document.querySelector('.order-kanban').innerHTML = newBoard.innerHTML;
                    document.querySelector('.header-stats').innerHTML = doc.querySelector('.header-stats').innerHTML;
                    currentOrderCount = newCount;
                });
        }, 15000); // Poll every 15s
    </script>
</body>
</html>
