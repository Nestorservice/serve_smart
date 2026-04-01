<?php
/**
 * SIGR - Admin Controller
 * 
 * Gestion du back-office.
 */

namespace Controllers;

use Core\Session;
use Core\Helpers;
use Models\CategoryModel;
use Models\ProductModel;
use Models\OrderModel;
use Models\StockModel;
use Models\TableModel;
use Models\UserModel;

class AdminController extends \Core\Controller
{
    private Session $session;
    
    public function __construct()
    {
        $this->session = Session::getInstance();
        
        // Vérifier l'authentification
        if (!$this->session->isStaffLoggedIn()) {
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
    }
    
    /**
     * Dashboard principal
     */
    public function dashboard(): void
    {
        $orderModel = new OrderModel();
        $stockModel = new StockModel();
        
        // Statistiques du jour
        $todayStats = $orderModel->getSalesStats();
        $lowStockCount = $stockModel->countLowStock();
        $pendingOrders = count($orderModel->getPendingPayments());
        $topProducts = $orderModel->getTopProducts(5);
        
        $this->render('admin/dashboard', [
            'stats' => [
                'today_sales' => $todayStats['paid_sales'] ?? 0,
                'total_orders' => $todayStats['total_orders'] ?? 0,
                'pending_orders' => $pendingOrders,
                'low_stock' => $lowStockCount
            ],
            'topProducts' => $topProducts,
            'staffName' => $this->session->getStaffName(),
            'staffRole' => $this->session->getStaffRole()
        ]);
    }
    
    /**
     * Liste du menu
     */
    public function menuList(): void
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();
        
        $this->render('admin/menu', [
            'products' => $productModel->getAll(),
            'categories' => $categoryModel->getAll(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Formulaire création produit
     */
    public function menuCreate(): void
    {
        $categoryModel = new CategoryModel();
        
        $this->render('admin/menu-form', [
            'product' => null,
            'categories' => $categoryModel->getAll(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Enregistrer un produit
     */
    public function menuStore(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $productModel = new ProductModel();
        
        $data = [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'name_fr' => Helpers::sanitize($_POST['name_fr'] ?? ''),
            'name_en' => Helpers::sanitize($_POST['name_en'] ?? ''),
            'description_fr' => Helpers::sanitize($_POST['description_fr'] ?? ''),
            'description_en' => Helpers::sanitize($_POST['description_en'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0),
            'preparation_time' => (int) ($_POST['preparation_time'] ?? 15),
            'is_available' => isset($_POST['is_available']),
            'is_featured' => isset($_POST['is_featured']),
            'requires_stock' => isset($_POST['requires_stock'])
        ];
        
        // Gestion de l'image uploadée
        if (!empty($_FILES['image']['name'])) {
            $data['image_url'] = $this->handleImageUpload($_FILES['image']);
        }
        
        $productId = $productModel->create($data);
        
        $this->session->setFlash('success', 'Produit créé avec succès');
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Formulaire modification produit
     */
    public function menuEdit(string $id): void
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();
        
        $product = $productModel->getById((int) $id);
        
        if (!$product) {
            header('Location: ' . Helpers::url('admin/menu'));
            exit;
        }
        
        $this->render('admin/menu-form', [
            'product' => $product,
            'categories' => $categoryModel->getAll(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Mettre à jour un produit
     */
    public function menuUpdate(string $id): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $productModel = new ProductModel();
        
        $data = [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'name_fr' => Helpers::sanitize($_POST['name_fr'] ?? ''),
            'name_en' => Helpers::sanitize($_POST['name_en'] ?? ''),
            'description_fr' => Helpers::sanitize($_POST['description_fr'] ?? ''),
            'description_en' => Helpers::sanitize($_POST['description_en'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0),
            'preparation_time' => (int) ($_POST['preparation_time'] ?? 15),
            'is_available' => isset($_POST['is_available']),
            'is_featured' => isset($_POST['is_featured']),
            'requires_stock' => isset($_POST['requires_stock'])
        ];
        
        if (!empty($_FILES['image']['name'])) {
            $data['image_url'] = $this->handleImageUpload($_FILES['image']);
        }
        
        $productModel->update((int) $id, $data);
        
        $this->session->setFlash('success', 'Produit modifié avec succès');
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Supprimer un produit
     */
    public function menuDelete(string $id): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $productModel = new ProductModel();
        $productModel->delete((int) $id);
        
        $this->session->setFlash('success', 'Produit supprimé');
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Activer/Désactiver un produit
     */
    public function menuToggle(string $id): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $productModel = new ProductModel();
        $product = $productModel->getById((int) $id);
        
        if ($product) {
            $productModel->update((int) $id, [
                'is_available' => !$product['is_available']
            ]);
            $status = !$product['is_available'] ? 'activé' : 'désactivé';
            $this->session->setFlash('success', "Produit {$status}");
        }
        
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Liste des catégories
     */
    public function categoryList(): void
    {
        $categoryModel = new CategoryModel();
        
        $this->render('admin/categories', [
            'categories' => $categoryModel->getAllWithProductCount(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Créer une catégorie
     */
    public function categoryStore(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $categoryModel = new CategoryModel();
        
        $categoryModel->create([
            'name_fr' => Helpers::sanitize($_POST['name_fr'] ?? ''),
            'name_en' => Helpers::sanitize($_POST['name_en'] ?? ''),
            'description_fr' => Helpers::sanitize($_POST['description_fr'] ?? ''),
            'description_en' => Helpers::sanitize($_POST['description_en'] ?? ''),
            'icon' => Helpers::sanitize($_POST['icon'] ?? 'bi-grid'),
            'display_order' => (int) ($_POST['display_order'] ?? 0)
        ]);
        
        $this->session->setFlash('success', 'Catégorie créée');
        header('Location: ' . Helpers::url('admin/categories'));
        exit;
    }
    
    /**
     * Liste des stocks
     */
    public function stockList(): void
    {
        $stockModel = new StockModel();
        
        $this->render('admin/stock', [
            'stocks' => $stockModel->getAll(),
            'lowStock' => $stockModel->getLowStock(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Mettre à jour le stock
     */
    public function stockUpdate(string $id = ''): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $stockModel = new StockModel();
        
        // Support both URL param and POST body for product_id
        $productId = !empty($id) ? (int) $id : (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['stock_quantity'] ?? $_POST['quantity'] ?? 0);
        $notes = Helpers::sanitize($_POST['notes'] ?? '');
        
        $stockModel->updateQuantity(
            $productId,
            $quantity,
            'restock',
            $this->session->getStaffId(),
            $notes
        );
        
        if (Helpers::isAjax()) {
            Helpers::jsonSuccess(['quantity' => $quantity]);
        }
        
        $this->session->setFlash('success', 'Stock mis à jour');
        header('Location: ' . Helpers::url('admin/stock'));
        exit;
    }
    
    /**
     * Liste des tables
     */
    public function tableList(): void
    {
        $tableModel = new TableModel();
        
        $this->render('admin/tables', [
            'tables' => $tableModel->getWithActiveOrders(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Créer une table
     */
    public function tableStore(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $tableModel = new TableModel();
        
        $tableModel->create([
            'table_number' => Helpers::sanitize($_POST['table_number'] ?? ''),
            'capacity' => (int) ($_POST['capacity'] ?? 4),
            'zone' => Helpers::sanitize($_POST['zone'] ?? 'Principale')
        ]);
        
        $this->session->setFlash('success', 'Table créée');
        header('Location: ' . Helpers::url('admin/tables'));
        exit;
    }
    
    /**
     * Générer QR code pour une table
     */
    public function generateQR(string $id): void
    {
        $tableModel = new TableModel();
        $table = $tableModel->getById((int) $id);
        
        if (!$table) {
            header('Location: ' . Helpers::url('admin/tables'));
            exit;
        }
        
        $qrUrl = $tableModel->getQRCodeUrl((int) $id);
        
        // Utiliser l'API QR Code
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrUrl);
        
        $this->render('admin/qr-display', [
            'table' => $table,
            'qrUrl' => $qrUrl,
            'qrImage' => $qrApiUrl,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Liste des commandes
     */
    public function orderList(): void
    {
        $orderModel = new OrderModel();
        
        // Filtres
        $status = $_GET['status'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $this->render('admin/orders', [
            'orders' => $orderModel->getPendingPayments(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Détails d'une commande
     */
    public function orderDetails(string $id): void
    {
        $orderModel = new OrderModel();
        $order = $orderModel->getById((int) $id);
        
        if (!$order) {
            header('Location: ' . Helpers::url('admin/orders'));
            exit;
        }
        
        $this->render('admin/order-details', [
            'order' => $order,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Statistiques
     */
    public function stats(): void
    {
        $orderModel = new OrderModel();
        
        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end'] ?? date('Y-m-d');
        
        $this->render('admin/stats', [
            'stats' => $orderModel->getSalesStats($startDate, $endDate),
            'topProducts' => $orderModel->getTopProducts(10, $startDate, $endDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Paramètres
     */
    public function settings(): void
    {
        $this->render('admin/settings', [
            'settings' => [
                'restaurant_name' => Helpers::getSetting('restaurant_name'),
                'currency' => Helpers::getSetting('currency'),
                'tax_rate' => Helpers::getSetting('tax_rate'),
                'order_prefix' => Helpers::getSetting('order_prefix')
            ],
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Mettre à jour les paramètres
     */
    public function settingsUpdate(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $db = \Core\Database::getInstance();
        
        $settings = [
            'restaurant_name' => Helpers::sanitize($_POST['restaurant_name'] ?? ''),
            'tax_rate' => (float) ($_POST['tax_rate'] ?? 0),
            'order_prefix' => Helpers::sanitize($_POST['order_prefix'] ?? 'CMD')
        ];
        
        foreach ($settings as $key => $value) {
            $db->execute(
                "UPDATE settings SET setting_value = ? WHERE setting_key = ?",
                [$value, $key]
            );
        }
        
        $this->session->setFlash('success', 'Paramètres mis à jour');
        header('Location: ' . Helpers::url('admin/settings'));
        exit;
    }
    
    /**
     * Liste des utilisateurs
     */
    public function userList(): void
    {
        if (!$this->session->hasRole('admin')) {
            header('Location: ' . Helpers::url('admin'));
            exit;
        }
        
        $userModel = new UserModel();
        
        $this->render('admin/users', [
            'users' => $userModel->getAll(),
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Créer un utilisateur
     */
    public function userStore(): void
    {
        if (!$this->session->hasRole('admin')) {
            Helpers::jsonError('Accès refusé', 403);
        }
        
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token invalide', 403);
        }
        
        $userModel = new UserModel();
        
        $userModel->create([
            'username' => Helpers::sanitize($_POST['username'] ?? ''),
            'email' => Helpers::sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'waiter',
            'full_name' => Helpers::sanitize($_POST['full_name'] ?? '')
        ]);
        
        $this->session->setFlash('success', 'Utilisateur créé');
        header('Location: ' . Helpers::url('admin/users'));
        exit;
    }
    
    /**
     * Gérer l'upload d'image
     */
    private function handleImageUpload(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Vérifier le type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        // Vérifier la taille
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return null;
        }
        
        // Générer un nom unique
        $ext = Helpers::getFileExtension($file['name']);
        $filename = Helpers::uniqueFilename($ext);
        
        // Créer le dossier si nécessaire
        $uploadDir = UPLOAD_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Déplacer le fichier
        $destination = $uploadDir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ASSETS_URL . '/images/uploads/' . $filename;
        }
        
        return null;
    }
}
