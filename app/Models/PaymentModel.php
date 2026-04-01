<?php
/**
 * SIGR - Payment Model
 */

namespace Models;

class PaymentModel extends \Core\Model
{
    /**
     * Créer un enregistrement de paiement
     */
    public function create(array $data): int
    {
        return $this->db->insert('payments', $data);
    }
    
    /**
     * Trouver un paiement par sa référence
     */
    public function getByRef(string $ref): ?array
    {
        return $this->db->queryOne("SELECT * FROM payments WHERE transaction_ref = ?", [$ref]);
    }
    
    /**
     * Mettre à jour le statut d'un paiement
     */
    public function updateStatus(int $paymentId, string $status, array $extraData = []): int
    {
        $data = ['status' => $status];
        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        $data = array_merge($data, $extraData);
        return $this->db->update('payments', $data, 'id = ?', [$paymentId]);
    }
    
    /**
     * Paiements d'une commande
     */
    public function getByOrderId(int $orderId): array
    {
        return $this->db->query("SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC", [$orderId]);
    }
}
