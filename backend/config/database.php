<?php
/**
 * Database Connection Class
 * 
 * Handles database connections using PDO with support for both
 * environment variables (.env file) and configuration constants.
 * 
 * Priority order:
 * 1. Environment variables ($_ENV) - for production deployments
 * 2. Configuration constants - for development/local environments
 * 
 * @package    ShopifyPrintApp
 * @subpackage Config
 * @version    1.0.0
 * @since      1.0.0
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $charset;
    public $conn;

    /**
     * Database constructor
     * 
     * Initializes database connection parameters from environment variables
     * or configuration constants as fallback.
     * 
     * @throws Exception If required database configuration is missing
     */
    public function __construct() {
        // Try environment variables first (for production), fall back to constants (for development)
        $this->host = $this->getConfigValue('DB_HOST');
        $this->db_name = $this->getConfigValue('DB_NAME');
        $this->username = $this->getConfigValue('DB_USERNAME');
        $this->password = $this->getConfigValue('DB_PASSWORD', true); // Allow empty for local dev
        $this->port = $this->getConfigValue('DB_PORT', true) ?: '3306';
        $this->charset = $this->getConfigValue('DB_CHARSET', true) ?: 'utf8mb4';
    }

    /**
     * Get configuration value from environment or constants
     * 
     * This method follows the industry-standard pattern of preferring
     * environment variables for production deployments while allowing
     * constants for development environments.
     * 
     * @param string $key Configuration key (e.g., 'DB_HOST')
     * @param bool $allowEmpty Whether to allow empty values (default: false)
     * @return string Configuration value
     * @throws Exception If required configuration is missing
     */
    private function getConfigValue($key, $allowEmpty = false) {
        // Priority 1: Check environment variables (from .env file or system)
        $value = $_ENV[$key] ?? null;
        
        // Priority 2: Check if constant is defined (from config.php)
        if ($value === null && defined($key)) {
            $value = constant($key);
        }
        
        // Validate that we have a value (unless empty is allowed)
        if (!$allowEmpty && ($value === null || $value === '')) {
            throw new Exception(
                "Required database configuration '{$key}' is missing. " .
                "Please set it in .env file or config.php"
            );
        }
        
        return $value ?? '';
    }

    /**
     * Get database connection
     * 
     * Establishes a PDO connection to the database with optimized settings
     * for security and performance.
     * 
     * Connection options:
     * - ERRMODE_EXCEPTION: Throws exceptions on errors (better error handling)
     * - FETCH_ASSOC: Returns associative arrays by default
     * - EMULATE_PREPARES: Disabled for better security and performance
     * 
     * @return PDO Database connection object
     * @throws Exception If connection fails
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Build DSN (Data Source Name) for PDO connection
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $this->host,
                $this->port,
                $this->db_name,
                $this->charset
            );
            
            // PDO connection options following security best practices
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return associative arrays
                PDO::ATTR_EMULATE_PREPARES => false,             // Use native prepared statements
                PDO::ATTR_PERSISTENT => false,                   // Don't use persistent connections
                PDO::ATTR_STRINGIFY_FETCHES => false             // Return native types
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // Log the full error for debugging (but don't expose to users)
            $errorMessage = sprintf(
                "Database connection failed: %s (Host: %s, Database: %s)",
                $exception->getMessage(),
                $this->host,
                $this->db_name
            );
            
            error_log($errorMessage);
            
            // Throw a user-friendly error message (don't expose sensitive details)
            throw new Exception(
                "Database connection failed. Please check your configuration and try again later."
            );
        }
        
        return $this->conn;
    }
}
?>