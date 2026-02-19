<?php
/**
 * SIGR - User Model
 */

namespace Models;

use Core\Database;
use Core\Helpers;

class UserModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtenir un utilisateur par ID
     */
    public function getById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM users WHERE id = ? AND is_active = TRUE",
            [$id]
        );
    }
    
    /**
     * Obtenir un utilisateur par username
     */
    public function getByUsername(string $username): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM users WHERE username = ? AND is_active = TRUE",
            [$username]
        );
    }
    
    /**
     * Obtenir un utilisateur par email
     */
    public function getByEmail(string $email): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM users WHERE email = ? AND is_active = TRUE",
            [$email]
        );
    }
    
    /**
     * Obtenir tous les utilisateurs
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT id, username, email, role, full_name, is_active, last_login, created_at 
             FROM users 
             ORDER BY role, full_name"
        );
    }
    
    /**
     * Obtenir les utilisateurs par rôle
     */
    public function getByRole(string $role): array
    {
        return $this->db->query(
            "SELECT id, username, email, role, full_name, is_active, last_login 
             FROM users 
             WHERE role = ? AND is_active = TRUE 
             ORDER BY full_name",
            [$role]
        );
    }
    
    /**
     * Créer un utilisateur
     */
    public function create(array $data): int
    {
        // Hash du mot de passe
        if (isset($data['password'])) {
            $data['password_hash'] = Helpers::hashPassword($data['password']);
            unset($data['password']);
        }
        
        return $this->db->insert('users', $data);
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update(int $id, array $data): int
    {
        // Hash du mot de passe si modifié
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = Helpers::hashPassword($data['password']);
        }
        unset($data['password']);
        
        return $this->db->update('users', $data, 'id = ?', [$id]);
    }
    
    /**
     * Désactiver un utilisateur
     */
    public function deactivate(int $id): int
    {
        return $this->db->update('users', ['is_active' => false], 'id = ?', [$id]);
    }
    
    /**
     * Vérifier les identifiants
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->getByUsername($username);
        
        if (!$user) {
            return null;
        }
        
        if (!Helpers::verifyPassword($password, $user['password_hash'])) {
            return null;
        }
        
        // Mettre à jour last_login
        $this->db->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );
        
        return $user;
    }
    
    /**
     * Compter les utilisateurs par rôle
     */
    public function countByRole(): array
    {
        return $this->db->query(
            "SELECT role, COUNT(*) as count 
             FROM users 
             WHERE is_active = TRUE 
             GROUP BY role"
        );
    }
}
