<?php
/**
 * SIGR - Order Item Model
 */

namespace Models;

class OrderItemModel extends \Core\Model
{
    /**
     * Récupérer les items d'une commande
     */
    public function getByOrderId(int $orderId): array
    {
        return $this->db->query(
            "SELECT oi.*, p.name_fr, p.name_en, p.preparation_time 
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             ORDER BY oi.id ASC",
            [$orderId]
        );
    }
    
    /**
     * Mettre à jour le statut d'un item
     */
    public function updateStatus(int $itemId, string $status): int
    {
        return $this->db->update('order_items', ['status' => $status], 'id = ?', [$itemId]);
    }
    
    /**
     * Obtenir les items en cuisine (non terminés)
     */
    public function getKitchenItems(): array
    {
        return $this->db->query(
            "SELECT oi.*, p.name_fr, p.name_en, 
                    o.order_number, o.created_at as order_time,
                    t.table_number
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             JOIN orders o ON oi.order_id = o.id
             JOIN restaurant_tables t ON o.table_id = t.id
             WHERE oi.status IN ('pending', 'preparing')
               AND o.status IN ('confirmed', 'preparing')
             ORDER BY o.created_at ASC"
        );
    }
}
