<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/cors.php';

// Enable CORS
enableCORS();

error_log("CORS Headers Set - Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'No Origin'));

header('Content-Type: application/json');

function send_json(bool $success, string $message, $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(false, 'Method not allowed. Use POST.', null, 405);
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$name = trim($input['name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

// Basic validation
if ($name === '' || $email === '' || $password === '') {
    send_json(false, 'Name, email, and password are required.', null, 422);
}

try {
    // Get database connection
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        send_json(false, 'Email is already registered. Please use a different email.', null, 409);
    }

    // Hash password securely
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($passwordHash === false) {
        throw new Exception('Password hashing failed');
    }

    // Insert user as customer
    $stmt = $pdo->prepare('
        INSERT INTO users (name, email, password_hash, role, created_at, updated_at)
        VALUES (:name, :email, :password_hash, :role, NOW(), NOW())
    ');

    $stmt->execute([
        ':name'          => $name,
        ':email'         => $email,
        ':password_hash' => $passwordHash,
        ':role'          => 'customer',
    ]);

    $userId = (int) $pdo->lastInsertId();

    send_json(true, 'Registration successful. You can now log in.', [
        'user_id' => $userId,
        'name'    => $name,
        'email'   => $email,
        'role'    => 'customer',
    ], 201);

} catch (PDOException $e) {
    error_log('Database error during registration: ' . $e->getMessage());
    send_json(false, 'Database error. Please try again later.', null, 500);
    
} catch (Throwable $e) {
    error_log('Unexpected error during registration: ' . $e->getMessage());
    send_json(false, 'Server error. Please try again later.', null, 500);
}