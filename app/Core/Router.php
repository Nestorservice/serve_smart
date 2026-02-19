<?php
/**
 * SIGR - Router
 * 
 * Système de routage simple pour gérer les URLs.
 * Supporte les routes avec paramètres et les groupes par préfixe.
 */

namespace Core;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private string $prefix = '';
    private array $middlewares = [];
    
    /**
     * Ajouter un préfixe de groupe
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $this->prefix = $previousPrefix . $prefix;
        $callback($this);
        $this->prefix = $previousPrefix;
    }
    
    /**
     * Ajouter une route GET
     */
    public function get(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }
    
    /**
     * Ajouter une route POST
     */
    public function post(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }
    
    /**
     * Ajouter une route pour plusieurs méthodes
     */
    public function match(array $methods, string $path, callable|array|string $handler, ?string $name = null): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler, $name);
        }
        return $this;
    }
    
    /**
     * Ajouter une route pour toutes les méthodes
     */
    public function any(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->match(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $path, $handler, $name);
    }
    
    /**
     * Ajouter une route
     */
    private function addRoute(string $method, string $path, callable|array|string $handler, ?string $name = null): self
    {
        $fullPath = $this->prefix . $path;
        
        // Normaliser le path (retirer le trailing slash, comme dans resolve)
        $fullPath = rtrim($fullPath, '/') ?: '/';
        
        // Convertir les paramètres {param} en regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $fullPath);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => $pattern,
            'handler' => $handler,
            'name' => $name,
            'middlewares' => $this->middlewares
        ];
        
        if ($name) {
            $this->namedRoutes[$name] = $fullPath;
        }
        
        return $this;
    }
    
    /**
     * Ajouter un middleware
     */
    public function middleware(string|array $middlewares): self
    {
        $this->middlewares = array_merge($this->middlewares, (array) $middlewares);
        return $this;
    }
    
    /**
     * Résoudre la route pour une requête
     */
    public function resolve(string $method, string $uri): ?array
    {
        // Nettoyer l'URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        
        // Retirer le base path si présent
        $basePath = BASE_URL;
        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extraire les paramètres nommés
                $params = array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
                
                return [
                    'handler' => $route['handler'],
                    'params' => $params,
                    'middlewares' => $route['middlewares'],
                    'name' => $route['name']
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Dispatcher la requête
     */
    public function dispatch(?string $uri = null, ?string $method = null): mixed
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'];
        $method = $method ?? $_SERVER['REQUEST_METHOD'];
        
        $route = $this->resolve($method, $uri);
        
        if (!$route) {
            return $this->handleNotFound();
        }
        
        // Exécuter les middlewares
        foreach ($route['middlewares'] as $middleware) {
            $result = $this->runMiddleware($middleware);
            if ($result !== true) {
                return $result;
            }
        }
        
        // Exécuter le handler
        return $this->runHandler($route['handler'], $route['params']);
    }
    
    /**
     * Exécuter un handler
     */
    private function runHandler(callable|array|string $handler, array $params): mixed
    {
        // Handler sous forme de string "Controller@method"
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler);
            $controller = "\\Controllers\\{$controller}";
            $instance = new $controller();
            return call_user_func_array([$instance, $method], $params);
        }
        
        // Handler sous forme de tableau [Controller::class, 'method']
        if (is_array($handler)) {
            [$controller, $method] = $handler;
            if (is_string($controller)) {
                $controller = new $controller();
            }
            return call_user_func_array([$controller, $method], $params);
        }
        
        // Handler sous forme de callable
        return call_user_func_array($handler, $params);
    }
    
    /**
     * Exécuter un middleware
     */
    private function runMiddleware(string $middleware): mixed
    {
        $middlewareClass = "\\Middlewares\\{$middleware}";
        
        if (class_exists($middlewareClass)) {
            $instance = new $middlewareClass();
            return $instance->handle();
        }
        
        // Middlewares intégrés
        return match($middleware) {
            'auth' => $this->authMiddleware(),
            'guest' => $this->guestMiddleware(),
            'table' => $this->tableMiddleware(),
            'admin' => $this->adminMiddleware(),
            'kitchen' => $this->kitchenMiddleware(),
            'cashier' => $this->cashierMiddleware(),
            default => true
        };
    }
    
    /**
     * Middleware d'authentification staff
     */
    private function authMiddleware(): mixed
    {
        $session = Session::getInstance();
        if (!$session->isStaffLoggedIn()) {
            return $this->redirect('/admin/login');
        }
        return true;
    }
    
    /**
     * Middleware invité (non connecté)
     */
    private function guestMiddleware(): mixed
    {
        $session = Session::getInstance();
        if ($session->isStaffLoggedIn()) {
            return $this->redirect('/admin');
        }
        return true;
    }
    
    /**
     * Middleware session table (client)
     */
    private function tableMiddleware(): mixed
    {
        $session = Session::getInstance();
        if (!$session->validateTableSession()) {
            return $this->redirect('/');
        }
        return true;
    }
    
    /**
     * Middleware admin seulement
     */
    private function adminMiddleware(): mixed
    {
        $session = Session::getInstance();
        if (!$session->hasRole(['admin', 'manager'])) {
            $this->handleForbidden();
            return false;
        }
        return true;
    }
    
    /**
     * Middleware cuisine
     */
    private function kitchenMiddleware(): mixed
    {
        $session = Session::getInstance();
        if (!$session->hasRole(['admin', 'manager', 'chef'])) {
            $this->handleForbidden();
            return false;
        }
        return true;
    }
    
    /**
     * Middleware caisse
     */
    private function cashierMiddleware(): mixed
    {
        $session = Session::getInstance();
        if (!$session->hasRole(['admin', 'manager', 'cashier'])) {
            $this->handleForbidden();
            return false;
        }
        return true;
    }
    
    /**
     * Générer une URL pour une route nommée
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route '{$name}' non trouvée");
        }
        
        $path = $this->namedRoutes[$name];
        
        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }
        
        return BASE_URL . $path;
    }
    
    /**
     * Redirection
     */
    public function redirect(string $url, int $status = 302): never
    {
        header("Location: " . BASE_URL . $url, true, $status);
        exit;
    }
    
    /**
     * Gestion 404
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            require VIEWS_PATH . '/errors/404.php';
        } else {
            echo '<h1>404 - Page non trouvée</h1>';
        }
        exit;
    }
    
    /**
     * Gestion 403
     */
    private function handleForbidden(): void
    {
        http_response_code(403);
        if (file_exists(VIEWS_PATH . '/errors/403.php')) {
            require VIEWS_PATH . '/errors/403.php';
        } else {
            echo '<h1>403 - Accès interdit</h1>';
        }
        exit;
    }
}
