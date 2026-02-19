<?php
/**
 * SIGR Client Table Scan Page - FoodDesk Style
 */

use Core\Helpers;

$pageTitle = 'Scanner votre table';
$table = $table ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= e($pageTitle) ?> - SIGR</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        * { font-family: 'Poppins', sans-serif; }
        
        body {
            background: var(--gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .scan-card {
            background: white;
            border-radius: 30px;
            padding: 3rem;
            max-width: 450px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .table-icon {
            width: 120px;
            height: 120px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .table-number {
            font-size: 4rem;
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-start {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 1rem 3rem;
            font-size: 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-start:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .error-state {
            color: #dc3545;
        }
        
        .scan-icon {
            font-size: 4rem;
            color: white;
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
        
        <h2 class="mb-2">Bienvenue !</h2>
        <p class="text-muted mb-4">Vous êtes à la</p>
        
        <div class="table-number mb-4">Table <?= e($table['number']) ?></div>
        
        <p class="text-muted mb-4">
            Explorez notre menu et passez votre commande directement depuis votre téléphone.
        </p>
        
        <a href="<?= url('client/menu?table=' . $table['id']) ?>" class="btn btn-start">
            <i class="bi bi-grid me-2"></i>Voir le Menu
        </a>
        
        <?php else: ?>
        <!-- Erreur ou pas de table -->
        <div class="table-icon" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
            <i class="bi bi-exclamation-triangle scan-icon"></i>
        </div>
        
        <h2 class="error-state mb-3">Table non trouvée</h2>
        
        <p class="text-muted mb-4">
            Le QR code scanné n'est pas valide ou la table n'existe pas.
            <br>Veuillez demander de l'aide à un serveur.
        </p>
        
        <a href="<?= url('/') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-house me-2"></i>Retour à l'accueil
        </a>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
