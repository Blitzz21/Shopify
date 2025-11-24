<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

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

// Start session
session_start();

// Store user info for response before destroying session
$userInfo = null;
if (isset($_SESSION['user_name'])) {
    $userInfo = [
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email']
    ];
}

// Completely destroy session
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

send_json(true, 'Logout successful.', $userInfo);
?>