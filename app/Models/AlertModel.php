<?php
/**
 * SIGR - Alert Model
 */

namespace Models;

class AlertModel extends \Core\Model
{
    /**
     * Créer une alerte
     */
    public function create(array $data): int
    {
        return $this->db->insert('alerts', $data);
    }
    
    /**
     * Obtenir les alertes non lues
     */
    public function getUnread(): array
    {
        return $this->db->query(
            "SELECT * FROM alerts 
             WHERE is_read = FALSE 
             ORDER BY 
               CASE priority 
                 WHEN 'critical' THEN 1 
                 WHEN 'high' THEN 2 
                 WHEN 'medium' THEN 3 
                 WHEN 'low' THEN 4 
               END ASC, created_at DESC"
        );
    }
    
    /**
     * Marquer comme lue
     */
    public function markAsRead(int $alertId, int $userId): int
    {
        return $this->db->update(
            'alerts', 
            ['is_read' => true, 'read_by' => $userId, 'read_at' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$alertId]
        );
    }
    
    /**
     * Compter les alertes
     */
    public function countUnread(): int
    {
        $result = $this->db->queryOne("SELECT count(*) as total FROM alerts WHERE is_read = FALSE");
        return (int) $result['total'];
    }
}
