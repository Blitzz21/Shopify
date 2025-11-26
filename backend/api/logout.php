<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/cors.php';

// Enable CORS (same as login/register)
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

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(false, 'Method not allowed. Use POST.', null, 405);
}

// Start session and destroy it
session_start();

// Clear all session variables
$_SESSION = [];

// Delete session cookie (if exists)
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

// Destroy session
session_destroy();

send_json(true, 'Logged out successfully.');
