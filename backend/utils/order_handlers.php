<?php
/**
 * Order / Webhook handler functions
 * Extracted from webhooks.php so they can be reused
 */

require_once __DIR__ . '/helpers.php'; // for logInfo, logError, etc.

/**
 * Log webhook to database
 */
if (!function_exists('logWebhookToDatabase')) {
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
}

/**
 * Handle order creation
 */
if (!function_exists('handleOrderCreate')) {
    function handleOrderCreate($db, $orderData) {
        try {
            $orderId = $orderData['id'];
            $orderNumber = $orderData['order_number'] ?? $orderData['name'];
            $customerEmail = $orderData['customer']['email'] ?? $orderData['email'] ?? null;
            $totalPrice = $orderData['total_price'] ?? 0;
            $currency = $orderData['currency'] ?? 'USD';

            logInfo('Starting order creation', [
                'shopify_order_id' => $orderId,
                'order_number' => $orderNumber,
                'line_items_count' => count($orderData['line_items'] ?? [])
            ]);

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
            logInfo('Order inserted successfully', ['db_order_id' => $dbOrderId]);

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

                logInfo('Processing line item', [
                    'line_item_id' => $item['id'],
                    'design_id_found' => $designId,
                    'properties' => $properties
                ]);

                if ($designId) {
                    // First, verify the design exists
                    $checkDesignQuery = "SELECT id, product_id FROM designs WHERE id = :design_id";
                    $checkStmt = $db->prepare($checkDesignQuery);
                    $checkStmt->bindParam(':design_id', $designId);
                    $checkStmt->execute();
                    $design = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$design) {
                        logError('Design not found', ['design_id' => $designId]);
                        continue; // Skip this line item
                    }

                    logInfo('Design found', ['design' => $design]);

                    // Insert order item
                    $itemQuery = "INSERT INTO order_items 
                                  (order_id, design_id, product_id, shopify_line_item_id, quantity, unit_price, total_price) 
                                  VALUES (:order_id, :design_id, :product_id, :line_item_id, :quantity, :unit_price, :total_price)";

                    $itemStmt = $db->prepare($itemQuery);
                    $itemStmt->bindParam(':order_id', $dbOrderId);
                    $itemStmt->bindParam(':design_id', $designId);
                    $itemStmt->bindParam(':product_id', $design['product_id']);
                    $lineItemId = $item['id'];
                    $itemStmt->bindParam(':line_item_id', $lineItemId);
                    $quantity = $item['quantity'];
                    $itemStmt->bindParam(':quantity', $quantity);
                    $unitPrice = $item['price'];
                    $itemStmt->bindParam(':unit_price', $unitPrice);
                    $lineTotalPrice = floatval($item['price']) * intval($item['quantity']);
                    $itemStmt->bindParam(':total_price', $lineTotalPrice);
                    $itemStmt->execute();

                    $orderItemId = $db->lastInsertId();
                    logInfo('Order item inserted', ['order_item_id' => $orderItemId]);

                    // Create print job
                    $jobQuery = "INSERT INTO print_jobs 
                                 (order_item_id, design_id, status) 
                                 VALUES (:order_item_id, :design_id, 'queued')";

                    $jobStmt = $db->prepare($jobQuery);
                    $jobStmt->bindParam(':order_item_id', $orderItemId);
                    $jobStmt->bindParam(':design_id', $designId);
                    $jobStmt->execute();

                    $printJobId = $db->lastInsertId();
                    logInfo('Print job created', ['print_job_id' => $printJobId]);
                } else {
                    logInfo('No design_id found in line item properties');
                }
            }

            updateWebhookStatus($db, 'orders/create', $orderId, 'processed');

            logInfo('Order created successfully', [
                'shopify_order_id' => $orderId,
                'db_order_id' => $dbOrderId
            ]);

        } catch (Exception $e) {
            logError('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            updateWebhookStatus($db, 'orders/create', $orderData['id'], 'failed', $e->getMessage());
            throw $e;
        }
    }
}

/**
 * Update webhook log status
 */
if (!function_exists('updateWebhookStatus')) {
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
}

/**
 * Handle order creation
 */
if (!function_exists('handleOrderCreate')) {
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
                    // Insert order item (joins on designs to get product_id)
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
                    $lineTotalPrice = floatval($item['price']) * intval($item['quantity']);
                    $itemStmt->bindParam(':total_price', $lineTotalPrice);
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
}

/**
 * Handle order update
 */
if (!function_exists('handleOrderUpdate')) {
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
}

/**
 * Handle order fulfillment
 */
if (!function_exists('handleOrderFulfilled')) {
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
}

/**
 * Handle order cancellation
 */
if (!function_exists('handleOrderCancelled')) {
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
}
?>