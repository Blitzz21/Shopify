<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

header('Content-Type: application/json');

// Only admin users can access the print station
requireAdmin();

function send_json(bool $success, string $message, $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

// Get current user info
$currentUser = getCurrentUser();

try {
    // Your print station logic here
    // For now, just return a success message with user info
    
    send_json(true, 'Welcome to the Print Station!', [
        'user' => $currentUser,
        'print_station_info' => [
            'status' => 'operational',
            'printers_online' => 3,
            'queue_length' => 12,
            'today_prints' => 47
        ]
    ]);

} catch (Throwable $e) {
    error_log('Print station error: ' . $e->getMessage());
    send_json(false, 'Server error. Please try again later.', null, 500);
}
?>