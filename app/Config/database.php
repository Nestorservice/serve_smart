<?php
/**
 * SIGR - Configuration Base de Données
 * 
 * Ce fichier contient les paramètres de connexion MySQL.
 * À adapter selon votre environnement (développement/production).
 */

// Options PDO de base
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_STRINGIFY_FETCHES  => false,
    PDO::ATTR_TIMEOUT            => 10, // Timeout de connexion en secondes
];

// Support SSL pour les BDD cloud (Aiven, PlanetScale, etc.)
// Définir DB_SSL=true dans les variables d'environnement sur Render
if (getenv('DB_SSL') === 'true' || getenv('DB_SSL') === '1') {
    // Utiliser le bundle CA par défaut du système
    $caCertPath = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';
    if (file_exists($caCertPath)) {
        $pdoOptions[PDO::MYSQL_ATTR_SSL_CA] = $caCertPath;
    }
    $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

// Support DATABASE_URL (format: mysql://user:pass@host:port/dbname)
$dbUrl = getenv('DATABASE_URL');
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3306;
$dbName = getenv('DB_NAME') ?: 'sigr_restaurant';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

if ($dbUrl) {
    $parsed = parse_url($dbUrl);
    if ($parsed) {
        $dbHost = $parsed['host'] ?? $dbHost;
        $dbPort = $parsed['port'] ?? $dbPort;
        $dbName = ltrim($parsed['path'] ?? '', '/') ?: $dbName;
        $dbUser = $parsed['user'] ?? $dbUser;
        $dbPass = $parsed['pass'] ?? $dbPass;
    }
}

return [
    // Paramètres de connexion
    'host'     => $dbHost,
    'port'     => (int)$dbPort,
    'database' => $dbName,
    'username' => $dbUser,
    'password' => $dbPass,
    'charset'  => 'utf8mb4',
    
    // Options PDO
    'options' => $pdoOptions,
    
    // Préfixe des tables (vide par défaut)
    'prefix' => '',
];
