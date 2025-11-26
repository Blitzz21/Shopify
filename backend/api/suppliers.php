<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

enableCORS();
header('Content-Type: application/json');

// Require admin role for all supplier operations
requireAdmin();

// Fix the database connection path
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$pdo = $db->getConnection();

function send_json(bool $success, string $message, $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // List all suppliers
            $stmt = $pdo->query("
                SELECT id, name, code, contact_email, phone, api_base_url, 
                       is_active, created_at, updated_at 
                FROM suppliers 
                ORDER BY name
            ");
            $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_json(true, 'Suppliers retrieved', $suppliers);
            break;

        case 'POST':
            // Create new supplier
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['name']) || !isset($input['code'])) {
                send_json(false, 'Name and code are required', null, 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO suppliers (name, code, contact_email, phone, api_base_url, is_active)
                VALUES (:name, :code, :contact_email, :phone, :api_base_url, :is_active)
            ");

            $stmt->execute([
                ':name' => $input['name'],
                ':code' => $input['code'],
                ':contact_email' => $input['contact_email'] ?? null,
                ':phone' => $input['phone'] ?? null,
                ':api_base_url' => $input['api_base_url'] ?? null,
                ':is_active' => $input['is_active'] ?? 1
            ]);

            $supplierId = $pdo->lastInsertId();
            send_json(true, 'Supplier created successfully', ['id' => $supplierId], 201);
            break;

        case 'PUT':
            // Update supplier
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                send_json(false, 'Supplier ID is required', null, 400);
            }

            $stmt = $pdo->prepare("
                UPDATE suppliers 
                SET name = :name, code = :code, contact_email = :contact_email, 
                    phone = :phone, api_base_url = :api_base_url, is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $input['id'],
                ':name' => $input['name'],
                ':code' => $input['code'],
                ':contact_email' => $input['contact_email'] ?? null,
                ':phone' => $input['phone'] ?? null,
                ':api_base_url' => $input['api_base_url'] ?? null,
                ':is_active' => $input['is_active'] ?? 1
            ]);

            send_json(true, 'Supplier updated successfully');
            break;

        case 'DELETE':
            // Delete supplier
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                send_json(false, 'Supplier ID is required', null, 400);
            }

            $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);

            send_json(true, 'Supplier deleted successfully');
            break;

        default:
            send_json(false, 'Method not allowed', null, 405);
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        send_json(false, 'Supplier code already exists', null, 400);
    }
    send_json(false, 'Database error: ' . $e->getMessage(), null, 500);
} catch (Exception $e) {
    send_json(false, 'Error: ' . $e->getMessage(), null, 500);
}