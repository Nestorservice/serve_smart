<?php
/**
 * SIGR - Configuration Base de Données
 * 
 * Ce fichier contient les paramètres de connexion MySQL.
 * À adapter selon votre environnement (développement/production).
 */

return [
    // Paramètres de connexion
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'sigr_restaurant',
    'username' => 'root',
    'password' => '',
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
