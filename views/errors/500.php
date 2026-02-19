<?php
/**
 * SIGR 500 Error Page - FoodDesk Style
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Erreur serveur - SIGR</title>
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
        
        * { font-family: 'Poppins', sans-serif; }
        
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            padding: 2rem;
        }
        
        .error-code {
            font-size: 10rem;
            font-weight: 800;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
            animation: shake 0.5s infinite;
        }
        
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }
        
        .btn-home {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-retry {
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            background: transparent;
            transition: all 0.3s;
        }
        
        .btn-retry:hover {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="bi bi-exclamation-triangle error-icon d-block"></i>
        <div class="error-code">500</div>
        <h2 class="mb-3">Erreur serveur</h2>
        <p class="text-muted mb-4">
            Une erreur inattendue s'est produite. Notre équipe a été notifiée.
            <br>Veuillez réessayer dans quelques instants.
        </p>
        <div class="d-flex gap-3 justify-content-center">
            <button onclick="location.reload()" class="btn btn-retry">
                <i class="bi bi-arrow-clockwise me-2"></i>Réessayer
            </button>
            <a href="<?= url('/') ?>" class="btn btn-home">
                <i class="bi bi-house me-2"></i>Accueil
            </a>
        </div>
    </div>
</body>
</html>
