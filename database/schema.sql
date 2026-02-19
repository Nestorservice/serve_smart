-- ============================================
-- SIGR - Système Intégré de Gestion de Restaurant
-- Base de données MySQL
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS sigr_restaurant 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sigr_restaurant;

-- ============================================
-- TABLE: restaurant_tables
-- Tables physiques du restaurant
-- ============================================
DROP TABLE IF EXISTS restaurant_tables;
CREATE TABLE restaurant_tables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_number VARCHAR(10) NOT NULL UNIQUE,
    qr_code_token VARCHAR(64) NOT NULL UNIQUE,
    capacity INT DEFAULT 4,
    zone VARCHAR(50) DEFAULT 'Principale',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_token (qr_code_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: table_sessions
-- Sessions client sécurisées par table
-- ============================================
DROP TABLE IF EXISTS table_sessions;
CREATE TABLE table_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_id INT NOT NULL,
    session_token VARCHAR(128) NOT NULL UNIQUE,
    client_ip VARCHAR(45),
    user_agent TEXT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: categories
-- Catégories du menu (bilingue)
-- ============================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    description_fr TEXT,
    description_en TEXT,
    icon VARCHAR(50) DEFAULT 'bi-grid',
    image_url VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (display_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: products
-- Produits/Plats du menu (bilingue)
-- ============================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name_fr VARCHAR(150) NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    description_fr TEXT,
    description_en TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    preparation_time INT DEFAULT 15 COMMENT 'Minutes',
    is_available BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    requires_stock BOOLEAN DEFAULT FALSE COMMENT 'Si TRUE, vérifie le stock',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_category (category_id),
    INDEX idx_available (is_available),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: stock
-- Gestion des stocks (boissons, ingrédients)
-- ============================================
DROP TABLE IF EXISTS stock;
CREATE TABLE stock (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL UNIQUE,
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'unité',
    low_threshold INT DEFAULT 10 COMMENT 'Seuil alerte stock bas',
    last_restock_at TIMESTAMP NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_quantity (quantity),
    INDEX idx_low (quantity, low_threshold)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: users
-- Utilisateurs staff du restaurant
-- ============================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'chef', 'waiter', 'cashier') NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    avatar_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: orders
-- Commandes clients
-- ============================================
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    table_id INT NOT NULL,
    table_session_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'pending', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method ENUM('cash', 'orange_money', 'mtn_money', 'moov', 'card') NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Pourcentage TVA',
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes TEXT,
    client_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    ready_at TIMESTAMP NULL,
    served_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE RESTRICT,
    FOREIGN KEY (table_session_id) REFERENCES table_sessions(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_payment (payment_status),
    INDEX idx_table (table_id),
    INDEX idx_created (created_at),
    INDEX idx_number (order_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: order_items
-- Items de chaque commande
-- ============================================
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    special_instructions TEXT,
    status ENUM('pending', 'preparing', 'ready', 'served', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: payments
-- Historique des paiements
-- ============================================
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('cash', 'orange_money', 'mtn_money', 'moov', 'card') NOT NULL,
    transaction_ref VARCHAR(100),
    phone_number VARCHAR(20) COMMENT 'Pour Mobile Money',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    processed_by INT NULL COMMENT 'ID caissier si cash',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_method (method),
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: stock_history
-- Historique mouvements de stock
-- ============================================
DROP TABLE IF EXISTS stock_history;
CREATE TABLE stock_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    quantity_change INT NOT NULL COMMENT 'Positif = ajout, négatif = retrait',
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    reason ENUM('sale', 'restock', 'adjustment', 'waste', 'return') NOT NULL,
    order_id INT NULL,
    user_id INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_reason (reason),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: alerts
-- Alertes système (stock bas, commandes, etc.)
-- ============================================
DROP TABLE IF EXISTS alerts;
CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('low_stock', 'new_order', 'order_ready', 'payment_pending', 'system') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    reference_type VARCHAR(50) COMMENT 'product, order, payment, etc.',
    reference_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    read_by INT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (read_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_read (is_read),
    INDEX idx_priority (priority),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: settings
-- Configuration du système
-- ============================================
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- Configuration par défaut
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('restaurant_name', 'SIGR Restaurant', 'string', 'Nom du restaurant'),
('restaurant_name_short', 'SIGR', 'string', 'Nom court du restaurant'),
('currency', 'XAF', 'string', 'Devise utilisée'),
('currency_symbol', 'FCFA', 'string', 'Symbole de la devise'),
('tax_rate', '0', 'number', 'Taux de TVA en pourcentage'),
('order_prefix', 'CMD', 'string', 'Préfixe des numéros de commande'),
('session_duration', '10800', 'number', 'Durée session client en secondes (3h)'),
('low_stock_threshold', '10', 'number', 'Seuil par défaut alerte stock bas'),
('enable_mobile_payment', 'true', 'boolean', 'Activer paiements Mobile Money'),
('enable_card_payment', 'false', 'boolean', 'Activer paiements carte'),
('default_language', 'fr', 'string', 'Langue par défaut'),
('kitchen_sound_enabled', 'true', 'boolean', 'Activer sons en cuisine');

-- Utilisateur admin par défaut (mot de passe: admin123)
-- Hash généré avec: password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12])
INSERT INTO users (username, email, password_hash, role, full_name) VALUES
('admin', 'admin@sigr.local', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4p6ZL0xVxQhkMGCO', 'admin', 'Administrateur');

-- Tables du restaurant (exemple 10 tables)
INSERT INTO restaurant_tables (table_number, qr_code_token, capacity, zone) VALUES
('01', UPPER(SHA2(CONCAT('table01', RAND(), NOW()), 256)), 2, 'Terrasse'),
('02', UPPER(SHA2(CONCAT('table02', RAND(), NOW()), 256)), 2, 'Terrasse'),
('03', UPPER(SHA2(CONCAT('table03', RAND(), NOW()), 256)), 4, 'Terrasse'),
('04', UPPER(SHA2(CONCAT('table04', RAND(), NOW()), 256)), 4, 'Salle Principale'),
('05', UPPER(SHA2(CONCAT('table05', RAND(), NOW()), 256)), 4, 'Salle Principale'),
('06', UPPER(SHA2(CONCAT('table06', RAND(), NOW()), 256)), 6, 'Salle Principale'),
('07', UPPER(SHA2(CONCAT('table07', RAND(), NOW()), 256)), 6, 'Salle Principale'),
('08', UPPER(SHA2(CONCAT('table08', RAND(), NOW()), 256)), 8, 'Salle VIP'),
('09', UPPER(SHA2(CONCAT('table09', RAND(), NOW()), 256)), 8, 'Salle VIP'),
('10', UPPER(SHA2(CONCAT('table10', RAND(), NOW()), 256)), 10, 'Salle VIP');

-- Catégories du menu
INSERT INTO categories (name_fr, name_en, description_fr, description_en, icon, display_order) VALUES
('Entrées', 'Starters', 'Nos délicieuses entrées pour commencer', 'Our delicious starters to begin', 'bi-egg-fried', 1),
('Plats Principaux', 'Main Courses', 'Plats traditionnels et modernes', 'Traditional and modern dishes', 'bi-cup-hot', 2),
('Grillades', 'Grilled', 'Viandes et poissons grillés', 'Grilled meats and fish', 'bi-fire', 3),
('Accompagnements', 'Sides', 'Pour accompagner vos plats', 'To accompany your dishes', 'bi-basket', 4),
('Boissons', 'Beverages', 'Boissons fraîches et chaudes', 'Cold and hot beverages', 'bi-cup-straw', 5),
('Desserts', 'Desserts', 'Finissez en douceur', 'End on a sweet note', 'bi-cake', 6);

-- Exemples de produits
INSERT INTO products (category_id, name_fr, name_en, description_fr, description_en, price, preparation_time, requires_stock) VALUES
-- Entrées
(1, 'Salade Verte', 'Green Salad', 'Salade fraîche de saison', 'Fresh seasonal salad', 2500, 5, FALSE),
(1, 'Soupe du Jour', 'Soup of the Day', 'Soupe maison du jour', 'Homemade soup of the day', 3000, 10, FALSE),
-- Plats Principaux
(2, 'Poulet Braisé', 'Grilled Chicken', 'Poulet braisé aux épices africaines', 'Chicken grilled with African spices', 5500, 25, FALSE),
(2, 'Poisson Braisé', 'Grilled Fish', 'Poisson frais du jour braisé', 'Fresh grilled fish of the day', 6500, 30, FALSE),
(2, 'Riz Sauce Arachide', 'Rice with Peanut Sauce', 'Riz accompagné de sauce arachide', 'Rice with peanut sauce', 4000, 20, FALSE),
-- Grillades
(3, 'Brochettes de Boeuf', 'Beef Skewers', '4 brochettes de boeuf grillées', '4 grilled beef skewers', 4500, 15, FALSE),
(3, 'Côtes de Porc', 'Pork Ribs', 'Côtes de porc marinées', 'Marinated pork ribs', 5000, 20, FALSE),
-- Accompagnements
(4, 'Plantain Frit', 'Fried Plantain', 'Bananes plantain frites', 'Fried plantain bananas', 1500, 10, FALSE),
(4, 'Attiéké', 'Attiéké', 'Semoule de manioc', 'Cassava couscous', 1000, 5, FALSE),
-- Boissons (avec gestion de stock)
(5, 'Coca-Cola', 'Coca-Cola', 'Canette 33cl', '33cl can', 1000, 1, TRUE),
(5, 'Fanta Orange', 'Fanta Orange', 'Canette 33cl', '33cl can', 1000, 1, TRUE),
(5, 'Eau Minérale', 'Mineral Water', 'Bouteille 50cl', '50cl bottle', 500, 1, TRUE),
(5, 'Jus de Gingembre', 'Ginger Juice', 'Verre 25cl fait maison', 'Homemade 25cl glass', 1500, 5, FALSE),
(5, 'Bière Locale', 'Local Beer', 'Bouteille 65cl', '65cl bottle', 1500, 1, TRUE),
-- Desserts
(6, 'Fruits de Saison', 'Seasonal Fruits', 'Assiette de fruits frais', 'Fresh fruit plate', 2000, 5, FALSE),
(6, 'Gâteau Chocolat', 'Chocolate Cake', 'Part de gâteau maison', 'Slice of homemade cake', 2500, 3, TRUE);

-- Stock initial pour les produits avec gestion de stock
INSERT INTO stock (product_id, quantity, unit, low_threshold) 
SELECT id, 50, 'unité', 10 FROM products WHERE requires_stock = TRUE;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- FIN DU SCRIPT
-- ============================================
