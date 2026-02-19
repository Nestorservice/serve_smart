<?php
/**
 * SIGR - Client Controller
 * 
 * Gère les interactions des clients (menu, panier, commande).
 */

namespace Controllers;

use Core\Session;
use Core\Helpers;
use Models\CategoryModel;
use Models\ProductModel;
use Models\OrderModel;
use Models\TableModel;

class ClientController
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
     * Scan d'une table via QR Code
     * Route: GET /table/{tableNumber}
     */
    public function scanTable(string $tableNumber): void
    {
        // Vérifier que la table existe
        $table = $this->tableModel->getByNumber($tableNumber);
        
        if (!$table) {
            // Table invalide
            Helpers::render('errors/invalid-table', [
                'message' => Helpers::__('errors.invalid_table')
            ]);
            return;
        }
        
        // Créer une nouvelle session pour cette table
        $this->session->createTableSession($table['id']);
        
        // Rediriger vers le menu
        header('Location: ' . Helpers::url('client/menu'));
        exit;
    }
    
    /**
     * Afficher le menu complet
     * Route: GET /client/menu
     */
    public function showMenu(): void
    {
        // Si ?table=XX est passé, créer la session automatiquement
        if (!empty($_GET['table'])) {
            $tableNumber = $_GET['table'];
            $table = $this->tableModel->getByNumber($tableNumber);
            if ($table && !$this->session->validateTableSession()) {
                $this->session->createTableSession($table['id']);
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
        
        Helpers::render('client/menu', [
            'categories' => $categories,
            'products' => $products,
            'featured' => $featured,
            'selectedCategory' => $selectedCategory,
            'tableNumber' => $tableSession ? $tableSession['table_number'] : ($_GET['table'] ?? null),
            'lang' => $lang,
            'cart' => $tableSession ? $this->getCart() : [],
            'readOnly' => !$tableSession
        ]);
    }
    
    /**
     * Afficher les produits d'une catégorie
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
        
        Helpers::render('client/category', [
            'category' => $category,
            'products' => $products,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $lang,
            'cart' => $this->getCart()
        ]);
    }
    
    /**
     * Afficher un produit
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
        
        Helpers::render('client/product', [
            'product' => $product,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $lang,
            'cart' => $this->getCart()
        ]);
    }
    
    /**
     * Afficher le panier
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
        
        Helpers::render('client/cart', [
            'cartItems' => $cartItems,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $this->session->getLanguage()
        ]);
    }
    
    /**
     * Ajouter un produit au panier
     * Route: POST /client/cart/add
     */
    public function addToCart(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token CSRF invalide', 403);
        }
        
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            Helpers::jsonError('Session expirée', 401);
        }
        
        $data = Helpers::isAjax() ? Helpers::getJsonInput() : $_POST;
        
        $productId = (int) ($data['product_id'] ?? 0);
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $instructions = Helpers::sanitize($data['instructions'] ?? '');
        
        // Vérifier le produit
        $product = $this->productModel->getById($productId);
        
        if (!$product) {
            Helpers::jsonError('Produit introuvable', 404);
        }
        
        if (!$this->productModel->isAvailable($productId, $quantity)) {
            Helpers::jsonError(Helpers::__('errors.stock_insufficient'), 400);
        }
        
        // Ajouter au panier (session)
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
        
        Helpers::jsonSuccess([
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->getCartTotal()
        ], Helpers::__('client.added_to_cart'));
    }
    
    /**
     * Mettre à jour un item du panier
     * Route: POST /client/cart/update
     */
    public function updateCart(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token CSRF invalide', 403);
        }
        
        $data = Helpers::isAjax() ? Helpers::getJsonInput() : $_POST;
        
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
        
        Helpers::jsonSuccess([
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->getCartTotal()
        ]);
    }
    
    /**
     * Retirer un item du panier
     * Route: POST /client/cart/remove
     */
    public function removeFromCart(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token CSRF invalide', 403);
        }
        
        $data = Helpers::isAjax() ? Helpers::getJsonInput() : $_POST;
        $key = $data['key'] ?? '';
        
        $cart = $this->getCart();
        
        if (isset($cart[$key])) {
            unset($cart[$key]);
            $this->saveCart($cart);
        }
        
        Helpers::jsonSuccess([
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->getCartTotal()
        ]);
    }
    
    /**
     * Passer la commande
     * Route: POST /client/order
     */
    public function placeOrder(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token CSRF invalide', 403);
        }
        
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            Helpers::jsonError('Session expirée', 401);
        }
        
        $cart = $this->getCart();
        
        if (empty($cart)) {
            Helpers::jsonError('Panier vide', 400);
        }
        
        $data = Helpers::isAjax() ? Helpers::getJsonInput() : $_POST;
        $notes = Helpers::sanitize($data['notes'] ?? '');
        
        // Vérifier la disponibilité de tous les produits
        foreach ($cart as $item) {
            if (!$this->productModel->isAvailable($item['product_id'], $item['quantity'])) {
                $product = $this->productModel->getById($item['product_id']);
                $productName = $product['name_fr'] ?? 'Produit';
                Helpers::jsonError("Stock insuffisant pour: {$productName}", 400);
            }
        }
        
        try {
            // Créer la commande
            $orderId = $this->orderModel->create(
                (int) $tableSession['table_id'],
                (int) $tableSession['id'],
                array_values($cart),
                $notes
            );
            
            // Confirmer et décrémenter le stock
            $this->orderModel->confirm($orderId);
            
            // Vider le panier
            $this->saveCart([]);
            
            // Récupérer la commande créée
            $order = $this->orderModel->getById($orderId);
            
            Helpers::jsonSuccess([
                'order_id' => $orderId,
                'order_number' => $order['order_number']
            ], Helpers::__('order.order_received'));
            
        } catch (\Exception $e) {
            Helpers::jsonError($e->getMessage(), 500);
        }
    }
    
    /**
     * Suivre une commande
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
        
        // Vérifier que la commande appartient à cette session
        if (!$order || (int) $order['table_session_id'] !== (int) $tableSession['id']) {
            header('Location: ' . Helpers::url('client/menu'));
            exit;
        }
        
        Helpers::render('client/order-tracking', [
            'order' => $order,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $this->session->getLanguage()
        ]);
    }
    
    /**
     * Page de paiement
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
        
        Helpers::render('client/payment', [
            'order' => $order,
            'tableNumber' => $tableSession['table_number'],
            'lang' => $this->session->getLanguage()
        ]);
    }
    
    /**
     * Traiter le paiement
     * Route: POST /client/payment/process
     */
    public function processPayment(): void
    {
        if (!Helpers::validateCsrf()) {
            Helpers::jsonError('Token CSRF invalide', 403);
        }
        
        $tableSession = $this->session->validateTableSession();
        if (!$tableSession) {
            Helpers::jsonError('Session expirée', 401);
        }
        
        $data = Helpers::isAjax() ? Helpers::getJsonInput() : $_POST;
        $orderId = (int) ($data['order_id'] ?? 0);
        $method = $data['method'] ?? 'cash';
        
        $order = $this->orderModel->getById($orderId);
        
        if (!$order || (int) $order['table_session_id'] !== (int) $tableSession['id']) {
            Helpers::jsonError('Commande invalide', 404);
        }
        
        // Pour le paiement en espèces, simplement marquer comme "paiement en attente"
        if ($method === 'cash') {
            $this->orderModel->updateStatus($orderId, 'served');
            Helpers::jsonSuccess([
                'message' => Helpers::__('payment.pay_at_cashier')
            ]);
        }
        
        // TODO: Intégration Mobile Money (Orange, MTN, Moov)
        // Pour l'instant, marquer comme en attente
        Helpers::jsonSuccess([
            'message' => Helpers::__('payment.payment_pending')
        ]);
    }
    
    // =========================================
    // Méthodes Privées - Gestion Panier
    // =========================================
    
    /**
     * Obtenir le panier depuis la session
     */
    private function getCart(): array
    {
        return $_SESSION['cart'] ?? [];
    }
    
    /**
     * Sauvegarder le panier en session
     */
    private function saveCart(array $cart): void
    {
        $_SESSION['cart'] = $cart;
    }
    
    /**
     * Obtenir le nombre d'articles dans le panier
     */
    private function getCartCount(): int
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }
    
    /**
     * Obtenir le total du panier
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
     * Enrichir les items du panier avec les infos produits
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
