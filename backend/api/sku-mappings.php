<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

enableCORS();
header('Content-Type: application/json');
requireAdmin();

// Fix the database connection path
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$pdo = $db->getConnection();

function send_json(bool $success, string $message, $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // List all SKU mappings with product and supplier info
            $stmt = $pdo->query("
                SELECT sm.*, p.name as product_name, s.name as supplier_name, s.code as supplier_code
                FROM sku_mappings sm
                JOIN products p ON sm.product_id = p.id
                JOIN suppliers s ON sm.supplier_id = s.id
                ORDER BY p.name, s.name
            ");
            $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_json(true, 'SKU mappings retrieved', $mappings);
            break;

        case 'POST':
            // Create new SKU mapping
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['product_id', 'supplier_id', 'supplier_sku'];
            foreach ($required as $field) {
                if (!isset($input[$field])) {
                    send_json(false, "Missing required field: $field", null, 400);
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO sku_mappings 
                (product_id, supplier_id, supplier_sku, default_color, default_size, notes, is_active)
                VALUES (:product_id, :supplier_id, :supplier_sku, :default_color, :default_size, :notes, :is_active)
            ");

            $stmt->execute([
                ':product_id' => $input['product_id'],
                ':supplier_id' => $input['supplier_id'],
                ':supplier_sku' => $input['supplier_sku'],
                ':default_color' => $input['default_color'] ?? null,
                ':default_size' => $input['default_size'] ?? null,
                ':notes' => $input['notes'] ?? null,
                ':is_active' => $input['is_active'] ?? 1
            ]);

            $mappingId = $pdo->lastInsertId();
            send_json(true, 'SKU mapping created successfully', ['id' => $mappingId], 201);
            break;

        case 'PUT':
            // Update SKU mapping
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                send_json(false, 'Mapping ID is required', null, 400);
            }

            $stmt = $pdo->prepare("
                UPDATE sku_mappings 
                SET supplier_sku = :supplier_sku, default_color = :default_color, 
                    default_size = :default_size, notes = :notes, is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $input['id'],
                ':supplier_sku' => $input['supplier_sku'],
                ':default_color' => $input['default_color'] ?? null,
                ':default_size' => $input['default_size'] ?? null,
                ':notes' => $input['notes'] ?? null,
                ':is_active' => $input['is_active'] ?? 1
            ]);

            send_json(true, 'SKU mapping updated successfully');
            break;

        case 'DELETE':
            // Delete SKU mapping
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                send_json(false, 'Mapping ID is required', null, 400);
            }

            $stmt = $pdo->prepare("DELETE FROM sku_mappings WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);

            send_json(true, 'SKU mapping deleted successfully');
            break;

        default:
            send_json(false, 'Method not allowed', null, 405);
    }

} catch (PDOException $e) {
    send_json(false, 'Database error: ' . $e->getMessage(), null, 500);
} catch (Exception $e) {
    send_json(false, 'Error: ' . $e->getMessage(), null, 500);
}