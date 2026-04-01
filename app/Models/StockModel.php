<?php
/**
 * SIGR - Stock Model
 */

namespace Models;

use Core\Database;

class StockModel extends \Core\Model
{
    
    /**
     * Obtenir le stock d'un produit
     */
    public function getByProductId(int $productId): ?array
    {
        return $this->db->queryOne(
            "SELECT s.*, p.name_fr, p.name_en
             FROM stock s
             JOIN products p ON s.product_id = p.id
             WHERE s.product_id = ?",
            [$productId]
        );
    }
    
    /**
     * Obtenir tous les stocks
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT s.*, p.name_fr, p.name_en, p.price, p.is_available
             FROM stock s
             JOIN products p ON s.product_id = p.id
             ORDER BY p.name_fr"
        );
    }
    
    /**
     * Obtenir les produits avec stock bas
     */
    public function getLowStock(): array
    {
        return $this->db->query(
            "SELECT s.*, p.name_fr, p.name_en, p.image_url
             FROM stock s
             JOIN products p ON s.product_id = p.id
             WHERE s.quantity <= s.low_threshold AND p.is_available = TRUE
             ORDER BY s.quantity ASC"
        );
    }
    
    /**
     * Mettre à jour la quantité en stock
     */
    public function updateQuantity(int $productId, int $quantity, string $reason = 'adjustment', ?int $userId = null, ?string $notes = null): bool
    {
        return $this->db->transaction(function(Database $db) use ($productId, $quantity, $reason, $userId, $notes) {
            // Obtenir le stock actuel avec verrouillage
            $stock = $db->queryOne(
                "SELECT quantity FROM stock WHERE product_id = ? FOR UPDATE",
                [$productId]
            );
            
            if (!$stock) {
                return false;
            }
            
            $quantityBefore = $stock['quantity'];
            $quantityChange = $quantity - $quantityBefore;
            
            // Mettre à jour le stock
            $db->execute(
                "UPDATE stock SET quantity = ?, last_restock_at = NOW() WHERE product_id = ?",
                [$quantity, $productId]
            );
            
            // Enregistrer l'historique
            $db->insert('stock_history', [
                'product_id' => $productId,
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantity,
                'reason' => $reason,
                'user_id' => $userId,
                'notes' => $notes
            ]);
            
            return true;
        });
    }
    
    /**
     * Ajouter du stock
     */
    public function addStock(int $productId, int $quantity, ?int $userId = null, ?string $notes = null): bool
    {
        return $this->db->transaction(function(Database $db) use ($productId, $quantity, $userId, $notes) {
            $stock = $db->queryOne(
                "SELECT quantity FROM stock WHERE product_id = ? FOR UPDATE",
                [$productId]
            );
            
            if (!$stock) {
                return false;
            }
            
            $newQuantity = $stock['quantity'] + $quantity;
            
            $db->execute(
                "UPDATE stock SET quantity = ?, last_restock_at = NOW() WHERE product_id = ?",
                [$newQuantity, $productId]
            );
            
            $db->insert('stock_history', [
                'product_id' => $productId,
                'quantity_change' => $quantity,
                'quantity_before' => $stock['quantity'],
                'quantity_after' => $newQuantity,
                'reason' => 'restock',
                'user_id' => $userId,
                'notes' => $notes
            ]);
            
            return true;
        });
    }
    
    /**
     * Définir le seuil d'alerte
     */
    public function setThreshold(int $productId, int $threshold): int
    {
        return $this->db->update('stock', ['low_threshold' => $threshold], 'product_id = ?', [$productId]);
    }
    
    /**
     * Obtenir l'historique de stock d'un produit
     */
    public function getHistory(int $productId, int $limit = 50): array
    {
        return $this->db->query(
            "SELECT sh.*, u.full_name as user_name
             FROM stock_history sh
             LEFT JOIN users u ON sh.user_id = u.id
             WHERE sh.product_id = ?
             ORDER BY sh.created_at DESC
             LIMIT ?",
            [$productId, $limit]
        );
    }
    
    /**
     * Nombre de produits en alerte stock bas
     */
    public function countLowStock(): int
    {
        return (int) $this->db->queryValue(
            "SELECT COUNT(*) FROM stock s
             JOIN products p ON s.product_id = p.id
             WHERE s.quantity <= s.low_threshold AND p.is_available = TRUE"
        );
    }
}
