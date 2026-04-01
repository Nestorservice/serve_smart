<?php
/**
 * SIGR - API Controller
 * 
 * Endpoints JSON pour le frontend et le long polling cuisine.
 */

namespace Controllers;

use Core\Session;
use Core\Helpers;
use Models\CategoryModel;
use Models\ProductModel;
use Models\OrderModel;

class ApiController extends \Core\Controller
{
    private Session $session;
    private CategoryModel $categoryModel;
    private ProductModel $productModel;
    private OrderModel $orderModel;
    
    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
    }
    
    /**
     * Obtenir le menu complet
     * Route: GET /api/menu
     */
    public function getMenu(): void
    {
        $lang = $this->session->getLanguage();
        $categories = $this->categoryModel->getAllWithProductCount();
        
        $menu = [];
        foreach ($categories as $cat) {
            $products = $this->productModel->getByCategory($cat['id']);
            
            $menu[] = [
                'id' => $cat['id'],
                'name' => $lang === 'en' ? $cat['name_en'] : $cat['name_fr'],
                'description' => $lang === 'en' ? $cat['description_en'] : $cat['description_fr'],
                'icon' => $cat['icon'],
                'image' => $cat['image_url'],
                'products' => array_map(function($p) use ($lang) {
                    return $this->formatProduct($p, $lang);
                }, $products)
            ];
        }
        
        Helpers::jsonSuccess($menu);
    }
    
    /**
     * Obtenir les produits d'une catégorie
     * Route: GET /api/products/{categoryId}
     */
    public function getProducts(string $categoryId): void
    {
        $lang = $this->session->getLanguage();
        $products = $this->productModel->getByCategory((int) $categoryId);
        
        $formatted = array_map(function($p) use ($lang) {
            return $this->formatProduct($p, $lang);
        }, $products);
        
        Helpers::jsonSuccess($formatted);
    }
    
    /**
     * Obtenir un produit
     * Route: GET /api/product/{productId}
     */
    public function getProduct(string $productId): void
    {
        $lang = $this->session->getLanguage();
        $product = $this->productModel->getById((int) $productId);
        
        if (!$product) {
            Helpers::jsonError('Produit introuvable', 404);
        }
        
        Helpers::jsonSuccess($this->formatProduct($product, $lang));
    }
    
    /**
     * Mettre à jour le panier (API)
     * Route: POST /api/cart
     */
    public function updateCart(): void
    {
        $tableSession = $this->session->validateTableSession();
        
        if (!$tableSession) {
            Helpers::jsonError('Session expirée', 401);
        }
        
        $data = $this->getJsonInput();
        $action = $data['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $productId = (int) ($data['product_id'] ?? 0);
                $quantity = max(1, (int) ($data['quantity'] ?? 1));
                $instructions = Helpers::sanitize($data['instructions'] ?? '');
                
                $product = $this->productModel->getById($productId);
                if (!$product) {
                    Helpers::jsonError('Produit introuvable', 404);
                }
                
                $cart = $_SESSION['cart'] ?? [];
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
                
                $_SESSION['cart'] = $cart;
                break;
                
            case 'update':
                $key = $data['key'] ?? '';
                $quantity = max(0, (int) ($data['quantity'] ?? 0));
                
                $cart = $_SESSION['cart'] ?? [];
                if (isset($cart[$key])) {
                    if ($quantity > 0) {
                        $cart[$key]['quantity'] = $quantity;
                    } else {
                        unset($cart[$key]);
                    }
                    $_SESSION['cart'] = $cart;
                }
                break;
                
            case 'remove':
                $key = $data['key'] ?? '';
                $cart = $_SESSION['cart'] ?? [];
                unset($cart[$key]);
                $_SESSION['cart'] = $cart;
                break;
                
            case 'clear':
                $_SESSION['cart'] = [];
                break;
        }
        
        $cart = $_SESSION['cart'] ?? [];
        $count = array_sum(array_column($cart, 'quantity'));
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        Helpers::jsonSuccess([
            'cart_count' => $count,
            'cart_total' => $total,
            'cart_total_formatted' => Helpers::formatPrice($total)
        ]);
    }
    
    /**
     * Obtenir le statut d'une commande
     * Route: GET /api/order/{orderId}/status
     */
    public function getOrderStatus(string $orderId): void
    {
        $order = $this->orderModel->getById((int) $orderId);
        
        if (!$order) {
            Helpers::jsonError('Commande introuvable', 404);
        }
        
        $lang = $this->session->getLanguage();
        
        Helpers::jsonSuccess([
            'order_number' => $order['order_number'],
            'status' => $order['status'],
            'status_label' => Helpers::__("order_status.{$order['status']}"),
            'payment_status' => $order['payment_status'],
            'total' => Helpers::formatPrice($order['total_amount']),
            'created_at' => Helpers::formatDate($order['created_at']),
            'items' => array_map(function($item) use ($lang) {
                return [
                    'name' => $lang === 'en' ? $item['name_en'] : $item['name_fr'],
                    'quantity' => $item['quantity'],
                    'status' => $item['status']
                ];
            }, $order['items'])
        ]);
    }
    
    /**
     * Obtenir les commandes pour la cuisine
     * Route: GET /api/kitchen/orders
     */
    public function getKitchenOrders(): void
    {
        // Vérifier authentification staff
        if (!$this->session->hasRole(['admin', 'manager', 'chef'])) {
            Helpers::jsonError('Accès interdit', 403);
        }
        
        $orders = $this->orderModel->getForKitchen();
        
        $formatted = array_map(function($order) {
            $orderData = $this->orderModel->getById($order['id']);
            return [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'table_number' => $order['table_number'],
                'status' => $order['status'],
                'created_at' => Helpers::formatDate($order['created_at']),
                'time_ago' => Helpers::timeAgo($order['created_at']),
                'items' => array_map(function($item) {
                    return [
                        'id' => $item['id'],
                        'name' => $item['name_fr'],
                        'quantity' => $item['quantity'],
                        'instructions' => $item['special_instructions'],
                        'status' => $item['status']
                    ];
                }, $orderData['items'] ?? [])
            ];
        }, $orders);
        
        Helpers::jsonSuccess($formatted);
    }
    
    /**
     * Long Polling pour la cuisine
     * Route: GET /api/kitchen/poll
     */
    public function pollKitchenOrders(): void
    {
        // Vérifier authentification staff
        if (!$this->session->hasRole(['admin', 'manager', 'chef'])) {
            Helpers::jsonError('Accès interdit', 403);
        }
        
        $since = (int) ($_GET['since'] ?? time());
        $timeout = POLLING_TIMEOUT;
        $start = time();
        
        // Libérer la session pour permettre d'autres requêtes concurrentes !
        session_write_close();
        
        while (time() - $start < $timeout) {
            $newOrders = $this->orderModel->getNewOrdersSince($since);
            
            if (!empty($newOrders)) {
                $formatted = array_map(function($order) {
                    $orderData = $this->orderModel->getById($order['id']);
                    return [
                        'id' => $order['id'],
                        'order_number' => $order['order_number'],
                        'table_number' => $order['table_number'],
                        'items' => array_map(function($item) {
                            return [
                                'name' => $item['name_fr'],
                                'quantity' => $item['quantity'],
                                'instructions' => $item['special_instructions']
                            ];
                        }, $orderData['items'] ?? [])
                    ];
                }, $newOrders);
                
                Helpers::jsonSuccess([
                    'orders' => $formatted,
                    'timestamp' => time()
                ]);
            }
            
            sleep(2); // Spécifié par le rapport : sleep(2) en boucle
        }
        
        // Timeout sans nouvelles commandes
        Helpers::jsonSuccess([
            'orders' => [],
            'timestamp' => time()
        ]);
    }
    
    /**
     * Mettre à jour le statut d'une commande
     * Route: POST /api/kitchen/order/{orderId}/status
     */
    public function updateOrderStatus(string $orderId): void
    {
        if (!$this->session->hasRole(['admin', 'manager', 'chef'])) {
            Helpers::jsonError('Accès interdit', 403);
        }
        
        $data = $this->getJsonInput();
        $status = $data['status'] ?? '';
        
        if ($this->orderModel->updateStatus((int) $orderId, $status)) {
            Helpers::jsonSuccess(['status' => $status]);
        } else {
            Helpers::jsonError('Échec mise à jour', 400);
        }
    }
    
    /**
     * Obtenir les traductions
     * Route: GET /api/translations/{lang}
     */
    public function getTranslations(string $lang): void
    {
        if (!in_array($lang, SUPPORTED_LANGUAGES)) {
            $lang = DEFAULT_LANGUAGE;
        }
        
        $file = LANG_PATH . '/' . $lang . '.json';
        
        if (!file_exists($file)) {
            Helpers::jsonError('Traductions non trouvées', 404);
        }
        
        $translations = json_decode(file_get_contents($file), true);
        Helpers::jsonSuccess($translations);
    }
    
    /**
     * Changer la langue
     * Route: POST /api/language
     */
    public function setLanguage(): void
    {
        $data = $this->getJsonInput();
        $lang = $data['lang'] ?? DEFAULT_LANGUAGE;
        
        if (in_array($lang, SUPPORTED_LANGUAGES)) {
            $this->session->setLanguage($lang);
            Helpers::jsonSuccess(['lang' => $lang]);
        } else {
            Helpers::jsonError('Langue non supportée', 400);
        }
    }
    
    /**
     * Formater un produit pour l'API
     */
    private function formatProduct(array $product, string $lang): array
    {
        return [
            'id' => $product['id'],
            'name' => $lang === 'en' ? $product['name_en'] : $product['name_fr'],
            'description' => $lang === 'en' ? $product['description_en'] : $product['description_fr'],
            'price' => $product['price'],
            'price_formatted' => Helpers::formatPrice($product['price']),
            'image' => $product['image_url'],
            'preparation_time' => $product['preparation_time'],
            'available' => $product['is_available'] && ($product['stock_quantity'] ?? 999999) > 0,
            'stock' => $product['stock_quantity'] ?? null
        ];
    }
}
