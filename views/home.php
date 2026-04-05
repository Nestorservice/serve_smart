<?php
/**
 * SIGR Home Page - FoodDesk Style
 */

use Core\Helpers;

$pageTitle = 'Welcome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= e($pageTitle) ?> - SIGR Restaurant</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: var(--gradient);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="60" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 100px 100px;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            from { background-position: 0 0; }
            to { background-position: 100px 100px; }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 5px 30px rgba(0,0,0,0.2);
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: rgba(255,255,255,0.9);
            font-weight: 300;
        }
        
        .btn-hero {
            padding: 1rem 3rem;
            font-size: 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .btn-hero-primary {
            background: white;
            color: var(--primary);
            border: none;
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            color: var(--secondary);
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-hero-outline:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-5px);
        }
        
        /* Features Section */
        .features-section {
            padding: 6rem 0;
            background: #f8f9fa;
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            transition: all 0.4s;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.2);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h4 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: #6c757d;
            line-height: 1.8;
        }
        
        /* How it works */
        .how-section {
            padding: 6rem 0;
            background: white;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin: 0 auto 1rem;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: var(--gradient);
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
        }
        
        .cta-section p {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
        }
        
        /* Footer */
        .footer {
            background: #1a1a2e;
            color: white;
            padding: 3rem 0;
        }
        
        .footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1rem;
            }
            .btn-hero {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 hero-content text-center text-lg-start">
                    <h1 class="hero-title mb-4">
                        <i class="bi bi-cup-hot-fill me-3"></i>SIGR Restaurant
                    </h1>
                    <p class="hero-subtitle mb-5">
                        Intelligent Restaurant Management System
                        <br>Order easily from your table
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="<?= url('client/menu') ?>" class="btn btn-hero btn-hero-primary">
                            <i class="bi bi-grid me-2"></i>View Menu
                        </a>
                        <a href="<?= url('admin/login') ?>" class="btn btn-hero btn-hero-outline">
                            <i class="bi bi-person-circle me-2"></i>Administration
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-center">
                    <div style="font-size: 15rem; opacity: 0.3;">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3">Why Choose SIGR?</h2>
                <p class="text-muted lead">A complete solution to modernize your restaurant</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <h4>QR Code Ordering</h4>
                        <p>Your customers scan the code on their table and order directly from their phone.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-display"></i>
                        </div>
                        <h4>Kitchen Screen</h4>
                        <p>Cooks see orders in real-time and can process them efficiently.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h4>Statistics</h4>
                        <p>Track your sales, identify your popular products and optimize your business.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h4>Stock Management</h4>
                        <p>Keep an eye on your stocks and receive alerts when they are low.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <h4>Real-time Tracking</h4>
                        <p>Customers can track their order progress live.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h4>100% Responsive</h4>
                        <p>Interface adapted to all devices: smartphones, tablets, computers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How it works -->
    <section class="how-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3">How it works?</h2>
                <p class="text-muted lead">3 simple steps to order</p>
            </div>
            
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="step-number">1</div>
                    <h5 class="fw-bold">Scan the QR Code</h5>
                    <p class="text-muted">Use your phone's camera to scan the code on your table.</p>
                </div>
                <div class="col-md-4">
                    <div class="step-number">2</div>
                    <h5 class="fw-bold">Choose your dishes</h5>
                    <p class="text-muted">Browse our menu and add your favorite dishes to the cart.</p>
                </div>
                <div class="col-md-4">
                    <div class="step-number">3</div>
                    <h5 class="fw-bold">Confirm and enjoy</h5>
                    <p class="text-muted">Confirm your order and follow its preparation in real-time.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to order?</h2>
            <p>Discover our delicious dishes prepared with love</p>
            <a href="<?= url('client/menu') ?>" class="btn btn-hero btn-hero-primary">
                <i class="bi bi-grid me-2"></i>View Menu
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <h5 class="mb-1"><i class="bi bi-cup-hot-fill me-2"></i>SIGR Restaurant</h5>
                    <small class="opacity-75">© <?= date('Y') ?> All rights reserved</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?= url('admin/login') ?>" class="me-3"><i class="bi bi-person-circle me-1"></i>Admin</a>
                    <a href="<?= url('kitchen') ?>" class="me-3"><i class="bi bi-display me-1"></i>Kitchen</a>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
