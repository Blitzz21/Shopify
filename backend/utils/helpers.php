<?php
/**
 * Utility Helper Functions
 * Common functions used throughout the application
 */

/**
 * Set CORS headers for API responses
 */
function setCorsHeaders() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Allow common development origins
    $allowedOrigins = [
        'http://127.0.0.1:5500',  // VS Code Live Server
        'http://localhost:3000',   // React dev server
        'http://localhost:5173',   // Vite dev server
        'http://localhost:8080',   // Alternative port
        'http://localhost',        // Direct localhost
        'http://localhost/Shopify' // Your project
    ];
    
    // Check if current origin is allowed
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: " . $origin);
    } else if (DEBUG_MODE) {
        // In development, allow any origin for testing
        header("Access-Control-Allow-Origin: *");
    }
    
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key");
    header("Access-Control-Allow-Credentials: true");
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Send JSON response and exit
 */
function sendResponse($statusCode, $data, $exit = true) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    if ($exit) {
        exit();
    }
}

/**
 * Send success response
 */
function sendSuccess($data = [], $message = 'Success', $statusCode = HTTP_OK) {
    sendResponse($statusCode, [
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Send error response
 */
function sendError($message, $statusCode = HTTP_BAD_REQUEST, $errors = []) {
    sendResponse($statusCode, [
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ]);
}

/**
 * Get JSON input from request body
 */
function getJsonInput() {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON input', HTTP_BAD_REQUEST);
    }
    
    return $data ?? [];
}

/**
 * Validate required fields in data
 */
function validateRequired($data, $requiredFields) {
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missingFields[] = $field;
        }
    }
    
    return [
        'valid' => empty($missingFields),
        'missing' => $missingFields
    ];
}

/**
 * Sanitize string input
 */
function sanitizeString($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalFilename, $prefix = 'design_') {
    $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
    $uniqueName = $prefix . uniqid() . '_' . time() . '.' . $extension;
    return $uniqueName;
}

/**
 * Validate uploaded image file
 */
function validateImageFile($file) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown upload error';
        $errors[] = $errorMsg;
        
        return ['valid' => false, 'errors' => $errors];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $maxMB = MAX_FILE_SIZE / 1024 / 1024;
        $errors[] = "File size exceeds {$maxMB}MB limit";
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        $errors[] = "Invalid file extension. Allowed: " . implode(', ', ALLOWED_EXTENSIONS);
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        $errors[] = "Invalid file type. Allowed types: " . implode(', ', ALLOWED_MIME_TYPES);
    }
    
    // Get image info
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = "File is not a valid image";
        return ['valid' => false, 'errors' => $errors];
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'info' => [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'],
            'size' => $file['size']
        ]
    ];
}

/**
 * Generate thumbnail from image
 */
function generateThumbnail($sourcePath, $destPath, $maxWidth = THUMBNAIL_WIDTH, $maxHeight = THUMBNAIL_HEIGHT) {
    try {
        list($width, $height, $type) = getimagesize($sourcePath);
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create new image
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        
        // Load source image based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                // Preserve transparency
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
                break;
            default:
                return false;
        }
        
        // Resize
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $destPath, PREVIEW_QUALITY);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $destPath, 9);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($thumb);
        
        return true;
    } catch (Exception $e) {
        logError('Thumbnail generation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Calculate DPI from image dimensions and print area
 */
function calculateDPI($imageWidth, $imageHeight, $printWidth, $printHeight) {
    $dpiWidth = $imageWidth / $printWidth;
    $dpiHeight = $imageHeight / $printHeight;
    
    return min($dpiWidth, $dpiHeight);
}

/**
 * Validate image meets print requirements
 */
function validatePrintRequirements($imageWidth, $imageHeight, $printWidth, $printHeight, $minDPI = MIN_DPI) {
    $requiredWidth = $printWidth * $minDPI;
    $requiredHeight = $printHeight * $minDPI;
    
    $valid = ($imageWidth >= $requiredWidth && $imageHeight >= $requiredHeight);
    
    return [
        'valid' => $valid,
        'uploaded' => [
            'width' => $imageWidth,
            'height' => $imageHeight,
            'dpi' => calculateDPI($imageWidth, $imageHeight, $printWidth, $printHeight)
        ],
        'required' => [
            'width' => $requiredWidth,
            'height' => $requiredHeight,
            'dpi' => $minDPI
        ]
    ];
}

/**
 * Get or create user session ID
 */
function getUserSessionId() {
    if (!isset($_SESSION['user_session_id'])) {
        $_SESSION['user_session_id'] = 'session_' . uniqid() . '_' . time();
    }
    return $_SESSION['user_session_id'];
}

/**
 * Log message to file
 */
function logMessage($message, $level = 'info', $context = []) {
    $logFile = LOG_PATH . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
    $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Log error
 */
function logError($message, $context = []) {
    logMessage($message, 'ERROR', $context);
}

/**
 * Log info
 */
function logInfo($message, $context = []) {
    logMessage($message, 'INFO', $context);
}

/**
 * Verify Shopify webhook signature
 */
function verifyShopifyWebhook($data, $hmacHeader) {
    $calculatedHmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_WEBHOOK_SECRET, true));
    return hash_equals($calculatedHmac, $hmacHeader);
}

/**
 * Get file size in human readable format
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function getClientIp() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
            return $_SERVER[$key];
        }
    }
    
    return 'UNKNOWN';
}
?>