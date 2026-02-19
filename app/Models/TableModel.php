<?php
/**
 * SIGR - Table Model
 */

namespace Models;

use Core\Database;

class TableModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtenir toutes les tables
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT * FROM restaurant_tables WHERE is_active = TRUE ORDER BY table_number"
        );
    }
    
    /**
     * Obtenir une table par ID
     */
    public function getById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM restaurant_tables WHERE id = ? AND is_active = TRUE",
            [$id]
        );
    }
    
    /**
     * Obtenir une table par numéro
     */
    public function getByNumber(string $number): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM restaurant_tables WHERE table_number = ? AND is_active = TRUE",
            [$number]
        );
    }
    
    /**
     * Obtenir une table par token QR
     */
    public function getByToken(string $token): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM restaurant_tables WHERE qr_code_token = ? AND is_active = TRUE",
            [$token]
        );
    }
    
    /**
     * Créer une nouvelle table
     */
    public function create(array $data): int
    {
        // Générer un token QR unique
        $data['qr_code_token'] = strtoupper(hash('sha256', uniqid('table_', true) . random_bytes(16)));
        
        return $this->db->insert('restaurant_tables', $data);
    }
    
    /**
     * Mettre à jour une table
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update('restaurant_tables', $data, 'id = ?', [$id]);
    }
    
    /**
     * Régénérer le token QR d'une table
     */
    public function regenerateToken(int $id): string
    {
        $newToken = strtoupper(hash('sha256', uniqid('table_', true) . random_bytes(16)));
        $this->db->update('restaurant_tables', ['qr_code_token' => $newToken], 'id = ?', [$id]);
        return $newToken;
    }
    
    /**
     * Supprimer une table (soft delete)
     */
    public function delete(int $id): int
    {
        return $this->db->update('restaurant_tables', ['is_active' => false], 'id = ?', [$id]);
    }
    
    /**
     * Obtenir les tables avec leurs commandes actives
     */
    public function getWithActiveOrders(): array
    {
        return $this->db->query(
            "SELECT t.*, 
                    COUNT(o.id) as active_orders,
                    SUM(CASE WHEN o.payment_status = 'unpaid' THEN o.total_amount ELSE 0 END) as pending_amount
             FROM restaurant_tables t
             LEFT JOIN orders o ON t.id = o.table_id 
                 AND o.status NOT IN ('paid', 'cancelled')
             WHERE t.is_active = TRUE
             GROUP BY t.id
             ORDER BY t.table_number"
        );
    }
    
    /**
     * Générer l'URL du QR code pour une table
     */
    public function getQRCodeUrl(int $tableId): string
    {
        $table = $this->getById($tableId);
        if (!$table) {
            return '';
        }
        
        // URL de l'application
        $baseUrl = ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://';
        $baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl .= BASE_URL . '/table/' . $table['table_number'];
        
        return $baseUrl;
    }
}
