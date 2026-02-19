<?php
/**
 * SIGR - Product Model
 */

namespace Models;

use Core\Database;

class ProductModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtenir tous les produits disponibles
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT p.*, c.name_fr as category_name_fr, c.name_en as category_name_en,
                    COALESCE(s.quantity, 999999) as stock_quantity
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN stock s ON p.id = s.product_id
             WHERE p.is_available = TRUE
             ORDER BY c.display_order, p.id"
        );
    }
    
    /**
     * Obtenir les produits par catégorie
     */
    public function getByCategory(int $categoryId): array
    {
        return $this->db->query(
            "SELECT p.*, COALESCE(s.quantity, 999999) as stock_quantity
             FROM products p
             LEFT JOIN stock s ON p.id = s.product_id
             WHERE p.category_id = ? AND p.is_available = TRUE
             ORDER BY p.id",
            [$categoryId]
        );
    }
    
    /**
     * Obtenir un produit par ID
     */
    public function getById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT p.*, c.name_fr as category_name_fr, c.name_en as category_name_en,
                    COALESCE(s.quantity, 999999) as stock_quantity,
                    s.low_threshold
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN stock s ON p.id = s.product_id
             WHERE p.id = ?",
            [$id]
        );
    }
    
    /**
     * Obtenir les produits mis en avant
     */
    public function getFeatured(int $limit = 6): array
    {
        return $this->db->query(
            "SELECT p.*, COALESCE(s.quantity, 999999) as stock_quantity
             FROM products p
             LEFT JOIN stock s ON p.id = s.product_id
             WHERE p.is_featured = TRUE AND p.is_available = TRUE
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Vérifier la disponibilité d'un produit
     */
    public function isAvailable(int $id, int $quantity = 1): bool
    {
        $product = $this->getById($id);
        
        if (!$product || !$product['is_available']) {
            return false;
        }
        
        // Si le produit nécessite une gestion de stock
        if ($product['requires_stock']) {
            return $product['stock_quantity'] >= $quantity;
        }
        
        return true;
    }
    
    /**
     * Rechercher des produits
     */
    public function search(string $query): array
    {
        $searchTerm = "%{$query}%";
        return $this->db->query(
            "SELECT p.*, COALESCE(s.quantity, 999999) as stock_quantity
             FROM products p
             LEFT JOIN stock s ON p.id = s.product_id
             WHERE p.is_available = TRUE 
               AND (p.name_fr LIKE ? OR p.name_en LIKE ? OR p.description_fr LIKE ? OR p.description_en LIKE ?)
             ORDER BY p.name_fr",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
        );
    }
    
    /**
     * Créer un produit
     */
    public function create(array $data): int
    {
        $productId = $this->db->insert('products', $data);
        
        // Si le produit nécessite un stock, créer l'entrée
        if (!empty($data['requires_stock'])) {
            $this->db->insert('stock', [
                'product_id' => $productId,
                'quantity' => 0,
                'low_threshold' => 10
            ]);
        }
        
        return $productId;
    }
    
    /**
     * Mettre à jour un produit
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update('products', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer un produit (soft delete)
     */
    public function delete(int $id): int
    {
        return $this->db->update('products', ['is_available' => false], 'id = ?', [$id]);
    }
}
