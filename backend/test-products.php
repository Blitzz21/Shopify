<?php
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple test response
echo json_encode([
    'success' => true,
    'message' => 'API is working!',
    'data' => [
        'products' => [
            [
                'id' => 1,
                'name' => 'Test T-Shirt',
                'price' => 29.99,
                'type' => 'apparel'
            ],
            [
                'id' => 2, 
                'name' => 'Test Mug',
                'price' => 19.99,
                'type' => 'drinkware'
            ]
        ]
    ]
]);
?>