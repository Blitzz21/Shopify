<?php
/**
 * Download Thumbnail Images
 * Secure endpoint for accessing thumbnails
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

// Set CORS headers
setCorsHeaders();

// Get design ID from query parameter
$designId = isset($_GET['design_id']) ? intval($_GET['design_id']) : 0;

if ($designId <= 0) {
    sendError('Valid design ID is required', HTTP_BAD_REQUEST);
}

try {
    // Initialize database
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendError('Database connection failed', HTTP_INTERNAL_ERROR);
    }

    // Get design with thumbnail path
    $query = "
        SELECT 
            id,
            original_filename,
            preview_path,
            mime_type
        FROM designs 
        WHERE id = :design_id
        LIMIT 1
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':design_id', $designId, PDO::PARAM_INT);
    $stmt->execute();

    $design = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$design) {
        sendError('Design not found', HTTP_NOT_FOUND);
    }

    if (empty($design['preview_path']) || !file_exists($design['preview_path'])) {
        sendError('Thumbnail not found for this design', HTTP_NOT_FOUND);
    }

    $filePath = $design['preview_path'];
    
    // Set appropriate content type for inline display
    $mimeType = $design['mime_type'] ?? 'image/png';
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="thumbnail_' . $design['original_filename'] . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
    
    // Read file and output to browser
    readfile($filePath);
    exit;

} catch (Exception $e) {
    logError('Thumbnail download failed: ' . $e->getMessage(), [
        'design_id' => $designId,
        'trace' => $e->getTraceAsString()
    ]);
    sendError('Failed to load thumbnail', HTTP_INTERNAL_ERROR);
}
?>