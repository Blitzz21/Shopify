<?php
/**
 * Download Print-Ready Files
 * Secure endpoint for downloading Polaris print files
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

// Set CORS headers
setCorsHeaders();

// Initialize database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendError('Database connection failed', HTTP_INTERNAL_ERROR);
}

// Get job ID from query parameter
$jobId = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

if ($jobId <= 0) {
    sendError('Valid job ID is required', HTTP_BAD_REQUEST);
}

try {
    // Get print job with file path
    $query = "
        SELECT 
            pj.id,
            pj.print_file_path,
            pj.status,
            d.original_filename,
            o.shopify_order_number
        FROM print_jobs pj
        INNER JOIN designs d ON pj.design_id = d.id
        INNER JOIN order_items oi ON pj.order_item_id = oi.id
        INNER JOIN orders o ON oi.order_id = o.id
        WHERE pj.id = :job_id
        LIMIT 1
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
    $stmt->execute();

    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        sendError('Print job not found', HTTP_NOT_FOUND);
    }

    if (empty($job['print_file_path']) || !file_exists($job['print_file_path'])) {
        sendError('Print file not found for this job', HTTP_NOT_FOUND);
    }

    $filePath = $job['print_file_path'];
    $filename = sprintf(
        'order_%s_job_%d_%s',
        $job['shopify_order_number'],
        $job['id'],
        $job['original_filename']
    );

    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Clear output buffer
    flush();
    
    // Read file and output to browser
    readfile($filePath);
    exit;

} catch (Exception $e) {
    logError('Print file download failed: ' . $e->getMessage(), [
        'job_id' => $jobId,
        'trace' => $e->getTraceAsString()
    ]);
    sendError('Failed to download print file', HTTP_INTERNAL_ERROR);
}
?>