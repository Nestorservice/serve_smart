<?php
/**
 * SIGR - Stock History Model
 */

namespace Models;

class StockHistoryModel extends \Core\Model
{
    /**
     * Enregistrer un mouvement de stock
     */
    public function log(int $productId, int $qtyChange, string $reason, ?int $orderId = null, ?int $userId = null, ?string $notes = null): int
    {
        // Obtenir quantité courante
        $currentStock = $this->db->queryOne("SELECT quantity FROM stock WHERE product_id = ?", [$productId]);
        $qtyBefore = $currentStock ? (int)$currentStock['quantity'] : 0;
        $qtyAfter = $qtyBefore + ($qtyChange);

        return $this->db->insert('stock_history', [
            'product_id' => $productId,
            'quantity_change' => $qtyChange,
            'quantity_before' => $qtyBefore,
            'quantity_after' => $qtyAfter,
            'reason' => $reason,
            'order_id' => $orderId,
            'user_id' => $userId,
            'notes' => $notes
        ]);
    }
    
    /**
     * Obtenir l'historique d'un produit
     */
    public function getByProductId(int $productId): array
    {
        return $this->db->query(
            "SELECT sh.*, u.full_name, o.order_number 
             FROM stock_history sh
             LEFT JOIN users u ON sh.user_id = u.id
             LEFT JOIN orders o ON sh.order_id = o.id
             WHERE sh.product_id = ?
             ORDER BY sh.created_at DESC",
            [$productId]
        );
    }
    
    /**
     * Obtenir l'historique récent complet
     */
    public function getRecentHistory(int $limit = 50): array
    {
        return $this->db->query(
            "SELECT sh.*, p.name_fr, p.name_en, u.full_name as author, o.order_number
             FROM stock_history sh
             JOIN products p ON sh.product_id = p.id
             LEFT JOIN users u ON sh.user_id = u.id
             LEFT JOIN orders o ON sh.order_id = o.id
             ORDER BY sh.created_at DESC LIMIT ?",
            [$limit]
        );
    }
}
