<?php
/**
 * Authentication Middleware
 * 
 * Provides functions to protect routes and check user authentication
 */

/**
 * Require authentication - redirects to login if not authenticated
 */
function requireAuth() {
    session_start();
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required. Please log in.'
        ]);
        exit;
    }
}

/**
 * Require admin role - redirects if not admin
 */
function requireAdmin() {
    session_start();
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required. Please log in.'
        ]);
        exit;
    }
    
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required. You do not have permission to access this resource.'
        ]);
        exit;
    }
}

/**
 * Get current user information
 */
function getCurrentUser() {
    session_start();
    
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    return null;
}

/**
 * Check if user is authenticated (without terminating)
 */
function isAuthenticated() {
    session_start();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user is admin (without terminating)
 */
function isAdmin() {
    session_start();
    return isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true && 
           $_SESSION['user_role'] === 'admin';
}
?>