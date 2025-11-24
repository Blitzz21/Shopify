<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

header('Content-Type: application/json');

// Any authenticated user can access their dashboard
requireAuth();

function send_json(bool $success, string $message, $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

$currentUser = getCurrentUser();

try {
    // Customer dashboard logic here
    send_json(true, 'Welcome to your Dashboard!', [
        'user' => $currentUser,
        'dashboard_stats' => [
            'total_orders' => 5,
            'pending_prints' => 2,
            'completed_prints' => 3,
            'account_created' => date('Y-m-d', strtotime('-7 days'))
        ]
    ]);

} catch (Throwable $e) {
    error_log('Dashboard error: ' . $e->getMessage());
    send_json(false, 'Server error. Please try again later.', null, 500);
}
?>