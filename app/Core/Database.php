<?php
/**
 * SIGR - Database Singleton
 * 
 * Classe de gestion de la connexion PDO avec support
 * des transactions pour la gestion des stocks.
 */

namespace Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    private bool $inTransaction = false;
    
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $config = require APP_PATH . '/Config/database.php';
        
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        
        try {
            $this->pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new Exception('Erreur de connexion à la base de données: ' . $e->getMessage());
            }
            throw new Exception('Erreur de connexion à la base de données');
        }
    }
    
    /**
     * Empêcher le clonage (Singleton)
     */
    private function __clone() {}
    
    /**
     * Obtenir l'instance unique de Database
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir l'objet PDO directement
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
    
    /**
     * Exécuter une requête préparée (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql Requête SQL avec placeholders
     * @param array $params Paramètres à binder
     * @return int Nombre de lignes affectées
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Exécuter une requête SELECT et retourner tous les résultats
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return array Tableau de résultats
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Exécuter une requête SELECT et retourner une seule ligne
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return array|null Résultat ou null
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Retourner une seule valeur (première colonne de la première ligne)
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return mixed Valeur ou null
     */
    public function queryValue(string $sql, array $params = []): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insérer une ligne et retourner l'ID généré
     * 
     * @param string $table Nom de la table
     * @param array $data Données à insérer (colonne => valeur)
     * @return int ID de la ligne insérée
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->execute($sql, array_values($data));
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Mettre à jour des lignes
     * 
     * @param string $table Nom de la table
     * @param array $data Données à modifier
     * @param string $where Condition WHERE
     * @param array $whereParams Paramètres du WHERE
     * @return int Nombre de lignes modifiées
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Supprimer des lignes
     * 
     * @param string $table Nom de la table
     * @param string $where Condition WHERE
     * @param array $params Paramètres
     * @return int Nombre de lignes supprimées
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->execute($sql, $params);
    }
    
    /**
     * Démarrer une transaction
     */
    public function beginTransaction(): bool
    {
        if ($this->inTransaction) {
            return false;
        }
        $this->inTransaction = $this->pdo->beginTransaction();
        return $this->inTransaction;
    }
    
    /**
     * Valider une transaction
     */
    public function commit(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        $result = $this->pdo->commit();
        $this->inTransaction = false;
        return $result;
    }
    
    /**
     * Annuler une transaction
     */
    public function rollback(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        $result = $this->pdo->rollBack();
        $this->inTransaction = false;
        return $result;
    }
    
    /**
     * Vérifier si une transaction est en cours
     */
    public function isInTransaction(): bool
    {
        return $this->inTransaction;
    }
    
    /**
     * Exécuter du code dans une transaction
     * 
     * @param callable $callback Fonction à exécuter
     * @return mixed Résultat du callback
     * @throws Exception En cas d'erreur, rollback automatique
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Échapper un identifiant SQL (nom de table/colonne)
     */
    public function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
