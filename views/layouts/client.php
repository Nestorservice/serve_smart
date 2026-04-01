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
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;1,400&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            /* Palette Luxe: Dark & Gold */
            --primary: #D4AF37;       /* Gold/Champagne */
            --primary-light: #F2E3B6; /* Light Gold */
            --primary-dark: #AA8B2B;  /* Deep Gold */
            --accent: #E5C158;        /* Bright Gold */
            --dark: #0A0A0A;          /* Charcoal Black */
            --dark-2: #121212;        /* Dark Grey */
            --dark-3: #1A1A1A;        /* Lighter Grey */
            --success: #28a745;
            --danger: #dc3545;
            --text: #FDFBF7;          /* Off-White */
            --text-light: #A0A0A0;    /* Silver/Grey */
            --bg: #111111;            /* Background */
            --card-bg: #1A1A1A;       /* Card Background */
            --border: rgba(212, 175, 55, 0.2); /* Thin gold border */
            --border-light: rgba(255, 255, 255, 0.08);
            --radius: 4px;            /* Sharp, elegant corners */
            --radius-sm: 2px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 16px 48px rgba(0, 0, 0, 0.6);
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            --gradient-dark: linear-gradient(135deg, #111111 0%, #0A0A0A 100%);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Lato', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            letter-spacing: 0.3px;
        }
        
        h1, h2, h3, h4, h5, h6, .brand, .hero-content h1 {
            font-family: 'Playfair Display', serif;
        }
        
        /* ============ HEADER ============ */
        .client-header {
            background: var(--bg);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1050;
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
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
            gap: 0.8rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.4rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .brand-icon {
            color: var(--primary);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .header-nav .nav-link {
            color: var(--text-light);
            text-decoration: none;
            padding: 0.5rem;
            font-family: 'Lato', sans-serif;
            font-weight: 300;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.4s;
            border-bottom: 1px solid transparent;
        }
        
        .header-nav .nav-link:hover,
        .header-nav .nav-link.active {
            color: var(--primary);
            border-bottom: 1px solid var(--primary);
        }
        
        .table-indicator {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--primary);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 400;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Cart button - Minimalist outline */
        .cart-btn {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            text-decoration: none;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 400;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.4s;
            white-space: nowrap;
        }
        
        .cart-btn:hover {
            background: var(--primary);
            color: var(--dark);
        }
        
        .cart-count {
            background: var(--primary);
            color: var(--dark);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.4s;
        }
        .cart-btn:hover .cart-count {
            background: var(--dark);
            color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .header-nav-links { display: none; }
            .brand span { display: none; }
            .cart-btn span.cart-label { display: none; }
            .cart-btn { padding: 0.5rem 1rem; }
        }
        
        /* ============ HERO ============ */
        .menu-hero {
            background: var(--bg);
            position: relative;
            padding: 4rem 0;
            margin-top: 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        .hero-content {
            position: relative;
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1.2rem;
        }
        
        .hero-content h1 {
            color: var(--text);
            font-size: 3rem;
            font-weight: 400;
            margin-bottom: 1rem;
            line-height: 1.1;
            font-style: italic;
        }
        
        .hero-highlight {
            color: var(--primary);
        }
        
        .hero-content p {
            color: var(--text-light);
            font-size: 1.05rem;
            font-weight: 300;
            margin-bottom: 2rem;
            letter-spacing: 0.5px;
        }
        
        .hero-search {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.4s;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .hero-search:focus-within {
            border-color: var(--primary);
            background: rgba(255,255,255,0.05);
        }
        
        .hero-search i { color: var(--primary); font-size: 1.1rem; }
        
        .hero-search input {
            background: none;
            border: none;
            outline: none;
            color: var(--text);
            font-size: 0.95rem;
            font-family: 'Lato', sans-serif;
            width: 100%;
        }
        
        .hero-search input::placeholder { color: var(--text-light); font-weight: 300; }
        
        @media (max-width: 576px) {
            .menu-hero { padding: 2.5rem 0; }
            .hero-content h1 { font-size: 2.2rem; }
        }
        
        /* ============ CATEGORIES NAV ============ */
        .categories-nav {
            position: sticky;
            top: 76px;
            z-index: 1040;
            background: rgba(17, 17, 17, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-light);
            transition: all 0.4s;
        }
        
        .categories-scroll {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            padding: 0 1rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        .cat-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            font-size: 0.9rem;
            font-weight: 400;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            text-decoration: none;
            color: var(--text-light);
            border-bottom: 1px solid transparent;
            transition: all 0.4s;
        }
        
        .cat-chip:hover { color: var(--primary); border-bottom: 1px solid var(--primary); }
        
        .cat-chip.active {
            color: var(--primary);
            border-bottom: 1px solid var(--primary);
        }
        
        .cat-chip .cat-count {
            color: var(--dark);
            background: var(--text-light);
            padding: 0.1rem 0.4rem;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: 700;
        }
        
        .cat-chip.active .cat-count {
            background: var(--primary);
        }
        
        /* ============ SECTIONS ============ */
        .featured-section, .products-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1rem;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 1rem;
        }
        
        .section-header h2 {
            font-size: 1.8rem;
            font-weight: 400;
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--primary);
        }
        
        .product-count {
            font-size: 0.85rem;
            font-weight: 300;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-light);
        }
        
        /* Featured */
        .featured-scroll {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            scrollbar-width: none;
        }
        
        .featured-card {
            flex: 0 0 280px;
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border-light);
            overflow: hidden;
            position: relative;
            transition: all 0.5s;
        }
        
        .featured-card:hover { 
            border-color: var(--primary); 
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.1); 
        }
        
        .featured-img {
            position: relative;
            height: 180px;
            overflow: hidden;
        }
        
        .featured-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; opacity: 0.85; filter: grayscale(20%); }
        .featured-card:hover .featured-img img { transform: scale(1.05); filter: grayscale(0%); opacity: 1; }
        
        .featured-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--primary);
            color: var(--dark);
            padding: 0.3rem 0.8rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .featured-info {
            padding: 1.2rem;
            text-align: center;
        }
        
        .featured-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        
        .price-tag {
            font-family: 'Lato', sans-serif;
            font-weight: 300;
            letter-spacing: 1px;
            color: var(--primary);
            font-size: 1rem;
        }
        
        .featured-add-btn {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--primary);
            background: transparent;
            color: var(--primary);
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s;
        }
        
        .featured-add-btn:hover { background: var(--primary); color: var(--dark); transform: rotate(90deg); }
        
        /* ============ PRODUCTS GRID ============ */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        @media (max-width: 576px) {
            .products-grid { grid-template-columns: 1fr; gap: 1.5rem; }
        }
        
        .product-card2 {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border-light);
            overflow: hidden;
            transition: all 0.5s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card2:hover {
            border-color: var(--border);
            box-shadow: var(--shadow);
            transform: translateY(-5px);
        }
        
        .product-img-wrap {
            position: relative;
            height: 220px;
            overflow: hidden;
            background: var(--dark);
        }
        
        .product-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
            transition: all 0.8s ease;
        }
        
        .product-card2:hover .product-img-wrap img { transform: scale(1.05); opacity: 1; }
        
        .prod-badge {
            position: absolute;
            padding: 0.3rem 0.8rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .badge-hot {
            top: 0;
            left: 0;
            background: var(--primary);
            color: var(--dark);
        }
        
        .badge-time {
            bottom: 10px;
            right: 10px;
            background: rgba(10, 10, 10, 0.8);
            border: 1px solid var(--border-light);
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .product-details {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .product-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        
        .product-desc {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        .product-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--border-light);
            padding-top: 1rem;
        }
        
        .product-price2 {
            font-size: 1.1rem;
            font-weight: 300;
            letter-spacing: 1px;
            color: var(--primary);
        }
        
        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.5rem 1.2rem;
            font-size: 0.85rem;
            font-weight: 400;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.4s;
        }
        
        .add-btn:hover {
            background: var(--primary);
            color: var(--dark);
        }
        
        .add-btn.added, .featured-add-btn.added {
            background: var(--text) !important;
            color: var(--dark) !important;
            border-color: var(--text) !important;
        }
        
        .scan-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: var(--text-light);
            border: 1px solid var(--border-light);
            padding: 0.5rem 1.2rem;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.4s;
        }
        
        .scan-btn:hover { border-color: var(--primary); color: var(--primary); }
        
        .unavailable-badge {
            color: var(--danger);
            font-size: 0.85rem;
            font-style: italic;
        }
        
        /* Empty state */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 5rem 1rem;
        }
        
        .empty-icon { font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem; }
        .empty-state h3 { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 400; margin-bottom: 0.5rem; }
        .empty-state p { color: var(--text-light); font-weight: 300; font-style: italic; }
        
        /* ============ FLOATING CART ============ */
        .floating-cart {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
            text-decoration: none;
            animation: floatUp 0.8s ease-out;
        }
        
        @keyframes floatUp {
            from { transform: translateX(-50%) translateY(100px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
        
        .floating-cart-inner {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--primary);
            color: var(--dark);
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 400;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
            transition: all 0.4s;
        }
        
        .floating-cart:hover .floating-cart-inner {
            background: var(--text);
            box-shadow: 0 10px 40px rgba(255, 255, 255, 0.2);
        }
        
        .fc-badge {
            background: var(--dark);
            color: var(--primary);
            padding: 0.1rem 0.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.8rem;
        }
        
        @media (min-width: 769px) { .floating-cart { display: none; } }
        
        /* ============ FOOTER ============ */
        .client-footer {
            background: var(--dark);
            color: var(--text-light);
            padding: 3rem 0;
            margin-top: 4rem;
            text-align: center;
            border-top: 1px solid var(--border-light);
        }
        
        .client-footer p {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 1rem;
            font-style: italic;
        }
        
        .client-footer small { font-weight: 300; letter-spacing: 1px; text-transform: uppercase; font-size: 0.75rem; }
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
