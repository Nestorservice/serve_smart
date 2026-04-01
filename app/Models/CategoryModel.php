<?php
/**
 * SIGR - Category Model
 */

namespace Models;

use Core\Database;

class CategoryModel extends \Core\Model
{
    
    /**
     * Obtenir toutes les catégories actives
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT * FROM categories WHERE is_active = TRUE ORDER BY display_order, id"
        );
    }
    
    /**
     * Obtenir une catégorie par ID
     */
    public function getById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM categories WHERE id = ? AND is_active = TRUE",
            [$id]
        );
    }
    
    /**
     * Obtenir les catégories avec le nombre de produits
     */
    public function getAllWithProductCount(): array
    {
        return $this->db->query(
            "SELECT c.*, COUNT(p.id) as product_count 
             FROM categories c 
             LEFT JOIN products p ON c.id = p.category_id AND p.is_available = TRUE
             WHERE c.is_active = TRUE 
             GROUP BY c.id 
             ORDER BY c.display_order, c.id"
        );
    }
    
    /**
     * Créer une catégorie
     */
    public function create(array $data): int
    {
        return $this->db->insert('categories', $data);
    }
    
    /**
     * Mettre à jour une catégorie
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update('categories', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer une catégorie (soft delete)
     */
    public function delete(int $id): int
    {
        return $this->db->update('categories', ['is_active' => false], 'id = ?', [$id]);
    }
}
