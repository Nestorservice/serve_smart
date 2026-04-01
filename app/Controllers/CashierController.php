<?php
/**
 * SIGR - Cashier Controller
 */

namespace Controllers;

use Core\Session;
use Models\OrderModel;
use Models\PaymentModel;

class CashierController extends \Core\Controller
{
    private OrderModel $orderModel;
    private PaymentModel $paymentModel;
    
    public function __construct()
    {
        // Protection middleware: redirige si non authentifié en tant que caissier
        $this->requireRole(['admin', 'manager', 'cashier']);
        
        $this->orderModel = new OrderModel();
        $this->paymentModel = new PaymentModel();
    }
    
    /**
     * Dashboard caissier (Commandes du jour, CA)
     * Route: GET /cashier/
     */
    public function dashboard(): void
    {
        $orders = $this->orderModel->getDailyOrders();
        $stats = $this->orderModel->getDailyStats();
        
        $this->render('admin/cashier_dashboard', [
            'orders' => $orders,
            'stats' => $stats
        ]);
    }
    
    /**
     * Liste des commandes à payer ou en cours
     * Route: GET /cashier/orders
     */
    public function orders(): void
    {
        $orders = $this->orderModel->getPendingPayments();
        
        $this->render('admin/orders', [
            'orders' => $orders
        ]);
    }
    
    /**
     * Détails et encaissement d'une commande
     * Route: GET /cashier/order/{orderId}
     */
    public function orderDetails(string $orderId): void
    {
        $order = $this->orderModel->getById((int) $orderId);
        if (!$order) {
            $this->redirect('cashier/orders');
        }
        
        $payments = $this->paymentModel->getByOrderId((int) $orderId);
        
        $this->render('admin/order_detail', [
            'order' => $order,
            'payments' => $payments
        ]);
    }
    
    /**
     * Traiter le paiement
     * Route: POST /cashier/payment/{orderId}
     */
    public function processPayment(string $orderId): void
    {
        $data = $this->getJsonInput() ?: $_POST;
        
        $amount = (float)($data['amount'] ?? 0);
        $method = $data['method'] ?? 'cash';
        $ref = $data['transaction_ref'] ?? null;
        
        $session = Session::getInstance();
        $staffId = $session->getStaffId();
        
        $paymentId = $this->paymentModel->create([
            'order_id' => (int) $orderId,
            'amount' => $amount,
            'method' => $method,
            'transaction_ref' => $ref,
            'status' => 'completed',
            'processed_by' => $staffId
        ]);
        
        // Mettre à jour la commande
        $this->orderModel->markAsPaid((int) $orderId, $method);
        
        $this->redirect('cashier/order/' . (int) $orderId);
    }
    
    private function requireRole(array $roles): void
    {
        $session = Session::getInstance();
        if (!$session->isStaffLoggedIn() || !$session->hasRole($roles)) {
            $this->redirect('admin/login');
        }
    }
}
