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
    <meta name="theme-color" content="#1a1c22">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?= url('public/assets/images/favicon.png') ?>">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
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
            height: calc(100vh - 76px); /* subtract header */
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

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .app-layout { flex-direction: column; height: auto; overflow: visible; }
            .sidebar-cart { width: 100%; height: 500px; margin-top: 20px; }
            .main-content { height: auto; overflow: visible; padding: 10px; }
            .header-search { display: none; }
        }

        @media (max-width: 576px) {
            .app-header { padding: 1rem; }
            .sidebar-cart { border-radius: 0; border: none; height: auto; min-height: 400px; }
            .main-content { border-radius: 0; border: none; background: transparent; }
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
            <a href="<?= url('client/order-tracking') ?>" class="action-btn" title="Track Order">
                <i class="bi bi-bell"></i>
            </a>
            <a href="<?= url('client/cart') ?>" class="action-btn d-lg-none" title="Cart">
                <i class="bi bi-bag"></i>
            </a>
        </div>
    </header>

    <?= $content ?? '' ?>

    <!-- Flash Messages (Overlays nicely) -->
    <?php 
    $session = \Core\Session::getInstance();
    if ($session->hasFlash()): 
    ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
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
