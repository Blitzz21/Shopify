<?php
/**
 * Print Jobs API Endpoint
 * View and update print jobs (internal / admin use)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php'; 
require_once __DIR__ . '/../utils/print_file_generator.php';  

// Set CORS headers
setCorsHeaders();

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendError('Database connection failed', HTTP_INTERNAL_ERROR);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetPrintJobs($db);
            break;

        case 'POST':
            handleUpdatePrintJob($db);
            break;

        default:
            sendError('Method not allowed', HTTP_METHOD_NOT_ALLOWED);
    }
} catch (Exception $e) {
    logError('Print Jobs API Error: ' . $e->getMessage(), [
        'method' => $method,
        'trace'  => $e->getTraceAsString()
    ]);
    sendError('Internal server error', HTTP_INTERNAL_ERROR);
}

/**
 * GET /print-jobs.php
 * Optional query params:
 *   - status=queued|preparing|printing|printed|shipped|failed
 *   - limit=50
 */
function handleGetPrintJobs(PDO $db) {
    $status = isset($_GET['status']) ? sanitizeString($_GET['status']) : null;
    $limit  = isset($_GET['limit'])  ? max(1, intval($_GET['limit'])) : 50;

    $allowedStatuses = ['queued','preparing','printing','printed','shipped','failed'];

    $params = [];
    $where  = '';

    if ($status !== null) {
        if (!in_array($status, $allowedStatuses, true)) {
            sendError('Invalid status filter', HTTP_BAD_REQUEST, [
                'allowed_statuses' => $allowedStatuses
            ]);
        }
        $where = 'WHERE pj.status = :status';
        $params[':status'] = $status;
    }

    $sql = "
        SELECT
            pj.id                AS job_id,
            pj.status            AS job_status,
            pj.priority          AS job_priority,
            pj.print_file_path   AS job_print_file_path,
            pj.notes             AS job_notes,
            pj.created_at        AS job_created_at,
            pj.updated_at        AS job_updated_at,
            
            o.id                 AS order_id,
            o.shopify_order_id,
            o.shopify_order_number,
            o.order_status,
            o.fulfillment_status,
            
            oi.id                AS order_item_id,
            oi.quantity          AS order_item_quantity,
            oi.unit_price        AS order_item_unit_price,
            oi.total_price       AS order_item_total_price,
            
            p.id                 AS product_id,
            p.name               AS product_name,
            p.product_type       AS product_type,
            
            d.id                 AS design_id,
            d.original_filename  AS design_original_filename,
            d.preview_path       AS design_preview_path
        FROM print_jobs pj
        INNER JOIN order_items oi ON pj.order_item_id = oi.id
        INNER JOIN orders o       ON oi.order_id = o.id
        INNER JOIN designs d      ON pj.design_id = d.id
        INNER JOIN products p     ON oi.product_id = p.id
        $where
        ORDER BY pj.priority DESC, pj.created_at ASC
        LIMIT :limit
    ";

    $stmt = $db->prepare($sql);

    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();

    $jobs = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $jobs[] = [
            'id'           => intval($row['job_id']),
            'status'       => $row['job_status'],
            'priority'     => intval($row['job_priority']),
            'print_file'   => $row['job_print_file_path'],
            'print_file_url' => $row['job_print_file_path'] 
                ? str_replace('\\', '/', str_replace(ROOT_PATH, BASE_URL, $row['job_print_file_path']))
                : null,
             'print_file_download_url' => $row['job_print_file_path'] 
                ? BASE_URL . 'backend/api/download-print-file.php?job_id=' . intval($row['job_id'])
                : null,  
            'notes'        => $row['job_notes'],
            'created_at'   => $row['job_created_at'],
            'updated_at'   => $row['job_updated_at'],
            'order' => [
                'id'                 => intval($row['order_id']),
                'shopify_order_id'   => $row['shopify_order_id'],
                'shopify_order_num'  => $row['shopify_order_number'],
                'order_status'       => $row['order_status'],
                'fulfillment_status' => $row['fulfillment_status'],
            ],
            'order_item' => [
                'id'         => intval($row['order_item_id']),
                'quantity'   => intval($row['order_item_quantity']),
                'unit_price' => floatval($row['order_item_unit_price']),
                'total_price'=> floatval($row['order_item_total_price']),
            ],
            'product' => [
                'id'           => intval($row['product_id']),
                'name'         => $row['product_name'],
                'product_type' => $row['product_type'],
            ],
            'design' => [
                'id'               => intval($row['design_id']),
                'original_filename'=> $row['design_original_filename'],
                'preview_url'      => BASE_URL . ltrim($row['design_preview_path'], '/'),
            ],
        ];
    }

    sendSuccess([
        'count' => count($jobs),
        'jobs'  => $jobs
    ], 'Print jobs retrieved successfully');
}

/**
 * POST /print-jobs.php
 * JSON body:
 *   {
 *     "job_id": 123,
 *     "status": "printing",           // required
 *     "print_file_path": "/path.png", // optional
 *     "notes": "anything"             // optional
 *   }
 *
 * This simulates processing / updating a job (later this is where NinjaPOD call goes).
 */
function handleUpdatePrintJob(PDO $db) {
    $data = getJsonInput();

     // Handle generate_file action for Polaris
    if (isset($data['action']) && $data['action'] === 'generate_file') {
        if (empty($data['job_id'])) {
            sendError('Print job ID is required', HTTP_BAD_REQUEST);
        }

        $jobId = intval($data['job_id']);

        try {
            $generator = new PrintFileGenerator($db);
            $job = $generator->generateForJob($jobId);

            sendSuccess([
                'job' => $job
            ], 'Print-ready file generated successfully');
        } catch (Exception $e) {
            logError('Print file generation failed: ' . $e->getMessage());
            sendError('Failed to generate print file', HTTP_INTERNAL_ERROR, [
                'details' => $e->getMessage()
            ]);
        }
        exit; // Stop further processing
    }

    // Validate required fields
    $validation = validateRequired($data, ['job_id', 'status']);
    if (!$validation['valid']) {
        sendError('Missing required fields', HTTP_BAD_REQUEST, [
            'missing_fields' => $validation['missing']
        ]);
    }

    $jobId = intval($data['job_id']);
    $status = sanitizeString($data['status']);
    $printFilePath = isset($data['print_file_path']) ? sanitizeString($data['print_file_path']) : null;
    $notes  = isset($data['notes']) ? sanitizeString($data['notes']) : null;

    $allowedStatuses = ['queued','preparing','printing','printed','shipped','failed'];
    if (!in_array($status, $allowedStatuses, true)) {
        sendError('Invalid status value', HTTP_BAD_REQUEST, [
            'allowed_statuses' => $allowedStatuses
        ]);
    }

    // Make sure job exists
    $checkStmt = $db->prepare("SELECT * FROM print_jobs WHERE id = :id");
    $checkStmt->bindParam(':id', $jobId, PDO::PARAM_INT);
    $checkStmt->execute();
    $job = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        sendError('Print job not found', HTTP_NOT_FOUND);
    }

    // Build dynamic UPDATE query
    $updates = ["status = :status", "updated_at = CURRENT_TIMESTAMP"];
    $params = [':status' => $status, ':id' => $jobId];

    if ($printFilePath !== null) {
        $updates[] = "print_file_path = :print_file_path";
        $params[':print_file_path'] = $printFilePath;
    }
    
    if ($notes !== null) {
        $updates[] = "notes = CONCAT(COALESCE(notes, ''), ' ', :notes)";
        $params[':notes'] = $notes;
    }

    $sql = "UPDATE print_jobs SET " . implode(', ', $updates) . " WHERE id = :id";

    $stmt = $db->prepare($sql);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    if ($stmt->execute()) {
        logInfo('Print job updated', [
            'job_id' => $jobId,
            'status' => $status
        ]);

        // Return updated job
        $reloadStmt = $db->prepare("SELECT * FROM print_jobs WHERE id = :id");
        $reloadStmt->bindParam(':id', $jobId, PDO::PARAM_INT);
        $reloadStmt->execute();
        $updatedJob = $reloadStmt->fetch(PDO::FETCH_ASSOC);

        sendSuccess([
            'job' => $updatedJob
        ], 'Print job updated successfully');
    } else {
        sendError('Failed to update print job', HTTP_INTERNAL_ERROR);
    }
}
?>