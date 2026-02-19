<?php
/**
 * SIGR - Configuration Générale
 * 
 * Ce fichier contient toutes les constantes et paramètres
 * de configuration de l'application.
 */

// Mode debug (désactiver en production)
define('DEBUG_MODE', true);

// Chemins de base
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('LANG_PATH', ROOT_PATH . '/lang');

// URL de base (à adapter selon votre configuration)
define('BASE_URL', '/serve_smart');
define('ASSETS_URL', BASE_URL . '/public/assets');

// Configuration session client
define('SESSION_NAME', 'SIGR_SESSION');
define('SESSION_LIFETIME', 10800); // 3 heures en secondes
define('SESSION_COOKIE_SECURE', false); // true en production HTTPS
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Strict');

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
