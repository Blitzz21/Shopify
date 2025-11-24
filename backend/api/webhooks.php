<?php
/**
 * Shopify Webhooks API Endpoint
 * Handles incoming webhooks from Shopify
 */

header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include required files
require_once __DIR__ . '/../utils/order_handlers.php';  
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../utils/helpers.php';

// Get raw POST data
$rawData = file_get_contents('php://input');

// Get headers
$hmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
$topic = $_SERVER['HTTP_X_SHOPIFY_TOPIC'] ?? '';
$shopDomain = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? '';

// Log webhook received
logInfo('Webhook received', [
    'topic' => $topic,
    'shop' => $shopDomain,
    'size' => strlen($rawData)
]);

// Verify webhook authenticity
if (!verifyShopifyWebhook($rawData, $hmacHeader)) {
    logError('Webhook verification failed', [
        'topic' => $topic,
        'shop' => $shopDomain
    ]);
    http_response_code(HTTP_UNAUTHORIZED);
    die(json_encode(['error' => 'Webhook verification failed']));
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    logError('Database connection failed in webhook');
    http_response_code(HTTP_INTERNAL_ERROR);
    die(json_encode(['error' => 'Database connection failed']));
}

// Parse webhook data
$webhookData = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logError('Invalid JSON in webhook');
    http_response_code(HTTP_BAD_REQUEST);
    die(json_encode(['error' => 'Invalid JSON']));
}

// Log webhook to database
logWebhookToDatabase($db, $topic, $webhookData);

// Handle webhook based on topic
try {
    switch ($topic) {
        case 'orders/create':
            handleOrderCreate($db, $webhookData);
            break;
        
        case 'orders/updated':
            handleOrderUpdate($db, $webhookData);
            break;
        
        case 'orders/fulfilled':
            handleOrderFulfilled($db, $webhookData);
            break;
        
        case 'orders/cancelled':
            handleOrderCancelled($db, $webhookData);
            break;
        
        default:
            logInfo('Unhandled webhook topic', ['topic' => $topic]);
            http_response_code(HTTP_OK);
            echo json_encode(['message' => 'Webhook received but not processed']);
            exit;
    }
    
    http_response_code(HTTP_OK);
    echo json_encode(['success' => true, 'message' => 'Webhook processed successfully']);
    
} catch (Exception $e) {
    logError('Webhook processing error', [
        'topic' => $topic,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(HTTP_INTERNAL_ERROR);
    echo json_encode(['error' => 'Failed to process webhook']);
}

/**
 * Log webhook to database
 */
function logWebhookToDatabase($db, $topic, $data) {
    try {
        $query = "INSERT INTO webhook_logs (topic, shopify_order_id, payload, status) 
                  VALUES (:topic, :order_id, :payload, 'received')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':topic', $topic);
        $orderId = $data['id'] ?? null;
        $stmt->bindParam(':order_id', $orderId);
        $payload = json_encode($data);
        $stmt->bindParam(':payload', $payload);
        $stmt->execute();
    } catch (Exception $e) {
        logError('Failed to log webhook to database: ' . $e->getMessage());
    }
}

/**
 * Update webhook log status
 */
function updateWebhookStatus($db, $topic, $orderId, $status, $errorMsg = null) {
    try {
        $query = "UPDATE webhook_logs 
                  SET status = :status, error_message = :error_msg 
                  WHERE topic = :topic AND shopify_order_id = :order_id 
                  ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':error_msg', $errorMsg);
        $stmt->bindParam(':topic', $topic);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
    } catch (Exception $e) {
        logError('Failed to update webhook status: ' . $e->getMessage());
    }
}

/**
 * Handle order creation
 */
function handleOrderCreate($db, $orderData) {
    try {
        $orderId = $orderData['id'];
        $orderNumber = $orderData['order_number'] ?? $orderData['name'];
        $customerEmail = $orderData['customer']['email'] ?? $orderData['email'] ?? null;
        $totalPrice = $orderData['total_price'] ?? 0;
        $currency = $orderData['currency'] ?? 'USD';
        
        // Insert order
        $query = "INSERT INTO orders 
                  (shopify_order_id, shopify_order_number, customer_email, total_amount, currency, order_status, fulfillment_status) 
                  VALUES (:order_id, :order_number, :customer_email, :total_amount, :currency, 'pending', 'unfulfilled')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':order_number', $orderNumber);
        $stmt->bindParam(':customer_email', $customerEmail);
        $stmt->bindParam(':total_amount', $totalPrice);
        $stmt->bindParam(':currency', $currency);
        $stmt->execute();
        
        $dbOrderId = $db->lastInsertId();
        
        // Process line items
        foreach ($orderData['line_items'] as $item) {
            $properties = $item['properties'] ?? [];
            $designId = null;
            
            // Look for design_id in properties
            foreach ($properties as $prop) {
                if ($prop['name'] === 'design_id' || $prop['name'] === '_design_id') {
                    $designId = $prop['value'];
                    break;
                }
            }
            
            if ($designId) {
                // Insert order item
                $itemQuery = "INSERT INTO order_items 
                              (order_id, design_id, product_id, shopify_line_item_id, quantity, unit_price, total_price) 
                              SELECT :order_id, :design_id, product_id, :line_item_id, :quantity, :unit_price, :total_price
                              FROM designs WHERE id = :design_id2";
                
                $itemStmt = $db->prepare($itemQuery);
                $itemStmt->bindParam(':order_id', $dbOrderId);
                $itemStmt->bindParam(':design_id', $designId);
                $itemStmt->bindParam(':design_id2', $designId);
                $lineItemId = $item['id'];
                $itemStmt->bindParam(':line_item_id', $lineItemId);
                $quantity = $item['quantity'];
                $itemStmt->bindParam(':quantity', $quantity);
                $unitPrice = $item['price'];
                $itemStmt->bindParam(':unit_price', $unitPrice);
                $totalPrice = floatval($item['price']) * intval($item['quantity']);
                $itemStmt->bindParam(':total_price', $totalPrice);
                $itemStmt->execute();
                
                $orderItemId = $db->lastInsertId();
                
                // Create print job
                $jobQuery = "INSERT INTO print_jobs 
                             (order_item_id, design_id, status) 
                             VALUES (:order_item_id, :design_id, 'queued')";
                
                $jobStmt = $db->prepare($jobQuery);
                $jobStmt->bindParam(':order_item_id', $orderItemId);
                $jobStmt->bindParam(':design_id', $designId);
                $jobStmt->execute();
            }
        }
        
        updateWebhookStatus($db, 'orders/create', $orderId, 'processed');
        
        logInfo('Order created successfully', [
            'shopify_order_id' => $orderId,
            'db_order_id' => $dbOrderId
        ]);
        
    } catch (Exception $e) {
        updateWebhookStatus($db, 'orders/create', $orderData['id'], 'failed', $e->getMessage());
        throw $e;
    }
}

/**
 * Handle order update
 */
function handleOrderUpdate($db, $orderData) {
    try {
        $orderId = $orderData['id'];
        $fulfillmentStatus = $orderData['fulfillment_status'] ?? 'unfulfilled';
        $financialStatus = $orderData['financial_status'] ?? 'pending';
        
        $query = "UPDATE orders 
                  SET fulfillment_status = :fulfillment_status,
                      order_status = :order_status,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE shopify_order_id = :order_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fulfillment_status', $fulfillmentStatus);
        $orderStatus = ($financialStatus === 'paid') ? 'processing' : 'pending';
        $stmt->bindParam(':order_status', $orderStatus);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        updateWebhookStatus($db, 'orders/updated', $orderId, 'processed');
        
        logInfo('Order updated successfully', ['order_id' => $orderId]);
        
    } catch (Exception $e) {
        updateWebhookStatus($db, 'orders/updated', $orderData['id'], 'failed', $e->getMessage());
        throw $e;
    }
}

/**
 * Handle order fulfillment
 */
function handleOrderFulfilled($db, $orderData) {
    try {
        $orderId = $orderData['id'];
        
        $query = "UPDATE orders 
                  SET fulfillment_status = 'fulfilled',
                      order_status = 'completed',
                      updated_at = CURRENT_TIMESTAMP
                  WHERE shopify_order_id = :order_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        // Update print jobs
        $jobQuery = "UPDATE print_jobs pj
                     INNER JOIN order_items oi ON pj.order_item_id = oi.id
                     INNER JOIN orders o ON oi.order_id = o.id
                     SET pj.status = 'shipped'
                     WHERE o.shopify_order_id = :order_id";
        
        $jobStmt = $db->prepare($jobQuery);
        $jobStmt->bindParam(':order_id', $orderId);
        $jobStmt->execute();
        
        updateWebhookStatus($db, 'orders/fulfilled', $orderId, 'processed');
        
        logInfo('Order fulfilled successfully', ['order_id' => $orderId]);
        
    } catch (Exception $e) {
        updateWebhookStatus($db, 'orders/fulfilled', $orderData['id'], 'failed', $e->getMessage());
        throw $e;
    }
}

/**
 * Handle order cancellation
 */
function handleOrderCancelled($db, $orderData) {
    try {
        $orderId = $orderData['id'];
        
        $query = "UPDATE orders 
                  SET order_status = 'cancelled',
                      updated_at = CURRENT_TIMESTAMP
                  WHERE shopify_order_id = :order_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        // Cancel print jobs
        $jobQuery = "UPDATE print_jobs pj
                     INNER JOIN order_items oi ON pj.order_item_id = oi.id
                     INNER JOIN orders o ON oi.order_id = o.id
                     SET pj.status = 'failed'
                     WHERE o.shopify_order_id = :order_id 
                     AND pj.status IN ('queued', 'preparing')";
        
        $jobStmt = $db->prepare($jobQuery);
        $jobStmt->bindParam(':order_id', $orderId);
        $jobStmt->execute();
        
        updateWebhookStatus($db, 'orders/cancelled', $orderId, 'processed');
        
        logInfo('Order cancelled successfully', ['order_id' => $orderId]);
        
    } catch (Exception $e) {
        updateWebhookStatus($db, 'orders/cancelled', $orderData['id'], 'failed', $e->getMessage());
        throw $e;
    }
}
?>