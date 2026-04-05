<?php
/**
 * SIGR - Order Model
 * 
 * Gère les commandes avec logique de transactions pour le stock.
 */

namespace Models;

use Core\Database;
use Core\Helpers;
use Exception;

class OrderModel extends \Core\Model
{
    
    /**
     * Créer une nouvelle commande
     */
    public function create(int $tableId, int $sessionId, array $items, ?string $notes = null): int
    {
        return $this->db->transaction(function(Database $db) use ($tableId, $sessionId, $items, $notes) {
            // Générer le numéro de commande
            $orderNumber = Helpers::generateOrderNumber();
            
            // Calculer les totaux
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            // Obtenir le taux de TVA
            $taxRate = Helpers::getSetting('tax_rate', 0);
            $taxAmount = $subtotal * ($taxRate / 100);
            $total = $subtotal + $taxAmount;
            
            // Créer la commande
            $orderId = $db->insert('orders', [
                'order_number' => $orderNumber,
                'table_id' => $tableId,
                'table_session_id' => $sessionId,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
                'notes' => $notes
            ]);
            
            // Ajouter les items
            foreach ($items as $item) {
                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'special_instructions' => $item['instructions'] ?? null
                ]);
            }
            
            // Créer une alerte pour la cuisine
            $db->insert('alerts', [
                'type' => 'new_order',
                'priority' => 'high',
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'title' => "Nouvelle commande #{$orderNumber}",
                'message' => "Commande pour la table {$tableId}"
            ]);
            
            return $orderId;
        });
    }
    
    /**
     * Confirmer une commande et décrémenter le stock
     */
    public function confirm(int $orderId): bool
    {
        return $this->db->transaction(function(Database $db) use ($orderId) {
            // Récupérer les items avec verrouillage pour éviter les conflits
            $items = $db->query(
                "SELECT oi.*, p.requires_stock, s.quantity as stock_qty
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.id
                 LEFT JOIN stock s ON p.id = s.product_id
                 WHERE oi.order_id = ?
                 FOR UPDATE",
                [$orderId]
            );
            
            // Vérifier que le stock est suffisant pour tous les items
            foreach ($items as $item) {
                if ($item['requires_stock'] && $item['stock_qty'] < $item['quantity']) {
                    throw new Exception("Stock insuffisant pour le produit #{$item['product_id']}");
                }
            }
            
            // Décrémenter le stock
            foreach ($items as $item) {
                if ($item['requires_stock']) {
                    $quantityBefore = $item['stock_qty'];
                    $quantityAfter = $quantityBefore - $item['quantity'];
                    
                    $db->execute(
                        "UPDATE stock SET quantity = ? WHERE product_id = ?",
                        [$quantityAfter, $item['product_id']]
                    );
                    
                    // Historique du mouvement de stock
                    $db->insert('stock_history', [
                        'product_id' => $item['product_id'],
                        'quantity_change' => -$item['quantity'],
                        'quantity_before' => $quantityBefore,
                        'quantity_after' => $quantityAfter,
                        'reason' => 'sale',
                        'order_id' => $orderId
                    ]);
                    
                    // Vérifier si alerte stock bas nécessaire
                    $stock = $db->queryOne(
                        "SELECT quantity, low_threshold FROM stock WHERE product_id = ?",
                        [$item['product_id']]
                    );
                    
                    if ($stock && $stock['quantity'] <= $stock['low_threshold']) {
                        $product = $db->queryOne(
                            "SELECT name_fr FROM products WHERE id = ?",
                            [$item['product_id']]
                        );
                        
                        $db->insert('alerts', [
                            'type' => 'low_stock',
                            'priority' => 'medium',
                            'reference_type' => 'product',
                            'reference_id' => $item['product_id'],
                            'title' => "Stock bas: {$product['name_fr']}",
                            'message' => "Reste seulement {$stock['quantity']} unité(s)"
                        ]);
                    }
                }
            }
            
            // Mettre à jour le statut de la commande
            $db->execute(
                "UPDATE orders SET status = 'confirmed', confirmed_at = NOW() WHERE id = ?",
                [$orderId]
            );
            
            return true;
        });
    }
    
    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $data = ['status' => $status];
        
        // Ajouter les timestamps appropriés
        switch ($status) {
            case 'confirmed':
                $data['confirmed_at'] = date('Y-m-d H:i:s');
                break;
            case 'ready':
                $data['ready_at'] = date('Y-m-d H:i:s');
                break;
            case 'served':
                $data['served_at'] = date('Y-m-d H:i:s');
                break;
            case 'paid':
                $data['paid_at'] = date('Y-m-d H:i:s');
                $data['payment_status'] = 'paid';
                break;
        }
        
        return $this->db->update('orders', $data, 'id = ?', [$orderId]) > 0;
    }
    
    /**
     * Obtenir une commande par ID
     */
    public function getById(int $id): ?array
    {
        $order = $this->db->queryOne(
            "SELECT o.*, rt.table_number 
             FROM orders o
             JOIN restaurant_tables rt ON o.table_id = rt.id
             WHERE o.id = ?",
            [$id]
        );
        
        if ($order) {
            $order['items'] = $this->getOrderItems($id);
        }
        
        return $order;
    }
    
    /**
     * Obtenir une commande par numéro
     */
    public function getByNumber(string $orderNumber): ?array
    {
        $order = $this->db->queryOne(
            "SELECT o.*, rt.table_number 
             FROM orders o
             JOIN restaurant_tables rt ON o.table_id = rt.id
             WHERE o.order_number = ?",
            [$orderNumber]
        );
        
        if ($order) {
            $order['items'] = $this->getOrderItems($order['id']);
        }
        return $order;
    }

    /**
     * Obtenir les items d'une commande
     */
    public function getOrderItems(int $orderId): array
    {
        return $this->db->query(
            "SELECT oi.*, p.name_fr, p.name_en, p.image_url
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$orderId]
        );
    }
    
    /**
     * Obtenir les commandes d'une session table
     */
    public function getBySession(int $sessionId): array
    {
        return $this->db->query(
            "SELECT * FROM orders WHERE table_session_id = ? ORDER BY created_at DESC",
            [$sessionId]
        );
    }

    /**
     * Obtenir les commandes pour la cuisine
     */
    public function getForKitchen(): array
    {
        return $this->db->query(
            "SELECT o.*, rt.table_number
             FROM orders o
             JOIN restaurant_tables rt ON o.table_id = rt.id
             WHERE o.status IN ('pending', 'confirmed', 'preparing', 'ready')
             ORDER BY o.created_at ASC"
        );
    }

    /**
     * Obtenir les nouvelles commandes depuis un timestamp
     */
    public function getNewOrdersSince(int $timestamp): array
    {
        $datetime = date('Y-m-d H:i:s', $timestamp);
        
        return $this->db->query(
            "SELECT o.*, rt.table_number
             FROM orders o
             JOIN restaurant_tables rt ON o.table_id = rt.id
             WHERE o.status = 'confirmed' AND o.confirmed_at > ?
             ORDER BY o.confirmed_at ASC",
            [$datetime]
        );
    }

    /**
     * Obtenir les commandes en attente de paiement
     */
    public function getPendingPayments(): array
    {
        return $this->db->query(
            "SELECT o.*, rt.table_number
             FROM orders o
             JOIN restaurant_tables rt ON o.table_id = rt.id
             WHERE o.payment_status = 'unpaid' AND o.status NOT IN ('cancelled')
             ORDER BY o.created_at DESC"
        );
    }

    /**
     * Statistiques des ventes
     */
    public function getSalesStats(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-m-d');
        $endDate = $endDate ?? date('Y-m-d');
        
        return $this->db->queryOne(
            "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as average_order,
                SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_sales
             FROM orders
             WHERE DATE(created_at) BETWEEN ? AND ?
               AND status NOT IN ('cancelled')",
            [$startDate, $endDate]
        );
    }
    
    /**
     * Top produits vendus
     */
    public function getTopProducts(int $limit = 5, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?? date('Y-m-d');
        
        return $this->db->query(
            "SELECT p.id, p.name_fr, p.name_en, p.image_url,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.total_price) as total_revenue
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             JOIN orders o ON oi.order_id = o.id
             WHERE DATE(o.created_at) BETWEEN ? AND ?
               AND o.status NOT IN ('cancelled')
             GROUP BY p.id
             ORDER BY total_sold DESC
             LIMIT ?",
            [$startDate, $endDate, $limit]
        );
    }
    
    /**
     * Obtenir les commandes du jour
     */
    public function getDailyOrders(): array
    {
        $date = date('Y-m-d');
        return $this->db->query(
            "SELECT o.*, rt.table_number 
             FROM orders o
             JOIN restaurant_tables rt ON o.table_id = rt.id
             WHERE DATE(o.created_at) = ?
             ORDER BY o.created_at DESC",
            [$date]
        );
    }
    
    /**
     * Stats du jour
     */
    public function getDailyStats(): array
    {
        return $this->getSalesStats(date('Y-m-d'), date('Y-m-d'));
    }
    
    /**
     * Marquer comme payé
     */
    public function markAsPaid(int $orderId, string $method): bool
    {
        return $this->db->update(
            'orders', 
            ['payment_status' => 'paid', 'payment_method' => $method, 'paid_at' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$orderId]
        ) > 0;
    }
}
