<?php
/**
 * SIGR - Table Session Model
 */

namespace Models;

class TableSessionModel extends \Core\Model
{
    /**
     * Trouver une session active par son token
     */
    public function getActiveByToken(string $token): ?array
    {
        return $this->db->queryOne(
            "SELECT ts.*, t.table_number, t.capacity 
             FROM table_sessions ts
             JOIN restaurant_tables t ON ts.table_id = t.id
             WHERE ts.session_token = ? 
               AND ts.is_active = TRUE",
            [$token]
        );
    }

    /**
     * Créer une nouvelle session
     */
    public function create(array $data): int
    {
        return $this->db->insert('table_sessions', $data);
    }

    /**
     * Terminer (désactiver) toutes les sessions d'une table
     */
    public function endAllForTable(int $tableId): int
    {
        return $this->db->update(
            'table_sessions', 
            ['is_active' => false], 
            'table_id = ? AND is_active = TRUE', 
            [$tableId]
        );
    }
    
    /**
     * Mettre à jour l'activité d'une session
     */
    public function updateActivity(int $sessionId): int
    {
        return $this->db->execute(
            "UPDATE table_sessions SET last_activity = NOW() WHERE id = ?",
            [$sessionId]
        );
    }
}
