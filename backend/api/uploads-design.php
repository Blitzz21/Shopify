<?php
/**
 * Upload Design API Endpoint
 * RESTful API for design upload and management
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

// Include required files - Use __DIR__ for reliable path resolution
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Design.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../utils/helpers.php';

// Set CORS headers
setCorsHeaders();

// Initialize database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendError('Database connection failed', HTTP_INTERNAL_ERROR);
}

$design = new Design($db);
$product = new Product($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
try {
    switch($method) {
        case 'POST':
            handleUpload($design, $product, $db);
            break;
        
        case 'GET':
            handleGet($design);
            break;
        
        case 'PUT':
            handlePut($design);
            break;
        
        case 'DELETE':
            handleDelete($design);
            break;
        
        default:
            sendError('Method not allowed', HTTP_METHOD_NOT_ALLOWED);
    }
} catch (Exception $e) {
    logError('Design API Error: ' . $e->getMessage(), [
        'method' => $method,
        'trace' => $e->getTraceAsString()
    ]);
    sendError('Internal server error', HTTP_INTERNAL_ERROR);
}

/**
 * Handle file upload
 */
function handleUpload($design, $product, $db) {
    // Check if file was uploaded
    if (!isset($_FILES['design_file'])) {
        sendError('No file uploaded', HTTP_BAD_REQUEST);
    }
    
    $file = $_FILES['design_file'];
    $sessionId = getUserSessionId();
    
    // Validate file
    $validation = validateImageFile($file);
    if (!$validation['valid']) {
        sendError('File validation failed', HTTP_BAD_REQUEST, $validation['errors']);
    }
    
    $imageInfo = $validation['info'];
    
    // Get and validate product ID
    if (!isset($_POST['product_id'])) {
        sendError('Product ID is required', HTTP_BAD_REQUEST);
    }
    
    $productId = intval($_POST['product_id']);
    $product->id = $productId;
    
    if (!$product->readOne()) {
        sendError('Product not found', HTTP_NOT_FOUND);
    }
    
    // Validate image meets product print requirements
    $printValidation = validatePrintRequirements(
        $imageInfo['width'],
        $imageInfo['height'],
        $product->print_area_width,
        $product->print_area_height,
        $product->min_dpi
    );
    
    if (!$printValidation['valid']) {
        sendError('Image resolution too low for print quality', HTTP_BAD_REQUEST, [
            'uploaded' => $printValidation['uploaded'],
            'required' => $printValidation['required'],
            'message' => sprintf(
                'Your image is %dx%d pixels (%.0f DPI). For best print quality, we need at least %dx%d pixels (%d DPI).',
                $printValidation['uploaded']['width'],
                $printValidation['uploaded']['height'],
                $printValidation['uploaded']['dpi'],
                $printValidation['required']['width'],
                $printValidation['required']['height'],
                $printValidation['required']['dpi']
            )
        ]);
    }
    
    // Begin transaction
    try {
        $db->beginTransaction();
        
        // Generate unique filename
        $storedFilename = generateUniqueFilename($file['name']);
        $filePath = DESIGN_PATH . $storedFilename;
        $thumbFilename = 'thumb_' . $storedFilename;
        $previewPath = PREVIEW_PATH . $thumbFilename;
        
        // 🔧 IMPROVED: Ensure directories exist
        if (!file_exists(DESIGN_PATH)) {
            mkdir(DESIGN_PATH, 0755, true);
        }
        if (!file_exists(PREVIEW_PATH)) {
            mkdir(PREVIEW_PATH, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file to: ' . $filePath);
        }
        
        // 🔧 IMPROVED: Better thumbnail generation with detailed error handling
        $thumbnailGenerated = false;
        $thumbnailError = null;
        
        if (function_exists('generateThumbnail')) {
            if (generateThumbnail($filePath, $previewPath)) {
                $thumbnailGenerated = true;
                logInfo('Thumbnail generated successfully', [
                    'source' => $filePath,
                    'thumbnail' => $previewPath
                ]);
            } else {
                $thumbnailError = 'Thumbnail generation function failed';
                logError('Thumbnail generation failed for: ' . $filePath);
            }
        } else {
            $thumbnailError = 'generateThumbnail function not available';
            logError('generateThumbnail function not found');
        }
        
        // If thumbnail generation failed, use original as fallback
        if (!$thumbnailGenerated) {
            $previewPath = $filePath; // Fallback to original
            logError('Using original file as thumbnail fallback', [
                'design_file' => $filePath,
                'error' => $thumbnailError
            ]);
        }
        
        // Get design configuration from POST data
        $designConfig = [
            'position' => sanitizeString($_POST['position'] ?? 'center'),
            'scale' => floatval($_POST['scale'] ?? 1.0),
            'rotation' => intval($_POST['rotation'] ?? 0),
            'offset_x' => intval($_POST['offset_x'] ?? 0),
            'offset_y' => intval($_POST['offset_y'] ?? 0)
        ];
        
        // Set design properties
        $design->session_id = $sessionId;
        $design->product_id = $productId;
        $design->original_filename = $file['name'];
        $design->stored_filename = $storedFilename;
        $design->file_path = $filePath; // 🔧 Store absolute path
        $design->preview_path = $previewPath; // 🔧 Store absolute path
        $design->file_size = $file['size'];
        $design->mime_type = $imageInfo['mime'];
        $design->width = $imageInfo['width'];
        $design->height = $imageInfo['height'];
        $design->dpi = intval($printValidation['uploaded']['dpi']);
        $design->design_config = json_encode($designConfig);
        $design->status = 'processed';
        
        // Save to database
        if (!$design->create()) {
            throw new Exception('Failed to save design to database');
        }
        
        $db->commit();
        
        // 🔧 IMPROVED: Generate web URLs for response
        $designArray = $design->toArray();
        $designArray['file_url'] = generateWebUrl($filePath);
        $designArray['preview_url'] = generateWebUrl($previewPath);
        
        logInfo('Design uploaded successfully', [
            'design_id' => $design->id,
            'session_id' => $sessionId,
            'product_id' => $productId,
            'filename' => $file['name'],
            'thumbnail_generated' => $thumbnailGenerated
        ]);
        
        sendSuccess([
            'design' => $designArray
        ], 'Design uploaded successfully', HTTP_CREATED);
        
    } catch (Exception $e) {
        $db->rollback();
        
        // Clean up files if they were created
        if (isset($filePath) && file_exists($filePath)) {
            unlink($filePath);
        }
        if (isset($previewPath) && file_exists($previewPath) && $previewPath !== $filePath) {
            unlink($previewPath);
        }
        
        logError('Upload failed: ' . $e->getMessage(), [
            'file_path' => $filePath ?? 'unknown',
            'preview_path' => $previewPath ?? 'unknown'
        ]);
        sendError('Failed to process upload: ' . $e->getMessage(), HTTP_INTERNAL_ERROR);
    }
}

/**
 * Generate web URL from filesystem path
 */
function generateWebUrl(string $filePath): string {
    if (empty($filePath)) {
        return '';
    }
    
    // Normalize path separators
    $normalizedPath = str_replace('\\', '/', $filePath);
    
    // Convert absolute path to web URL
    if (strpos($normalizedPath, ROOT_PATH) === 0) {
        $relativePath = substr($normalizedPath, strlen(ROOT_PATH));
        return rtrim(BASE_URL, '/') . '/' . ltrim($relativePath, '/');
    }
    
    // Already a web URL or relative path
    if (strpos($normalizedPath, 'http') === 0) {
        return $normalizedPath;
    }
    
    return rtrim(BASE_URL, '/') . '/' . ltrim($normalizedPath, '/');
}

/**
 * Handle GET requests
 */
function handleGet($design) {
    $sessionId = getUserSessionId();
    
    // Get single design
    if (isset($_GET['id'])) {
        $design->id = intval($_GET['id']);
        
        if (!$design->readOne()) {
            sendError('Design not found', HTTP_NOT_FOUND);
        }
        
        // Verify ownership
        if ($design->session_id !== $sessionId) {
            sendError('Access denied', HTTP_FORBIDDEN);
        }
        
        $designArray = $design->toArray();
        // 🔧 ADDED: Generate web URLs for response
        $designArray['file_url'] = generateWebUrl($design->file_path);
        $designArray['preview_url'] = generateWebUrl($design->preview_path);
        
        sendSuccess(['design' => $designArray], 'Design retrieved successfully');
    }
    // Get all designs for session
    else {
        $design->session_id = $sessionId;
        $stmt = $design->readBySession();
        $designs = [];
        
        // Convert rows directly to arrays (more efficient)
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $designArray = Design::rowToArray($row);
            // 🔧 ADDED: Generate web URLs for each design
            $designArray['file_url'] = generateWebUrl($designArray['file_path']);
            $designArray['preview_url'] = generateWebUrl($designArray['preview_path']);
            $designs[] = $designArray;
        }
        
        sendSuccess([
            'count' => count($designs),
            'designs' => $designs
        ], 'Designs retrieved successfully');
    }
}

/**
 * Handle PUT requests (Update design)
 */
function handlePut($design) {
    $sessionId = getUserSessionId();
    $data = getJsonInput();
    
    // Validate design ID
    if (!isset($data['id'])) {
        sendError('Design ID is required', HTTP_BAD_REQUEST);
    }
    
    $design->id = intval($data['id']);
    
    // Check if design exists
    if (!$design->readOne()) {
        sendError('Design not found', HTTP_NOT_FOUND);
    }
    
    // Verify ownership
    if ($design->session_id !== $sessionId) {
        sendError('Access denied', HTTP_FORBIDDEN);
    }
    
    // Update design configuration if provided
    if (isset($data['design_config'])) {
        $design->design_config = json_encode($data['design_config']);
    }
    
    // Update status if provided
    if (isset($data['status'])) {
        $design->status = sanitizeString($data['status']);
    }
    
    // Update design
    if ($design->update()) {
        logInfo('Design updated', ['design_id' => $design->id]);
        
        $designArray = $design->toArray();
        // 🔧 ADDED: Generate web URLs for response
        $designArray['file_url'] = generateWebUrl($design->file_path);
        $designArray['preview_url'] = generateWebUrl($design->preview_path);
        
        sendSuccess(['design' => $designArray], 'Design updated successfully');
    } else {
        sendError('Failed to update design', HTTP_INTERNAL_ERROR);
    }
}

/**
 * Handle DELETE requests
 */
function handleDelete($design) {
    $sessionId = getUserSessionId();
    
    // Get design ID
    if (!isset($_GET['id'])) {
        sendError('Design ID is required', HTTP_BAD_REQUEST);
    }
    
    $design->id = intval($_GET['id']);
    
    // Check if design exists
    if (!$design->readOne()) {
        sendError('Design not found', HTTP_NOT_FOUND);
    }
    
    // Verify ownership
    if ($design->session_id !== $sessionId) {
        sendError('Access denied', HTTP_FORBIDDEN);
    }
    
    // Check if hard delete is requested
    $hard = isset($_GET['hard']) && filter_var($_GET['hard'], FILTER_VALIDATE_BOOLEAN);
    
    // Delete design
    if ($design->delete($hard)) {
        logInfo('Design deleted', ['design_id' => $design->id, 'hard' => $hard]);
        sendSuccess([], 'Design deleted successfully');
    } else {
        sendError('Failed to delete design', HTTP_INTERNAL_ERROR);
    }
}
?>