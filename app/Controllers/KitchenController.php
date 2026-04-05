<?php
/**
 * SIGR - Kitchen Controller
 * 
 * Real-time interface for the kitchen.
 */

namespace Controllers;

use Core\Session;
use Core\Helpers;
use Models\OrderModel;
use Models\UserModel;

class KitchenController extends \Core\Controller
{
    private Session $session;
    private OrderModel $orderModel;
    
    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->orderModel = new OrderModel();
    }
    
    /**
     * Display kitchen screen
     * Route: GET /kitchen
     */
    public function display(): void
    {
        // Check authentication
        if (!$this->session->hasRole(['admin', 'manager', 'chef'])) {
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        $orders = $this->orderModel->getForKitchen();
        
        // Enrich each order with its items
        $enrichedOrders = [];
        foreach ($orders as $order) {
            $order['items'] = $this->orderModel->getOrderItems($order['id']);
            $enrichedOrders[] = $order;
        }
        
        $this->render('kitchen/display', [
            'orders' => $enrichedOrders,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Update order status from the kitchen
     * Route: POST /kitchen/status/{id}
     */
    public function updateStatus(string $id): void
    {
        if (!$this->session->hasRole(['admin', 'manager', 'chef'])) {
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        if (!Helpers::validateCsrf()) {
            $this->session->setFlash('error', 'Invalid token');
            header('Location: ' . Helpers::url('kitchen'));
            exit;
        }
        
        $status = $_POST['status'] ?? '';
        
        if ($this->orderModel->updateStatus((int) $id, $status)) {
            $this->session->setFlash('success', 'Status updated');
        } else {
            $this->session->setFlash('error', 'Update failed');
        }
        
        header('Location: ' . Helpers::url('kitchen'));
        exit;
    }
    
    /**
     * Kitchen login form
     * Route: GET /kitchen/login
     */
    public function loginForm(): void
    {
        if ($this->session->hasRole(['admin', 'manager', 'chef'])) {
            header('Location: ' . Helpers::url('kitchen'));
            exit;
        }
        
        $this->render('kitchen/login', [
            'error' => $this->session->getFlash('error')
        ]);
    }
    
    /**
     * Process login
     * Route: POST /kitchen/login
     */
    public function login(): void
    {
        if (!Helpers::validateCsrf()) {
            $this->session->setFlash('error', 'Invalid token');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        $username = Helpers::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->session->setFlash('error', 'Please fill in all fields');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        $userModel = new UserModel();
        $user = $userModel->getByUsername($username);
        
        if (!$user || !Helpers::verifyPassword($password, $user['password_hash'])) {
            $this->session->setFlash('error', 'Incorrect credentials');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        if (!in_array($user['role'], ['admin', 'manager', 'chef'])) {
            $this->session->setFlash('error', 'Unauthorized access');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        // Successful login
        $this->session->loginStaff($user['id'], $user['role'], $user['full_name']);
        
        header('Location: ' . Helpers::url('kitchen'));
        exit;
    }
}
