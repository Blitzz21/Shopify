<?php
/**
 * Bootstrap File - Environment Variable Loader
 * 
 * Loads environment variables from .env file if it exists.
 * This is optional - if .env doesn't exist, the application will
 * fall back to constants defined in config.php.
 * 
 * @package    ShopifyPrintApp
 * @subpackage Config
 * @version    1.0.0
 * @since      1.0.0
 */

/**
 * Load environment variables from .env file
 * 
 * Parses a .env file and loads variables into $_ENV and putenv().
 * This function is designed to be non-fatal - if the .env file
 * doesn't exist, the application will use constants from config.php.
 * 
 * @param string $path Path to .env file
 * @return bool True if file was loaded, false if file doesn't exist
 * @throws Exception If file exists but cannot be read
 */
function loadEnvironmentVariables($path) {
    // Check if .env file exists
    if (!file_exists($path)) {
        // In development mode, this is acceptable - will use config.php constants
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Info: .env file not found at {$path}. Using config.php constants instead.");
        }
        return false;
    }
    
    // Check if file is readable
    if (!is_readable($path)) {
        throw new Exception("❌ .env file exists but is not readable: " . $path);
    }
    
    // Read and parse .env file
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if ($lines === false) {
        throw new Exception("❌ Failed to read .env file: " . $path);
    }
    
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Skip lines without '=' separator
        if (strpos($line, '=') === false) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Warning: Skipping invalid .env line " . ($lineNum + 1) . ": " . $line);
            }
            continue;
        }
        
        // Parse key=value pair
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove surrounding quotes if present
        $value = trim($value, '"\'');

        // Set environment variable
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
    
    return true;
}

// Attempt to load .env file (non-fatal if it doesn't exist)
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env';
loadEnvironmentVariables($envPath);
?>