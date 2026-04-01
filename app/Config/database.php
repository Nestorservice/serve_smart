<?php
/**
 * SIGR - Configuration Base de Données
 * 
 * Ce fichier contient les paramètres de connexion MySQL.
 * À adapter selon votre environnement (développement/production).
 */

return [
    // Paramètres de connexion (Priorité aux Variables d'Environnement)
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3306,
    'database' => getenv('DB_NAME') ?: 'sigr_restaurant',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'charset'  => 'utf8mb4',
    
    // Options PDO
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ],
    
    // Préfixe des tables (vide par défaut)
    'prefix' => '',
];
