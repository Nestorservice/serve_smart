<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= e($pageTitle ?? 'Restaurant') ?> - Saveurs d'Afrique</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1a1a2e">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?= url('public/assets/images/favicon.png') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #E85D04;
            --primary-light: #F48C06;
            --primary-dark: #DC2F02;
            --accent: #FAA307;
            --dark: #1a1a2e;
            --dark-2: #16213e;
            --dark-3: #0f3460;
            --success: #06D6A0;
            --danger: #EF476F;
            --text: #2d3436;
            --text-light: #636e72;
            --bg: #FAFAFA;
            --card-bg: #FFFFFF;
            --border: #f0f0f0;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow: 0 4px 24px rgba(0,0,0,0.06);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            --gradient-dark: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Outfit', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        
        /* ============ HEADER ============ */
        .client-header {
            background: var(--gradient-dark);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1050;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        
        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .brand-icon {
            width: 38px;
            height: 38px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .header-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .header-nav .nav-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .header-nav .nav-link:hover,
        .header-nav .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .table-indicator {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            color: white;
            padding: 0.4rem 0.9rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        /* Cart button - ALWAYS visible, never in hamburger */
        .cart-btn {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: none;
            white-space: nowrap;
        }
        
        .cart-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(232,93,4,0.4);
            color: white;
        }
        
        .cart-count {
            background: white;
            color: var(--primary);
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 800;
        }
        
        /* Hide nav links on mobile but keep cart visible */
        @media (max-width: 768px) {
            .header-nav-links { display: none; }
            .brand span { display: none; }
            .cart-btn span.cart-label { display: none; }
        }
        
        /* ============ HERO ============ */
        .menu-hero {
            background: var(--gradient-dark);
            position: relative;
            padding: 2.5rem 0 3.5rem;
            margin-top: -1px;
            overflow: hidden;
        }
        
        .menu-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .hero-content {
            position: relative;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            color: var(--accent);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }
        
        .hero-content h1 {
            color: white;
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }
        
        .hero-highlight {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-content p {
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .hero-search {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50px;
            padding: 0.6rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .hero-search:focus-within {
            background: rgba(255,255,255,0.15);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(232,93,4,0.2);
        }
        
        .hero-search i { color: rgba(255,255,255,0.5); font-size: 1.1rem; }
        
        .hero-search input {
            background: none;
            border: none;
            outline: none;
            color: white;
            font-size: 0.95rem;
            font-family: inherit;
            width: 100%;
        }
        
        .hero-search input::placeholder { color: rgba(255,255,255,0.4); }
        
        @media (max-width: 576px) {
            .menu-hero { padding: 1.5rem 0 2.5rem; }
            .hero-content h1 { font-size: 1.6rem; }
        }
        
        /* ============ CATEGORIES NAV ============ */
        .categories-nav {
            position: sticky;
            top: 56px;
            z-index: 1040;
            background: var(--bg);
            padding: 0.8rem 0;
            transition: box-shadow 0.3s;
        }
        
        .categories-nav.scrolled {
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        
        .categories-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0 1rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        .categories-scroll::-webkit-scrollbar { display: none; }
        
        .cat-chip {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            text-decoration: none;
            color: var(--text);
            background: white;
            border: 1.5px solid var(--border);
            transition: all 0.3s;
        }
        
        .cat-chip:hover { border-color: var(--primary); color: var(--primary); }
        
        .cat-chip.active {
            background: var(--gradient);
            border-color: transparent;
            color: white;
        }
        
        .cat-chip .cat-count {
            background: rgba(0,0,0,0.08);
            padding: 0.1rem 0.45rem;
            border-radius: 50px;
            font-size: 0.75rem;
        }
        
        .cat-chip.active .cat-count {
            background: rgba(255,255,255,0.25);
        }
        
        /* ============ SECTIONS ============ */
        .featured-section, .products-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .section-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .product-count {
            background: var(--border);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-light);
        }
        
        /* Featured horizontal scroller */
        .featured-scroll {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            scrollbar-width: none;
        }
        
        .featured-scroll::-webkit-scrollbar { display: none; }
        
        .featured-card {
            flex: 0 0 220px;
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            transition: all 0.3s;
        }
        
        .featured-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        
        .featured-img {
            position: relative;
            height: 140px;
            overflow: hidden;
        }
        
        .featured-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
        .featured-card:hover .featured-img img { transform: scale(1.08); }
        
        .featured-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }
        
        .featured-info {
            padding: 0.8rem;
        }
        
        .featured-info h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .price-tag {
            font-weight: 800;
            color: var(--primary);
            font-size: 0.95rem;
        }
        
        .featured-add-btn {
            position: absolute;
            bottom: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--gradient);
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(232,93,4,0.3);
        }
        
        .featured-add-btn:hover { transform: scale(1.15); }
        
        /* ============ PRODUCTS GRID ============ */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.2rem;
        }
        
        @media (max-width: 576px) {
            .products-grid { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
        }
        
        .product-card2 {
            background: var(--card-bg);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card2:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }
        
        .product-img-wrap {
            position: relative;
            height: 180px;
            overflow: hidden;
        }
        
        @media (max-width: 576px) { .product-img-wrap { height: 130px; } }
        
        .product-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card2:hover .product-img-wrap img { transform: scale(1.08); }
        
        .prod-badge {
            position: absolute;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }
        
        .badge-hot {
            top: 8px;
            left: 8px;
            background: rgba(239,71,111,0.9);
            color: white;
        }
        
        .badge-time {
            top: 8px;
            right: 8px;
            background: rgba(0,0,0,0.6);
            color: white;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .product-details {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        @media (max-width: 576px) { .product-details { padding: 0.7rem; } }
        
        .product-name {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        @media (max-width: 576px) { .product-name { font-size: 0.85rem; } }
        
        .product-desc {
            color: var(--text-light);
            font-size: 0.82rem;
            margin-bottom: 0.8rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        @media (max-width: 576px) { .product-desc { display: none; } }
        
        .product-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        
        .product-price2 {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
        }
        
        @media (max-width: 576px) { .product-price2 { font-size: 0.9rem; } }
        
        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: var(--gradient);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .add-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 16px rgba(232,93,4,0.35);
        }
        
        .add-btn.added, .featured-add-btn.added {
            background: var(--success) !important;
        }
        
        @media (max-width: 576px) {
            .add-btn { padding: 0.4rem 0.7rem; font-size: 0.75rem; }
        }
        
        .scan-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: transparent;
            color: var(--primary);
            border: 1.5px solid var(--primary);
            padding: 0.45rem 0.9rem;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .scan-btn:hover { background: var(--primary); color: white; }
        
        .unavailable-badge {
            background: #f0f0f0;
            color: #999;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* Empty state */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-icon { font-size: 3rem; margin-bottom: 0.8rem; }
        .empty-state h3 { font-weight: 700; color: var(--text); margin-bottom: 0.4rem; }
        .empty-state p { color: var(--text-light); }
        
        /* ============ FLOATING CART ============ */
        .floating-cart {
            position: fixed;
            bottom: 1.2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
            text-decoration: none;
            animation: floatUp 0.5s ease-out;
        }
        
        @keyframes floatUp {
            from { transform: translateX(-50%) translateY(100px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
        
        .floating-cart-inner {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: var(--dark);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            transition: all 0.3s;
        }
        
        .floating-cart:hover .floating-cart-inner {
            background: var(--primary);
            transform: scale(1.03);
        }
        
        .fc-left {
            position: relative;
        }
        
        .fc-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--danger);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
        }
        
        @media (min-width: 769px) { .floating-cart { display: none; } }
        
        /* ============ FOOTER ============ */
        .client-footer {
            background: var(--dark);
            color: white;
            padding: 2rem 0;
            margin-top: 2rem;
            text-align: center;
        }
        
        .client-footer small { color: rgba(255,255,255,0.4); }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="client-header">
        <div class="container">
            <div class="header-inner">
                <a class="brand" href="<?= url('/') ?>">
                    <div class="brand-icon"><i class="bi bi-fire"></i></div>
                    <span>Saveurs d'Afrique</span>
                </a>
                
                <div class="header-nav">
                    <div class="header-nav-links">
                        <a class="nav-link <?= ($currentPage ?? '') === 'menu' ? 'active' : '' ?>" href="<?= url('client/menu' . (isset($tableNumber) && $tableNumber ? '?table=' . e($tableNumber) : '')) ?>">
                            <i class="bi bi-grid-fill me-1"></i> Menu
                        </a>
                    </div>
                    
                    <?php if (isset($tableNumber) && $tableNumber): ?>
                    <div class="table-indicator">
                        <i class="bi bi-geo-alt-fill"></i> Table <?= e($tableNumber) ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Cart button - ALWAYS outside hamburger -->
                    <a href="<?= url('client/cart') ?>" class="cart-btn">
                        <i class="bi bi-cart3"></i>
                        <span class="cart-label">Panier</span>
                        <?php $cartCount = isset($cart) ? count($cart) : 0; ?>
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-count"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container-fluid px-0">
            <?= $content ?? '' ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="client-footer">
        <div class="container">
            <p class="mb-1">
                <i class="bi bi-fire text-warning"></i> 
                <strong>Saveurs d'Afrique</strong> — Goûtez l'authenticité
            </p>
            <small>© <?= date('Y') ?> Tous droits réservés</small>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>
