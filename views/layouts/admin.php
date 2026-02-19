<?php
/**
 * SIGR Admin Layout - Based on FoodDesk Template
 * 
 * Variables disponibles:
 * - $pageTitle : titre de la page
 * - $currentPage : page active pour le menu
 * - $content : contenu principal
 * - $scripts : scripts additionnels (optionnel)
 */

use Core\Session;
use Core\Helpers;

$userName = Session::getInstance()->getStaffName() ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= e($pageTitle ?? 'Dashboard') ?> - SIGR Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?= url('public/assets/images/favicon.png') ?>">
    
    <!-- Vendor CSS -->
    <link href="<?= url('public/assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') ?>" rel="stylesheet">
    <link href="<?= url('public/assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') ?>" rel="stylesheet">
    <link href="<?= url('public/assets/vendor/jquery-nice-select/css/nice-select.css') ?>" rel="stylesheet">
    <link href="<?= url('public/assets/vendor/swiper/css/swiper-bundle.min.css') ?>" rel="stylesheet">
    
    <!-- Main CSS -->
    <link href="<?= url('public/assets/css/style.css') ?>" rel="stylesheet">
    <!-- SIGR Custom CSS -->
    <link href="<?= url('public/assets/css/sigr-custom.css') ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Dark Mode CSS -->
    <style>
    body.dark-mode {
        background-color: #1a1d23 !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .content-body { background-color: #1a1d23 !important; }
    body.dark-mode .header { background-color: #242731 !important; border-bottom: 1px solid #2d3038 !important; }
    body.dark-mode .header-content { background-color: #242731 !important; }
    body.dark-mode .nav-header { background-color: #242731 !important; border-right: 1px solid #2d3038 !important; }
    body.dark-mode .dlabnav { background-color: #242731 !important; border-right: 1px solid #2d3038 !important; }
    body.dark-mode .dlabnav .metismenu > li > a { color: #b3b8c8 !important; }
    body.dark-mode .dlabnav .metismenu > li:hover > a,
    body.dark-mode .dlabnav .metismenu > li.mm-active > a { color: #fff !important; }
    body.dark-mode .card { background-color: #242731 !important; border: 1px solid #2d3038 !important; color: #e4e6eb !important; }
    body.dark-mode .card-header { background-color: #242731 !important; border-bottom: 1px solid #2d3038 !important; }
    body.dark-mode .card-footer { background-color: #242731 !important; border-top: 1px solid #2d3038 !important; }
    body.dark-mode .card-title { color: #e4e6eb !important; }
    body.dark-mode .table { color: #e4e6eb !important; border-color: #2d3038 !important; }
    body.dark-mode .table > :not(caption) > * > * { background-color: transparent !important; color: #e4e6eb !important; border-color: #2d3038 !important; }
    body.dark-mode .table-hover > tbody > tr:hover > * { background-color: #2d3038 !important; }
    body.dark-mode .table-warning > * { background-color: rgba(255,193,7,0.1) !important; }
    body.dark-mode .table-danger > * { background-color: rgba(220,53,69,0.1) !important; }
    body.dark-mode .modal-content { background-color: #242731 !important; border: 1px solid #2d3038 !important; color: #e4e6eb !important; }
    body.dark-mode .modal-header { border-bottom-color: #2d3038 !important; }
    body.dark-mode .modal-footer { border-top-color: #2d3038 !important; }
    body.dark-mode .form-control, body.dark-mode .form-select { background-color: #1a1d23 !important; border-color: #2d3038 !important; color: #e4e6eb !important; }
    body.dark-mode .form-control:focus, body.dark-mode .form-select:focus { border-color: #667eea !important; box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25) !important; }
    body.dark-mode .form-control::placeholder { color: #6c757d !important; }
    body.dark-mode .input-group-text { background-color: #2d3038 !important; border-color: #2d3038 !important; color: #b3b8c8 !important; }
    body.dark-mode .form-check-input { background-color: #1a1d23 !important; border-color: #2d3038 !important; }
    body.dark-mode .form-check-input:checked { background-color: #667eea !important; border-color: #667eea !important; }
    body.dark-mode .list-group-item { background-color: #242731 !important; border-color: #2d3038 !important; color: #e4e6eb !important; }
    body.dark-mode .dropdown-menu { background-color: #242731 !important; border: 1px solid #2d3038 !important; }
    body.dark-mode .dropdown-item { color: #e4e6eb !important; }
    body.dark-mode .dropdown-item:hover { background-color: #2d3038 !important; }
    body.dark-mode .btn-outline-secondary { color: #b3b8c8 !important; border-color: #2d3038 !important; }
    body.dark-mode .btn-outline-secondary:hover { background-color: #2d3038 !important; color: #fff !important; }
    body.dark-mode .btn-outline-primary { color: #667eea !important; }
    body.dark-mode .btn-light { background-color: #2d3038 !important; border-color: #2d3038 !important; color: #e4e6eb !important; }
    body.dark-mode .text-black, body.dark-mode .text-dark { color: #e4e6eb !important; }
    body.dark-mode .text-muted { color: #8a8fa3 !important; }
    body.dark-mode .bg-light { background-color: #2d3038 !important; }
    body.dark-mode .bg-white { background-color: #242731 !important; }
    body.dark-mode .border { border-color: #2d3038 !important; }
    body.dark-mode .border-bottom { border-color: #2d3038 !important; }
    body.dark-mode .footer { background-color: #242731 !important; border-top: 1px solid #2d3038 !important; }
    body.dark-mode .footer p { color: #8a8fa3 !important; }
    body.dark-mode .alert { border: 1px solid #2d3038 !important; }
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 { color: #e4e6eb !important; }
    body.dark-mode .page-titles { background-color: transparent !important; }
    body.dark-mode .hamburger .line { background: #e4e6eb !important; }
    body.dark-mode .header-info2 h6 { color: #e4e6eb !important; }
    body.dark-mode .brand-title { color: #667eea !important; }
    body.dark-mode .badge.bg-light { color: #e4e6eb !important; }
    body.dark-mode #preloader { background: #1a1d23 !important; }
    /* Dark mode toggle button */
    .dark-mode-toggle { cursor: pointer; font-size: 1.3rem; padding: 8px 12px; border-radius: 8px; transition: all 0.3s ease; border: none; background: transparent; }
    .dark-mode-toggle:hover { background: rgba(102,126,234,0.15); }
    .dark-mode-toggle i { transition: transform 0.3s ease; }
    body.dark-mode .dark-mode-toggle { color: #ffc107 !important; }
    body:not(.dark-mode) .dark-mode-toggle { color: #495057 !important; }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- Main wrapper -->
    <div id="main-wrapper">
        
        <!--Nav header -->
        <div class="nav-header">
            <a href="<?= url('admin') ?>" class="brand-logo">
                <svg class="logo-abbr" width="39" height="31" viewBox="0 0 39 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M25.125 21.125L26.9952 23.2623C27.6771 24.0417 28.8616 24.1206 29.6409 23.4387C29.7036 23.3839 29.7625 23.325 29.8173 23.2623L31.6875 21.125H36.375C35.2848 26.5762 30.4985 30.5 24.9393 30.5H14.0607C8.5015 30.5 3.71523 26.5762 2.625 21.125H25.125Z" fill="var(--primary)"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M36.375 9.875H2.625C3.71523 4.4238 8.5015 0.5 14.0607 0.5H24.9393C30.4985 0.5 35.2848 4.4238 36.375 9.875Z" fill="var(--primary)"/>
                    <path opacity="0.3" d="M36.375 13.625H2.625C1.58947 13.625 0.75 14.4645 0.75 15.5C0.75 16.5355 1.58947 17.375 2.625 17.375H36.375C37.4105 17.375 38.25 16.5355 38.25 15.5C38.25 14.4645 37.4105 13.625 36.375 13.625Z" fill="var(--primary)"/>
                </svg>
                <span class="brand-title text-primary fs-24 fw-bold">SIGR</span>
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="container d-block my-0">
                        <div class="d-flex align-items-center justify-content-sm-between justify-content-end">
                            <div class="header-left">
                                <div class="nav-item d-flex align-items-center">
                                    <h4 class="mb-0 text-black font-w600"><?= e($pageTitle ?? 'Dashboard') ?></h4>
                                </div>
                            </div>
                            
                            <ul class="navbar-nav header-right">
                                <li class="nav-item">
                                    <button class="dark-mode-toggle" id="darkModeToggle" title="Mode sombre">
                                        <i class="bi bi-moon-fill"></i>
                                    </button>
                                </li>
                                <li>
                                    <div class="dropdown header-profile2">
                                        <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                                            <div class="header-info2 d-flex align-items-center">
                                                <img src="<?= url('public/assets/images/avatar/1.jpg') ?>" alt="">
                                                <div class="d-flex align-items-center sidebar-info">
                                                    <div>
                                                        <h6 class="font-w500 mb-0 ms-2"><?= e($userName) ?></h6>
                                                    </div>	
                                                    <i class="fas fa-chevron-down"></i>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="<?= url('admin/settings') ?>" class="dropdown-item ai-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                                <span class="ms-2">Paramètres</span>
                                            </a>
                                            <form action="<?= url('admin/logout') ?>" method="POST" class="d-inline w-100">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item ai-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                                    <span class="ms-2">Déconnexion</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="dlabnav border-right">
            <div class="dlabnav-scroll">
                <?php $staffRole = Session::getInstance()->getStaffRole(); ?>
                <p class="menu-title style-1">Menu Principal</p>
                <ul class="metismenu" id="menu">
                    <!-- Dashboard - visible pour tous -->
                    <li class="<?= ($currentPage ?? '') === 'dashboard' ? 'mm-active' : '' ?>">
                        <a href="<?= url('admin') ?>">
                            <i class="bi bi-grid"></i>
                            <span class="nav-text">Tableau de bord</span>
                        </a>
                    </li>
                    
                    <!-- Commandes - visible pour tous -->
                    <li class="<?= ($currentPage ?? '') === 'orders' ? 'mm-active' : '' ?>">
                        <a href="<?= url('admin/orders') ?>">
                            <i class="bi bi-bag-check"></i>
                            <span class="nav-text">Commandes</span>
                        </a>
                    </li>
                    
                    <?php if (in_array($staffRole, ['admin', 'manager'])): ?>
                    <!-- Restaurant (Menu/Catégories) - admin & manager -->
                    <li class="<?= in_array($currentPage ?? '', ['menu', 'categories']) ? 'mm-active' : '' ?>">
                        <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                            <i class="bi bi-shop-window"></i>
                            <span class="nav-text">Restaurant</span>
                        </a>
                        <ul aria-expanded="false">
                            <li><a href="<?= url('admin/menu') ?>" class="<?= ($currentPage ?? '') === 'menu' ? 'mm-active' : '' ?>">Menu</a></li>
                            <li><a href="<?= url('admin/categories') ?>" class="<?= ($currentPage ?? '') === 'categories' ? 'mm-active' : '' ?>">Catégories</a></li>
                        </ul>
                    </li>
                    
                    <!-- Stocks - admin & manager -->
                    <li class="<?= ($currentPage ?? '') === 'stock' ? 'mm-active' : '' ?>">
                        <a href="<?= url('admin/stock') ?>">
                            <i class="bi bi-box-seam"></i>
                            <span class="nav-text">Stocks</span>
                        </a>
                    </li>
                    
                    <!-- Tables & QR - admin & manager -->
                    <li class="<?= ($currentPage ?? '') === 'tables' ? 'mm-active' : '' ?>">
                        <a href="<?= url('admin/tables') ?>">
                            <i class="bi bi-qr-code"></i>
                            <span class="nav-text">Tables & QR</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($staffRole, ['admin', 'manager'])): ?>
                    <li class="menu-title">Autre</li>
                    
                    <!-- Statistiques - admin & manager -->
                    <li class="<?= ($currentPage ?? '') === 'stats' ? 'mm-active' : '' ?>">
                        <a href="<?= url('admin/stats') ?>">
                            <i class="bi bi-bar-chart"></i>
                            <span class="nav-text">Statistiques</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($staffRole === 'admin'): ?>
                    <!-- Utilisateurs - admin uniquement -->
                    <li class="<?= ($currentPage ?? '') === 'users' ? 'mm-active' : '' ?>">
                        <a href="<?= url('admin/users') ?>">
                            <i class="bi bi-people"></i>
                            <span class="nav-text">Utilisateurs</span>
                        </a>
                    </li>
                    
                    <!-- Paramètres - admin uniquement -->
                    <li class="<?= ($currentPage ?? '') === 'settings' ? 'mm-active' : '' ?>">
                        <a href="<?= url('admin/settings') ?>">
                            <i class="bi bi-gear"></i>
                            <span class="nav-text">Paramètres</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($staffRole, ['admin', 'manager', 'chef'])): ?>
                    <!-- Écran Cuisine - admin, manager & chef -->
                    <li class="<?= ($currentPage ?? '') === 'kitchen' ? 'mm-active' : '' ?>">
                        <a href="<?= url('kitchen') ?>" target="_blank">
                            <i class="bi bi-display"></i>
                            <span class="nav-text">Écran Cuisine</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Badge rôle -->
                <div class="text-center mt-4 px-3">
                    <?php
                    $roleLabels = [
                        'admin' => ['Admin', 'danger'],
                        'manager' => ['Gérant', 'primary'],
                        'chef' => ['Cuisinier', 'warning'],
                        'cashier' => ['Caissier', 'success'],
                        'waiter' => ['Serveur', 'info'],
                    ];
                    $roleInfo = $roleLabels[$staffRole] ?? ['Staff', 'secondary'];
                    ?>
                    <span class="badge bg-<?= $roleInfo[1] ?> px-3 py-2">
                        <i class="bi bi-person-badge me-1"></i><?= $roleInfo[0] ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Content body -->
        <div class="content-body" style="min-height: 882px;">
            <div class="container">
                
                <?php 
                // Messages flash
                $flashMessages = Session::getInstance()->getFlash();
                foreach ($flashMessages as $type => $messages): 
                    $alertClass = $type === 'error' ? 'danger' : ($type === 'success' ? 'success' : 'info');
                    foreach ($messages as $message): 
                ?>
                <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show">
                    <?= e($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endforeach; endforeach; ?>
                
                <!-- Page Content -->
                <?= $content ?? '' ?>
                
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="copyright">
                <p>Copyright © <?= date('Y') ?> <a href="<?= url('/') ?>" target="_blank">SIGR</a>. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= url('public/assets/vendor/global/global.min.js') ?>"></script>
    <script src="<?= url('public/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') ?>"></script>
    <script src="<?= url('public/assets/vendor/jquery-nice-select/js/jquery.nice-select.min.js') ?>"></script>
    <script src="<?= url('public/assets/vendor/swiper/js/swiper-bundle.min.js') ?>"></script>
    <script src="<?= url('public/assets/js/custom.js') ?>"></script>
    <script src="<?= url('public/assets/js/dlabnav-init.js') ?>"></script>
    
    <?= $scripts ?? '' ?>
    
    <!-- Dark Mode Toggle Script -->
    <script>
    (function() {
        const toggle = document.getElementById('darkModeToggle');
        const icon = toggle?.querySelector('i');
        const isDark = localStorage.getItem('sigr_dark_mode') === 'true';
        
        if (isDark) {
            document.body.classList.add('dark-mode');
            if (icon) { icon.className = 'bi bi-sun-fill'; }
        }
        
        toggle?.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const nowDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('sigr_dark_mode', nowDark);
            if (icon) {
                icon.className = nowDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                icon.style.transform = 'rotate(360deg)';
                setTimeout(() => icon.style.transform = '', 300);
            }
        });
    })();
    </script>
</body>
</html>
