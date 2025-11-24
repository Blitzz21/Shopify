<?php
/**
 * Application Configuration File
 * 
 * This file contains all application-wide constants and configuration settings
 * for the Shopify Print App. It follows industry best practices for security,
 * cross-platform compatibility, and maintainability.
 * 
 * Key Features:
 * - Secure path resolution using realpath() to prevent directory traversal
 * - Cross-platform compatibility using DIRECTORY_SEPARATOR
 * - Comprehensive documentation following PHPDoc standards
 * - Environment-aware configuration (development/staging/production)
 * - Security-first approach with validation and sanitization
 * 
 * @package    ShopifyPrintApp
 * @subpackage Config
 * @author     Your Name
 * @version    1.0.0
 * @since      1.0.0
 * 
 * @important  ROOT_PATH must be defined before any path constants that depend on it
 * @see        https://www.php.net/manual/en/function.realpath.php
 * @see        https://www.php.net/manual/en/dir.constants.php
 */

// ==================== APPLICATION SETTINGS ====================

// Base URL - Your application's base URL
define('BASE_URL', 'http://localhost/Shopify/');

// Application Info
define('SITE_NAME', 'Shopify Print App');
define('APP_VERSION', '1.0.0');
define('DEBUG_MODE', true);

// Environment
define('APP_ENV', 'development'); // development, staging, production

// ==================== DATABASE SETTINGS ====================

// Database Configuration (Fallback - prefer .env)
define('DB_HOST', 'localhost');
define('DB_NAME', 'Shopify');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// ==================== CORS & API SETTINGS ====================

// CORS Settings
define('CORS_ALLOWED_ORIGINS', 'http://localhost:3000, http://localhost:5173, http://localhost:8080, http://localhost:8081');
define('CORS_ALLOWED_METHODS', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
define('CORS_ALLOWED_HEADERS', 'Content-Type, Authorization, X-Requested-With, X-Shopify-Access-Token, X-API-Key');
define('CORS_MAX_AGE', 3600);

// HTTP Status Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_ACCEPTED', 202);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_CONFLICT', 409);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_TOO_MANY_REQUESTS', 429);
define('HTTP_INTERNAL_ERROR', 500);
define('HTTP_SERVICE_UNAVAILABLE', 503);

// API Settings
define('API_RATE_LIMIT', 100); // Requests per minute
define('API_TIMEOUT', 30); // Seconds

// ==================== FILE UPLOAD SETTINGS ====================

// File Size Limits
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB in bytes
define('MAX_IMAGE_SIZE', 20 * 1024 * 1024); // 20MB for high-res images
define('MAX_FILES_PER_UPLOAD', 5);

// Image Quality Settings
define('MIN_DPI', 150);
define('MAX_DPI', 1200);
define('MIN_IMAGE_WIDTH', 100);
define('MIN_IMAGE_HEIGHT', 100);
define('MAX_IMAGE_WIDTH', 10000);
define('MAX_IMAGE_HEIGHT', 10000);

// Allowed File Formats
define('ALLOWED_EXTENSIONS', ['png', 'jpg', 'jpeg', 'svg', 'pdf', 'ai', 'eps', 'webp']);
define('ALLOWED_MIME_TYPES', [
    'image/png',
    'image/jpeg',
    'image/jpg',
    'image/svg+xml',
    'image/webp',
    'application/pdf',
    'application/postscript',
    'application/illustrator'
]);

// ==================== FILE PATHS (Base) ====================

/**
 * Root Path Definition
 * 
 * This constant MUST be defined before any other path constants that depend on it.
 * It represents the absolute filesystem path to the application root directory.
 * 
 * Security Considerations:
 * - Uses realpath() to resolve symbolic links and prevent directory traversal attacks
 * - Normalizes path separators for cross-platform compatibility
 * - Validates that the path exists before definition
 * 
 * @var string Absolute path to application root with trailing directory separator
 * 
 * @example
 * // ROOT_PATH will be something like: /var/www/html/Shopify/
 * // or on Windows: C:\xampp2\htdocs\Shopify\
 * 
 * @since 1.0.0
 */
if (!defined('ROOT_PATH')) {
    // Calculate root path: config.php is in backend/config/, so go up two levels to reach root
    // Path structure: root/backend/config/config.php -> root/
    $calculatedPath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
    
    // Validate that the path was resolved successfully
    if ($calculatedPath === false) {
        // Fallback: use dirname() twice if realpath fails (shouldn't happen in normal operation)
        // Go up two levels: backend/config/ -> backend/ -> root/
        $calculatedPath = dirname(dirname(dirname(__FILE__)));
        if (DEBUG_MODE) {
            error_log('Warning: realpath() failed for ROOT_PATH, using fallback calculation');
        }
    }
    
    // Normalize path separator and ensure trailing separator
    $rootPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $calculatedPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    
    // Additional security: ensure path doesn't contain dangerous patterns
    if (strpos($rootPath, '..') !== false || strpos($rootPath, "\0") !== false) {
        throw new RuntimeException('Invalid ROOT_PATH detected. Security check failed.');
    }
    
    define('ROOT_PATH', $rootPath);
}

// ==================== DESIGN & PRINT SETTINGS ====================

/**
 * Design Upload Paths
 * 
 * These paths define where design files, previews, temporary files, and backups
 * are stored. All paths are relative to ROOT_PATH and use DIRECTORY_SEPARATOR
 * for cross-platform compatibility.
 * 
 * Security Note: These directories should have proper access controls (.htaccess
 * or equivalent) to prevent direct web access to uploaded files.
 * 
 * @see ROOT_PATH Base path constant
 */
define('DESIGN_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'designs' . DIRECTORY_SEPARATOR);
define('PREVIEW_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR);
define('TEMP_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);
define('BACKUP_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR);
// Print-ready files (for Polaris)
define('PRINT_READY_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'print_ready' . DIRECTORY_SEPARATOR);

// Design Configuration Defaults
define('DEFAULT_POSITION', 'center');
define('DEFAULT_SCALE', 1.0);
define('DEFAULT_ROTATION', 0);
define('DEFAULT_OFFSET_X', 0);
define('DEFAULT_OFFSET_Y', 0);
define('DEFAULT_OPACITY', 1.0);

// Design Status Constants
define('DESIGN_STATUS_PENDING', 'pending');
define('DESIGN_STATUS_PROCESSED', 'processed');
define('DESIGN_STATUS_APPROVED', 'approved');
define('DESIGN_STATUS_REJECTED', 'rejected');
define('DESIGN_STATUS_DELETED', 'deleted');
define('DESIGN_STATUS_ARCHIVED', 'archived');

// Print Quality Settings
define('PRINT_QUALITY_STANDARD', 150);
define('PRINT_QUALITY_HIGH', 300);
define('PRINT_QUALITY_PREMIUM', 600);

// ==================== THUMBNAIL & PREVIEW SETTINGS ====================

// Thumbnail Dimensions
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 300);
define('PREVIEW_WIDTH', 800);
define('PREVIEW_HEIGHT', 800);

// Image Quality
define('PREVIEW_QUALITY', 85);
define('THUMBNAIL_QUALITY', 75);
define('WEB_QUALITY', 90);

// ==================== SHOPIFY INTEGRATION ====================

// Shopify API Settings
define('SHOPIFY_API_VERSION', '2024-01');
define('SHOPIFY_API_TIMEOUT', 30);
define('SHOPIFY_RATE_LIMIT', 40); // Requests per second

// Webhook Settings
define('SHOPIFY_WEBHOOK_SECRET', 'your_webhook_secret_here');
define('SHOPIFY_APP_URL', 'https://your-app.myshopify.com');

// Webhook Topics (for future use)
define('WEBHOOK_TOPICS', [
    'products/create',
    'products/update',
    'products/delete',
    'orders/create',
    'orders/updated',
    'app/uninstalled'
]);

// ==================== SECURITY SETTINGS ====================

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_NAME', 'shopify_print_app');
define('SESSION_SECURE', false); // Set to true in production with HTTPS

// Encryption (for sensitive data)
define('ENCRYPTION_KEY', 'your_encryption_key_here'); // Change this!
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// CSRF Protection
define('CSRF_TOKEN_LENGTH', 32);
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// ==================== FILE PATHS ====================

/**
 * Application Directory Paths
 * 
 * All paths are relative to ROOT_PATH and use DIRECTORY_SEPARATOR for
 * cross-platform compatibility (Windows/Unix).
 * 
 * @see ROOT_PATH Base path constant defined above
 */

// Application Structure Paths
define('BACKEND_PATH', ROOT_PATH . 'backend' . DIRECTORY_SEPARATOR);
define('FRONTEND_PATH', ROOT_PATH . 'frontend' . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', BACKEND_PATH . 'config' . DIRECTORY_SEPARATOR);
define('MODELS_PATH', BACKEND_PATH . 'models' . DIRECTORY_SEPARATOR);
define('UTILS_PATH', BACKEND_PATH . 'utils' . DIRECTORY_SEPARATOR);
define('API_PATH', BACKEND_PATH . 'api' . DIRECTORY_SEPARATOR);

// System Paths (for file operations, logging, caching)
// Note: These paths should be outside the web root in production for security
define('UPLOAD_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('LOG_PATH', ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', ROOT_PATH . 'cache' . DIRECTORY_SEPARATOR);
// TEMP_PATH is already defined above in Design Settings section (line 138)

// ==================== LOGGING SETTINGS ====================

// Log Levels
define('LOG_LEVEL_ERROR', 'ERROR');
define('LOG_LEVEL_WARNING', 'WARNING');
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_DEBUG', 'DEBUG');

// Log Files
define('ERROR_LOG', LOG_PATH . 'error.log');
define('APP_LOG', LOG_PATH . 'application.log');
define('API_LOG', LOG_PATH . 'api.log');
define('UPLOAD_LOG', LOG_PATH . 'upload.log');

// ==================== CACHE SETTINGS ====================

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_DIR', CACHE_PATH);

// ==================== EMAIL SETTINGS ====================

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_SECURE', 'tls');

// Notification Emails
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('SUPPORT_EMAIL', 'support@yourdomain.com');
define('NO_REPLY_EMAIL', 'noreply@yourdomain.com');

// ==================== PERFORMANCE SETTINGS ====================

// Memory and Execution
define('MEMORY_LIMIT', '256M');
define('MAX_EXECUTION_TIME', 30);
define('MAX_INPUT_TIME', 60);

// Image Processing
define('IMAGE_MEMORY_LIMIT', '512M');
define('IMAGE_PROCESSING_TIMEOUT', 60);

// ==================== MISC SETTINGS ====================

// Timezone
date_default_timezone_set('America/New_York');

// Locale
setlocale(LC_ALL, 'en_US.UTF-8');

// Character Encoding
mb_internal_encoding('UTF-8');

// ==================== ERROR HANDLING ====================

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
}

// Memory and Time Limits
ini_set('memory_limit', MEMORY_LIMIT);
ini_set('max_execution_time', MAX_EXECUTION_TIME);
ini_set('max_input_time', MAX_INPUT_TIME);

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => '',
        'secure' => SESSION_SECURE,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_name(SESSION_NAME);
    
    // Only start session if not in CLI mode
    if (php_sapi_name() !== 'cli') {
        session_start();
    }
}

// ==================== ENVIRONMENT CHECKS ====================

// Check if required directories exist and are writable
function checkEnvironment() {
    $required_dirs = [UPLOAD_PATH, LOG_PATH, CACHE_PATH, TEMP_PATH];
    $writable_dirs = [DESIGN_PATH, PREVIEW_PATH, TEMP_PATH, LOG_PATH, CACHE_PATH];
    
    foreach ($required_dirs as $dir) {
        if (!file_exists($dir)) {
            if (DEBUG_MODE) {
                error_log("Missing directory: " . $dir);
            }
        }
    }
    
    foreach ($writable_dirs as $dir) {
        if (file_exists($dir) && !is_writable($dir)) {
            if (DEBUG_MODE) {
                error_log("Directory not writable: " . $dir);
            }
        }
    }
}

// Run environment check
if (DEBUG_MODE) {
    checkEnvironment();
}

?>