<?php
/**
 * Products API Endpoint
 * RESTful API for product operations
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

// ✅ CORRECTED PATHS - Use the same structure as your test file
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../utils/helpers.php';

// Set CORS headers
setCorsHeaders();

// Initialize database and product
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendError('Database connection failed', HTTP_INTERNAL_ERROR);
}

$product = new Product($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
try {
    switch($method) {
        case 'GET':
            handleGet($product);
            break;
        
        case 'POST':
            handlePost($product);
            break;
        
        case 'PUT':
            handlePut($product);
            break;
        
        case 'DELETE':
            handleDelete($product);
            break;
        
        default:
            sendError('Method not allowed', HTTP_METHOD_NOT_ALLOWED);
    }
} catch (Exception $e) {
    logError('API Error: ' . $e->getMessage());
    sendError('Internal server error', HTTP_INTERNAL_ERROR);
}

// Initialize database and product
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendError('Database connection failed', HTTP_INTERNAL_ERROR);
}

$product = new Product($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
try {
    switch($method) {
        case 'GET':
            handleGet($product);
            break;
        
        case 'POST':
            handlePost($product);
            break;
        
        case 'PUT':
            handlePut($product);
            break;
        
        case 'DELETE':
            handleDelete($product);
            break;
        
        default:
            sendError('Method not allowed', HTTP_METHOD_NOT_ALLOWED);
    }
} catch (Exception $e) {
    logError('API Error: ' . $e->getMessage());
    sendError('Internal server error', HTTP_INTERNAL_ERROR);
}

/**
 * Handle GET requests
 */
function handleGet($product) {
    // Get single product by ID
    if (isset($_GET['id'])) {
        $product->id = intval($_GET['id']);
        
        if ($product->readOne()) {
            sendSuccess($product->toArray(), 'Product retrieved successfully');
        } else {
            sendError('Product not found', HTTP_NOT_FOUND);
        }
    }
    // Get product by Shopify ID
    elseif (isset($_GET['shopify_id'])) {
        $product->shopify_product_id = sanitizeString($_GET['shopify_id']);
        
        if ($product->readByShopifyId()) {
            sendSuccess($product->toArray(), 'Product retrieved successfully');
        } else {
            sendError('Product not found', HTTP_NOT_FOUND);
        }
    }
    // Get products by type
    elseif (isset($_GET['type'])) {
        $type = sanitizeString($_GET['type']);
        $stmt = $product->getByType($type);
        $products = [];
        
        // Convert rows directly to arrays (more efficient than creating Product instances)
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = Product::rowToArray($row);
        }
        
        sendSuccess($products, 'Products retrieved successfully');
    }
    // Get all products
    else {
        $activeOnly = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : true;
        $stmt = $product->read($activeOnly);
        $products = [];
        
        // Convert rows directly to arrays (more efficient than creating Product instances)
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = Product::rowToArray($row);
        }
        
        sendSuccess([
            'count' => count($products),
            'products' => $products
        ], 'Products retrieved successfully');
    }
}

/**
 * Handle POST requests (Create product)
 */
function handlePost($product) {
    $data = getJsonInput();
    
    // Validate required fields
    $validation = validateRequired($data, [
        'shopify_product_id',
        'name',
        'print_area_width',
        'print_area_height'
    ]);
    
    if (!$validation['valid']) {
        sendError('Missing required fields', HTTP_BAD_REQUEST, [
            'missing_fields' => $validation['missing']
        ]);
    }
    
    // Set product properties
    $product->shopify_product_id = sanitizeString($data['shopify_product_id']);
    $product->shopify_variant_id = sanitizeString($data['shopify_variant_id'] ?? '');
    $product->name = sanitizeString($data['name']);
    $product->description = sanitizeString($data['description'] ?? '');
    $product->base_price = floatval($data['base_price'] ?? 0);
    $product->print_area_width = intval($data['print_area_width']);
    $product->print_area_height = intval($data['print_area_height']);
    $product->min_dpi = intval($data['min_dpi'] ?? MIN_DPI);
    $product->max_file_size = intval($data['max_file_size'] ?? MAX_FILE_SIZE);
    $product->allowed_formats = json_encode($data['allowed_formats'] ?? ALLOWED_EXTENSIONS);
    $product->product_type = sanitizeString($data['product_type'] ?? 'apparel');
    $product->is_active = isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : true;
    
    // Create product
    if ($product->create()) {
        logInfo('Product created', ['product_id' => $product->id, 'name' => $product->name]);
        sendSuccess([
            'product_id' => $product->id,
            'product' => $product->toArray()
        ], 'Product created successfully', HTTP_CREATED);
    } else {
        sendError('Failed to create product', HTTP_INTERNAL_ERROR);
    }
}

/**
 * Handle PUT requests (Update product)
 */
function handlePut($product) {
    $data = getJsonInput();
    
    // Validate product ID
    if (!isset($data['id'])) {
        sendError('Product ID is required', HTTP_BAD_REQUEST);
    }
    
    $product->id = intval($data['id']);
    
    // Check if product exists
    if (!$product->readOne()) {
        sendError('Product not found', HTTP_NOT_FOUND);
    }
    
    // Update properties if provided
    if (isset($data['name'])) {
        $product->name = sanitizeString($data['name']);
    }
    if (isset($data['description'])) {
        $product->description = sanitizeString($data['description']);
    }
    if (isset($data['base_price'])) {
        $product->base_price = floatval($data['base_price']);
    }
    if (isset($data['print_area_width'])) {
        $product->print_area_width = intval($data['print_area_width']);
    }
    if (isset($data['print_area_height'])) {
        $product->print_area_height = intval($data['print_area_height']);
    }
    if (isset($data['min_dpi'])) {
        $product->min_dpi = intval($data['min_dpi']);
    }
    if (isset($data['max_file_size'])) {
        $product->max_file_size = intval($data['max_file_size']);
    }
    if (isset($data['allowed_formats'])) {
        $product->allowed_formats = json_encode($data['allowed_formats']);
    }
    if (isset($data['product_type'])) {
        $product->product_type = sanitizeString($data['product_type']);
    }
    if (isset($data['is_active'])) {
        $product->is_active = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
    }
    
    // Update product
    if ($product->update()) {
        logInfo('Product updated', ['product_id' => $product->id]);
        sendSuccess($product->toArray(), 'Product updated successfully');
    } else {
        sendError('Failed to update product', HTTP_INTERNAL_ERROR);
    }
}

/**
 * Handle DELETE requests
 */
function handleDelete($product) {
    // Get product ID
    if (!isset($_GET['id'])) {
        sendError('Product ID is required', HTTP_BAD_REQUEST);
    }
    
    $product->id = intval($_GET['id']);
    
    // Check if product exists
    if (!$product->readOne()) {
        sendError('Product not found', HTTP_NOT_FOUND);
    }
    
    // Check if hard delete is requested
    $hard = isset($_GET['hard']) && filter_var($_GET['hard'], FILTER_VALIDATE_BOOLEAN);
    
    // Delete product
    if ($product->delete($hard)) {
        $deleteType = $hard ? 'permanently deleted' : 'deactivated';
        logInfo("Product {$deleteType}", ['product_id' => $product->id]);
        sendSuccess([], "Product {$deleteType} successfully");
    } else {
        sendError('Failed to delete product', HTTP_INTERNAL_ERROR);
    }
}
?>