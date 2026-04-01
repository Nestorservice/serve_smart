<?php
/**
 * SIGR - Kitchen Controller
 * 
 * Interface temps réel pour la cuisine.
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
     * Afficher l'écran cuisine
     * Route: GET /kitchen
     */
    public function display(): void
    {
        // Vérifier l'authentification
        if (!$this->session->hasRole(['admin', 'manager', 'chef'])) {
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        $orders = $this->orderModel->getForKitchen();
        
        // Grouper par statut
        $confirmed = array_filter($orders, fn($o) => $o['status'] === 'confirmed');
        $preparing = array_filter($orders, fn($o) => $o['status'] === 'preparing');
        
        $this->render('kitchen/display', [
            'confirmedOrders' => $confirmed,
            'preparingOrders' => $preparing,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Formulaire de connexion cuisine
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
     * Traiter la connexion
     * Route: POST /kitchen/login
     */
    public function login(): void
    {
        if (!Helpers::validateCsrf()) {
            $this->session->setFlash('error', 'Token invalide');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        $username = Helpers::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->session->setFlash('error', 'Veuillez remplir tous les champs');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        $userModel = new UserModel();
        $user = $userModel->getByUsername($username);
        
        if (!$user || !Helpers::verifyPassword($password, $user['password_hash'])) {
            $this->session->setFlash('error', 'Identifiants incorrects');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        if (!in_array($user['role'], ['admin', 'manager', 'chef'])) {
            $this->session->setFlash('error', 'Accès non autorisé');
            header('Location: ' . Helpers::url('kitchen/login'));
            exit;
        }
        
        // Connexion réussie
        $this->session->loginStaff($user['id'], $user['role'], $user['full_name']);
        
        header('Location: ' . Helpers::url('kitchen'));
        exit;
    }
}
