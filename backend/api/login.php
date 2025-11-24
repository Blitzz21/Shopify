<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/cors.php';

// Enable CORS
enableCORS();

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

// Get input data based on content type
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

// Validation
if ($email === '' || $password === '') {
    send_json(false, 'Email and password are required.', null, 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(false, 'Invalid email address format.', null, 422);
}

try {
    // Get database connection using Database class
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Find active user by email
    $stmt = $pdo->prepare('
        SELECT id, name, email, password_hash, role 
        FROM users 
        WHERE email = ? AND is_active = TRUE 
        LIMIT 1
    ');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Check if user exists and password is correct
    if (!$user) {
        send_json(false, 'Invalid email or password.', null, 401);
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        send_json(false, 'Invalid email or password.', null, 401);
    }

    // Start session and store user data
    session_start();
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Store user information in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Login successful
    send_json(true, 'Login successful.', [
        'user_id' => $user['id'],
        'name'    => $user['name'],
        'email'   => $user['email'],
        'role'    => $user['role'],
    ]);

} catch (PDOException $e) {
    error_log('Database error during login: ' . $e->getMessage());
    send_json(false, 'Database error. Please try again later.', null, 500);
    
} catch (Throwable $e) {
    error_log('Unexpected error during login: ' . $e->getMessage());
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        send_json(false, 'Server error: ' . $e->getMessage(), null, 500);
    } else {
        send_json(false, 'Server error. Please try again later.', null, 500);
    }
}
?>