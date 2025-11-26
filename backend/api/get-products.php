<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

enableCORS();
header('Content-Type: application/json');
requireAdmin();

require_once __DIR__ . '/../config/database.php';
$db = new Database();
$pdo = $db->getConnection();

try {
    $stmt = $pdo->query("
        SELECT id, name, description, base_price, product_type, is_active 
        FROM products 
        WHERE is_active = 1 
        ORDER BY name
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Products retrieved',
        'data' => $products
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}