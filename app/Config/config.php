<?php
/**
 * SIGR - Configuration Générale
 * 
 * Ce fichier contient toutes les constantes et paramètres
 * de configuration de l'application.
 */

// Mode debug (désactiver en production via APP_ENV=production)
$isProduction = getenv('APP_ENV') === 'production';
define('DEBUG_MODE', !$isProduction);

// Chemins de base
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('LANG_PATH', ROOT_PATH . '/lang');

// URL de base dynamique
// Si APP_URL est défini, on l'utilise, sinon on détecte si on est à la racine ou dans /serve_smart
if (getenv('APP_URL')) {
    define('BASE_URL', rtrim(getenv('APP_URL'), '/'));
} else {
    // Détection auto du dossier (utile pour XAMPP vs Render)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseDir = str_replace(['/public/index.php', '/index.php'], '', $scriptName);
    define('BASE_URL', $baseDir ?: '');
}

define('ASSETS_URL', BASE_URL . '/public/assets');

// Configuration session client
define('SESSION_NAME', 'SIGR_SESSION');
define('SESSION_LIFETIME', 10800); // 3 heures en secondes

// Détection HTTPS pour les cookies sécurisés
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

define('SESSION_COOKIE_SECURE', $isProduction || $isHttps);
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Lax');

// Configuration sécurité
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);

// Configuration Long Polling
define('POLLING_TIMEOUT', 30); // secondes
define('POLLING_INTERVAL', 500000); // microsecondes (0.5s)

// Langues supportées
define('SUPPORTED_LANGUAGES', ['fr', 'en']);
define('DEFAULT_LANGUAGE', 'fr');

// Configuration upload images
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/uploads');

// Timezone
date_default_timezone_set('Africa/Douala');

// Erreur handling
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Autoloader simple pour les classes
spl_autoload_register(function ($class) {
    // Convertir namespace en chemin de fichier
    $classPath = str_replace('\\', '/', $class);
    $file = APP_PATH . '/' . $classPath . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    // Essayer aussi sans le préfixe App
    $file = APP_PATH . '/' . str_replace('App/', '', $classPath) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});
