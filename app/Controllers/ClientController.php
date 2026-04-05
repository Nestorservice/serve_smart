<?php
/**
 * SIGR - Client Controller
 * 
 * Manages client interactions (menu, cart, order).
 */

namespace Controllers;

use Core\Session;
use Core\Helpers;
use Models\CategoryModel;
use Models\ProductModel;
use Models\OrderModel;
use Models\TableModel;

class ClientController extends \Core\Controller
{
    private Session $session;
    private CategoryModel $categoryModel;
    private ProductModel $productModel;
    private OrderModel $orderModel;
    private TableModel $tableModel;
    
    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
        $this->tableModel = new TableModel();
    }
    
    /**
     * Scan a table via QR Code
     * Route: GET /table/{tableNumber}
     */
    public function scanTable(string $tableNumber): void
    {
        // Check if table exists
        $table = $this->tableModel->getByNumber($tableNumber);
        
        if (!$table) {
            // Invalid table
            $this->render('errors/invalid-table', [
                'message' => Helpers::__('errors.invalid_table')
            ]);
            return;
        }
        
        // Create a new session for this table
        $this->session->createTableSession($table['id']);
        
        // Redirect to menu
        header('Location: ' . Helpers::url('client/menu'));
        exit;
    }
    
    /**
     * Show full menu
     * Route: GET /client/menu
     */
    public function showMenu(): void
    {
        // If ?table=XX is passed, create or update the session
        if (!empty($_GET['table'])) {
            $tableNumber = $_GET['table'];
            $table = $this->tableModel->getByNumber($tableNumber);
            if ($table) {
                $currentSession = $this->session->validateTableSession();
                if (!$currentSession || (int)$currentSession['table_id'] !== (int)$table['id']) {
                    $this->session->createTableSession($table['id']);
                    // Redirect so the cookie is available on next request
                    header('Location: ' . Helpers::url('client/menu'));
                    exit;
                }
            }
        }
        
        $tableSession = $this->session->validateTableSession();
        
        $categories = $this->categoryModel->getAllWithProductCount();
        $featured = $this->productModel->getFeatured();
        $lang = $this->session->getLanguage();
        
        // Fetch products - with optional category filter
        $selectedCategory = !empty($_GET['category']) ? (int)$_GET['category'] : null;
        if ($selectedCategory) {
            $products = $this->productModel->getByCategory($selectedCategory);
        } else {
            $products = $this->productModel->getAll();
        }
        
        $this->render('client/menu', [
            'categories' => $categories,
            'products' => $products,
            'featured' => $featured,
            'selectedCategory' => $selectedCategory,
            'tableNumber' => $tableSession ? $tableSession['table_number'] : ($_GET['table'] ?? null),
            'lang' => $lang,
            'cart' => $tableSession ? $this->enrichCartItems($this->getCart()) : [],
            'readOnly' => !$tableSession
        ]);
    }
    
    /**
     * Show category products
     * Route: GET /client/menu/{categoryId}
     */
    public function showCategory(string $categoryId): void
    {
        $tableSession = $this->session->validateTableSession();
        
        if (!$tableSession) {
            header('Location: ' . Helpers::url('/'));
            exit;
        }
        
        $category = $this->categoryModel->getById((int) $categoryId);
        
        if (!$category) {
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        $products = $this->productModel->getByCategory((int) $categoryId);
        $lang = $this->session->getLanguage();
        
        $this->render('client/category', [
            'category' => $category,
            'products' => $products,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $lang,
            'cart' => $this->getCart()
        ]);
    }
    
    /**
     * Show a product
     * Route: GET /client/product/{productId}
     */
    public function showProduct(string $productId): void
    {
        $tableSession = $this->session->validateTableSession();
        
        if (!$tableSession) {
            header('Location: ' . Helpers::url('/'));
            exit;
        }
        
        $product = $this->productModel->getById((int) $productId);
        
        if (!$product) {
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        $lang = $this->session->getLanguage();
        
        $this->render('client/product', [
            'product' => $product,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $lang,
            'cart' => $this->getCart()
        ]);
    }
    
    /**
     * Show cart
     * Route: GET /client/cart
     */
    public function showCart(): void
    {
        $tableSession = $this->session->validateTableSession();
        
        if (!$tableSession) {
            header('Location: ' . Helpers::url('/'));
            exit;
        }
        
        $cart = $this->getCart();
        $cartItems = $this->enrichCartItems($cart);
        
        $this->render('client/cart', [
            'cartItems' => $cartItems,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $this->session->getLanguage()
        ]);
    }
    
    /**
     * Add a product to cart
     * Route: POST /client/cart/add
     */
    public function addToCart(): void
    {
        $isAjax = Helpers::isAjax();
        
        if (!Helpers::validateCsrf()) {
            if ($isAjax) {
                Helpers::jsonError('Invalid CSRF token', 403);
            }
            $this->session->setFlash('error', 'Invalid CSRF token');
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            if ($isAjax) {
                Helpers::jsonError('Session expired', 401);
            }
            header('Location: ' . Helpers::url('/'));
            exit;
        }
        
        // Combine POST and JSON input for AJAX requests to support both FormData and JSON
        $data = $isAjax ? array_merge($_POST, $this->getJsonInput()) : $_POST;
        
        $productId = (int) ($data['product_id'] ?? 0);
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $instructions = Helpers::sanitize($data['instructions'] ?? '');
        
        // Check product
        $product = $this->productModel->getById($productId);
        
        if (!$product) {
            if ($isAjax) {
                Helpers::jsonError('Product not found', 404);
            }
            $this->session->setFlash('error', 'Product not found');
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        if (!$this->productModel->isAvailable($productId, $quantity)) {
            if ($isAjax) {
                Helpers::jsonError(Helpers::__('errors.stock_insufficient'), 400);
            }
            $this->session->setFlash('error', 'Insufficient stock');
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        // Add to cart (session)
        $cart = $this->getCart();
        $key = $productId . '_' . md5($instructions);
        
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'instructions' => $instructions,
                'price' => $product['price']
            ];
        }
        
        $this->saveCart($cart);
        
        if ($isAjax) {
            Helpers::jsonSuccess([
                'cart_count' => $this->getCartCount(),
                'cart_total' => $this->getCartTotal()
            ], Helpers::__('client.added_to_cart'));
        }
        
        // Non-AJAX: redirect back to menu
        $this->session->setFlash('success', 'Added to cart!');
        $redirectUrl = 'client/menu';
        if (!empty($data['table'])) {
            $redirectUrl .= '?table=' . $data['table'];
        }
        header('Location: ' . Helpers::url($redirectUrl));
        exit;
    }
    
    /**
     * Update a cart item
     * Route: POST /client/cart/update
     */
    public function updateCart(): void
    {
        if (!Helpers::validateCsrf()) {
            if (Helpers::isAjax()) {
                Helpers::jsonError('Invalid CSRF token', 403);
            }
            $this->session->setFlash('error', 'Invalid CSRF token');
            header('Location: ' . Helpers::url('client/cart'));
            exit;
        }
        
        $isAjax = Helpers::isAjax();
        $data = $isAjax ? array_merge($_POST, $this->getJsonInput()) : $_POST;
        
        $key = $data['key'] ?? '';
        $quantity = max(0, (int) ($data['quantity'] ?? 0));
        
        $cart = $this->getCart();
        
        if (isset($cart[$key])) {
            if ($quantity > 0) {
                $cart[$key]['quantity'] = $quantity;
            } else {
                unset($cart[$key]);
            }
            $this->saveCart($cart);
        }
        
        if (Helpers::isAjax()) {
            Helpers::jsonSuccess([
                'cart_count' => $this->getCartCount(),
                'cart_total' => $this->getCartTotal()
            ]);
        }
        
        header('Location: ' . Helpers::url('client/cart'));
        exit;
    }
    
    /**
     * Remove an item from cart
     * Route: POST /client/cart/remove
     */
    public function removeFromCart(): void
    {
        if (!Helpers::validateCsrf()) {
            if (Helpers::isAjax()) {
                Helpers::jsonError('Invalid CSRF token', 403);
            }
            $this->session->setFlash('error', 'Invalid CSRF token');
            header('Location: ' . Helpers::url('client/cart'));
            exit;
        }
        
        $isAjax = Helpers::isAjax();
        $data = $isAjax ? array_merge($_POST, $this->getJsonInput()) : $_POST;
        $key = $data['key'] ?? '';
        
        $cart = $this->getCart();
        
        // Handle clear all
        if ($key === '__clear_all__') {
            $this->saveCart([]);
        } elseif (isset($cart[$key])) {
            unset($cart[$key]);
            $this->saveCart($cart);
        }
        
        if (Helpers::isAjax()) {
            Helpers::jsonSuccess([
                'cart_count' => $this->getCartCount(),
                'cart_total' => $this->getCartTotal()
            ]);
        }
        
        header('Location: ' . Helpers::url('client/cart'));
        exit;
    }
    
    /**
     * Place an order
     * Route: POST /client/order
     */
    public function placeOrder(): void
    {
        $isAjax = Helpers::isAjax();
        
        if (!Helpers::validateCsrf()) {
            if ($isAjax) {
                Helpers::jsonError('Invalid CSRF token', 403);
            }
            $this->session->setFlash('error', 'Invalid CSRF token');
            header('Location: ' . Helpers::url('client/cart'));
            exit;
        }
        
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            if ($isAjax) {
                Helpers::jsonError('Session expired', 401);
            }
            header('Location: ' . Helpers::url('/'));
            exit;
        }
        
        $cart = $this->getCart();
        
        if (empty($cart)) {
            if ($isAjax) {
                Helpers::jsonError('Empty cart', 400);
            }
            $this->session->setFlash('error', 'Your cart is empty');
            header('Location: ' . Helpers::url('client/cart'));
            exit;
        }
        
        $data = $isAjax ? array_merge($_POST, $this->getJsonInput()) : $_POST;
        $notes = Helpers::sanitize($data['notes'] ?? '');
        
        // Check availability of all products
        foreach ($cart as $item) {
            if (!$this->productModel->isAvailable($item['product_id'], $item['quantity'])) {
                $product = $this->productModel->getById($item['product_id']);
                $productName = $product['name_en'] ?? $product['name_fr'] ?? 'Product';
                if ($isAjax) {
                    Helpers::jsonError("Insufficient stock for: {$productName}", 400);
                }
                $this->session->setFlash('error', "Insufficient stock for: {$productName}");
                header('Location: ' . Helpers::url('client/cart'));
                exit;
            }
        }
        
        try {
            // Create the order
            $orderId = $this->orderModel->create(
                (int) $tableSession['table_id'],
                (int) $tableSession['id'],
                array_values($cart),
                $notes
            );
            
            // Confirm and decrement stock
            $this->orderModel->confirm($orderId);
            
            // Empty the cart
            $this->saveCart([]);
            
            // Get the created order
            $order = $this->orderModel->getById($orderId);
            
            if ($isAjax) {
                Helpers::jsonSuccess([
                    'order_id' => $orderId,
                    'order_number' => $order['order_number']
                ], Helpers::__('order.order_received'));
            }
            
            // Redirect to payment for standard form POST since the button says "Continue to payment"
            header('Location: ' . Helpers::url('client/payment/' . $orderId));
            exit;
            
        } catch (\Exception $e) {
            if ($isAjax) {
                Helpers::jsonError($e->getMessage(), 500);
            }
            $this->session->setFlash('error', $e->getMessage());
            header('Location: ' . Helpers::url('client/cart'));
            exit;
        }
    }
    
    /**
     * Track most recent order (nav shortcut)
     */
    public function trackLatestOrder(): void
    {
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            $this->session->setFlash('danger', 'Session expired.');
            $this->redirect('home');
            return;
        }

        $orders = $this->orderModel->getBySession((int) $tableSession['id']);
        if (empty($orders)) {
            $this->session->setFlash('info', 'You have no active orders.');
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }

        // orderModel returns orders sorted by date descending (most recent first)
        $latestOrder = $orders[0];
        header('Location: ' . Helpers::url('client/order/' . $latestOrder['id']));
        exit;
    }

    /**
     * Track an order
     * Route: GET /client/order/{orderId}
     */
    public function trackOrder(string $orderId): void
    {
        $tableSession = $this->session->validateTableSession();
        
        if (!$tableSession) {
            header('Location: ' . Helpers::url('/'));
            exit;
        }
        
        $order = $this->orderModel->getById((int) $orderId);
        $orderItems = $order ? ($order['items'] ?? $this->orderModel->getOrderItems((int) $orderId)) : [];
        
        // Verify that order belongs to this session
        if (!$order || (int) $order['table_session_id'] !== (int) $tableSession['id']) {
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        $this->render('client/order-tracking', [
            'order' => $order,
            'orderItems' => $orderItems,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $this->session->getLanguage()
        ]);
    }
    
    /**
     * Payment page
     * Route: GET /client/payment/{orderId}
     */
    public function showPayment(string $orderId): void
    {
        $tableSession = $this->session->validateTableSession();
        
        if (!$tableSession) {
            header('Location: ' . Helpers::url('/'));
            exit;
        }
        
        $order = $this->orderModel->getById((int) $orderId);
        
        if (!$order || (int) $order['table_session_id'] !== (int) $tableSession['id']) {
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        $this->render('client/payment', [
            'order' => $order,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $this->session->getLanguage()
        ]);
    }
    
    /**
     * Process payment
     * Route: POST /client/payment/process
     */
    public function processPayment(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Invalid CSRF token', 403);
        }
        
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            Helpers::jsonError('Session expired', 401);
        }
        
        $isAjax = Helpers::isAjax();
        $data = $isAjax ? array_merge($_POST, $this->getJsonInput()) : $_POST;
        
        $orderId = (int) ($data['order_id'] ?? 0);
        $method = $data['method'] ?? 'cash';
        $phone = $data['phone'] ?? null;
        
        $order = $this->orderModel->getById($orderId);
        
        if (!$order || (int) $order['table_session_id'] !== (int) $tableSession['id']) {
            Helpers::jsonError('Invalid order', 404);
        }
        
        // For cash, simply say it will be paid at the cashier.
        // Cuisine status remains "confirmed"
        if ($method === 'cash') {
            Helpers::jsonSuccess([
                'message' => Helpers::__('payment.pay_at_cashier'),
                'redirect' => url('client/ticket/' . $orderId)
            ]);
        }
        
        // TODO: Mobile Money Integration (Orange, MTN, Moov)
        // Simulate a successful payment
        
        $paymentModel = new \Models\PaymentModel();
        $ref = 'SIM_' . strtoupper($method) . '_' . time();
        
        $paymentModel->create([
            'order_id' => $orderId,
            'amount' => $order['total_amount'],
            'method' => $method,
            'transaction_ref' => $ref,
            'status' => 'completed',
            'phone_number' => $phone
        ]);
        
        $this->orderModel->markAsPaid($orderId, $method);
        
        Helpers::jsonSuccess([
            'message' => Helpers::__('payment.payment_successful'),
            'transaction_ref' => $ref,
            'redirect' => url('client/ticket/' . $orderId)
        ]);
    }
    
    /**
     * Show payment receipt (Ticket)
     */
    public function showTicket(int $orderId): void
    {
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            $this->session->setFlash('danger', 'Session expired.');
            $this->redirect('home');
            return;
        }
        
        $order = $this->orderModel->getById($orderId);
        
        if (!$order || (int) $order['table_session_id'] !== (int) $tableSession['id']) {
            $this->session->setFlash('danger', 'Order not found.');
            $this->redirect('client.menu');
            return;
        }
        
        $this->render('client/ticket', [
            'order' => $order,
            'tableNumber' => $tableSession['table_number']
        ]);
    }
    
    // =========================================
    // Private Methods - Cart Management
    // =========================================
    
    /**
     * Get cart from session
     */
    private function getCart(): array
    {
        return $_SESSION['cart'] ?? [];
    }
    
    /**
     * Save cart to session
     */
    private function saveCart(array $cart): void
    {
        $_SESSION['cart'] = $cart;
    }
    
    /**
     * Get number of items in cart
     */
    private function getCartCount(): int
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }
    
    /**
     * Get cart total amount
     */
    private function getCartTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
    
    /**
     * Enrich cart items with product info
     */
    private function enrichCartItems(array $cart): array
    {
        $enriched = [];
        
        foreach ($cart as $key => $item) {
            $product = $this->productModel->getById($item['product_id']);
            
            if ($product) {
                $enriched[] = [
                    'key' => $key,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'instructions' => $item['instructions'],
                    'total' => $item['price'] * $item['quantity']
                ];
            }
        }
        
        return $enriched;
    }
}
