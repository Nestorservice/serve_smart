<?php
/**
 * SIGR - Visual Aiven Data Verifier
 */

// Aiven Credentials (Loading from Environment for Security)
$host = getenv('DB_HOST') ?: 'mysql-32853e87-nestorservices6-1b99.j.aivencloud.com';
$port = getenv('DB_PORT') ?: 28513;
$dbName = getenv('DB_NAME') ?: 'defaultdb';
$user = getenv('DB_USER') ?: 'avnadmin';
$pass = getenv('DB_PASS') ?: '';

echo "Verifying Sigma Aiven Data...\n";
echo "------------------------------\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "[OK] Connected to Aiven Cloud\n";

    // 1. Categories
    $stmt = $pdo->query("SELECT id, name_fr, name_en FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
    echo "\nCategories found (" . count($categories) . "):\n";
    foreach ($categories as $cat) {
        $stmtProd = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
        $stmtProd->execute([$cat['id']]);
        $total = $stmtProd->fetch()['total'];
        echo " - ID {$cat['id']} | {$cat['name_fr']} ({$cat['name_en']}) : $total dishes\n";
    }

    // 2. Sample Recipes/Dishes
    echo "\nLatest Dishes Added:\n";
    $stmt = $pdo->query("SELECT name_fr, price FROM products LIMIT 5");
    $dishes = $stmt->fetchAll();
    foreach ($dishes as $dish) {
        echo " - " . $dish['name_fr'] . " (" . number_format($dish['price'], 0, '.', ' ') . " FCFA)\n";
    }

    echo "------------------------------\n";
    echo "Verification Success! Your database is fully online.\n";
    
} catch (PDOException $e) {
    die("\n[ERROR] Connection failed: " . $e->getMessage() . "\n");
}
