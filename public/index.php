<?php
/**
 * SIGR - Main Entry Point
 * 
 * All requests pass through this file.
 */

// Load configuration
require_once dirname(__DIR__) . '/app/Config/config.php';

// Load global helpers
require_once APP_PATH . '/Core/Helpers.php';

use Core\Router;
use Core\Session;
use Core\Helpers;

// Initialize session
$session = Session::getInstance();

// Create router
$router = new Router();

// =========================================
// PUBLIC ROUTES (Client via QR Code)
// =========================================

// Home page / QR Scanner
$router->get('/', function() {
    Helpers::render('home');
}, 'home');

// Scan a table via QR Code
$router->get('/table/{tableNumber}', 'ClientController@scanTable', 'table.scan');

// =========================================
// CLIENT ROUTES (requires table session)
// =========================================

$router->group('/client', function(Router $r) {
    // These routes require a valid table session
    $r->get('/menu', 'ClientController@showMenu', 'client.menu');
    $r->get('/menu/{categoryId}', 'ClientController@showCategory', 'client.category');
    $r->get('/product/{productId}', 'ClientController@showProduct', 'client.product');
    
    // Cart
    $r->get('/cart', 'ClientController@showCart', 'client.cart');
    $r->post('/cart/add', 'ClientController@addToCart', 'client.cart.add');
    $r->post('/cart/update', 'ClientController@updateCart', 'client.cart.update');
    $r->post('/cart/remove', 'ClientController@removeFromCart', 'client.cart.remove');
    
    // Order
    $r->post('/order', 'ClientController@placeOrder', 'client.order');
    $r->get('/order-tracking', 'ClientController@trackLatestOrder', 'client.order.tracking.latest');
    $r->get('/order/{orderId}', 'ClientController@trackOrder', 'client.order.track');
    
    // Payment
    $r->get('/payment/{orderId}', 'ClientController@showPayment', 'client.payment');
    $r->post('/payment/process', 'ClientController@processPayment', 'client.payment.process');
    
    // Ticket / Receipt
    $r->get('/ticket/{orderId}', 'ClientController@showTicket', 'client.ticket');
});

// =========================================
// API ROUTES (JSON)
// =========================================

$router->group('/api', function(Router $r) {
    // Public API
    $r->get('/menu', 'ApiController@getMenu', 'api.menu');
    $r->get('/products/{categoryId}', 'ApiController@getProducts', 'api.products');
    $r->get('/product/{productId}', 'ApiController@getProduct', 'api.product');
    
    // Client API (requires table session)
    $r->post('/cart', 'ApiController@updateCart', 'api.cart');
    $r->get('/order/{orderId}/status', 'ApiController@getOrderStatus', 'api.order.status');
    
    // Kitchen API (Long Polling)
    $r->get('/kitchen/orders', 'ApiController@getKitchenOrders', 'api.kitchen.orders');
    $r->get('/kitchen/poll', 'ApiController@pollKitchenOrders', 'api.kitchen.poll');
    $r->post('/kitchen/order/{orderId}/status', 'ApiController@updateOrderStatus', 'api.kitchen.status');
    
    // Translations
    $r->get('/translations/{lang}', 'ApiController@getTranslations', 'api.translations');
    
    // Change language
    $r->post('/language', 'ApiController@setLanguage', 'api.language');
});

// =========================================
// KITCHEN ROUTES
// =========================================

$router->group('/kitchen', function(Router $r) {
    $r->get('/', 'KitchenController@display', 'kitchen.display');
    $r->get('/login', 'KitchenController@loginForm', 'kitchen.login');
    $r->post('/login', 'KitchenController@login', 'kitchen.login.post');
    $r->post('/status/{id}', 'KitchenController@updateStatus', 'kitchen.status.update');
});

// =========================================
// CASHIER ROUTES
// =========================================

$router->group('/cashier', function(Router $r) {
    $r->get('/', 'CashierController@dashboard', 'cashier.dashboard');
    $r->get('/orders', 'CashierController@orders', 'cashier.orders');
    $r->get('/order/{orderId}', 'CashierController@orderDetails', 'cashier.order');
    $r->post('/payment/{orderId}', 'CashierController@processPayment', 'cashier.payment');
});

// =========================================
// ADMINISTRATION ROUTES
// =========================================

$router->group('/admin', function(Router $r) {
    // Authentication
    $r->get('/login', 'AuthController@loginForm', 'admin.login');
    $r->post('/login', 'AuthController@login', 'admin.login.post');
    $r->post('/logout', 'AuthController@logout', 'admin.logout');
    
    // Dashboard (requires auth)
    $r->get('/', 'AdminController@dashboard', 'admin.dashboard');
    $r->get('/dashboard', 'AdminController@dashboard', 'admin.dashboard.alt');
    
    // Menu Management
    $r->get('/menu', 'AdminController@menuList', 'admin.menu');
    $r->get('/menu/add', 'AdminController@menuCreate', 'admin.menu.create');
    $r->get('/menu/create', 'AdminController@menuCreate', 'admin.menu.create.alt');
    $r->post('/menu/store', 'AdminController@menuStore', 'admin.menu.store');
    $r->get('/menu/edit/{id}', 'AdminController@menuEdit', 'admin.menu.edit');
    $r->post('/menu/update/{id}', 'AdminController@menuUpdate', 'admin.menu.update');
    $r->post('/menu/toggle/{id}', 'AdminController@menuToggle', 'admin.menu.toggle');
    $r->post('/menu/delete/{id}', 'AdminController@menuDelete', 'admin.menu.delete');
    
    // Categories
    $r->get('/categories', 'AdminController@categoryList', 'admin.categories');
    $r->post('/categories/store', 'AdminController@categoryStore', 'admin.categories.store');
    $r->post('/categories/{id}/update', 'AdminController@categoryUpdate', 'admin.categories.update');
    $r->post('/categories/{id}/delete', 'AdminController@categoryDelete', 'admin.categories.delete');
    
    // Stocks
    $r->get('/stock', 'AdminController@stockList', 'admin.stock');
    $r->post('/stock/update', 'AdminController@stockUpdate', 'admin.stock.update');
    $r->post('/stock/{id}/update', 'AdminController@stockUpdate', 'admin.stock.update.alt');
    $r->get('/stock/alerts', 'AdminController@stockAlerts', 'admin.stock.alerts');
    
    // Tables & QR Codes
    $r->get('/tables', 'AdminController@tableList', 'admin.tables');
    $r->post('/tables/store', 'AdminController@tableStore', 'admin.tables.store');
    $r->get('/tables/{id}/qr', 'AdminController@generateQR', 'admin.tables.qr');
    $r->get('/tables/qr-all', 'AdminController@generateAllQR', 'admin.tables.qr.all');
    
    // Orders
    $r->get('/orders', 'AdminController@orderList', 'admin.orders');
    $r->get('/orders/{id}', 'AdminController@orderDetails', 'admin.orders.details');
    $r->post('/orders/{id}/status', 'AdminController@orderStatusUpdate', 'admin.orders.status');
    
    // Statistics
    $r->get('/stats', 'AdminController@stats', 'admin.stats');
    $r->get('/stats/sales', 'AdminController@salesStats', 'admin.stats.sales');
    
    // Settings
    $r->get('/settings', 'AdminController@settings', 'admin.settings');
    $r->post('/settings', 'AdminController@settingsUpdate', 'admin.settings.update');
    $r->post('/settings/save', 'AdminController@settingsUpdate', 'admin.settings.save');
    
    // Users
    $r->get('/users', 'AdminController@userList', 'admin.users');
    $r->post('/users/store', 'AdminController@userStore', 'admin.users.store');
    $r->post('/users/{id}/update', 'AdminController@userUpdate', 'admin.users.update');
});

// =========================================
// DISPATCH
// =========================================

$router->dispatch();
