<?php
/**
 * SIGR - Helpers
 * 
 * Fonctions utilitaires globales pour l'application.
 */

namespace Core {

class Helpers
{
    /**
     * Échapper une chaîne pour prévenir les attaques XSS
     */
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Alias court pour escape
     */
    public static function e(mixed $value): string
    {
        return self::escape($value);
    }
    
    /**
     * Générer une URL complète
     */
    public static function url(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }
    
    /**
     * URL des assets
     */
    public static function asset(string $path): string
    {
        return ASSETS_URL . '/' . ltrim($path, '/');
    }
    
    /**
     * Charger une vue avec des données
     */
    public static function view(string $view, array $data = []): string
    {
        extract($data);
        
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("Vue non trouvée: {$view}");
        }
        
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
    
    /**
     * Rendre une vue et l'afficher
     */
    public static function render(string $view, array $data = []): void
    {
        echo self::view($view, $data);
    }
    
    /**
     * Obtenir une traduction
     */
    public static function trans(string $key, array $replace = [], ?string $lang = null): string
    {
        static $translations = [];
        
        $lang = $lang ?? Session::getInstance()->getLanguage();
        
        // Charger le fichier de traduction si pas en cache
        if (!isset($translations[$lang])) {
            $file = LANG_PATH . '/' . $lang . '.json';
            if (file_exists($file)) {
                $translations[$lang] = json_decode(file_get_contents($file), true) ?? [];
            } else {
                $translations[$lang] = [];
            }
        }
        
        // Récupérer la traduction (supporte les clés imbriquées avec .)
        $value = $translations[$lang];
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return $key; // Retourner la clé si traduction non trouvée
            }
            $value = $value[$segment];
        }
        
        // Remplacer les placeholders
        foreach ($replace as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, $replacement, $value);
        }
        
        return $value;
    }
    
    /**
     * Alias court pour trans()
     */
    public static function __(string $key, array $replace = []): string
    {
        return self::trans($key, $replace);
    }
    
    /**
     * Formater un prix avec la devise
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
     * Formater une date
     */
    public static function formatDate(string|\DateTimeInterface $date, string $format = 'd/m/Y H:i'): string
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }
    
    /**
     * Formater une date relative (il y a X minutes)
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
            return $diff->m . ' mois';
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
     * Générer un numéro de commande unique
     */
    public static function generateOrderNumber(): string
    {
        $prefix = self::getSetting('order_prefix', 'CMD');
        $date = date('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        
        return "{$prefix}-{$date}-{$random}";
    }
    
    /**
     * Obtenir un paramètre de configuration depuis la BDD
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
                
                // Convertir selon le type
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
     * Réponse JSON
     */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Réponse JSON de succès
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
     * Réponse JSON d'erreur
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
     * Vérifier si la requête est AJAX
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Obtenir les données POST en JSON
     */
    public static function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Générer un token CSRF HTML
     */
    public static function csrfField(): string
    {
        $token = Session::getInstance()->generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::e($token) . '">';
    }
    
    /**
     * Valider le token CSRF
     */
    public static function validateCsrf(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return Session::getInstance()->validateCsrfToken($token);
    }
    
    /**
     * Sanitizer une chaîne
     */
    public static function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }
    
    /**
     * Valider un email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Hasher un mot de passe
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
    }
    
    /**
     * Vérifier un mot de passe
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Obtenir l'extension d'un fichier
     */
    public static function getFileExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Générer un nom de fichier unique
     */
    public static function uniqueFilename(string $extension): string
    {
        return uniqid('upload_', true) . '.' . $extension;
    }
    
    /**
     * Tronquer un texte
     */
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Slug pour URL
     */
    public static function slug(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}

} // End namespace Core

// Définir les fonctions globales dans le namespace global
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
