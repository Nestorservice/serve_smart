<?php
/**
 * SIGR Client Layout - Pure POS/Menu Style (Dribbble dark theme)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= e($pageTitle ?? 'Restaurant') ?> - African Flavors</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <!-- PWA & Mobile Optimization -->
    <link rel="manifest" href="<?= BASE_URL ?>/public/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIGR">
    
    <style>
        :root {
            /* Dribbble POS Palette */
            --bg-color: #1a1c23;         /* Deep dark charcoal background */
            --card-bg: #22252e;          /* Slightly lighter dark for cards */
            --card-hover: #2a2e39;
            --primary-orange: #ff9f1c;   /* Vibrant orange accent */
            --primary-hover: #ffb042;
            --text-main: #ffffff;
            --text-muted: #8b92a5;
            --border-color: #2e3340;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --danger: #ff4757;
            --success: #2ed573;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* Generic utilities */
        .text-orange { color: var(--primary-orange) !important; }
        .bg-orange { background-color: var(--primary-orange) !important; }
        .text-muted-custom { color: var(--text-muted) !important; }

        /* HEADER */
        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 2rem;
            background: var(--bg-color);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-main);
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .header-brand i {
            color: var(--primary-orange);
            font-size: 1.6rem;
        }

        .header-search {
            background: var(--card-bg);
            border-radius: 50px;
            padding: 0.6rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 300px;
            border: 1px solid var(--border-color);
        }

        .header-search i { color: var(--text-muted); }
        .header-search input {
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-main);
            width: 100%;
            font-size: 0.95rem;
        }
        .header-search input::placeholder { color: var(--text-muted); }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .action-btn {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.2s;
        }
        .action-btn:hover {
            color: var(--text-main);
            border-color: var(--text-muted);
        }

        /* TABLE INDICATOR */
        .table-badge {
            background: rgba(255, 159, 28, 0.1);
            color: var(--primary-orange);
            border: 1px solid rgba(255, 159, 28, 0.2);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Layout structure */
        .app-layout {
            display: flex;
            height: calc(100vh - 76px - 70px); /* subtract header and bottom nav */
            overflow: hidden;
            padding: 0 1rem 1rem 1rem;
            gap: 20px;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
        }
        
        .main-content::-webkit-scrollbar { display: none; }

        .sidebar-cart {
            width: 380px;
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* BOTTOM NAVIGATION (Mobile) */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            display: none;
            justify-content: space-around;
            align-items: center;
            z-index: 1050;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 500;
            transition: 0.2s;
        }

        .nav-item i {
            font-size: 1.4rem;
            margin-bottom: 2px;
        }

        .nav-item.active {
            color: var(--primary-orange);
        }

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .app-layout { 
                flex-direction: column; 
                height: auto; 
                margin-bottom: 80px; /* space for bottom nav */
                padding-bottom: 30px;
            }
            .sidebar-cart { display: none; } /* On mobile we use dedicated cart page or modal */
            .main-content { height: auto; overflow: visible; padding: 10px; border: none; background: transparent; }
            .header-search { display: none; }
            .bottom-nav { display: flex; }
            .app-header { padding: 1rem; }
        }
    </style>
</head>
<body>
    
    <header class="app-header">
        <a href="<?= url('/') ?>" class="header-brand">
            <i class="bi bi-grid-fill"></i>
            Point of sales
        </a>
        
        <div class="header-search">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search menu...">
        </div>
        
        <div class="header-actions">
            <?php if (isset($tableNumber) && $tableNumber): ?>
                <div class="table-badge">
                    <i class="bi bi-geo-alt-fill me-1"></i> Table <?= e($tableNumber) ?>
                </div>
            <?php endif; ?>
            <a href="<?= url('client/order-tracking') ?>" class="action-btn d-none d-lg-flex" title="Track Order">
                <i class="bi bi-bell"></i>
            </a>
            <a href="<?= url('admin/login') ?>" class="action-btn" title="Admin">
                <i class="bi bi-person-circle"></i>
            </a>
        </div>
    </header>

    <?= $content ?? '' ?>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="<?= url('client/menu') ?>" class="nav-item <?= ($currentPage ?? '') === 'menu' ? 'active' : '' ?>">
            <i class="bi bi-grid"></i>
            <span>Menu</span>
        </a>
        <a href="<?= url('client/cart') ?>" class="nav-item <?= ($currentPage ?? '') === 'cart' ? 'active' : '' ?>">
            <div class="position-relative">
                <i class="bi bi-bag"></i>
                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.6rem; padding: 0.25em 0.5em;">
                    <?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?>
                </span>
                <?php endif; ?>
            </div>
            <span>Cart</span>
        </a>
        <a href="<?= url('client/order-tracking') ?>" class="nav-item <?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i>
            <span>Orders</span>
        </a>
    </nav>

    <!-- Flash Messages (Overlays nicely) -->
    <?php 
    $session = \Core\Session::getInstance();
    if ($session->hasFlash()): 
    ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000; margin-bottom: 75px;">
        <?php foreach (['success', 'danger', 'info', 'warning'] as $type): ?>
            <?php foreach ($session->getFlash($type) as $msg): ?>
            <div class="toast align-items-center text-white bg-<?= $type === 'danger' ? 'danger' : ($type === 'success' ? 'success' : 'primary') ?> border-0 mb-2 show" role="alert" style="border-radius: var(--radius-md);">
                <div class="d-flex">
                    <div class="toast-body fw-medium">
                        <?= $msg ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= BASE_URL ?>/public/sw.js')
                .then(reg => console.log('SW Registered'))
                .catch(err => console.log('SW Error:', err));
            });
        }

        // Auto-close toasts
        document.querySelectorAll('.toast').forEach(t => {
            setTimeout(() => {
                let bsToast = new bootstrap.Toast(t);
                bsToast.hide();
            }, 4000);
        });
    </script>
    <?= $scripts ?? '' ?>
</body>
</html>
