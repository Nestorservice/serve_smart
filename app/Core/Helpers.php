<?php
/**
 * SIGR - Helpers
 * 
 * Global utility functions for the application.
 */

namespace Core {

class Helpers
{
    /**
     * Escape a string to prevent XSS attacks
     */
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Short alias for escape
     */
    public static function e(mixed $value): string
    {
        return self::escape($value);
    }
    
    /**
     * Generate a full URL
     */
    public static function url(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }
    
    /**
     * Asset URL
     */
    public static function asset(string $path): string
    {
        return ASSETS_URL . '/' . ltrim($path, '/');
    }
    
    /**
     * Load a view with data
     */
    public static function view(string $view, array $data = []): string
    {
        extract($data);
        
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$view}");
        }
        
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
    
    /**
     * Render and display a view
     */
    public static function render(string $view, array $data = []): void
    {
        echo self::view($view, $data);
    }
    
    /**
     * Get a translation
     */
    public static function trans(string $key, array $replace = [], ?string $lang = null): string
    {
        static $translations = [];
        
        $lang = $lang ?? Session::getInstance()->getLanguage();
        
        // Load translation file if not cached
        if (!isset($translations[$lang])) {
            $file = LANG_PATH . '/' . $lang . '.json';
            if (file_exists($file)) {
                $translations[$lang] = json_decode(file_get_contents($file), true) ?? [];
            } else {
                $translations[$lang] = [];
            }
        }
        
        // Get translation (supports nested keys with .)
        $value = $translations[$lang];
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return $key; // Return key if translation not found
            }
            $value = $value[$segment];
        }
        
        // Replace placeholders
        foreach ($replace as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, $replacement, $value);
        }
        
        return $value;
    }
    
    /**
     * Short alias for trans()
     */
    public static function __(string $key, array $replace = []): string
    {
        return self::trans($key, $replace);
    }
    
    /**
     * Format price with currency
     */
    public static function formatPrice(float|int $amount, bool $showSymbol = true): string
    {
        $formatted = number_format($amount, 0, ',', ' ');
        
        if ($showSymbol) {
            return $formatted . ' FCFA';
        }
        
        return $formatted;
    }
    
    /**
     * Format a date
     */
    public static function formatDate(string|\DateTimeInterface $date, string $format = 'd/m/Y H:i'): string
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }
    
    /**
     * Format relative date (X minutes ago)
     */
    public static function timeAgo(string|\DateTimeInterface $date): string
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        $now = new \DateTime();
        $diff = $now->diff($date);
        
        $lang = Session::getInstance()->getLanguage();
        
        if ($diff->y > 0) {
            return $diff->y . ($lang === 'fr' ? ' an(s)' : ' year(s)');
        }
        if ($diff->m > 0) {
            return $diff->m . ($lang === 'fr' ? ' mois' : ' month(s)');
        }
        if ($diff->d > 0) {
            return $diff->d . ($lang === 'fr' ? ' jour(s)' : ' day(s)');
        }
        if ($diff->h > 0) {
            return $diff->h . ($lang === 'fr' ? ' heure(s)' : ' hour(s)');
        }
        if ($diff->i > 0) {
            return $diff->i . ' min';
        }
        
        return $lang === 'fr' ? 'À l\'instant' : 'Just now';
    }
    
    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = self::getSetting('order_prefix', 'ORD');
        $date = date('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        
        return "{$prefix}-{$date}-{$random}";
    }
    
    /**
     * Get configuration parameter from DB
     */
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        static $settings = null;
        
        if ($settings === null) {
            $db = Database::getInstance();
            $rows = $db->query("SELECT setting_key, setting_value, setting_type FROM settings");
            
            $settings = [];
            foreach ($rows as $row) {
                $value = $row['setting_value'];
                
                // Convert according to type
                switch ($row['setting_type']) {
                    case 'number':
                        $value = is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : 0;
                        break;
                    case 'boolean':
                        $value = in_array(strtolower($value), ['true', '1', 'yes', 'oui']);
                        break;
                    case 'json':
                        $value = json_decode($value, true);
                        break;
                }
                
                $settings[$row['setting_key']] = $value;
            }
        }
        
        return $settings[$key] ?? $default;
    }
    
    /**
     * JSON Response
     */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Success JSON response
     */
    public static function jsonSuccess(mixed $data = null, string $message = 'Success'): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Error JSON response
     */
    public static function jsonError(string $message, int $status = 400, mixed $errors = null): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
    
    /**
     * Check if request is AJAX
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get JSON POST data
     */
    public static function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Generate HTML CSRF token
     */
    public static function csrfField(): string
    {
        $token = Session::getInstance()->generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::e($token) . '">';
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrf(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return Session::getInstance()->validateCsrfToken($token);
    }
    
    /**
     * Sanitize a string
     */
    public static function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }
    
    /**
     * Validate an email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Hash a password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
    }
    
    /**
     * Verify a password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Get file extension
     */
    public static function getFileExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Generate unique filename
     */
    public static function uniqueFilename(string $extension): string
    {
        return uniqid('upload_', true) . '.' . $extension;
    }
    
    /**
     * Truncate text
     */
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }
    
    /**
     * URL Slug
     */
    public static function slug(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}

} // End namespace Core

// Define global functions in global namespace
namespace {
    use Core\Helpers;
    
    if (!function_exists('e')) {
        function e(mixed $value): string { return Helpers::escape($value); }
    }
    if (!function_exists('url')) {
        function url(string $path = ''): string { return Helpers::url($path); }
    }
    if (!function_exists('asset')) {
        function asset(string $path): string { return Helpers::asset($path); }
    }
    if (!function_exists('__')) {
        function __(string $key, array $replace = []): string { return Helpers::trans($key, $replace); }
    }
    if (!function_exists('csrf_field')) {
        function csrf_field(): string { return Helpers::csrfField(); }
    }
    if (!function_exists('config')) {
        function config(string $key, mixed $default = null): mixed { return Helpers::getSetting($key, $default); }
    }
}
