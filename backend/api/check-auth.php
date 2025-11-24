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

// Check if user is authenticated
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $userData = [
        'user_id' => $_SESSION['user_id'],
        'name'    => $_SESSION['user_name'],
        'email'   => $_SESSION['user_email'],
        'role'    => $_SESSION['user_role'],
        'login_time' => $_SESSION['login_time'] ?? null
    ];
    
    send_json(true, 'User is authenticated.', $userData);
} else {
    send_json(false, 'User not authenticated. Please log in.', null, 401);
}
?>