<?php
/**
 * SIGR - Auth Controller
 * 
 * Gère l'authentification du staff (admin, manager, chef, caissier).
 */

namespace Controllers;

use Core\Session;
use Core\Helpers;
use Models\UserModel;

class AuthController
{
    private Session $session;
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->userModel = new UserModel();
    }
    
    /**
     * Formulaire de connexion admin
     * Route: GET /admin/login
     */
    public function loginForm(): void
    {
        if ($this->session->isStaffLoggedIn()) {
            header('Location: ' . Helpers::url('admin'));
            exit;
        }
        
        Helpers::render('admin/login', [
            'error' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success')
        ]);
    }
    
    /**
     * Traiter la connexion
     * Route: POST /admin/login
     */
    public function login(): void
    {
        if (!Helpers::validateCsrf()) {
            $this->session->setFlash('error', 'Token de sécurité invalide');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        $username = Helpers::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->session->setFlash('error', 'Veuillez remplir tous les champs');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        // Authentifier
        $user = $this->userModel->authenticate($username, $password);
        
        if (!$user) {
            $this->session->setFlash('error', 'Identifiants incorrects');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        if (!$user['is_active']) {
            $this->session->setFlash('error', 'Compte désactivé');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        // Connexion réussie
        $this->session->loginStaff($user['id'], $user['role'], $user['full_name']);
        
        // Redirection selon le rôle
        $redirectUrl = match($user['role']) {
            'chef' => 'kitchen',
            default => 'admin'
        };
        
        header('Location: ' . Helpers::url($redirectUrl));
        exit;
    }
    
    /**
     * Déconnexion
     * Route: POST /admin/logout
     */
    public function logout(): void
    {
        $this->session->logoutStaff();
        $this->session->setFlash('success', 'Déconnexion réussie');
        header('Location: ' . Helpers::url('admin/login'));
        exit;
    }
}
