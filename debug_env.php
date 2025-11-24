<?php
// debug_env.php
require_once __DIR__ . '/backend/config/bootstrap.php';

header('Content-Type: application/json');

$debug_info = [];

// Check if .env was loaded
$debug_info['_ENV contents'] = $_ENV;

// Test Database class configuration
try {
    require_once __DIR__ . '/backend/config/database.php';
    $database = new Database();
    
    // Use reflection to check private properties
    $reflection = new ReflectionClass($database);
    $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
    
    foreach ($properties as $property) {
        $property->setAccessible(true);
        $value = $property->getValue($database);
        if ($property->getName() === 'password') {
            $value = $value === '' ? 'EMPTY' : 'SET';
        }
        $debug_info[$property->getName()] = $value;
    }
    
} catch (Exception $e) {
    $debug_info['error'] = $e->getMessage();
}

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>