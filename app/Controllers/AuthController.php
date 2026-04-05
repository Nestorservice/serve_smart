<?php
/**
 * SIGR - Auth Controller
 * 
 * Manages staff authentication (admin, manager, chef, cashier).
 */

namespace Controllers;

use Core\Session;
use Core\Helpers;
use Models\UserModel;

class AuthController extends \Core\Controller
{
    private Session $session;
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->userModel = new UserModel();
    }
    
    /**
     * Admin login form
     * Route: GET /admin/login
     */
    public function loginForm(): void
    {
        if ($this->session->isStaffLoggedIn()) {
            header('Location: ' . Helpers::url('admin'));
            exit;
        }
        
        $this->render('admin/login', [
            'error' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success')
        ]);
    }
    
    /**
     * Process login
     * Route: POST /admin/login
     */
    public function login(): void
    {
        if (!Helpers::validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        $username = Helpers::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->session->setFlash('error', 'Please fill in all fields');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        // Authenticate
        $user = $this->userModel->authenticate($username, $password);
        
        if (!$user) {
            $this->session->setFlash('error', 'Incorrect credentials');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        if (!$user['is_active']) {
            $this->session->setFlash('error', 'Account deactivated');
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
        
        // Successful login
        $this->session->loginStaff($user['id'], $user['role'], $user['full_name']);
        
        // Redirect based on role
        $redirectUrl = match($user['role']) {
            'chef' => 'kitchen',
            default => 'admin'
        };
        
        header('Location: ' . Helpers::url($redirectUrl));
        exit;
    }
    
    /**
     * Logout
     * Route: POST /admin/logout
     */
    public function logout(): void
    {
        $this->session->logoutStaff();
        $this->session->setFlash('success', 'Logout successful');
        header('Location: ' . Helpers::url('admin/login'));
        exit;
    }
}
