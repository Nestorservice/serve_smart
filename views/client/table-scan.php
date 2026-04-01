<?php
/**
 * SIGR Client Table Scan Page - Luxury Style
 */

use Core\Helpers;

$pageTitle = 'Bienvenue à notre Table';
$table = $table ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= e($pageTitle) ?> - Gastronomie</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts: Luxury Theme -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;1,400&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #D4AF37;
            --primary-dark: #AA8B2B;
            --bg: #111111;
            --card-bg: #1A1A1A;
            --text: #FDFBF7;
            --text-light: #A0A0A0;
            --border-light: rgba(212, 175, 55, 0.2);
            --gradient: linear-gradient(135deg, var(--bg) 0%, #000000 100%);
        }
        
        * { font-family: 'Lato', sans-serif; }
        h1, h2, h3, h4, .table-number { font-family: 'Playfair Display', serif; }
        
        body {
            background: var(--gradient);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .scan-card {
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: 4px;
            padding: 4rem 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 16px 48px rgba(0,0,0,0.6);
        }
        
        .table-icon {
            width: 100px;
            height: 100px;
            border: 1px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2.5rem;
            color: var(--primary);
            animation: pulse 3s infinite ease-in-out;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.4); }
            50% { box-shadow: 0 0 0 20px rgba(212, 175, 55, 0); }
        }
        
        .table-number {
            font-size: 3.5rem;
            font-weight: 400;
            color: var(--primary);
            margin-bottom: 2rem;
            font-style: italic;
        }
        
        .btn-start {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 1rem 3rem;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 300;
            transition: all 0.5s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-start:hover {
            background: var(--primary);
            color: #000;
        }
        
        .error-state {
            color: #dc3545;
            font-style: italic;
        }
        
        .scan-icon {
            font-size: 3rem;
        }
        
        .welcome-text {
            color: var(--primary);
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 300;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="scan-card">
        <?php if ($table): ?>
        <!-- Table trouvée -->
        <div class="table-icon">
            <i class="bi bi-qr-code-scan scan-icon"></i>
        </div>
        
        <h2 class="welcome-text">Bienvenue</h2>
        <p class="subtitle mb-4">Vous êtes confortablement installé à la</p>
        
        <div class="table-number">Table <?= e($table['number']) ?></div>
        
        <p class="text-muted mb-5" style="font-weight: 300; line-height: 1.6;">
            Découvrez nos créations culinaires et commandez en toute sérénité.
        </p>
        
        <a href="<?= url('client/menu?table=' . $table['id']) ?>" class="btn-start">
            Découvrir la Carte
        </a>
        
        <?php else: ?>
        <!-- Erreur ou pas de table -->
        <div class="table-icon" style="border-color: #dc3545; color: #dc3545; animation: none;">
            <i class="bi bi-exclamation-triangle scan-icon"></i>
        </div>
        
        <h2 class="error-state mb-3">Table Introuvable</h2>
        
        <p class="text-muted mb-5" style="font-weight: 300;">
            Le code scanné n'est malheureusement pas valide.
            <br>Notre équipe se tient à votre disposition.
        </p>
        
        <a href="<?= url('/') ?>" class="btn btn-outline-light" style="padding: 0.8rem 2rem; border-radius: 0; text-transform: uppercase; letter-spacing: 1px;">
            Retourner à l'accueil
        </a>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
