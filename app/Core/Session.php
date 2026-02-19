<?php
/**
 * SIGR - Session Manager
 * 
 * Gestion des sessions sécurisées pour les tables du restaurant.
 * Chaque client qui scanne un QR code obtient une session unique
 * liée à sa table.
 */

namespace Core;

use Exception;

class Session
{
    private static ?Session $instance = null;
    private Database $db;
    private ?array $tableSession = null;
    private ?array $staffSession = null;
    
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->initPhpSession();
    }
    
    /**
     * Obtenir l'instance unique
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialiser la session PHP native (pour le staff)
     */
    private function initPhpSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'secure' => SESSION_COOKIE_SECURE,
                'httponly' => SESSION_COOKIE_HTTPONLY,
                'samesite' => SESSION_COOKIE_SAMESITE
            ]);
            session_start();
        }
    }
    
    // =========================================
    // SESSIONS CLIENT (par table)
    // =========================================
    
    /**
     * Créer une nouvelle session pour une table
     * 
     * @param int $tableId ID de la table
     * @return string Token de session unique
     */
    public function createTableSession(int $tableId): string
    {
        // Désactiver les anciennes sessions pour cette table
        $this->db->execute(
            "UPDATE table_sessions SET is_active = FALSE WHERE table_id = ? AND is_active = TRUE",
            [$tableId]
        );
        
        // Générer un token unique
        $token = bin2hex(random_bytes(64));
        
        // Calculer l'expiration
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        // Insérer la nouvelle session
        $this->db->insert('table_sessions', [
            'table_id' => $tableId,
            'session_token' => $token,
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires_at' => $expiresAt,
            'is_active' => true
        ]);
        
        // Stocker le token dans un cookie sécurisé
        $this->setTableSessionCookie($token);
        
        return $token;
    }
    
    /**
     * Définir le cookie de session table
     */
    private function setTableSessionCookie(string $token): void
    {
        setcookie('table_session', $token, [
            'expires' => time() + SESSION_LIFETIME,
            'path' => '/',
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE
        ]);
    }
    
    /**
     * Valider et charger la session table depuis le cookie
     * 
     * @return array|null Données de session ou null si invalide
     */
    public function validateTableSession(): ?array
    {
        if ($this->tableSession !== null) {
            return $this->tableSession;
        }
        
        $token = $_COOKIE['table_session'] ?? null;
        if (!$token) {
            return null;
        }
        
        // Récupérer et valider la session
        $session = $this->db->queryOne(
            "SELECT ts.*, rt.table_number, rt.is_active as table_active
             FROM table_sessions ts
             JOIN restaurant_tables rt ON ts.table_id = rt.id
             WHERE ts.session_token = ?
               AND ts.is_active = TRUE
               AND ts.expires_at > NOW()
               AND rt.is_active = TRUE",
            [$token]
        );
        
        if (!$session) {
            // Session invalide, supprimer le cookie
            $this->destroyTableSession();
            return null;
        }
        
        // Mettre à jour l'activité
        $this->db->execute(
            "UPDATE table_sessions SET last_activity = NOW() WHERE id = ?",
            [$session['id']]
        );
        
        $this->tableSession = $session;
        return $session;
    }
    
    /**
     * Obtenir l'ID de la table de la session courante
     */
    public function getTableId(): ?int
    {
        $session = $this->validateTableSession();
        return $session ? (int) $session['table_id'] : null;
    }
    
    /**
     * Obtenir le numéro de table de la session courante
     */
    public function getTableNumber(): ?string
    {
        $session = $this->validateTableSession();
        return $session ? $session['table_number'] : null;
    }
    
    /**
     * Obtenir l'ID de session table
     */
    public function getTableSessionId(): ?int
    {
        $session = $this->validateTableSession();
        return $session ? (int) $session['id'] : null;
    }
    
    /**
     * Vérifier si une session table est valide pour une table spécifique
     */
    public function isValidForTable(int $tableId): bool
    {
        $session = $this->validateTableSession();
        return $session && (int) $session['table_id'] === $tableId;
    }
    
    /**
     * Détruire la session table courante
     */
    public function destroyTableSession(): void
    {
        $token = $_COOKIE['table_session'] ?? null;
        
        if ($token) {
            $this->db->execute(
                "UPDATE table_sessions SET is_active = FALSE WHERE session_token = ?",
                [$token]
            );
        }
        
        // Supprimer le cookie
        setcookie('table_session', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE
        ]);
        
        $this->tableSession = null;
    }
    
    // =========================================
    // SESSIONS STAFF (authentification admin/cuisine/caisse)
    // =========================================
    
    /**
     * Connecter un membre du staff
     */
    public function loginStaff(int $userId, string $role, string $fullName): void
    {
        $_SESSION['staff_id'] = $userId;
        $_SESSION['staff_role'] = $role;
        $_SESSION['staff_name'] = $fullName;
        $_SESSION['staff_logged_at'] = time();
        
        // Régénérer l'ID de session pour sécurité
        session_regenerate_id(true);
        
        // Mettre à jour last_login dans la BDD
        $this->db->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$userId]
        );
    }
    
    /**
     * Vérifier si un staff est connecté
     */
    public function isStaffLoggedIn(): bool
    {
        return isset($_SESSION['staff_id']) && !empty($_SESSION['staff_id']);
    }
    
    /**
     * Obtenir l'ID du staff connecté
     */
    public function getStaffId(): ?int
    {
        return $_SESSION['staff_id'] ?? null;
    }
    
    /**
     * Obtenir le rôle du staff connecté
     */
    public function getStaffRole(): ?string
    {
        return $_SESSION['staff_role'] ?? null;
    }
    
    /**
     * Obtenir le nom du staff connecté
     */
    public function getStaffName(): ?string
    {
        return $_SESSION['staff_name'] ?? null;
    }
    
    /**
     * Vérifier si le staff a un rôle spécifique
     */
    public function hasRole(string|array $roles): bool
    {
        $currentRole = $this->getStaffRole();
        if (!$currentRole) {
            return false;
        }
        
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        // L'admin a accès à tout
        if ($currentRole === 'admin') {
            return true;
        }
        
        return in_array($currentRole, $roles);
    }
    
    /**
     * Déconnecter le staff
     */
    public function logoutStaff(): void
    {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    // =========================================
    // CSRF Protection
    // =========================================
    
    /**
     * Générer un token CSRF
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Valider un token CSRF
     */
    public function validateCsrfToken(?string $token): bool
    {
        if (!$token || !isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Régénérer le token CSRF
     */
    public function regenerateCsrfToken(): string
    {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    // =========================================
    // Langue
    // =========================================
    
    /**
     * Définir la langue de l'utilisateur
     */
    public function setLanguage(string $lang): void
    {
        if (in_array($lang, SUPPORTED_LANGUAGES)) {
            $_SESSION['language'] = $lang;
            setcookie('language', $lang, [
                'expires' => time() + (365 * 24 * 60 * 60),
                'path' => '/',
                'secure' => SESSION_COOKIE_SECURE,
                'httponly' => false, // Accessible en JS
                'samesite' => 'Lax'
            ]);
        }
    }
    
    /**
     * Obtenir la langue courante
     */
    public function getLanguage(): string
    {
        // Priorité: session > cookie > navigateur > défaut
        if (isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }
        
        if (isset($_COOKIE['language']) && in_array($_COOKIE['language'], SUPPORTED_LANGUAGES)) {
            return $_COOKIE['language'];
        }
        
        // Détecter depuis le navigateur
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        foreach (SUPPORTED_LANGUAGES as $lang) {
            if (stripos($acceptLang, $lang) !== false) {
                return $lang;
            }
        }
        
        return DEFAULT_LANGUAGE;
    }
    
    // =========================================
    // Flash Messages
    // =========================================
    
    /**
     * Définir un message flash
     */
    public function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }
    
    /**
     * Obtenir et supprimer les messages flash
     */
    public function getFlash(?string $type = null): array
    {
        if ($type) {
            $messages = $_SESSION['flash'][$type] ?? [];
            unset($_SESSION['flash'][$type]);
            return $messages;
        }
        
        $messages = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $messages;
    }
    
    /**
     * Vérifier s'il y a des messages flash
     */
    public function hasFlash(?string $type = null): bool
    {
        if ($type) {
            return !empty($_SESSION['flash'][$type]);
        }
        return !empty($_SESSION['flash']);
    }
}
