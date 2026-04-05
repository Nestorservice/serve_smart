<?php
/**
 * SIGR - Admin Controller
 * 
 * Back-office management.
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
        
        // Check authentication
        if (!$this->session->isStaffLoggedIn()) {
            if ($this->session->getFlash('error') === null) {
                // Preemptively set message if needed, but header redirect is fine
            }
            header('Location: ' . Helpers::url('admin/login'));
            exit;
        }
    }
    
    /**
     * Main Dashboard
     */
    public function dashboard(): void
    {
        $orderModel = new OrderModel();
        $stockModel = new StockModel();
        
        // Today's statistics
        $todayStats = $orderModel->getSalesStats();
        $lowStockCount = $stockModel->countLowStock();
        $pendingOrders = count($orderModel->getPendingPayments());
        $topProducts = $orderModel->getTopProducts(5);
        $recentOrders = $orderModel->getDailyOrders();
        
        // Weekly sales data for chart
        $weeklySales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayStat = $orderModel->getSalesStats($date, $date);
            $weeklySales[] = (float) ($dayStat['total_sales'] ?? 0);
        }
        
        // Order status counts for pie chart
        $allDayOrders = $recentOrders;
        $orderStats = [
            'pending' => 0,
            'preparing' => 0,
            'ready' => 0,
            'served' => 0
        ];
        foreach ($allDayOrders as $o) {
            $st = $o['status'] ?? '';
            if (isset($orderStats[$st])) {
                $orderStats[$st]++;
            }
        }
        
        $this->render('admin/dashboard', [
            'todaySales' => (float) ($todayStats['total_sales'] ?? 0),
            'totalOrders' => (int) ($todayStats['total_orders'] ?? 0),
            'pendingOrders' => $pendingOrders,
            'lowStockCount' => $lowStockCount,
            'recentOrders' => array_slice($recentOrders, 0, 10),
            'popularProducts' => $topProducts,
            'weeklySales' => $weeklySales,
            'orderStats' => $orderStats,
            'staffName' => $this->session->getStaffName(),
            'staffRole' => $this->session->getStaffRole()
        ]);
    }
    
    /**
     * Menu list
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
     * Store a product
     */
    public function menuStore(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
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
        
        // Handle uploaded image
        if (!empty($_FILES['image']['name'])) {
            $data['image_url'] = $this->handleImageUpload($_FILES['image']);
        }
        
        $productId = $productModel->create($data);
        
        $this->session->setFlash('success', 'Product created successfully');
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Edit product form
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
     * Update a product
     */
    public function menuUpdate(string $id): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
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
            $uploadedPath = $this->handleImageUpload($_FILES['image']);
            if ($uploadedPath) {
                $data['image_url'] = $uploadedPath;
            }
        }
        
        $productModel->update((int) $id, $data);
        
        $this->session->setFlash('success', 'Product updated successfully');
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Delete a product
     */
    public function menuDelete(string $id): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
        }
        
        $productModel = new ProductModel();
        $productModel->delete((int) $id);
        
        $this->session->setFlash('success', 'Product deleted');
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Activate/Deactivate a product
     */
    public function menuToggle(string $id): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
        }
        
        $productModel = new ProductModel();
        $product = $productModel->getById((int) $id);
        
        if ($product) {
            $productModel->update((int) $id, [
                'is_available' => !$product['is_available']
            ]);
            $status = !$product['is_available'] ? 'activated' : 'deactivated';
            $this->session->setFlash('success', "Product {$status}");
        }
        
        header('Location: ' . Helpers::url('admin/menu'));
        exit;
    }
    
    /**
     * Category list
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
     * Create a category
     */
    public function categoryStore(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
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
        
        $this->session->setFlash('success', 'Category created');
        header('Location: ' . Helpers::url('admin/categories'));
        exit;
    }
    
    /**
     * Stock list
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
     * Update stock
     */
    public function stockUpdate(string $id = ''): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
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
        
        $this->session->setFlash('success', 'Stock updated');
        header('Location: ' . Helpers::url('admin/stock'));
        exit;
    }
    
    /**
     * Table list
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
     * Create a table
     */
    public function tableStore(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
        }
        
        $tableModel = new TableModel();
        
        $tableModel->create([
            'table_number' => Helpers::sanitize($_POST['table_number'] ?? ''),
            'capacity' => (int) ($_POST['capacity'] ?? 4),
            'zone' => Helpers::sanitize($_POST['zone'] ?? 'Main')
        ]);
        
        $this->session->setFlash('success', 'Table created');
        header('Location: ' . Helpers::url('admin/tables'));
        exit;
    }
    
    /**
     * Generate QR code for a table
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
        
        // Use QR Code API
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrUrl);
        
        $this->render('admin/qr-display', [
            'table' => $table,
            'qrUrl' => $qrUrl,
            'qrImage' => $qrApiUrl,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Order list
     */
    public function orderList(): void
    {
        $orderModel = new OrderModel();
        
        // Filters
        $status = $_GET['status'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Get all daily orders
        $allOrders = $orderModel->getDailyOrders();
        
        // Apply status filter if provided
        if ($status) {
            $allOrders = array_filter($allOrders, fn($o) => $o['status'] === $status);
        }
        
        $this->render('admin/orders', [
            'orders' => $allOrders,
            'statusFilter' => $status,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Order details
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
            'items' => $order['items'] ?? [],
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Update order status
     */
    public function orderStatusUpdate(string $id): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
        }
        
        $orderModel = new OrderModel();
        $status = $_POST['status'] ?? '';
        
        if ($orderModel->updateStatus((int) $id, $status)) {
            $this->session->setFlash('success', 'Status updated');
        } else {
            $this->session->setFlash('error', 'Update failed');
        }
        
        header('Location: ' . Helpers::url('admin/orders/' . $id));
        exit;
    }
    
    /**
     * Statistics
     */
    public function stats(): void
    {
        $orderModel = new OrderModel();
        
        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end'] ?? date('Y-m-d');
        
        // Basic stats for the period
        $periodStats = $orderModel->getSalesStats($startDate, $endDate);
        $todayStats = $orderModel->getSalesStats(date('Y-m-d'), date('Y-m-d'));
        
        $weekStart = date('Y-m-d', strtotime('-7 days'));
        $weekStats = $orderModel->getSalesStats($weekStart, date('Y-m-d'));
        
        $monthStart = date('Y-m-d', strtotime('-30 days'));
        $monthStats = $orderModel->getSalesStats($monthStart, date('Y-m-d'));

        // Orders by status
        $allOrders = $orderModel->getDailyOrders(); // We can use all orders or daily, but view implies recent summary
        // Let's get status distribution over period
        $db = \Core\Database::getInstance();
        $statusCounts = $db->query("SELECT status, COUNT(*) as count FROM orders WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY status", [$startDate, $endDate]);
        $orders_by_status = ['pending' => 0, 'preparing' => 0, 'ready' => 0, 'served' => 0];
        $completed_orders = 0; $pending_orders = 0; $cancelled_orders = 0;
        foreach ($statusCounts as $row) {
            $st = $row['status'];
            $c = (int)$row['count'];
            if (isset($orders_by_status[$st])) $orders_by_status[$st] = $c;
            if ($st === 'ready' || $st === 'paid') $completed_orders += $c;
            if ($st === 'pending' || $st === 'confirmed' || $st === 'preparing') $pending_orders += $c;
            if ($st === 'cancelled') $cancelled_orders += $c;
        }

        // Top products format: ['name_en', 'quantity', 'revenue']
        $rawTop = $orderModel->getTopProducts(10, $startDate, $endDate);
        $topProducts = [];
        foreach ($rawTop as $p) {
            $topProducts[] = [
                'name_en' => $p['name_en'] ?? $p['name_fr'],
                'quantity' => $p['total_sold'],
                'revenue' => $p['total_revenue']
            ];
        }

        // Sales by day
        $salesByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayStat = $orderModel->getSalesStats($date, $date);
            $dayName = date('D', strtotime($date));
            $salesByDay[$dayName] = (float) ($dayStat['total_sales'] ?? 0);
        }

        // Hourly orders (for today)
        $hourly_orders = array_fill(0, 24, 0);
        $todayOrders = $db->query("SELECT HOUR(created_at) as h, COUNT(*) as count FROM orders WHERE DATE(created_at) = ? GROUP BY HOUR(created_at)", [date('Y-m-d')]);
        foreach ($todayOrders as $row) {
            $hourly_orders[(int)$row['h']] = (int)$row['count'];
        }

        $stats = [
            'today_sales' => (float) ($todayStats['total_sales'] ?? 0),
            'week_sales' => (float) ($weekStats['total_sales'] ?? 0),
            'month_sales' => (float) ($monthStats['total_sales'] ?? 0),
            'total_orders' => (int) ($periodStats['total_orders'] ?? 0),
            'avg_order_value' => (float) ($periodStats['average_order'] ?? 0),
            'completed_orders' => $completed_orders,
            'pending_orders' => $pending_orders,
            'cancelled_orders' => $cancelled_orders,
            'top_products' => $topProducts,
            'sales_by_day' => $salesByDay,
            'orders_by_status' => $orders_by_status,
            'hourly_orders' => $hourly_orders,
        ];

        $this->render('admin/stats', [
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'staffName' => $this->session->getStaffName()
        ]);
    }
    
    /**
     * Settings
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
     * Update settings
     */
    public function settingsUpdate(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
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
        
        $this->session->setFlash('success', 'Settings updated');
        header('Location: ' . Helpers::url('admin/settings'));
        exit;
    }
    
    /**
     * User list
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
     * Create a user
     */
    public function userStore(): void
    {
        if (!$this->session->hasRole('admin')) {
            Helpers::jsonError('Access denied', 403);
        }
        
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid token', 403);
        }
        
        $userModel = new UserModel();
        
        $userModel->create([
            'username' => Helpers::sanitize($_POST['username'] ?? ''),
            'email' => Helpers::sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'waiter',
            'full_name' => Helpers::sanitize($_POST['full_name'] ?? '')
        ]);
        
        $this->session->setFlash('success', 'User created');
        header('Location: ' . Helpers::url('admin/users'));
        exit;
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Check type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        // Check size
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return null;
        }
        
        // --- PROD FEATURE: CLOUDINARY UPLOAD ---
        $cloudinary = new \Core\Cloudinary();
        if ($cloudinary->isConfigured()) {
            $cloudUrl = $cloudinary->upload($file['tmp_name']);
            if ($cloudUrl) {
                return $cloudUrl; // Full https URL
            }
        }
        
        // FALLBACK: LOCAL UPLOAD (for XAMPP or if Cloudinary fails)
        $ext = Helpers::getFileExtension($file['name']);
        $filename = Helpers::uniqueFilename($ext);
        
        $uploadDir = UPLOAD_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'public/assets/images/uploads/' . $filename;
        }
        
        return null;
    }
}
