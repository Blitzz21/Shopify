<?php
/**
 * Dev-only endpoint to simulate Shopify webhooks without real Shopify.
 * DO NOT expose this in production.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/order_handlers.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', HTTP_METHOD_NOT_ALLOWED);
}

// Only allow in development
if (APP_ENV !== 'development') {
    sendError('This endpoint is only available in development', HTTP_FORBIDDEN);
}

$data = getJsonInput();

$topic   = $data['topic']   ?? 'orders/create';
$payload = $data['payload'] ?? null;

if (!$payload || !is_array($payload)) {
    sendError('Missing or invalid payload', HTTP_BAD_REQUEST);
}

// Init DB
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendError('Database connection failed', HTTP_INTERNAL_ERROR);
}

try {
    // Log mock webhook like the real one
    logWebhookToDatabase($db, $topic, $payload);

    switch ($topic) {
        case 'orders/create':
            handleOrderCreate($db, $payload);
            break;

        case 'orders/updated':
            handleOrderUpdate($db, $payload);
            break;

        case 'orders/fulfilled':
            handleOrderFulfilled($db, $payload);
            break;

        case 'orders/cancelled':
            handleOrderCancelled($db, $payload);
            break;

        default:
            sendError('Unsupported topic for dev mock', HTTP_BAD_REQUEST);
    }

    sendSuccess([
        'topic'   => $topic,
        'orderId' => $payload['id'] ?? null
    ], 'Dev mock order processed successfully');

} catch (Exception $e) {
    logError('Dev mock order error: ' . $e->getMessage(), [
        'topic' => $topic,
    ]);
    sendError('Failed to process dev mock order: ' . $e->getMessage(), HTTP_INTERNAL_ERROR);
}
?>