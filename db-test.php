<?php
// test_connection.php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'shopify';
$username = 'root';
$password = ''; // Try empty, 'root', or your password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connected successfully!',
        'credentials_used' => [
            'host' => $host,
            'database' => $dbname,
            'username' => $username,
            'password_length' => strlen($password)
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $e->getMessage(),
        'credentials_tried' => [
            'host' => $host,
            'database' => $dbname,
            'username' => $username,
            'password_length' => strlen($password)
        ]
    ]);
}
?>