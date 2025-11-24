<?php
// test-connection.php

// Load dependencies
require_once 'config/bootstrap.php';  // Loads .env first
require_once 'config/config.php';     // Then config settings  
require_once 'config/database.php';   // Then database class

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "✅ Database connected successfully!";
    echo "<br>Database: " . $_ENV['DB_NAME'];
    echo "<br>Base URL: " . BASE_URL;
    echo "<br>Site Name: " . SITE_NAME;
    echo "<br>Root Path: " . ROOT_PATH;
    
    // Test if we can use the connection
    $stmt = $db->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<br>Connected to database: " . $result['db_name'];
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>